<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AvailabilityController extends Controller
{
    /**
     * Booking statuses that hold an astrologer's calendar slot. Cancelled
     * and no-show bookings free the slot back up.
     */
    private const BLOCKING_STATUSES = ['pending_payment', 'confirmed', 'completed'];

    private const MAX_RANGE_DAYS = 60;

    /**
     * Longest a single existing booking can run (Service.duration_minutes
     * is capped at 480 by ServiceController's admin validation), used to
     * widen the busy-booking query so a booking that starts before the
     * requested range but overlaps into it isn't missed.
     */
    private const MAX_BOOKING_DURATION_MINUTES = 480;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'astrologer_id' => ['required', 'integer', 'exists:astrologers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $service = Service::query()
            ->where('id', $validated['service_id'])
            ->where('astrologer_id', $validated['astrologer_id'])
            ->where('is_active', true)
            ->whereHas('astrologer', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();

        $from = CarbonImmutable::parse($validated['from'])->startOfDay();
        $to = CarbonImmutable::parse($validated['to'])->endOfDay();

        if ($from->diffInDays($to) > self::MAX_RANGE_DAYS) {
            $to = $from->addDays(self::MAX_RANGE_DAYS)->endOfDay();
        }

        $weeklySlots = AvailabilitySlot::query()
            ->where('astrologer_id', $validated['astrologer_id'])
            ->where('is_active', true)
            ->get()
            ->groupBy('weekday');

        $busyRanges = Booking::query()
            ->where('astrologer_id', $validated['astrologer_id'])
            ->whereIn('status', self::BLOCKING_STATUSES)
            // A booking starting before $from can still run into the
            // requested range, so look back far enough to catch it, then
            // let the per-slot overlap check in generateDaySlots() do the
            // precise filtering.
            ->where('slot', '>=', $from->subMinutes(self::MAX_BOOKING_DURATION_MINUTES))
            ->where('slot', '<', $to)
            ->with('service:id,duration_minutes')
            ->get()
            ->map(fn (Booking $booking) => [
                'start' => CarbonImmutable::parse($booking->slot),
                'end' => CarbonImmutable::parse($booking->slot)->addMinutes($booking->service->duration_minutes ?? 0),
            ]);

        $now = CarbonImmutable::now();
        $slots = [];

        for ($day = $from; $day->lessThanOrEqualTo($to); $day = $day->addDay()) {
            foreach ($weeklySlots->get($day->dayOfWeek, []) as $window) {
                $slots = array_merge($slots, $this->generateDaySlots($day, $window, $service->duration_minutes, $now, $busyRanges));
            }
        }

        return response()->json($slots);
    }

    /**
     * @param  Collection<int, array{start: CarbonImmutable, end: CarbonImmutable}>  $busyRanges
     * @return array<int, array{start: string, end: string, available: bool}>
     */
    private function generateDaySlots(
        CarbonImmutable $day,
        AvailabilitySlot $window,
        int $durationMinutes,
        CarbonImmutable $now,
        Collection $busyRanges
    ): array {
        $windowStart = $day->setTimeFromTimeString($window->start_time);
        $windowEnd = $day->setTimeFromTimeString($window->end_time);

        $slots = [];
        $cursor = $windowStart;

        while (true) {
            $slotStart = $cursor;
            $slotEnd = $cursor->addMinutes($durationMinutes);

            if ($slotEnd->greaterThan($windowEnd)) {
                break;
            }

            $isFuture = $slotStart->greaterThan($now);
            $isFree = $busyRanges->every(
                fn (array $busy) => $slotEnd->lessThanOrEqualTo($busy['start']) || $slotStart->greaterThanOrEqualTo($busy['end'])
            );

            if ($isFuture && $isFree) {
                $slots[] = [
                    'start' => $slotStart->toIso8601String(),
                    'end' => $slotEnd->toIso8601String(),
                    'available' => true,
                ];
            }

            $cursor = $slotEnd;
        }

        return $slots;
    }
}

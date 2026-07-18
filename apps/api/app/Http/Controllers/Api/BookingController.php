<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\BirthChart;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /** @see AvailabilityController::BLOCKING_STATUSES */
    private const BLOCKING_STATUSES = ['pending_payment', 'confirmed', 'completed'];

    private const MAX_BOOKING_DURATION_MINUTES = 480;

    public function store(Request $request): JsonResponse
    {
        $authClient = $request->user('sanctum');

        $rules = [
            'astrologer_id' => ['required', 'integer', 'exists:astrologers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'slot' => ['required', 'date', 'after:now'],
            'birth_details' => ['nullable', 'array'],
            'birth_chart_id' => ['nullable', 'integer', 'exists:birth_charts,id'],
            'guest' => ['sometimes', 'boolean'],
        ];

        if (! $authClient) {
            $rules['client.name'] = ['required', 'string', 'max:255'];
            $rules['client.email'] = ['required', 'email', 'max:255'];
            $rules['client.phone'] = ['required', 'string', 'max:50'];
        }

        $validated = $request->validate($rules);

        $service = Service::query()
            ->where('id', $validated['service_id'])
            ->where('astrologer_id', $validated['astrologer_id'])
            ->where('is_active', true)
            ->whereHas('astrologer', fn ($query) => $query->where('is_active', true))
            ->first();

        if (! $service) {
            throw ValidationException::withMessages(['service_id' => 'This service is not available.']);
        }

        $client = $authClient ?? Client::firstOrCreate(
            ['email' => $validated['client']['email']],
            ['name' => $validated['client']['name'], 'phone' => $validated['client']['phone'] ?? null]
        );

        if (! empty($validated['birth_chart_id'])) {
            $chart = BirthChart::find($validated['birth_chart_id']);

            if (! $chart || ($chart->client_id && $chart->client_id !== $client->id)) {
                throw ValidationException::withMessages(['birth_chart_id' => 'This birth chart is not available.']);
            }
        }

        if ($this->slotIsTaken($validated['astrologer_id'], $validated['slot'], $service->duration_minutes)) {
            throw ValidationException::withMessages(['slot' => 'This slot is no longer available.']);
        }

        $booking = Booking::create([
            'astrologer_id' => $validated['astrologer_id'],
            'service_id' => $validated['service_id'],
            'client_id' => $client->id,
            'slot' => $validated['slot'],
            'status' => 'pending_payment',
            'birth_details' => $validated['birth_details'] ?? null,
            'birth_chart_id' => $validated['birth_chart_id'] ?? null,
        ]);

        return (new BookingResource($booking->load('client')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $booking = Booking::query()->with('client')->findOrFail($id);
        $authClient = $request->user('sanctum');

        $ownsAsClient = $authClient && $booking->client_id === $authClient->id;
        $ownsAsGuest = $request->query('token') && hash_equals($booking->guest_token ?? '', (string) $request->query('token'));

        if (! $ownsAsClient && ! $ownsAsGuest) {
            abort(404);
        }

        return (new BookingResource($booking))->response();
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $bookings = Booking::query()
            ->where('client_id', $request->user('sanctum')->id)
            ->with('client')
            ->latest('slot')
            ->get();

        return BookingResource::collection($bookings);
    }

    /**
     * Mirrors AvailabilityController's overlap check: a booking starting
     * before $slot can still run into it, so widen the lookback by the
     * longest a booking can run.
     */
    private function slotIsTaken(int $astrologerId, string $slot, int $durationMinutes): bool
    {
        $start = CarbonImmutable::parse($slot);
        $end = $start->addMinutes($durationMinutes);

        return Booking::query()
            ->where('astrologer_id', $astrologerId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('slot', '>=', $start->subMinutes(self::MAX_BOOKING_DURATION_MINUTES))
            ->where('slot', '<', $end)
            ->with('service:id,duration_minutes')
            ->get()
            ->contains(function (Booking $existing) use ($start, $end) {
                $existingStart = CarbonImmutable::parse($existing->slot);
                $existingEnd = $existingStart->addMinutes($existing->service->duration_minutes ?? 0);

                return $start->lessThan($existingEnd) && $end->greaterThan($existingStart);
            });
    }
}

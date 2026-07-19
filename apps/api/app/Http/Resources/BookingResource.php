<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Booking */
class BookingResource extends JsonResource
{
    /**
     * @param  Booking  $resource
     */
    public function __construct($resource, private readonly ?Setting $setting = null)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Accepting an already-resolved Setting avoids one query per row
        // when serializing a collection (see BookingController::mine()).
        $setting = $this->setting ?? Setting::current();

        return [
            'id' => $this->id,
            'astrologer_id' => $this->astrologer_id,
            'service_id' => $this->service_id,
            'slot' => $this->slot?->toIso8601String(),
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'guest_token' => $this->guest_token,
            // Per docs/API_CONTRACT.md: "Returns booking with ... the UPI
            // ID/QR (from Settings) for the confirmation screen."
            'upi_id' => $setting->upi_id,
            'upi_qr_url' => $setting->upi_qr_path ? Storage::disk('public')->url($setting->upi_qr_path) : null,
            'client' => new ClientResource($this->whenLoaded('client')),
            'birth_details' => $this->birth_details,
            'birth_chart_id' => $this->birth_chart_id,
        ];
    }
}

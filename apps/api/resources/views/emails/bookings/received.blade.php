<x-mail::message>
# Thanks for your booking, {{ $booking->client->name }}

We've received your booking request. It's held as **pending payment** until we verify your payment.

**Reference:** {{ $booking->reference_number }}
**Service:** {{ $booking->service->name }}
**Astrologer:** {{ $booking->astrologer->name }}
**When:** {{ $booking->slot->format('l, d M Y \a\t g:i A') }}
**Amount:** ₹{{ number_format((float) $booking->service->price_inr, 2) }}

@if ($setting->upi_id)
## Complete your payment
Pay using the UPI ID below, then we'll confirm your booking once the payment is verified.

<x-mail::panel>
UPI ID: **{{ $setting->upi_id }}**
</x-mail::panel>

Please include your reference number **{{ $booking->reference_number }}** in the payment note where possible.
@else
We'll be in touch shortly with payment details to confirm your booking.
@endif

Thanks,<br>
{{ $setting->site_name }}
</x-mail::message>

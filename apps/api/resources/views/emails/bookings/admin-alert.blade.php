<x-mail::message>
# New booking awaiting payment

A new booking has been placed and is pending payment verification.

**Reference:** {{ $booking->reference_number }}
**Service:** {{ $booking->service->name }}
**Astrologer:** {{ $booking->astrologer->name }}
**When:** {{ $booking->slot->format('l, d M Y \a\t g:i A') }}
**Amount:** ₹{{ number_format((float) $booking->service->price_inr, 2) }}

## Client
**Name:** {{ $booking->client->name }}
**Email:** {{ $booking->client->email }}
**Phone:** {{ $booking->client->phone }}

Confirm the booking in the admin panel once you've verified the UPI payment.

{{ $setting->site_name }}
</x-mail::message>

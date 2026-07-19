<x-mail::message>
# Your booking is confirmed, {{ $booking->client->name }}

Your payment has been verified and your consultation is now confirmed.

**Reference:** {{ $booking->reference_number }}
**Service:** {{ $booking->service->name }}
**Astrologer:** {{ $booking->astrologer->name }}
**When:** {{ $booking->slot->format('l, d M Y \a\t g:i A') }}

@if ($booking->admin_notes)
## Meeting details
{{ $booking->admin_notes }}
@else
Our team will share the meeting details (link or in-person/phone information) with you ahead of your appointment.
@endif

We look forward to speaking with you.

Thanks,<br>
{{ $setting->site_name }}
</x-mail::message>

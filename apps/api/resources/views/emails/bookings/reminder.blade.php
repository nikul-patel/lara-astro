<x-mail::message>
# Your consultation is coming up, {{ $booking->client->name }}

This is a friendly reminder about your upcoming consultation.

**Reference:** {{ $booking->reference_number }}
**Service:** {{ $booking->service->name }}
**Astrologer:** {{ $booking->astrologer->name }}
**When:** {{ $booking->slot->format('l, d M Y \a\t g:i A') }}

@if ($booking->admin_notes)
## Meeting details
{{ $booking->admin_notes }}
@endif

If you need to reschedule, please reply to this email or contact us as soon as possible.

Thanks,<br>
{{ $setting->site_name }}
</x-mail::message>

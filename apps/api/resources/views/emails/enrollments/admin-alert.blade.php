<x-mail::message>
# New enrollment awaiting payment

A new course enrollment has been placed and is pending payment verification.

**Reference:** {{ $enrollment->reference_number }}
**Course:** {{ $enrollment->course->title }}
**Amount:** ₹{{ number_format((float) $enrollment->course->price_inr, 2) }}

## Client
**Name:** {{ $enrollment->client->name }}
**Email:** {{ $enrollment->client->email }}
**Phone:** {{ $enrollment->client->phone }}

Confirm the enrollment in the admin panel once you've verified the UPI payment.

{{ $setting->site_name }}
</x-mail::message>

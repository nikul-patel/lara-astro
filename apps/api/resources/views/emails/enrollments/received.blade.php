<x-mail::message>
# Thanks for enrolling, {{ $enrollment->client->name }}

We've received your enrollment. It's held as **pending payment** until we verify your payment.

**Reference:** {{ $enrollment->reference_number }}
**Course:** {{ $enrollment->course->title }}
**Amount:** ₹{{ number_format((float) $enrollment->course->price_inr, 2) }}

@if ($setting->upi_id)
## Complete your payment
Pay using the UPI ID below, then we'll unlock your course once the payment is verified.

<x-mail::panel>
UPI ID: **{{ $setting->upi_id }}**
</x-mail::panel>

Please include your reference number **{{ $enrollment->reference_number }}** in the payment note where possible.
@else
We'll be in touch shortly with payment details to confirm your enrollment.
@endif

Thanks,<br>
{{ $setting->site_name }}
</x-mail::message>

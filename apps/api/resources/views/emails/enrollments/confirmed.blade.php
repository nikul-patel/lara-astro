<x-mail::message>
# You're enrolled, {{ $enrollment->client->name }}

Your payment has been verified and your enrollment is now confirmed.

**Reference:** {{ $enrollment->reference_number }}
**Course:** {{ $enrollment->course->title }}

Your course content is now unlocked. Sign in to your account to start learning.

@if ($enrollment->course->relationLoaded('liveSessions') && $enrollment->course->liveSessions->isNotEmpty())
## Live sessions
You'll find the meeting links for your scheduled live sessions in your account.
@endif

Thanks,<br>
{{ $setting->site_name }}
</x-mail::message>

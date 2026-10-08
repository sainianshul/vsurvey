<x-mail::message>
# Profile Submitted Successfully

Hello **{{ $user->name }}**,

Thank you for completing your onboarding with VcanCares. We have successfully received your profile and documents.

Our administrative team is currently reviewing your application. This process usually takes 24-48 hours.

<x-mail::panel>
**What happens next?**
Once your profile is approved, you will receive a notification and can immediately start receiving care requests and placing bids in your area.
</x-mail::panel>

We appreciate your patience.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>

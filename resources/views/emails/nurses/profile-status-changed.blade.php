<x-mail::message>
# Profile Update: {{ ucfirst($status) }}

Hello **{{ $user->name }}**,

@if($status === 'approved')
**Congratulations!** Your VcanCares nurse profile has been successfully approved.

You are now an active member of our network. You can start exploring new care requests, place bids, and manage your bookings directly from your dashboard.

<x-mail::button :url="url('/dashboard')">
Go to Dashboard
</x-mail::button>

@else
We have reviewed your VcanCares nurse profile, but unfortunately, we could not approve it at this time.

<x-mail::panel>
**Reason for Rejection:**<br>
{{ $reason ?? 'Your profile did not meet our current requirements.' }}
</x-mail::panel>

Please review the feedback, update your profile or documents accordingly, and resubmit your application.

<x-mail::button :url="url('/profile/edit')">
Update Profile
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>

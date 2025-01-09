@component('mail::message')
Dear user,

You are receiving this email because you have verified this email
address previously on our system. Please click **Verify Email**
button below to verify your email address again in this new account.

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

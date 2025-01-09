@component('mail::message', ['account' => $account ?? null, 'affiliate' => null])
**Hello** {{ $user->first_name }} {{ $user->last_name }} !
<br><br>
Thanks for signing up to become our affiliate member.
<br><br>
**We really appreciate**
<br><br>
Please click the below link to verify your email
and activate your account!

@component('mail::button',['url' => $url])
Verify Email
@endcomponent


Thanks,<br>
{{ $account->store_name ?? $account->company }}
<br><br>
<hr>
If you’re having trouble clicking the "Verify Email" button, copy and paste the URL below into your web browser:
{{ $url }}
@endcomponent

@component('mail::message')
**Hello** {{$email_data['name']}} !
<br><br>
Thanks for signing up to become our affiliate partner.
<br><br>
**We really appreciate**
<br><br>
Please click the below link to verify your email
and activate your account!

@component('mail::button',['url'=>'https://'.$email_data['subdomain'].$email_data['url']])
Verify Email
@endcomponent


Thanks,<br>
{{config('app.name')}}
<br><br>
<hr>
If you’re having trouble clicking the "Verify Email" button, copy and paste the URL below into your web browser:
https://{{$email_data['subdomain']}}{{$email_data['url']}}
@endcomponent

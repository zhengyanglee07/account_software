@component('mail::message',['company_logo' => $sellerInfo->company_logo,'sellerCompanyName' => $sellerInfo->company])
**Hello** {{$ecommerceAccount->fname}}!
<br><br>
You are receiving this email because we received a password reset request for your account.
<br><br>
@component('mail::button',['url' => $url])
Reset Password
@endcomponent
<br><br>
This password reset link will expire in 60 minutes.
<br><br>
If you did not request a password reset, no further action is required.


<br><br><br>
Thanks,<br>
{{$sellerInfo->company}}
<br><br>
<hr>
If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
<a>{{$url}}</a>
@component('mail::footer')
@endcomponent
@endcomponent
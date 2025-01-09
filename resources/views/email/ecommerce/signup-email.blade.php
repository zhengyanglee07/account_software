@component('mail::message',[
    'company_logo' => $sellerInfo->company_logo,
    'sellerCompanyName' => $sellerInfo->company,
    'account'=>$sellerInfo,
    'affiliate' => null,
])
**Hello** {{$ecommerceAccount->full_name}}!
<br><br>
Thanks for signing up to become our customer.
<br><br>
**We really appreciate**
<br><br>
Please click the below link to verify your email {{$ecommerceAccount->email}} and activate your account!

@component('mail::button',['url'=>$sellerInfo->url])
Verify Email
@endcomponent


Thanks,<br>
{{$sellerInfo->company}}
<br><br>
<hr>
If you’re having trouble clicking the "Verify Email" button, copy and paste the URL below into your web browser:
<a>{{$sellerInfo->url}}</a>
@component('mail::footer')
@endcomponent
@endcomponent

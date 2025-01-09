@component('mail::layout')
@slot('header')
<tr>
<td class="header">
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@include('affiliate.member.company-logo', ['account' => $account])
</div>
</td>
</tr>
@endslot
**Hello** {{ $name }}!
<br><br>
<p>
We are happy you become one of our affiliate members!
</p>
<br>
<p>
Earn commissions now by logging in to your affiliate account and share your link with your friends and followers.
</p>
<br>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliates/login'])
Log in Now
@endcomponent
</div>

Thank, <br>
{{$account->company}}
<br><br>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ $account->company }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent

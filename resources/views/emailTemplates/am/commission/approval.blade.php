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
We are {{$status ==='approved' ? 'happy' : 'sorry'}} to inform you that your pending commission of {{$amount}} has been {{$status}}.
</p>
<br>
<p>
Continue to share your unique affiliate link for more commissions.
</p>
<br>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliates/login'])
Log In
@endcomponent

</div>

Regards, <br>
{{$account->store_name}}
<br><br>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ $account->company }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent

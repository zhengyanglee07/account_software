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
**Hi** {{ $name }},
<br><br>
<p>
We are {{$status === 'paid' ? 'thrilled': 'sorry'}} to inform you that your commission payout request for {{$amount}} has been {{$status === 'paid' ? 'approved and paid' : $status}} by the admin.
</p>
<br>
<p>
Continue to share your affiliate link for more commissions.
<br>
</p>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliates/login'])
Log In
@endcomponent
</div>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ $account->company }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent

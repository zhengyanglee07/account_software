@component('mail::layout')
@slot('header')
<tr>
<td class="header">
</td>
</tr>
@endslot
**Hi** {{ $name }},
<br><br>
<p>
One of your affiliate members, {{$affiliateEmail}}, requests for commission payout for the amount of {{$amount}}.
</p>
<br>
<p>
Click the button below to view the details of his payout request.
</p>
<br>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliate/members/payouts'])
View payout request
@endcomponent
</div>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} . @lang('Hypershapes. All rights reserved.')
@endcomponent
@endslot
@endcomponent

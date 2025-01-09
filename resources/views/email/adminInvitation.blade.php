@component('mail::layout')
@slot('header')
<tr>
<td class="header">
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
    <img
    	src="{{asset($account->company_logo === null ? "/images/hypershapes-favicon.png" : $account->company_logo)}}"
       	alt="hypershapes-logo"
       	width="5%"
    />
	<span style="margin-left: 20px;">{{ $account->company }}</span>
</div>
</td>
</tr>
@endslot
**Hello** {{ $invite['email'] }}!
<br><br>
We would like to invite you to be our Administrator!
<br><br>
Please click the button below to accept the Administrator role!

@component('mail::button',['url'=>$url])
Accept Invitation
@endcomponent


Thank you.<br>
<br><br>
<hr>
If you’re having trouble clicking the "Accept Invitation" button, copy and paste the URL below into your web browser:
<br>
<a href="{{ $url }}" style="overflow-wrap: anywhere;">{{ $url }}</a>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent
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
Someone has successfully signed up as affiliate member through your invitation link.
</p>
<br>
<p>
Currently you have {{$count}} sublines.
</p>
<p>
Login to your affiliate dashboard for more details.
</p>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliates/login'])
Log in dashboard
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

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
Someone has purchased something through your invitation link. You could earn the commission through this purchase.
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

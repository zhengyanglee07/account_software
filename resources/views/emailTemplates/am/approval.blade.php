@component('mail::layout')
@slot('header')
<tr>
<td class="header">
</td>
</tr>
@endslot
**Hi** {{ $name }}!
<br><br>
<p>
{{$affiliateName}} has signed up to become your affiliate member.
</p>
<br>
<p>
You can review and approve his application by clicking on the button below.
</p>
<br>
<div style="display: flex; justify-content: center; align-items: center; width: 100%;">
@component('mail::button',['url'=>'https://'.$domain.'/affiliate/members'])
Review Application
@endcomponent

</div>

Regards, <br>
Hypershapes
<br><br>

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} . @lang('Hypershapes. All rights reserved.')
@endcomponent
@endslot
@endcomponent

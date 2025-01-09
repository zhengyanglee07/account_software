<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
<tr>
<td align="center" style="font-size:0px;word-break:break-word;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
<tbody>
<tr>
<td style="width:168px;">
@if(($account->has_email_affiliate_badge ?? false))
<a href="{{config('app.url')}}/register{{$affiliate ? ('?affiliate_id='.$affiliate->affiliate_unique_id) : ''}}" target="_blank">
<img alt="hyper-logo" height="auto" src="https://media.hypershapes.com/images/email-affiliate-badge.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:16px;" width="168" />
</a>
@endif
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</td>
</tr>
</table>
</td>
</tr>

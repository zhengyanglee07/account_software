<tr>
<td class="header">
<a>
    @if(!$account)
        <img
            src="{{asset($company_logo)}}"
            alt="company-logo"
            width="250"
            style="width:250px; height:auto; max-width: 250px;-ms-interpolation-mode: bicubic;vertical-align: middle;border: 0;line-height: 100%;outline: none;text-decoration: none;"
        />
    @else
        @include('affiliate.member.company-logo', ['account' => $account])
    @endif
</a>
</td>
</tr>

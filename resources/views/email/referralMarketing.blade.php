@component('mail::message', [
    'company_logo' => $sellerInfo->company_logo,
    'sellerCompanyName' => $sellerInfo->company,
    'account'=> $sellerInfo,
    'affiliate'=> $affiliate,
    ])
{!! $content !!}
@endcomponent

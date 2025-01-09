@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', [
    'url' => config('app.url'),
    'company_logo'=> $company_logo ?? "/images/hypershapes-logo.png",
    'account' => $account ?? null
])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer',[
    'url' => config('app.url'),
    'company_logo'=> $company_logo ?? "/images/hypershapes-logo.png",
    'account' => $account ?? null,
    'affiliate' => $affiliate ?? null
])
© {{ date('Y') }} {{ isset($account) ? $account->company : config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent

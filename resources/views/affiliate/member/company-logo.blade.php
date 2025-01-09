@if ($account->company_logo ?? null)
    <img
        src="{{ $account->company_logo }}"
        class="affOnboarding__logo"
        alt="company-logo"
        height="64"
        style="width:auto; height:64px; max-width: 250px;-ms-interpolation-mode: bicubic;vertical-align: middle;border: 0;line-height: 100%;outline: none;text-decoration: none;"
    />
@else
    <h1>{{ $account->company }}</h1>
@endif
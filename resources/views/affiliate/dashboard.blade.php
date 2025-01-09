@extends('affiliate.layout.affiliateNav')


@section('style')

    <link href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
    rel="stylesheet"/>
    <link href="{{ asset('assets\dist\snow.css') }}"
    rel="stylesheet"/>

@endsection

@section('content')


<affiliate-dashboard
    :affiliate_user="{{$affiliate_user}}"
    :affiliate_log="{{json_encode($affiliate_log)}}"
    :affiliate_detail="{{$affiliate_detail}}"
    :referral_history="{{json_encode($affiliateCommissionLogs)}}"
    :affiliate_log_all = "{{json_encode($affiliateReferrals)}}"
    environment = "{{$environment}}"
    domain_url = "{{config('app.url')}}"
    :detail-cards ="{{json_encode($detailCards)}}">

</affiliate-dashboard>




@endsection
    {{-- <head> --}}
        {{-- <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap"
        rel="stylesheet">
        <link href="{{ asset('assets/vendors/custom/vendors/flaticon/flaticon.css') }}"
            rel="stylesheet"/>
        <link href="{{ asset('assets/vendors/custom/vendors/flaticon2/flaticon.css') }}"
            rel="stylesheet"/>
        <link href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
            rel="stylesheet"/>
        <!-- For people page filter custom field dropdown -->
        <link href="{{ asset('assets/css/demo9/bootstrap-select.css') }}"
            rel="stylesheet"/>

              <!-- For people page filter custom field dropdown -->
    <link href="{{ asset('assets/css/demo9/bootstrap-select.css') }}"
    rel="stylesheet"/> --}}
<!-- Global theme styles
    # very important for navbar, CRM, dashboard, people and account settings -->

        {{-- @yield('style')

        @stack('styles')
    </head>
    <body>

        <h1> Hi this is affiliate dashboard</h1>
        <button class="btn btn-primary">Submit</button>
        <a href="{{ route('affiliate.logout') }}"
        class="kt-menu__link log-out"
        style="padding: 11px 30px"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
     <span class="kt-menu__link-text"
           style="font-weight: normal;color : black">
         <i class="fas fa-sign-out-alt"
            style="font-size: 1rem;"></i>
         &ensp;&nbsp; Log Out
     </span>
     </a>
        <form id="logout-form" action="{{ route('affiliate.logout') }}"
            method="POST"
            style="display: none;">
            @csrf
            </form>



        {{-- app.js bundle with Vue --}}
        {{-- <script src="{{ mix('js/manifest.js') }}"></script>
        <script src="{{ mix('js/vendor.js') }}"></script>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>

 --}}

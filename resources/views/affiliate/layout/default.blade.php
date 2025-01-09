<!DOCTYPE html>
<html lang="en">
	<!-- begin::Head -->
	<head>
		<base href="../../../">
		<meta charset="utf-8" />
		<title>Hypershape Affiliate</title>
		<meta name="description" content="Login page example">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <link
            href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
            rel="stylesheet"/>
        <link href="{{ asset('assets/vendors/custom/vendors/flaticon/flaticon.css') }}"
            rel="stylesheet"/>
        <link href="{{ asset('assets/vendors/custom/vendors/flaticon2/flaticon.css') }}"
            rel="stylesheet"/>
        {{-- demo 1 styling --}}
        <link href="{{asset('assets\css\demo1\style.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets\css\demo1\plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
        <!--begin::Page Custom Styles(used by this page) -->
        <link href="{{asset('\assets\css\demo1\login-5.css')}}" rel="stylesheet" type="text/css" />

        <!--end::Page Custom Styles -->
        {{-- @endsection --}}
		<!--begin::Fonts -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Roboto:300,400,500,600,700">
		<!--end::Fonts -->
        <link rel="shortcut icon" href="{{ asset('images/hypershapes-favicon.png') }}"/>
    </head>
    <style>
        .form-wrapper{
            overflow-x:hidden;
            overflow-y:auto;
            height:380px;

        }
        .form-wrapper::-webkit-scrollbar{
            display:none;
        }


        @media (max-width: 768px){
            .kt-login__right, .kt-login__left{
                background-color: white !important;
                padding: 0 32px !important;
            }
            .kt-login__title{
                font-size: 18px !important;
                font-weight: 600 !important;
                font-family: "Inter", sans-serif !important;
            }
            .kt-login__desc{
                font-size: 12px !important;
               font-weight: 400 !important;
                font-family: "Roboto", sans-serif !important;
                 margin-bottom: 0;
            }

            .kt-login__right{
                padding-top: 50px !important;
            }

            .kt-login__actions .primary-white-button,.kt-login__actions .primary-square-button{
                font-size: 12px !important;
            }

            .row .col-md-6{
                padding: 5px 0 !important;
            }
        }
    </style>

	<!-- end::Head -->

<!-- begin::Body -->
<body class="kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header-mobile--fixed kt-subheader--enabled kt-subheader--fixed kt-subheader--solid kt-aside--enabled kt-aside--fixed kt-page--loading">
    <!-- begin:: Page -->
    <div class="kt-grid kt-grid--ver kt-grid--root">
        <div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v5 kt-login--signin" id="kt_login">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--desktop kt-grid--ver-desktop kt-grid--hor-tablet-and-mobile" style="background-image: url(assets/media/bg/bg-3.jpg);">
                <div class="kt-login__left" style="padding: 0 64px">
                    <div class="kt-login__wrapper w-100">
                        <div class="kt-login__content">
                            <a class="kt-login__logo" href="/">
                                <img src="{{asset("/images/hypershapes-logo.png")}}" style="width: 99%;">
                            </a>
                            <h3 class="kt-login__title h-two" style="color: black">BECOME OUR AFFILIATE PARTNER</h3>
                            <span class="kt-login__desc">
                                Earn up to 30% of commission when there is a new Hypershapes user subscribe from your affiliate link.
                            </span>
                            @if(Auth::guard('affiliateusers')->user())
                            <div class="kt-login__actions">
                                <form class="d-inline" method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" id="kt_login_signin" class="primary-white-button">Back to login</button>
                                </form>
                            </div>
                            @else
                            <div class="kt-login__actions">
                                <button type="button" id="kt_login_signin" class="primary-white-button">Back to login</button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="kt-login__right" style="padding: 0 64px;background-color: rgb(243 243 243)">
                    <div class="kt-login__wrapper w-100">
                        <div id="root">
                        @yield('content')
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- end:: Page -->
{{-- app.js bundle with Vue --}}
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>
<!-- begin::Global Config(global config for global JS sciprts) -->
<script type="text/javascript">
    var KTAppOptions = {
        "colors": {
            "state": {
                "brand": "#5d78ff",
                "dark": "#282a3c",
                "light": "#ffffff",
                "primary": "#5867dd",
                "success": "#34bfa3",
                "info": "#36a3f7",
                "warning": "#ffb822",
                "danger": "#fd3995"
            },
            "base": {
                "label": [
                    "#c5cbe3",
                    "#a1a8c3",
                    "#3d4465",
                    "#3e4466"
                ],
                "shape": [
                    "#f0f3ff",
                    "#d9dffa",
                    "#afb4d4",
                    "#646c9a"
                ]
            }
        }
    };

    document.getElementById("kt_login_signin").onclick = function(){
        window.location.href="/login";
    };
</script>
<!-- end::Global Config -->
<!--begin::Global Theme Bundle(used by all pages) -->
{{-- <script src="assets/plugins/global/plugins.bundle.js" type="text/javascript"></script> --}}
<script src="{{asset('assets\css\demo1\plugins.bundle.js')}}" type="text/javascript"></script>
<script src="{{asset('assets\js\demo1\scripts.bundle.js')}}" type="text/javascript"></script>

<!--end::Page Scripts -->
</body>
<!-- end::Body -->
</html>

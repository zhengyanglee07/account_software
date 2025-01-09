<!DOCTYPE html>

<html lang="en">

	<!-- begin::Head -->
	<head>
		<base href="../../../">
		<meta charset="utf-8" />
		<title>Hypershape Affiliate</title>
		<meta name="description" content="Login page example">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


        {{-- @section('style') --}}

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

		<!--begin::Page Custom Styles(used by this page) -->
		{{-- <link href="assets/css/pages/login/login-6.css" rel="stylesheet" type="text/css" /> --}}

		<!--end::Page Custom Styles -->


        <!--begin::Layout Skins(used by all pages) -->
        <link href="{{asset('assets\css\demo1\skins\header\base\light.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets\css\demo1\skins\header\menu\light.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets\css\demo1\skins\brand\dark.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets\css\demo1\skins\aside\dark.css')}}" rel="stylesheet" type="text/css" />

        <link rel="shortcut icon" href="{{ asset('images/hypershapes-favicon.png') }}"/>
		<!--end::Layout Skins -->
		{{-- <link rel="shortcut icon" href="assets/media/logos/favicon.ico" /> --}}
    </head>
    <style>
        .form-wrapper{
            overflow-x:hidden;
            overflow-y:auto;
            height:260px;

        }

        .form-wrapper::-webkit-scrollbar{
            display:none;
        }

        .kt-login.kt-login--v5 .kt-login__right .kt-login__wrapper .kt-login__form .form-control{
            padding: 0.65rem 1rem;

        }
        .kt-login.kt-login--v5 .kt-login__right .kt-login__wrapper .kt-login__form {
            margin-top: 3rem;
        }
        body{
             color: black;
        }

        .kt-login__title {
            color: black !important;
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

            .primary-white-button, .primary-square-button{
                font-size: 12px !important;
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
                            <h3 class="kt-login__title h-two">BECOME OUR AFFILIATE PARTNER</h3>
                            <span class="kt-login__desc p-two">
                                Earn up to 30% of commission when there is a new Hypershapes user subscribe from your affiliate link.
                            </span>
                            <div class="kt-login__actions" style="margin: 2rem 0 3rem 0">
                                <button type="button" id="kt_login_signin"  class="primary-white-button">Back to login</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-login__right" style="padding: 0 64px;background-color: rgb(243 243 243)">
                    <div class="kt-login__wrapper w-100">
                       <div class="kt-login__forgot">
                            <div class="kt-login__head">
                                <h2 class="kt-login__title h-two mb-5">Reset Password</h2>
                            </div>
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                            @endif
                            <div class="kt-login__form">
                                <form class="kt-form" method="POST" action="{{ route('affiliate.password.email') }}">
                                    @csrf
                                    <div class="form-group">
                                        <h5 class="h-five">Email Address</h5>
                                        <input class="form-control"  type="email" name="email" id="kt_email" autocomplete="email" value="{{ old('email') }}" required autofocus style="border-radius: 2px; border: 1px solid grey !important; height: 40px">
                                    </div>
                                    @error('email')
                                    <div style="width: 100%; margin-top: 5px">
                                        <span class="error-message error-font-size" role="alert">{{ $message }}</span>
                                    </div>
                                    @enderror

                                    <div class="kt-login__actions">
                                        <button id="kt_login_forgot_submit" type="submit" class="primary-square-button">Reset Password</button>
                                        {{-- <button id="kt_login_forgot_cancel" class="btn btn-outline-brand btn-pill">Cancel</button> --}}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end:: Page -->

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

		{{-- <script src="assets/js/scripts.bundle.js" type="text/javascript"></script> --}}

		<!--end::Global Theme Bundle -->

		<!--begin::Page Scripts(used by this page) -->
        {{-- <script src="assets/js/pages/custom/login/login-general.js" type="text/javascript"></script> --}}
        {{-- <script src="{{asset('assets\js\demo1\login-general.js')}}" type="text/javascript"></script> --}}

		<!--end::Page Scripts -->
	</body>

	<!-- end::Body -->
</html>

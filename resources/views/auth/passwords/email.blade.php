@extends('base')

@section('style')
    <style>
        .grey-font {
            color: #808080;
        }

        .input-style {
            width: 95%;
            border: none;
            color: #4a4a4a;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            border-radius: 4px;
            margin-bottom: 8px;
            padding: 0.65rem 1rem;
            height: calc(1.5em + 1.3rem + 2px);
            background-color: rgba(235, 237, 242, 0.4);
        }

        .input-style:focus {
            outline: 0;
            color: #4a4a4a;
            border-color: rgba(235, 237, 242, 0.4);
            background-color: rgba(235, 237, 242, 0.4);
        }

        .error-message {
            color: red !important;
            font-weight: 500;
            font-size: 14px;
        }

        .forgot-password-page h4{
            font-size: 24px;
        }
    </style>

    <style lang="scss" scoped>

        .primary-square-button {
            background-color: #7766F7 !important;
            color: #ffffff !important;
            width: 150px;
            min-height: 36px;
            height: auto;
            padding: 6px 16px !important;
            font-size: 12px !important;
            border-radius: 2.5px !important;
            text-transform: capitalize !important;
            border: none !important;
            letter-spacing: 0.5px;
            text-decoration: none;

            @media (max-width: 767px) {
                font-size: 12px !important;
            }
        }

        .primary-square-button:focus, .primary-white-button:focus {
            outline: none !important;
        }

        .primary-square-button:hover {
            background-color: rgb(133 20 235 / 0.9) !important;
            color: 12px !important;
            text-decoration: none !important;
            cursor: pointer;
        }

        .primary-white-button {
            background-color: #ffffff !important;
            color: #7766F7 !important;
            border: 1px solid #7766F7 !important;
            padding: 6px 16px !important;
            min-height: 36px;
            height: auto;
            font-size: 12px !important;
            border-radius: 4px !important;
            text-transform: capitalize !important;
            letter-spacing: 0.5px;
            text-decoration: none;
            border: none;

            /* @media (max-width: 767px) {
                font-size: 8px !important;
            } */
        }

        .primary-white-button:hover {
            background-image: linear-gradient(90deg, rgba(67,189,239,1) 0%, rgba(133,20,235,1) 100%) !important;
            color: 12px !important;
            text-decoration: none !important;
            cursor: pointer;
        }

        .inputCol {
            max-width: 480px;
            @media(max-width: 768px){
                width:100%;
            }
        }

        @media(max-width: 425px){
            .primary-white-button{
                font-size: 12px !important;
            }
        }

        @media(max-width: 576px){
            .primary-square-button{
                display: block !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="forgot-password-page-whole">
        <div class="forgot-password-page">
            {{-- Desktop view --}}

            <img src={{asset("/images/hypershapes-logo.png")}} class="onboarding-logo">


            {{-- Mobile view --}}
            <div class="row">
                <div class="d-block d-sm-none">
                    <img src={{asset("/images/hypershapes-logo.png")}} style="margin:
                         15px 0; height: 50px">
                </div>
            </div>

            <h4 class="forgot-password-page__main-title h-two" style="text-align: center;margin:36px 0">Reset Password</h4>

            <div class="inputCol w-100">
                @if (session('status'))
                    <div class="alert alert-success" role="alert" style="font-size: 14px;">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="inputContainer">
                        <div class="inputContainer__row text-start">
                            <label class="inputContainer__label h-five w-100">
                                Email Address
                            </label>
                            <input
                                id="email"
                                type="email"
                                class="inputContainer__input p-two @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                required autofocus
                                autocomplete="email"
                            >
                            @error('email')
                            <div style="width: 100%;margin-bottom: 20px;">
                                <span class="error-message error-font-size" role="alert">{{ $message }}</span>
                            </div>
                            @enderror
                        </div>

                    <div class="inputContainer__footer">
                        <button type="submit" class="primary-square-button">{{ __('Reset Password') }}</button>
                        <a href="/login" style="margin:auto 0 auto 20px; color: #202930; m" class="cancel-button p-two">Cancel</a>
                    </div>
                </form>
            </div>
        </div>


        {{-- <div class="container" style="margin-top: 50px">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card" style="text-align: center"> --}}
        {{-- <div class="card-header">{{ __('Reset Password') }}</div> --}}
        {{-- <div><img src={{asset("assets/media/logos/rapportstarlogo.png")}} class="logo-style"></div>
        <div class="kt-login__head"><h4 class="kt-login__title" style="color: #4a4a4a">Forgotten Password?</h4></div>
        <div><p class="grey-font" style="font-weight: 400; margin-top: 5px; font-size: 14px">Enter your email to reset your password</p></div>
        <br>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group row"> --}}
        {{-- <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label> --}}
        {{-- <div class="col-md-6">
            <input placeholder="Email" id="email" type="email" class="input-style @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        </div> --}}

        {{-- <div style="width:100%;" >
            <br>
            @error('email')
                <span style="color:red;" role="alert"><strong style="margin-bottom=50%;">{{ $message }}</strong></span>
            @enderror
        </div> --}}

        {{-- @error('email')
            <div style="width: 100%; margin-top: 5px">
                <span class="error-message" role="alert">{{ $message }}</span>
            </div>
        @enderror
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8" style="margin: 2px 25% 0 16.5%">
            <button type="submit" class="btn btn-brand kt-login__btn-primary">{{ __('Send Password Reset Link') }}</button>
            <a href="/login" style="margin-left: 10px" class="btn btn-secondary">Cancel</a>
        </div>
    </div> --}}

        {{-- <div>
            <span class="grey-font"><br><br><br><br>Don't have an account yet? &nbsp;</span>
            <a href="{{ route('register') }}" id="kt_login_signup" style="font-weight: 500; color: #4a4a4a">Sign Up!</a>
        </div> --}}
        {{-- </form>
    </div> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- </div> --}}
        {{-- </div> --}}

    </div>
@endsection

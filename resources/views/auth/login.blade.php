@extends('base')

@section('style')
    <style>

        .grey-font {
            color: #808080;
        }

        .input-style {
            width: 95%;
            border: none;
            color: #202930;
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

        .error-message{
            font-size: 14px;
            color: red !important;
        }

        input[type="checkbox"].purple-bg-checkbox{
            position: absolute;
            opacity: 0;
            z-index: -1;
        }

        input[type="checkbox"].purple-bg-checkbox+label:before {
            content: '';
            border: 1px solid grey;
            border-radius: 3px;
            display: inline-block;
            width: 16px;
            height: 16px;
            min-width: 16px;
            margin-right: 0.5rem;
            margin-top: 0.5rem;
            vertical-align: -2px;
            cursor: pointer;
        }

        input[type="checkbox"].purple-bg-checkbox:checked+label:before {
            background-image: url('/FontAwesomeSVG/check-white.svg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 12px;
            border-radius: 2px;
            background-color: #7766F7;
            color: white;
            cursor: pointer;
        }


    </style>

    <style lang="sass" scoped>

    </style>
@endsection

@section('content')

<div style="justify-content: center; display: flex; align-items: center;">
    <div class="loginContainer">
        <div class="loginContainerContainer">
        {{-- Desktop view --}}
        <div class="row">
            <div class="" style="text-align: center; width: 100%;">
                <img src={{asset("/images/hypershapes-logo.png")}}  class="onboarding-logo">
            </div>
        </div>

        {{-- Mobile view --}}
        {{-- <div class="row">
            <div class="d-block d-md-none">
                <img src={{asset("/images/hypershapes-logo.png")}} style="margin: 15px 0; height: 60px">
            </div>
        </div> --}}

        <!-- <div class="loggedOutMessage">
            <p class="loggedOutMessage__title">You've been logged out.</p>
            <p class="loggedOutMessage__subtitle">Don't worry, you can log in back below</p>
        </div> -->

        <h4 class="loginOrSignUp__main-title h-two" style="margin:36px 0;">Log In</h4>
        @if($errors->any())
            <div class="form-group d-flex mt-2" style="justify-content: center; align-items: center;">
                <p style="color: red; margin: 0">{{ session('errors')->first('token-not-applicable') }}</p>
            </div>
            <div class="form-group d-flex mt-2" style="justify-content: center; align-items: center;">
                <p style="color: red; margin: 0">{{ session('errors')->first('token-mismatched') }}</p>
            </div>
        @endif

        <div class="loginInputContainer">
            <form id='loginForm' method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group row">
                    <div style="width: 100%;">
                        <div class="inputContainer">
                            <div class="inputContainer__row text-start">
                                <label class="inputContainer__label h-five" for="email">Email</label>
                                <input id="email" type="email"
                                    class="inputContainer__input p-two @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}"  autocomplete="email" autofocus oninput="clearErrors()">
                            @error('email')
                            <span class="error-message error-font-size" role="alert">{{ $message }}</span>
                            @enderror
                            </div>
                            <div class="inputContainer__row text-start">
                                <label class="inputContainer__label h-five" for="password">Password</label>
                                <input id="password" type="password"
                                    class="inputContainer__input p-two @error('password') is-invalid @enderror" name="password"
                                    oninput="clearErrors()"
                                    autocomplete="current-password">
                            @error('password')
                             <span class="error-message error-font-size" role="alert">{{ $message }}</span>
                            @enderror
                            </div>
                        </div>


                    </div>

                </div>

                <div class="form-group row inputContainer__row" style="justify-content: center; ">
                    <div class=" form-check inputContainer__checkbox-wrapper">
                        <div class="col-lg inputContainer__checkbox">
                            <input
                                type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                style="margin-right: 10px;"
                                class="purple-bg-checkbox"
                            >
                            <label class="form-check-label p-two mb-0"
                                for="remember">{{ __('Keep me logged in') }}</label>
                        </div>
                        <div class="col-lg inputContainer__footer">
                            <button type="submit" class="primary-square-button" >{{ __('Log In') }}</button>
                        </div>
                    </div>


                    <div class="inputContainer__row">
                        <span class="p-two">Need a Hypershapes account? &nbsp;</span>
                        <a
                            href="{{ route('register') }}"
                            id="kt_login_signup"
                            class="inputContainer__redirect p-two"
                        >
                            Create an account
                        </a>
                    </div>


                </div>

                <div>
                    @if(Route::has('password.request'))
                    <a class="inputContainer__forgot-password p-two" style="text-decoration: underline; color: #202930;"
                        href="{{ route('password.request') }}">{{ __('Forget Password ?') }}</a>
                    @endif
                </div>

            </form>
        </div>
    </div>
</div>
</div>
@endsection
@section('script')
<script>
    function clearErrors() {
     var form = document.getElementById('loginForm').querySelectorAll('span.error-message');
        for (i=0; i<=form.length; i++) {
            form[i].innerText = '';
        }
    }
</script>
@endsection

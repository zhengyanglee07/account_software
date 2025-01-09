@extends('base')

@section('style')

    
    <style>
        .onboarding-logo {
            height: 56px;
            width: auto;
        }

        @media(max-width: $md-display) {
            .onboarding-logo{
                height: 42px;
            }
        }

        .forgot-password-page {
            height: 90vh;
        }
    </style>
@endsection
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="forgot-password-page">
            <img src={{asset("/images/hypershapes-logo.png")}} class="onboarding-logo">
            <h4 class="forgot-password-page__main-title h-two" style="text-align: center;margin:36px 0">Reset Password</h4>

            <div class="inputCol w-100">
                <form method="POST" action="{{ route('password.reset.submit') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="inputContainer" style="margin: 0 auto;">
                        <div class="inputContainer__row">
                            <label for="email" class="inputContainer__label h-five w-100">{{ __('Email Address') }}</label>
                            <input id="email" type="email" class="inputContainer__input p-two @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback p-two" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="inputContainer__row">
                            <label for="password" class="inputContainer__label h-five w-100">{{ __('Password') }}</label>
                            <input id="password" type="password" class="inputContainer__input p-two @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback p-two" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="inputContainer__row">
                            <label for="password-confirm" class="inputContainer__label h-five w-100">{{ __('Confirm Password') }}</label>
                            <input id="password-confirm" type="password" class="inputContainer__input p-two" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="inputContainer__row mb-0 d-flex justify-content-center">
                            <button type="submit" class="btn primary-square-button">{{ __('Reset Password') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

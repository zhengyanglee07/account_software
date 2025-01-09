@extends('affiliate.layout.default')

@section('content')
<div class="email-verify-page">

    <div class="left-bar">




        <div class="onboarding">
            <div class="w-100">
                <div class="verify-container">
                    @if (session('resent'))
                        <div class="m-auto alert alert-success alert-dismissible fade show w-100"
                             role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="row d-flex justify-content-center">
                    <div class="onboarding-container">
                        <div class="d-flex justify-content-center text-center">

                            <div class="verify-content p-two">
                                {{-- Desktop view --}}
                                <img src={{asset("/images/hypershapes-logo.png")}} style="margin: 24px 0; width: 50%">

                                {{-- Mobile view --}}
                                <div class="row">
                                    <div class="col-md-12 d-block d-sm-none">
                                        <img src={{asset("/images/hypershapes-logo.png")}} style="margin:
                                                15px 0; height: 60px">
                                    </div>
                                </div>
                                <p class="h-two">Verify Email</p>
                                <p class="verify-content__text mt-0">
                                    We have sent an email to {{$email}}
                                </p>
                                <p class="verify-content__text mt-0" style="margin-bottom: 20px;">
                                    Click the link in the email to confirm your address and activate your account.
                                </p>
                                <p class="verify-content__text"  style="margin-bottom: 20px;">
                                    Didn't get your email?
                                    <br>
                                    Check your spam, junk or promotion folder.
                                </p>
                                <div class="row">
                                    <div class="input-section text-center">
                                        <form class="d-inline" method="get" action="/email/reset">
                                            @csrf
                                            <button type="submit"  class="primary-square-button">
                                                {{ __('Resend email') }}
                                            </button>
                                        </form>
                                        {{-- <span v-show="loading">
                                            <i class="fas fa-circle-notch fa-spin pr-0"></i>
                                        </span> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('base')

@section('style')
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans', sans-serif;
            font-size: 14px;
            color: #202930;
        }
        .grey-font {
            color: #808080;
        }
        .email-verify-page {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .left-bar {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 40%;
            padding: 0 20px;
        }

        .right-bar {
            height: 100%;
            width: 60%;
        }

        @media(max-width:576px){
            .right-bar{
                display:none;
            }

            .left-bar{
                width:100%;
            }
        }
        .logInImage{
            height: 100%;
            width: 100%;
            text-align: center;
            object-fit: cover;
        }

        .logInImageContainer{
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .verify-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            background-color: #202930;
            padding: 12px;
            text-align: center;
            font-family: 'Noto Sans', sans-serif;
        }

        .verify-container__main-title {
            font-weight: bold;
            font-size: 14px;
            color: #fff;
            margin: 0;
        }

        .verify-container__sub-title {
            padding-top: 8px;
            font-size: 14px;
            color: #fff;
            margin: 0;
        }

        .verify-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-width: 480px;
        }

        .verify-content__text {
            text-align: center;
            margin-bottom: 20px;
        }

        .button-container {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }

        
        @media(max-width: 576px){
            .button-container{
                padding-bottom: 1.5rem;
            }
        }

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

            /* @media (max-width: 767px) {
                font-size: 8px !important;
            } */
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
            text-decoration: none !important;
            cursor: pointer;
            background-color: rgb(133 20 235 /0.05) !important; //$h-primary
        }


    </style>
@endsection

@section('content')
    <div class="email-verify-page">

        <div class="left-bar">

            <!-- @if (session('resent'))
                <div class="m-auto alert alert-success alert-dismissible fade show w-75"
                     role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif -->

            {{-- Desktop view --}}

            <img src={{asset("/images/hypershapes-logo.png")}} style="margin: 24px 0; width: 50%">


            {{-- Mobile view --}}
            <!-- <div class="row">
                <div class="col-md-12 d-block d-sm-none">
                    <img src={{asset("/images/hypershapes-logo.png")}} style="margin:
                         15px 0; height: 60px">
                </div>
            </div> -->

            <!-- <div class="verify-container">
                @if (session('resent'))
                    <p class="verify-container__main-title">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                    </p>
                @else
                    <p class="verify-container__main-title">Confirmation Email Is Sent!</p>
                    <p class="verify-container__sub-title">Check your mailbox to activate your account</p>
                @endif
            </div> -->

            <p class="h-two">Verify Email</p>

            <div class="verify-content p-two">
                <p class="verify-content__text mt-0">
					We have sent an email to {{$email}} 
				</p>
                <p class="verify-content__text mt-0" style="margin-bottom: 40px;">
					Click the link in the email to confirm your address and activate your account.
				</p>
				<p class="verify-content__text">
					Didn't get your email? 
                    <br>
                    Check your spam, junk or promotion folder.
				</p>
            </div>

            <div class="inputContainer__footer">
                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf

                    <button type="submit" class="primary-white-button" style="margin-right:20px; white-space:nowrap;">
                        {{ __('Resend email') }}
                    </button>

                </form>

                <form class="d-inline" method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="primary-square-button"  >
                        {{ __('Back to login') }}
                    </button>

                </form>
            </div>


            {{-- <a type="button" href="{{}}" class="btn btn-primary">
                {{ __('Back to login') }}
            </a> --}}
        </div>

        <div class="right-bar">
            <div class="logInImageContainer">
                <img src={{asset("/images/login_background_image.jpg")}} class="logInImage" >
            </div>
        </div>


    </div>
@endsection

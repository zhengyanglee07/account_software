@extends('base')

@section('style')
    <style>
        *:not(i) {
        font-family: 'Noto Sans', sans-serif,
        color: #202930;
        margin: 0;
        padding: 0;
        }

        .back-to-previous {
            color: #c2c9ce;
            text-decoration: none;
            font-size: 16px;
        }

        .back-to-previous:hover {
            text-decoration: none;
            color: #697887;
        }

        .back-icon {
            color: #c2c9ce;
            padding-right: 12px;
            font-size: 16px;
        }

        .main-title {
            font-size: 28px;
            font-weight: bold;
            margin: 12px 0;
        }

        .background-style {
            /* width: 97.2%; */
            padding-left: 100px;
            padding-right: 100px;
            background: transparent;

        }

        @media(max-width: 1024px){
            .background-style{
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
        }

        .title-style {
            font-size: 15px;
            line-height: 24px;
            font-weight: normal;
            width: 30;
        }

        .pb-10
        {
            padding-bottom: 10px;
        }

        .email-title
        {
            display: inline-block;
            width: 30%;
            padding-bottom: 10px;
        }

        .er-container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            float: right;
            padding-top: 20px;
        }

        @media(max-width: 576px){
            .er-container {
                width: 100%;
                margin-bottom: 1rem;
                flex-direction: column;
                padding-top: 50px;
            }

            .er-card {
                width: 100% !important;
                margin-bottom: 8px !important;
                margin-right: 4px !important;
            }

        }

        .er-card {
            width: 100%;
            height: 120px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #212529;
            margin-right: 30px;
            border: 1px solid #c2c9ce !important;
            border-radius: 5px;
            box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);
        }

        /*.er-card:nth-child(2), .er-card:nth-child(3){
            margin: 0 3.5px;
        }

        .er-card:nth-child(1){
            margin-right: 3.5px;
        }

        .er-card:nth-child(4){
            margin-left: 3.5px;
        }*/

        .er-subtitle {
            font-size: 16px;
            font-weight: 700;
            color:#7766F7;
        }

        .er-desc {
            font-size: 14px !important;
            margin-bottom: 0;
            color: grey !important;
            margin-bottom: 10px;
        }

        @media(max-width: 576px)
        {
            .display-flex
            {
                display: flex;
                flex-direction: column;
            }
            .row-report
            {
                display: flex;
                flex-direction: row;
                width: 100%;
            }
        }

        @media(min-width: 576px)
        {
            .row-report
            {
                display: flex;
                flex-direction: row;
                width: 100%;
            }
        }

    </style>
@endsection

@section('content')
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor background-style">
        <div>
            @if($navigateFrom === "email")
            <a href="{{ url('/emails') }}" class="back-to-previous">
                <i class="fa fa-chevron-left back-icon"></i>
                Back To All Emails
            </a>

            @else
            <a href="{{ url('/emails/report') }}" class="back-to-previous">
                <i class="fa fa-chevron-left back-icon"></i>
                Back To All Email Reports
            </a>
            @endif

            <div class="mt-3 display-flex">

                <h4 class="kt-subheader-search__title title-style h-five email-title">Name: {{ $emailName }} </h4>
                <h4 class="kt-subheader-search__title title-style h-five email-title">Subject: {{ $subject }}</h4>
                <h4 class="kt-subheader-search__title title-style h-five pb-10">
                    Sent Date:
                    @if (!$sentDate)
                        -
                    @else
                        {{ $sentDate }}
                    @endif
                </h4>

                <div class="er-container">

                    <div class="row-report">
                    <div class="er-card">
                        <p class="er-desc p-two">Sent</p>
                        <h5><strong class="er-subtitle h-two">{{ $sent }}</strong></h5>
                    </div>
                    <div class="er-card">
                        <p class="er-desc p-two">Opened</p>
                        <h5><strong class="er-subtitle h-two">{{ $opened }}</strong></h5>
                    </div>
                    </div>

                    <div class="row-report">
                    <div class="er-card">
                        <p class="er-desc p-two">Clicked</p>
                        <h5><strong class="er-subtitle h-two">{{ $clicked }}</strong></h5>
                    </div>
                    <div class="er-card">
                        <p class="er-desc p-two">Bounced</p>
                        <h5><strong class="er-subtitle h-two">{{ $bounced }}</strong></h5>
                    </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection


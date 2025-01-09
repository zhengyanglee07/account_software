@extends('base')

@section('style')

<style>
    *:not(i) {
        font-family: 'Noto Sans', sans-serif;
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
</style>

@endsection

@section('content')
    <div class="container col-sm-12">
        <a href="/emails"
           class="back-to-previous">
            <i
                class="fa fa-chevron-left back-icon">
            </i>

			Back to Email
        </a>

        <h3 class="main-title">
            {{ $email->name }}
        </h3>

        @if($email->emailDesign)
            <div style="background-color: {{ $email->emailDesign->body_bg_color }}">
                <div class="p-4">
                    <nested-components
                        :list="{{ $email->emailDesign->preview ?? '[]' }}">
                    </nested-components>
                </div>
            </div>
        @else
            <h3 class="text-black-50">Please create an email.</h3>
        @endif
    </div>
@endsection

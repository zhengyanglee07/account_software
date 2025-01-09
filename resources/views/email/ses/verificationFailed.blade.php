@extends('base')
@section('style')
<style>
    .grey-font {
        color: #808080;
    }
</style>
@endsection

@section('content')
<div class="container col-md-12" style="text-align: center">
    {{-- Desktop view --}}
    <div class="row">
        <div class="col-md-12 d-none d-sm-block">
            <img src={{ asset("assets/media/icons/error-cross.png") }} style="margin: 15px 0 25px 0; width: 13%">
        </div>
    </div>

    {{-- Mobile view --}}
    <div class="row">
        <div class="col-md-12 d-block d-sm-none">
            <img src={{ asset("assets/media/icons/error-cross.png") }} style="margin: 15px 0 25px 0; width: 35%">
        </div>
    </div>

    {{-- content --}}
    <br>
    <h1 class="kt-login__title" style="color: #4a4a4a">Failed</h1>
    <p class="grey-font mt-4" style="font-weight: 400; margin-top: 5px; font-size: 16px; padding: 8px 5px 12px 5px">
        Your email address is unable to verify. Please contact support.
        <br>

        <p class="grey-font mt-5" style="font-weight: 400; margin-top: 5px; font-size: 14px; padding: 8px 5px 12px 5px">
            <a href="/emails">back to email</a>
        </p>
    </p>
</div>
@endsection

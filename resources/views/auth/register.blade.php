@extends('base')

@section('content')
    <div style="justify-content: center; display: flex; align-items: center;">
        <div class="loginContainer">
            <div class="loginContainerContainer">
            {{-- Desktop view --}}
            <div class="row">
                <div class=""  style="text-align: center; width: 100%;">
                    <img src={{asset("/images/hypershapes-logo.png")}}  class="onboarding-logo">
                </div>
            </div>

            <h4 class="loginOrSignUp__main-title h-two" style="margin: 36px 0 20px 0;">Sign Up</h4>

            <div class="loginInputContainer">
				<register
				 affiliate_id = {{$affiliate_id}}
				></register>
            </div>
        </div>
    </div>
    </div>
@endsection
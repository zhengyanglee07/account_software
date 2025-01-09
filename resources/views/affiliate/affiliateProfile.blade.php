@extends('affiliate.layout.affiliateNav')


@section('style')

    <link href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
    rel="stylesheet"/>
    <link href="{{ asset('assets\dist\snow.css') }}"
    rel="stylesheet"/>

@endsection

@section('content')
    <affiliate-profile
        :affiliate-user = "{{$affiliate_user}}">
    </affiliate-profile>

@endsection

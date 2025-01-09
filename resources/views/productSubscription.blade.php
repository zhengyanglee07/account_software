@extends('base')

@section('style')
    {{-- add these for arrow icon in navbar save dropdown --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link
        href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/custom/vendors/flaticon/flaticon.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/custom/vendors/flaticon2/flaticon.css') }}"
          rel="stylesheet"/>
@endsection

@section('content')
    <product-subscription-dashboard
        :subscription="{{json_encode($subscription)}}"
        terminate-cycle="{{$terminateCycle}}"
    />
@endsection

@extends('base')

@section('style')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link
            href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}"
            rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/custom/vendors/flaticon/flaticon.css') }}"
          rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/custom/vendors/flaticon2/flaticon.css') }}"
          rel="stylesheet"/>
    {{-- Bootstrap 4.5 --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
          integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
    <script src="https://unpkg.com/vue-chartjs/dist/vue-chartjs.min.js"></script>
@endsection

@section('content')
    <segment-report
        :segments="{{ json_encode($segments) }}"
        :currency="{{ json_encode($defaultCurrency) }}"
        :exchange-rate="{{ json_encode($myrExchangeRate) }}"
    ></segment-report>
@endsection

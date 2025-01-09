@extends('base')

@section('title')
    Hypershapes | Email Builder
@endsection

@section('style')
    <link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endsection

@section('chatWidget')
    @production
    {{-- Freshdesk widget --}}
    <script>
        window.fwSettings={
        'widget_id':72000001684
        };
        !function(){if("function"!=typeof window.FreshworksWidget){var n=function(){n.q.push(arguments)};n.q=[],window.FreshworksWidget=n}}() 
    </script>
    <script type='text/javascript' src='https://widget.freshworks.com/widgets/72000001684.js' async defer></script>
    {{-- End of Freshdesk widget --}}
    @endproduction
@endsection

@section('content')
    <base-email-builder
        :email="{{ $email }}"
        :email-design="{{ $emailDesign }}"
        exit-url="{{ $exitUrl }}"
    ></base-email-builder>
@endsection


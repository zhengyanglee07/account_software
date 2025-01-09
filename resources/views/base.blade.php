<!doctype html>
<html lang="en">
<head>
    @production
        <!-- Google Tag Manager -->
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','GTM-WV5S9BQ');
        </script>
        <!-- End Google Tag Manager -->
    @endproduction

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Hypershapes</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-color/2.4.6/vue-color.min.js"></script>

    <link rel="shortcut icon" href="{{ asset('images/hypershapes-favicon.png') }}"/>

    <style>
        * {
            padding: 0;
            margin: 0;
        }

        #root {
            height: 100%;
        }
    </style>

    @yield('style')

    @stack('styles')
</head>
<body>
    @production
        <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WV5S9BQ" height="0" width="0" style="display:none;visibility:hidden">
            </iframe>
        </noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endproduction

    <div id="root">
        @yield('content')
    </div>

    @stack('scripts-before-app.js')

    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('script')
    @stack('scripts')
</body>
</html>


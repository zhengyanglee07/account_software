<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link 
      rel="preconnect" 
      href="https://fonts.googleapis.com">
      
    <link 
      rel="preconnect" 
      href="https://fonts.gstatic.com" 
      crossorigin>

    <link 
      rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
      integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" 
      crossorigin="anonymous" 
      referrerpolicy="no-referrer" />
    
    <link 
      rel="stylesheet" 
      href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <link 
      rel="shortcut icon" 
      href="https://media.hypershapes.com/images/hypershapes-favicon.png" />
    
    @vite('resources/app.js')
      
    @production
      <!-- Google Tag Manager -->
      <script>
          (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','GTM-WV5S9BQ');
      </script>
      <!-- End Google Tag Manager -->

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
    @inertia
  </body>
</html>
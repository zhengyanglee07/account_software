<!DOCTYPE html>
<html lang="en">
<head>
   <!--begin::Base Path (base relative path for assets of this page) -->
   <base href="../">
   <!--end::Base Path -->

   <!-- For people page filter custom field dropdown -->
   {{-- <link href="{{ asset('assets/css/demo9/bootstrap-select.css') }}"
       rel="stylesheet"/> --}}
   <!-- Global theme styles
       # very important for navbar, CRM, dashboard, people and account settings -->
   {{-- <link href="{{ asset('assets/css/demo9/style.bundle.min.css') }}"
       rel="stylesheet"/> --}}


   {{-- <link rel="shortcut icon" href="{{ asset('images/hypershapes_icon_bg_new.png') }}"/> --}}

   <meta name="csrf-token" content="{{ csrf_token() }}">
   <meta charset="utf-8" />
   <title>Hypershapes</title>
   <meta name="description" content="Latest updates and statistic charts">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
   <link href="{{ asset('assets/vendors/custom/vendors/flaticon/flaticon.css') }}" rel="stylesheet" />
   <link href="{{ asset('assets/vendors/custom/vendors/flaticon2/flaticon.css') }}" rel="stylesheet" />
   <link href="{{ asset('assets/vendors/general/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet" />

   <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <!-- Future use  -->
   <link href="{{ asset('assets/vendors/general/animate.css/animate.min.css') }}" rel="stylesheet" />
    <!-- Used on delete confirmation of funnel, landing page and CRM   -->
   <link href="{{ asset('assets/css/demo9/sweetalert2.css') }}" rel="stylesheet"/>
   <link href="{{ asset('hypershapes-favicon.png') }}" rel="shortcut icon" />
   <style>

       /* Hide scrollbar for Chrome, Safari and Opera */
    .body::-webkit-scrollbar {
        /* display: none; */
    }

    /* Hide scrollbar for IE, Edge and Firefox */

    .body {
    /* -ms-overflow-style: none;  IE and Edge */
    /* scrollbar-width: none;  Firefox */
    }
    .iconImg{
        float: left;
        width: 20px;
        margin-right: 10px;
    }
    @media (max-width:768px){

    .right_container_content {
            padding: calc(56px + 30px) 32px 30px 32px;
        }
    }


   </style>

   @yield('style')

   @stack('styles')

</head>
<body class="body">
    <div id="root">
        <div class="" style="width:100vw;">
            <div class="left_container" id="left_container">
                <div class="left_container_content">
                    <div class="logo">
                        <button class="logo_hypershapes" id="nav_hypershapes_dashboard" onclick='showNavtitle(this.id)'>
                            <img src="/images/hypershapes-logo.png" style="width: 100%; padding: 40px">
                        </button>
                    </div>
                    <ul class="content_nav">
                        <li class="content_nav_title">
                            <div class="content_nav_title_icon">
                                <div class="icon-preview" style="--icon-stroke:1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewbox="0 0 24 24" stroke-width="0.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <ellipse cx="12" cy="6" rx="8" ry="3"></ellipse>
                                        <path d="M4 6v6a8 3 0 0 0 16 0v-6"></path>
                                        <path d="M4 12v6a8 3 0 0 0 16 0v-6"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ url('/dashboard')}}" class="content_nav_title_redirect"
                                id="nav_dashboard">Dashboard</a>
                        </li>

                        {{-- <li class="content_nav_title">
                            <div class="content_nav_title_icon">
                                <div class="icon-preview" style="--icon-stroke:1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewbox="0 0 24 24" stroke-width="0.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"></path>
                                        <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ url('/payment')}}" class="content_nav_title_redirect"
                                id="nav_payment">Payment</a>
                        </li> --}}

                        <li class="content_nav_title">
                            <div class="content_nav_title_icon">
                                <div class="icon-preview" style="--icon-stroke:1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewbox="0 0 24 24" stroke-width="0.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                                    </svg>
                                </div>
                            </div>
                            <a href="{{ url('/profile')}}" class="content_nav_title_redirect"
                                id="nav_profile">Profile</a>
                        </li>


                    </ul>
                </div>
                {{-- <div class="content_nav_title" style="margin-top: 750px;margin-bottom: 5rem;">
                    <div class="content_nav_title_icon"><img src="/images/settings_icon.svg"></div>
                    <a href="#" class="content_nav_title_redirect" id="nav_settings">Settings</a>
                </div> --}}
            </div>
        </div>



        <div class="right_container" id="right_container">
            <div class="row right_container_nav" style="padding: 0 32px !important">
                <div class="title " id="titleNavShow"></div>
                <div class="sidebar_small_screen">
                    <div id="sidebar_button" class="sidebar_button">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
                <!-- $affiliate_user->tier($affiliate_user->affiliate_unique_id) -->
                <strong class="col" style="font-size: 14px">{{ Auth::guard('affiliateusers')->user()->tier() }}</strong>
                <div id="dropdownuser" class="col right_container_nav_profile">
                    <!--begin: User bar -->
                    <div class="right_container_nav_profile_select" data-bs-toggle="dropdown" data-offset="10px,0px">
                        <span id="accountName" style=" cursor: pointer;font-size: 14px">
                            {{ Auth::guard('affiliateusers')->user()->first_name }} {{ Auth::guard('affiliateusers')->user()->last_name }}&nbsp;
                            <i class="fas fa-caret-down"></i>
                        </span>
                    </div>
                    <!--use below badge element instead the user avatar to display username's first letter (remove kt-hidden class to display it) -->
                    {{-- <span
                            class="kt-badge kt-badge--username kt-badge--unified-success kt-badge--lg kt-badge--rounded kt-badge--bold kt-hidden">S</span> --}}
                    <ul class="dropdown-menu profile_dropdown">
                        {{-- <li class="settings profile_dropdown_li" aria-haspopup="true">
                            <a href="{{ url('/myprofile') }}" class="profile_dropdown_li_link_text">
                                <div>
                                    <i class="fas fa-user" style="font-size: 1rem;"></i>&ensp;&nbsp;&nbsp;My Profile
                                </div>
                            </a>
                        </li> --}}
                        <li class="settings profile_dropdown_li" aria-haspopup="true">
                            <a href="{{ route('affiliate.logout') }}" class="  profile_dropdown_li_link_text"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit(); localStorage.clear();">
                                <div>
                                    <i class="fas fa-sign-out-alt" style="font-size: 1rem;"></i>&ensp;&nbsp;&nbsp;Log Out
                                </div>
                            </a>

                            <form id="logout-form" action="{{ route('affiliate.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex" style="justify-content: center;">
                <div class="right_container_content" id="right_container_content">
                    @yield('content')
                </div>
            </div>

        </div>
    </div>


{{-- Sweet alert --}}
<script src="{{ asset('assets/vendors/general/sweetalert2/dist/sweetalert2.min.js') }}"></script>
{{-- <script src="{{ asset('assets/js/demo1/pages/components/extended/sweetalert2.js') }}"></script> --}}
{{-- End:: Sweet alert --}}

@stack('scripts-before-app.js')

{{-- app.js bundle with Vue --}}
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>

<script>
    var left = document.getElementById("left_container");
    var right = document.getElementById("right_container");


    function displayTitle(navTitle){
        console.log(navTitle);
        document.getElementById('title').innerText = navTitle;
        console.log(document.getElementById('title').innerText);
    }

    function hideSidebar(){
        left.style.display = "none";
    }

    $(window).resize(onresize);

    function onresize(){
        // console.log(screen.width);
        $('#sidebar_button').click(function(sideeee){
            if((screen.width < 769)){
                left.style.display = 'block';
            }
        });

        $('#right_container_content').click(function(sideeee){
            if((screen.width < 769)){
                left.style.display = 'none' ;
            }else left.style.display = 'block' ;
        });
        left.style.display = 'block';

        if(screen.width < 769){
        console.log('small');
        left.style.display = 'none';}
    }

    if(screen.width < 769){

    $('#sidebar_button').click(function(sideeee){
        sideeee.stopPropagation();
        left.style.display = 'block';
    });
    $(' #right_container_content').click(function(sideeee){
        left.style.display = 'none' ;
    });
    }
    else left.style.display = 'block';

    localStorage.setItem('storedAffiliateNavTitle', 'Dashboard')

    const navTitleInLocalStorage = localStorage.getItem('storedAffiliateNavTitle');
    if(navTitleInLocalStorage){
        document.getElementById('titleNavShow').innerHTML = localStorage.getItem('storedNavTitle');
        if(window.location.href=="{{ url('/dashboard') }}"){
            localStorage.setItem('storedNavTitle', 'Dashboard')
            document.getElementById('titleNavShow').innerHTML = localStorage.getItem('storedNavTitle');
            document.getElementById('nav_dashboard').style.color="#3490dc";
        }
        else if(window.location.href=="{{ url('/payment') }}"){
            localStorage.setItem('storedNavTitle', 'Payment')
            document.getElementById('titleNavShow').innerHTML = localStorage.getItem('storedNavTitle');
            document.getElementById('nav_payment').style.color="#3490dc";
        }
        else if(window.location.href=="{{ url('/profile') }}"){
            localStorage.setItem('storedNavTitle', 'Profile')
            document.getElementById('titleNavShow').innerHTML = localStorage.getItem('storedNavTitle');
            document.getElementById('nav_profile').style.color="#3490dc";
        }
    }
</script>

 {{-- additional user-defined scripts from blades --}}
@yield('script')

{{-- additional scripts, usually from partials --}}
@stack('scripts')
</body>
</html>

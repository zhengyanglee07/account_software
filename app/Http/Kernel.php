<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'affiliateTracking' =>\App\Http\Middleware\AffiliateTracking::class,
//            \App\Http\Middleware\Cors::class,
            'permissionChecker'=>\App\Http\Middleware\PermissionChecker::class,
            //* always put this as the last item in web middleware group
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],

        'api' => [
           EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
//            'bindings',
            SubstituteBindings::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        // 'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // user defined middlewares
        'CheckOnBoardingEmail' => \App\Http\Middleware\CheckOnBoardingEmail::class,
        'checkFirstName' => \App\Http\Middleware\CheckOnBoardingFirstName::class,
        'checkOnBoarding' => \App\Http\Middleware\CheckOnBoarding::class,
        'checkOnBoardingExpressSetup' => \App\Http\Middleware\CheckOnBoardingExpressSetup::class,
        'checkApi' => \App\Http\Middleware\CheckOnBoardingApi::class,
        'checkUserShop' => \App\Http\Middleware\CheckUserShop::class,
        'checkPeopleProfile' => \App\Http\Middleware\CheckPeopleProfile::class,
        'checkSubscription' => \App\Http\Middleware\CheckSubscription::class,
//        'cors' => \App\Http\Middleware\Cors::class,
        'checkPermission'=>\App\Http\Middleware\CheckPermissions::class,
        'globalChecker'=>\App\Http\Middleware\GlobalChecker::class,
        'permissionChecker'=>\App\Http\Middleware\PermissionChecker::class,
        'validateDomain' => \App\Http\Middleware\ValidateDomain::class,
        'onlineStoreApi' => \App\Http\Middleware\CheckOnlineStoreApi::class,
        'checkReturnUrl' => \App\Http\Middleware\checkReturnUrl::class,
        'verifyCustomerAccount' => \App\Http\Middleware\VerifyCustomerAccount::class,
        'AffiliateAuthentication' => \App\Http\Middleware\AffiliateAuthentication::class,
        'AffiliateMemberAuth' => \App\Http\Middleware\AffiliateMemberAuth::class,
        'AffiliateCheckVerified' => \App\Http\Middleware\AffiliateCheckVerified::class,
        'affiliateMemberStoreLastCookie' => \App\Http\Middleware\AffiliateMemberStoreLastCookie::class,
        'referralCampaignStoreLastCookie' => \App\Http\Middleware\ReferralCampaignStoreLastCookie::class,
        'validateAffiliateMemberDashboardDomain' => \App\Http\Middleware\ValidateAffiliateMemberDashboardDomain::class,
        'isCustomerAccountDisabled' => \App\Http\Middleware\checkCustomerAccountDisabled::class,
        'checkoutGuard' => \App\Http\Middleware\CheckoutGuard::class,
        'checkout' => \App\Http\Middleware\CheckoutMiddleware::class,

        // Custom middleware to fix production 403 invalid sig error
        // Replace default \Illuminate\Routing\Middleware\ValidateSignature middleware
        'signed' => \App\Http\Middleware\ValidateHttpsSignature::class,

        

    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        // \App\Http\Middleware\Cors::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}

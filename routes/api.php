<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * * Master Admin Auth
 */
Route::post('login', 'API\Auth\LoginController@masterAdminLogin');
Route::post('logout', 'API\Auth\LoginController@logout')->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    /**
     * * Lucky Draw Tool Auth
     */
    Route::post('/login', 'Auth\LoginController@apiLogin');
    Route::post('/logout', 'Auth\LoginController@apiLogout')->middleware('auth:sanctum');
    Route::get('auth', function () {
        return response()->json(['auth' => Auth::check()]);
    })->middleware('auth:sanctum');

    /**
     * * To handle image upload of lucky draw tool
     * ! There is no authorization on this API. Should be removed after a better solution is done
     */
    Route::post('/images/upload', 'ImagesController@store');

    //* Checkout - Customer Information
    Route::post('/update/customer/info', 'API\OnlineStoreTrackingController@updateCustomerInfo');

    //* Delyva Callback
    Route::post('/check/delyva/update', 'DelyvaController@orderUpdate'); // required

    //* Builder


    //* Products
    Route::controller(API\BuilderController::class)->group(function () {
        Route::post('/base-data', 'publishBaseData');
        Route::post('/builder/show', 'show');
        Route::get("/transform-data", "transformData");
    });

    /**
     * * Builder - Form Element
     */
    Route::controller(FormDataController::class)->group(function () {
        Route::prefix('/form')->group(function () {
            Route::post('/check/exist', 'create');
            Route::post('/submit', "submit");
        });
    });

    /**
     * * CRM Tracking
     */
    Route::controller(OnlineStoreTrackingController::class)->group(function () {
        Route::prefix('tracking')->group(function () {
            Route::post('/visitor', 'create');
            Route::post('/activity', 'trackNewActivity');
            Route::post('/conversion', 'recordConversion');
        });
    });

    //* Master Admin
    Route::middleware(['auth:sanctum'])->group(function () {
        /**
         * * Images
         */
        Route::controller(ImagesController::class)->group(function () {
            Route::prefix('images')->group(function () {
                Route::get('/', 'index');
                Route::post('/upload/builder', 'store');
                Route::delete('/delete/{image}', 'delete');
            });
        });

        /**
         * * Builder
         */
        Route::controller(API\BuilderController::class)->group(function () {
            Route::get('/{builderType}/{builderRefKey}/editor', 'edit');
            Route::prefix('builder')->group(function () {
                Route::get('/', 'index');
                Route::put('/', 'update');
                Route::put('/update/status/{page}', 'updateStatus');
            });
            Route::get("/transform-data", "transformData");
            Route::put("/transform/data", "transform");
        });

        /**
         * * Products
         */
        Route::controller(API\UserProductController::class)->group(function () {
            Route::prefix('products')->group(function () {
                Route::get('/', 'index');
            });
        });

        /**
         * * Currency
         */
        Route::controller(CurrencyController::class)->group(function () {
            Route::prefix('currency')->group(function () {
                Route::get('/', 'getDefaultCurrencyDetails');
            });
        });

        /**
         * * Template
         */
        Route::controller(TemplateController::class)->group(function () {
            Route::prefix('templates')->group(function () {
                Route::get('/', 'index');
                Route::post('/user', 'saveUserTemplate');
                Route::delete('/user/{template}', 'deleteUserTemplate');
            });
        });

        Route::middleware(['can:access-ma'])->group(function () {
            Route::post('/users/accounts/basic', 'API\UserInfoController@userAccountBasicInfo');
            Route::get('/users/accounts/quota', 'API\UserInfoController@userAccountQuotaUsage');
            Route::get('/users/accounts/rates', 'API\UserInfoController@getExchangeRatesBasedOnUSD');
            Route::get('/admin/report', 'API\AdminReportController@subscriptionsReport');
            Route::get('/admin/report/emails', 'API\AdminEmailReportController@getBouncesAndComplaints');
        });

        //* Email Builder
        Route::controller(API\EmailBuilderController::class)->group(function () {
            Route::get('/email-builder/base-data/{emailRef}/{designRef}', 'builderBaseData');
            Route::get('/email/preview/{designRef}', 'preview');
            Route::get('/email/templates', 'getEmailTemplate');
            Route::post('/email/design/{designRef}/save', 'saveDesign');
            Route::post('/email/design/{designRef}/template/save', 'saveDesignAsTemplate');
        });
    });

    Route::middleware('isCustomerAccountDisabled')->group(function () {
        Route::controller(API\EcommerceAuthController::class)->group(function () {
            Route::post('/signup', 'register');
            Route::post('/login', 'login');

            Route::group(['middleware' => 'auth:ecommerceUsers'], function () {
                Route::get('/logout', 'logout');
                Route::get('/email/verification', 'getEmailNotice');
                Route::get('/email/verify', 'verifyEmail');
                Route::get('/email/resend', 'resendEmail');
            });
        });
        Route::get('/check/auth', 'API\EcommerceController@checkAuth');
        Route::group(['middleware' => ['auth:ecommerceUsers', 'onlineStoreApi']], function () {
            Route::controller(API\EcommerceController::class)->group(function () {
                Route::get('/customer-account/data', 'getBaseData');
                Route::get('/store-credit/data', 'getStoreCreditData');
                Route::post('/address/update', 'updateAddress');
                Route::post('/profile/update', 'updateProfile');
                Route::post('/profile/pw/update', 'updatePassword');
            });
            Route::controller(API\EcommerceOrderController::class)->group(function () {
                Route::get('/orders/dashboard', 'getOrders');
                Route::get('/order/{refKey}', 'getOrderDetail');
            });
            Route::controller(API\EcommerceCourseController::class)->group(function () {
                Route::get('/courses', 'getCourses');
                Route::get('/courses/progress', 'updateLessonProgress');
                Route::get('/courses/{referenceKey}', 'getCoursesDetail');
            });
            Route::controller(API\EcommerceVirtualAssetController::class)->group(function () {
                Route::get('/virtual-assets', 'getVirtualAssets');
                Route::get('/virtual-assets/access/{id}', 'recordAccess');
            });
        });

        Route::controller(EcommerceForgetController::class)->group(function () {
            Route::get('/forgot', 'getForgotData');
            Route::post('/forgot/email', 'store');
        });

        Route::controller(EcommerceResetPasswordController::class)->group(function () {
            Route::get('password/reset/{token}', 'getResetData');
            Route::post('/password/reset', 'reset');
        });
    });

    Route::group(['middleware' => ['validateAffiliateMemberDashboardDomain'], 'prefix' => 'affiliates'], function () {
        Route::group(['namespace' => 'AffiliateMemberAuth'], function () {
            Route::get('/check/auth', 'VerificationController@checkAuth');

            Route::get('/signup', 'RegisterController@getSignupData');
            Route::post('/signup', 'RegisterController@register');

            Route::post('/login', 'LoginController@login');

            Route::get('/forget', 'ForgotPasswordController@getResetData');
            Route::post('/forget/email', 'ForgotPasswordController@sendResetLink');

            Route::get('/password/reset/{token}', 'ResetPasswordController@getResetData');
            Route::post('/password/reset', 'ResetPasswordController@reset');

            Route::group(['middleware' => ['auth:affiliateMember']], function () {
                Route::get('/logout', 'LoginController@logout');

                Route::controller(VerificationController::class)->group(function () {
                    Route::get('/confirm/email', 'getEmailConfirmationData');
                    Route::get('/email/resend', 'resend');
                    Route::get('/email/verify', 'verify');
                });
            });
        });
        Route::group(['middleware' => ['auth:affiliateMember', 'AffiliateMemberAuth'], 'controller' => AffiliateMemberDashboardController::class], function () {
            Route::get('/base-data', 'getBaseData');
            Route::get('/dashboard', 'getDashboardData');
            Route::get('/campaigns', 'getCampaignsData');
            Route::get('/conversions', 'getConversionsData');
            Route::get('/payouts', 'getPayoutsData');
            Route::post('/payouts', 'requestPayout');
            Route::get('/profile', 'getProfileData');
            Route::post('/profile', 'updateProfile');
            Route::post('/profile/pw', 'updatePassword');
        });
    });


    Route::group(['middleware' => 'checkout'], function () {
        Route::get('/product/{path}', 'onlineStoreController@getProducDescriptionData');
        Route::get('/shopping/cart', 'CheckoutController@getShoppingCartData');
        Route::controller(CheckoutController::class)->group(function () {
            Route::prefix('checkout')->group(function () {
                Route::get('/data/twostepform', 'getTwoStepFormData');
                Route::get('/data/{path}/{refKey?}', 'getCheckoutData')->middleware('checkoutGuard');

                // Route::post('/delivery/area', 'checkoutDeliveryArea');
                Route::post('/cart', 'checkoutCart');
                Route::get('/cart/update/{quantity}', 'checkoutCartItemQuantity');
                Route::post('/outOfStock', 'checkoutOutOfStock')->withoutMiddleware('checkout');
                // Route::post('/shipping/option', 'checkoutShippingOption')->withoutMiddleware('checkout');
                Route::post('/form', 'checkoutForm');
                Route::post('/salesForm', 'checkoutTwoStepForm');
                Route::post('/shipping', 'checkoutShipping');
                Route::post('/order', 'checkoutOrder');
                Route::get('/order/process', 'checkoutOrderProcess')->middleware('checkoutGuard');
                Route::get('/order/{paymentRef}', 'getOrderData')->withoutMiddleware('checkout');
                // Route::get('/{path}/{refKey?}', 'showCheckoutPages')->middleware('checkoutGuard');
            });
        });

        Route::get('/senangpay/redirect', 'PaymentController@getSenangpayResponse');
        Route::get('/ipay88/response', 'PaymentController@ipay88Response');
        Route::get('/payments/stripe/paymentIntent', 'PaymentController@createStripePaymentIntent');
        Route::get('/payments/stripe/fpx/paymentIntent', 'PaymentController@createStripeFPXPaymentIntent');

        Route::controller(PromotionController::class)->group(function () {
            Route::prefix('promotion')->group(function () {
                Route::get('/apply/{discountCode}', 'applyPromotion');
                Route::delete('/delete/{promotionId}', 'deleteAppliedPromotion');
            });
        });
    });

    Route::post('/easyparcel/check/quotation', 'EasyParcelController@shippingRateChecking');
    Route::post('/lalamove/check/quotation', 'LalamoveController@quotationChecking');
    Route::post('/delyva/check/quotation', 'DelyvaController@quotationCheck');

    Route::controller(LegalSettingsController::class)->group(function () {
        Route::prefix('legal')->group(function () {
            Route::get('/{type}', 'getLegalPolicy');
        });
    });

    Route::controller(SubscriptionController::class)->group(function () {
        Route::prefix('subscription')->group(function () {
            Route::get('/plans', 'getSubscriptionPlans');
        });
    });
});

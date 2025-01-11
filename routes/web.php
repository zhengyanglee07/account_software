<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

if (app()->environment(['production']) || app()->environment(['staging'])) {
    URL::forceScheme('https');
}

/*
 * To check whether the domain is registered in system
 * before assign SSL to a custom domain via caddy
 * Refer: https://caddyserver.com/docs/automatic-https#using-on-demand-tls
 */
Route::get('/domain/validate', 'DomainController@isDomainExisted');


//* Sales Form
Route::get('/landing/{id}/nextStepInFunnel', 'LandingPageController@nextStepInFunnel');
Route::get('/landing/{id}/otherStepInFunnel', 'LandingPageController@otherStepInFunnel');

//referral
Route::post('/referral-landing-page', 'ReferralCampaignController@saveReferralLandingPage');
Route::post('/referral-action-log', 'ProcessedContactReferralActionLogController@store');

Route::get('/get-customer-info/customer-info', "ProductReviewController@getCustomerInformation");
Route::get('/get-product-reviews/{productId}/contents', "ProductReviewController@getProductReviewContents");
Route::get('/get-customer-purchase-info/{productId}/purchased', "ProductReviewController@getCustomerPurchaseInfo");
Route::post('/send-product-review/product/{id}/product-review', "ProductReviewController@createProductReview");
Route::post('/review/image/store', "ProductReviewController@storeReviewImage");
Route::post('/review/image/cancel', "ProductReviewController@removeReviewImage");

// Variant & VariantValue
Route::get('/variants', "VariantController@getSharedVariants");
Route::get('/variants/modal', "VariantController@getVariantModal");
Route::post('/addSharedVariant', "VariantController@addSharedVariant");
Route::post('/product/edit-variant-priority', "VariantController@updateVariantPriority");
Route::post('/updateSharedVariant', "VariantController@updateVariants");
Route::delete('/deleteSharedVariant/{id}', "VariantController@deleteSharedVariantValue");

Route::post('/deleteProduct', "UsersProductController@deleteProduct");
Route::post('/changeProductStatus/{id}', "UsersProductController@changeProductStatus");
// Route::get('/product/{id}/variant/{account_id}','UsersProductController@getProductVariant');
Route::post('/updateSelectedProduct', 'UsersProductController@updateSelectedProduct');

Route::get('/social-proof/{accountId}', 'SocialProofController@fetchAllNotification');

//* Product details retrieval on publish
Route::post('/updateSelectedProduct', 'UsersProductController@updateSelectedProduct');

//subscription webhook (callback)
Route::post('/getBillingWebhook', 'SubscriptionController@getWebhookForRecurringBilling');
//                            _
//                         _ooOoo_
//                        o8888888o
//                        88" . "88
//                        (| -_- |)
//                        O\  =  /O
//                     ____/`---'\____
//                   .'  \\|     |//  `.
//                  /  \\|||  :  |||//  \
//                 /  _||||| -:- |||||_  \
//                 |   | \\\  -  /'| |   |
//                 | \_|  `\`---'//  |_/ |
//                 \  .-\__ `-. -'__/-.  /
//               ___`. .'  /--.--\  `. .'___
//            ."" '<  `.___\_<|>_/___.' _> \"".
//           | | :  `- \`. ;`. _/; .'/ /  .' ; |
//           \  \ `-.   \_\_`. _.'_/_/  -' _.' /
// ===========`-.`___`-.__\ \___  /__.-'_.'_.-'================
//                         `=--=-'

// custom SNS endpoint that replaced mail-tracker SNS endpoint
Route::post('/emails/sns', 'SNSController@handleSNSMessage');

Route::get('oauth/google', 'GoogleAdAudienceController@redirectToProvider');
Route::get('oauth/google/callback', 'GoogleAdAudienceController@handleProviderCallback');

// role
Route::get('/role/accept-invitation', 'RoleInvitationEmailController@assignRoleAssignment')->name('accept-invitation');
Route::group(['domain' => config('app.domain')], function () {
    /**
     * * Email Subscription
     */
    Route::controller(EmailSubscriptionController::class)->group(function () {
        // contact unsub/sub email (no need auth, coz they're accessed from user's clients)
        Route::get('/emails/unsubscribe/{contactRandomId}/success', 'showUnsubscribeSuccess');
        Route::get('/emails/subscribe/{contactRandomId}/{emailId}', 'subscribe');
        Route::get('/emails/unsubscribe/{contactRandomId}/{emailId}', 'showUnsubscribe');
        Route::post('/emails/unsubscribe/{processedContact}/{emailId}', 'unsubscribeContact');
    });
});
/**
 *           _    _ _______ _    _
 *     /\  | |  | |__   __| |  | |
 *    /  \ | |  | |  | |  | |__| |
 *   / /\ \| |  | |  | |  |  __  |
 *  / ____ \ |__| |  | |  | |  | |
 * /_/    \_\____/   |_|  |_|  |_|
 *
 */

require __DIR__ . '/auth.php';

Route::group([
    'domain' => config('app.domain'),
    'middleware' => ['auth', 'throttle:200,1', 'globalChecker']
], function () {
    Route::redirect('/', '/dashboard');
    // View logs in laravel
    Route::get('__logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

    /**
     * * Builder
     */
    Route::controller(BuilderController::class)->group(function () {
        Route::get('/builder/{pageType}/{refKey}/editor', 'edit');
        Route::get('/builder/{pageType}/{refKey}/preview', 'preview');
        Route::put('/builder/{pageType}', 'update');
    });

    /**
     * * Templates
     */
    Route::controller(TemplateController::class)->group(function () {
        Route::prefix('templates')->group(function () {
            Route::get('/', 'show');
            Route::get('/all', 'index');
            Route::post('/create', 'create');
            Route::post('/user', 'saveUserTemplate');
            Route::post('/general/snapshot', 'saveSnapshot');
            Route::post('/update/details/{template}', 'updateDetails');
            Route::put('/update/{template}', 'update');
            Route::put('/bulk-update', 'bulkUpdate');
            Route::delete('/user/{template}', 'deleteUserTemplate');
            Route::delete('/destroy/{id}', 'destroy');
        });
        // TODO to be removed
        // Route::prefix('template')->group(function () {
        //     Route::get('{id}/{type}/builder', 'edit');
        //     Route::get('preview/{id}', 'preview');
        //     Route::get('theme/{themeTemplate}', 'themeTemplate');
        // });
    });

    /**
     * * Images
     */
    Route::controller(ImagesController::class)->group(function () {
        Route::prefix('images')->group(function () {
            Route::get('/all', 'index');
            Route::post('/upload', 'store');
            Route::delete('/delete/{image}', 'delete');
        });
    });

    /**
     * * Domain
     */
    Route::controller(DomainController::class)->group(function () {
        Route::prefix('domain')->group(function () {
            Route::get('/settings',  'showSettings');
            Route::get('/on-demand/ssl', 'triggerOnDemandSSL');
            Route::post('/save', 'save');
            Route::post('/check', 'checkDomainAvailability');
            Route::post('/ministore', 'updateSubdomainToBeMiniStoreDomain');
            Route::put('/verify/{domain}', 'verifyCustomDomain');
            Route::put('/{domain}', 'update');
            Route::put('/{domain}/home', 'updateOnlineStoreHome');
            Route::delete('/{domain}', 'delete');
        });
    });

    /**
     * * Application
     */
    Route::controller(ApplicationController::class)->group(function () {
        Route::prefix('apps')->group(function () {
            Route::get('/all', 'getAllApplication');
            Route::post('/mini-store/setup', 'setupMiniStore');
            Route::post('/update/app', 'selectApp');
            Route::post('/update/sale-channel', 'selectSaleChannel');
            Route::get('/update/feature/{type}', 'selectFeature');
        });
    });

    /**
     * * Shipping
     */
    Route::controller(ShippingController::class)->group(function () {
        Route::prefix('shipping')->group(function () {
            Route::get('/settings', 'showShippingSettings');
            Route::get('/settings/check', 'checkShippingSettings');
            Route::get('/detail', 'fetchCountryAndRegion');
            Route::post('/setting/save', 'saveNewZone');
            Route::post('/setting/update', 'saveNewZone');
            Route::delete('/setting/delete/{id}', 'deleteShippingZone');
            Route::post('/setting/deliveryhour', 'saveDeliveryHour');
            Route::post('/setting/storepickup', 'saveStorePickup');
            Route::get('/settings/new', 'createShippingZones')->name('createShippingZone');
            Route::get('/settings/edit/{id}', 'editShippingSetting');
        });

        Route::prefix('easyparcel')->group(function () {
            Route::get('/settings', 'getEasyParcelSettings');
            Route::post('/save', 'storeEasyParcelSettings');
        });
        Route::prefix('lalamove')->group(function () {
            Route::get('/settings', 'getLalamoveSettings');
            Route::post('/save', 'storeLalamoveSettings');
        });

        Route::group(['prefix' => 'onboarding',  'middleware' => 'checkOnBoardingExpressSetup'], function () {
            Route::get('/shippings', 'showShippingMethods');
            Route::get('/shipping/setup/{type?}', 'showShippingSettings');
            Route::get('/shipping/{type?}', 'showShippingSettings');
        });
    });

    /**
     * * Easyparcel
     */
    Route::controller(EasyParcelController::class)->group(function () {
        Route::prefix('easyparcel')->group(function () {
            Route::post('/check/quotation', 'shippingRateChecking');
            Route::post('/check/order-status', 'parcelOrderStatusChecking');
        });
    });

    /**
     * * Lalamove
     */
    Route::controller(LalamoveController::class)->group(function () {
        Route::prefix('lalamove')->group(function () {
            Route::post('/check/quotation', 'quotationChecking');
            Route::get('/check/orders/{lalamoveDeliveryOrder}', 'orderDetailsChecking');
            Route::get('/check/orders/{lalamoveDeliveryOrder}/drivers/{driverId}', 'driverDetailsChecking');
        });
    });

    /**
     * * Delyva
     */
    Route::controller(DelyvaController::class)->group(function () {
        Route::prefix('delyva')->group(function () {
            Route::post('/check/quotation', 'quotationCheck');
            Route::get('/settings', 'getDelyvaSettings');
            Route::post('/orders/details/{reference_key}/fulfill', 'fulfillOrderWithDelyva');
            Route::post('/orders/update', 'orderUpdate');
        });
    });

    /**
     * * Cashback
     */
    Route::controller(CashbackController::class)->group(function () {
        Route::get('/marketing/cashback', 'getCashbackPage');
        Route::prefix('cashback')->group(function () {
            Route::post('/create', 'addCashback');
            Route::post('/update/{id}', 'updateCashback');
            Route::delete('/delete/{id}', 'deleteCashback');
            Route::get('/{refKey}', 'getCashbackSettingPage');
        });
    });

    /**
     * * Popup
     */
    Route::controller(PopupController::class)->group(function () {
        Route::get('/marketing/popups', 'index');
        Route::prefix('popup')->group(function () {
            // Route::get('all', 'index');
            // Route::get('preview/{popupSettings}', 'preview');
            // Route::put('update', 'update');
            Route::post('/store', 'store');
            Route::delete('/{popup}', 'destroy');
        });
    });

    /**
     * * Online Store
     */
    Route::controller(EcommerceController::class)->group(function () {
        Route::prefix('online-store')->group(function () {
            Route::get('/themes', 'showHeaderFooterPage');
            Route::get('/pages', 'showPages');
            Route::put('/editor/save', 'saveSection');
            Route::get('/preferences', 'editPreferences');
            Route::put('/update/preferences', 'updatePreferences');
            Route::put('/update/status/{headerFooter}', 'updateStatus');
            Route::post('/theme', 'createTheme');
            Route::post('/createNewTheme', 'createOnlineStoreTheme');
            Route::post('/createNewSection', 'createNewSection');
            Route::delete('/delete/{headerFooter}', 'deleteHeaderFooter');
        });
    });

    /**
     * * Ecommerce Navigation
     */
    Route::controller(EcommerceNavigationController::class)->group(function () {
        Route::prefix('online-store/menu')->group(function () {
            Route::get('/', 'index');
            Route::get('/{referenceKey}', 'getMenu');
            Route::post('/getAllMenu', 'getAllMenu');
            Route::post('/create', 'createNewMenu');
            Route::post('/update', 'saveMenuList');
            Route::delete('/{nav}', 'destroy');
        });
    });

    /**
     * * Landing
     */
    Route::controller(LandingPageController::class)->group(function () {
        Route::prefix('landing')->group(function () {
            Route::put('update', 'update');
            Route::put('insert/template', 'insertTemplate');
            Route::put('/update/status/{landing}', 'updateStatus');
            Route::post('create', 'create');
            Route::post('/duplicate', 'duplicate');
            Route::post('/create/from/template', 'createFromFunnelTemplate');
            Route::delete('/delete/{landing}', 'delete');
        });
    });

    /**
     * * Sales
     */
    Route::controller(SalesController::class)->group(function () {
        Route::prefix('sales')->group(function () {
            Route::get('/invoices', 'showAllInvoicePage');
            Route::get('/invoices/new', 'showInvoiceDetailPage');
            Route::post('/setting/save', 'saveSalesSetting');
            Route::post('/setting/country/save', 'saveSalesCountrySetting');
            Route::delete('/setting/delete/{id}', 'deleteSalesRegion');
            Route::get('/settings/edit/{id}', 'addNewSales');
            Route::get('/settings/{id}', 'addNewSales');
        });
    });

    /**
     * * Orders
     */
    Route::controller(OrderController::class)->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('/', 'index');
            Route::post('/update', "saveManualOrder");
            Route::post('/update/details', "updateOrderDetail");
            Route::post('/delete/details', "deleteOrderDetail");
            Route::post('/markAsPaid', "saveManualOrder");
            Route::post('/details/fulfillmentUpdate', "updateFulfillment");
            Route::post('/details/fulfillmentCancel', "cancelFulfillment");
            Route::post('/details/makeRefund', "orderRefund");
            Route::post('/details/{reference_key}/easyparcel/fulfill', 'fulfillOrderWithEasyParcel');
            Route::get('/details/{reference_key}/fulfillment/easyparcel', "showFulfillmentWithEasyParcel");
            Route::get('/details/{reference_key}/fulfillment/lalamove', "showFulfillmentWithLalamove");
            Route::get('/details/{reference_key}/fulfillment/delyva', "showFulfillmentWithDelyva");
            Route::get('/details/{reference_key}/fulfillmentstatus', "markOrderStatus");
            Route::post('/details/{reference_key}/lalamove/fulfill', 'fulfillOrderWithLalamove');
            Route::get('/create',  'OrderController@viewCreateOrder');
            Route::get('/details/{reference_key}/edit', "viewCreateOrder");
            Route::get('/details/{reference_key}/makeRefund', "makeRefund");
            Route::post('/details/contact/save', "saveContactInfo");
            Route::post('/details/note/save', "saveNote");
            Route::post('/details/shipping/save', "saveShippingAddress");
            Route::post('/details/billing/save', "saveBillingAddress");
            Route::get('/details/{reference_key}/packingSlip', "printPackingSlip");
            Route::post('/details/markpayment', "markPayment");
            Route::post('/details/archive', "updateAdditionalStatus");
            Route::post('/details/unarchive', "updateAdditionalStatus");
            Route::get('/details/{reference_key}', 'getOrder');
        });
    });

    /**
     * * People
     */
    Route::controller(ProfileController::class)->group(function () {
        Route::prefix('people')->group(function () {
            Route::get('/', 'index');
            Route::get('/profile/{contactRandomId}', 'view');
        });
    });

    /**
     * * Promotion
     */
    Route::controller(PromotionController::class)->group(function () {
        Route::prefix('promotion')->group(function () {
            Route::post('/save', 'savePromotion');
            Route::post('/delete', 'deletePromotion');
            Route::get('/getAllSegments', "getAllSegments");
            Route::get('/getShippingRegion', 'getShippingRegion');
            Route::get('/{type}/edit/{id}', 'addNewPromotion');
            Route::get('/{type}/create/{id}', 'addNewPromotion');
        });
    });

    /**
     * * Product
     */
    Route::controller(UsersProductController::class)->group(function () {
        Route::prefix('product')->group(function () {
            Route::get('/getallproduct', 'getAllProductVariant');
            Route::get('/getallcategory', 'getAllCategories');
            Route::get('/getAllActiveProducts', 'getAllActiveProducts');
        });
    });

    /**
     * * Email
     */
    Route::controller(EmailController::class)->group(function () {
        Route::prefix('emails')->group(function () {
            Route::post('/template/create', 'createTemplateEmail');
            Route::put('/group/{name}', 'createEmailGroup');
            Route::post('/group/assign', 'assignEmailGroup');
            Route::delete('/group/{name}', 'deleteEmailGroup');
        });
    });
    Route::controller(EmailDesignController::class)->group(function () {
        Route::prefix('emails')->group(function () {
            Route::post('/{email}/design/create', 'create');
            Route::put('/design/template/{emailDesign}/update', 'updateTemplateDetails');
            Route::delete('/design/template/{emailDesign}', 'deleteTemplate');
        });
    });

    /**
     * * Tax
     */
    Route::controller(TaxController::class)->group(function () {
        Route::prefix('tax')->group(function () {
            Route::get('/settings', 'showTaxSettings');
            Route::get('/detail', 'fetchCountryAndRegion');
            Route::post('/setting/save', 'saveTaxSetting');
            Route::post('/setting/country/save', 'saveTaxCountrySetting');
            Route::delete('/setting/delete/{id}', 'deleteTaxRegion');
            Route::get('/settings/edit/{id}', 'addNewTax');
            Route::get('/settings/{id}', 'addNewTax');
        });
    });



    /**
     * * Subscription
     */
    Route::controller(SubscriptionController::class)->group(function () {
        Route::prefix('subscription')->group(function () {
            Route::get('/get/yearly/subscription/coupons', 'getYearlySubscriptionCoupons');
            Route::post('/plan/update', 'upgradeSubscriptionPlan')->withoutMiddleware([globalChecker::class]);
            Route::post('/detail/save', 'saveSubscriptionDetail')->withoutMiddleware([globalChecker::class]);
            Route::post('/promoCode/validate', 'validatePromoCode')->withoutMiddleware([globalChecker::class]);
            Route::post('/paymentMethods/change', 'changePaymentMethods');
            Route::post('/cardDetail/save', 'saveCardDetail');
            Route::post('/detail', 'getSubscription');
            Route::get('/retry/invoice', 'getRetryInvoice')->withoutMiddleware([globalChecker::class]);
            Route::post('/save/retry/invoice', 'saveRetryInvoice')->withoutMiddleware([globalChecker::class]);
            Route::delete('/delete/retry/invoice', 'deleteRetryInvoice')->withoutMiddleware([globalChecker::class]);
            Route::get('/terminate', 'terminateSubscription');
        });
    });

    /**
     * * Billing
     */
    Route::controller(BillingSettingController::class)->group(function () {
        Route::prefix('billing')->group(function () {
            Route::get('/setting', 'showBillingSetting');
            Route::post('/setting/save', 'saveBillingSetting');
        });
        Route::prefix('subscription')->group(function () {
            Route::get('/invoices/{refKey}', 'createSubscriptionInvoicePDF');
        });
    });

    /**
     * * Checkout Settings
     */
    Route::controller(CheckoutSettingsController::class)->group(function () {
        Route::prefix('checkout')->group(function () {
            Route::post('/settings/save', 'saveCheckoutSettings');
        });
    });

    /**
     * * Currency
     */
    Route::controller(CurrencyController::class)->group(function () {
        Route::prefix('currency')->group(function () {
            Route::get('/all', 'getAllCurrrecies');
            Route::get('/settings', 'getCurrencySetting');
            Route::post('/setting/save', 'saveCurrencySetting');
            Route::get('/delete/{id}', 'deleteCurrencySetting');
            Route::get('/default', 'getDefaultCurrency');
            Route::post('/rounding/settings/save', 'saveRoundingSettings');
            Route::post('/latest/rate', 'getExchangeRate');
        });
        Route::post('/get/all/currency', 'getAllCurrrency');
    });

    /**
     * * Legal
     */
    Route::controller(LegalSettingsController::class)->group(function () {
        Route::prefix('legal')->group(function () {
            Route::post('/setting/save', 'saveLegalSetting');
        });
    });

    /**
     * * Location
     */
    Route::controller(LocationController::class)->group(function () {
        Route::prefix('location')->group(function () {
            Route::get('/settings', 'index');
            Route::post('/setting/save', 'store');
        });
    });

    /**
     * * Payment Settings
     */
    Route::controller(PaymentSettingController::class)->group(function () {
        Route::prefix('/payment/settings')->group(function () {
            Route::get('/', 'getPaymentSetting')->middleware('can:edit-payment,"edit-payment"');
            Route::post('/save', 'savePaymentSetting');
            Route::get('/{urlId}/{type}', 'addPaymentSettings');
        });

        Route::group(['prefix' => 'onboarding',  'middleware' => 'checkOnBoardingExpressSetup'], function () {
            Route::get('/payments',  'getPaymentSetting')->middleware('can:edit-payment,"edit-payment"');
            Route::get('/payment/setup/{urlId}/{type}', 'addPaymentSettings');
        });
    });

    /**
     * * Profile
     */
    Route::controller(MyProfileController::class)->group(function () {
        Route::prefix('profile')->group(function () {
            Route::post('/setting/save', 'saveData');
            Route::post('/password/change', 'changePassword');
        });
    });

    /**
     * * Account
     */
    Route::controller(AccountController::class)->group(function () {
        Route::prefix('account')->group(function () {
            Route::get('/setting', 'accountSettings');
            Route::get('/{account}/timezone', 'getAccountTimezone');
            Route::put('/timezone/update', 'updateTimezone');
            Route::put('/affiliate', 'updateEmailAffiliateSetting');
            Route::put('/setting/company/update', 'updateCompanyProfile');
            Route::put('/{account}/educated', 'updateEducationalStatus');
            Route::post('/details', 'saveAccountDetails');
        });
    });

    /**
     * * Funnel
     */
    Route::controller(FunnelController::class)->group(function () {
        Route::get('/funnels', 'index');
        Route::prefix('funnel')->group(function () {
            Route::get('/{referenceKey}', 'show');
            Route::put('/{funnelId}/update', 'update');
            Route::post('/create', 'create');
            Route::post('/reorder/landing', 'reorderLandingPages');
            Route::post('/duplicate/{funnel}', 'duplicate');
            Route::post('/shared/save', 'saveSharedFunnel');
            Route::delete('/{funnel}', 'delete');
        });
    });

    /**
     * * Referral Marketing
     */
    Route::controller(ReferralCampaignController::class)->group(function () {
        Route::prefix('referral')->group(function () {
            Route::get('campaigns', 'index');
            Route::get('campaigns/{ref}', 'show');
            Route::put('campaigns/update', 'update');
            Route::post('campaigns/create', 'store');
            Route::put('/status/{id}', 'status');
            Route::delete('/delete/{id}', 'destroy');
            Route::get('/fetch', 'fetch');
            Route::put('/campaign/emails/send-test', 'sendTestEmail');
            Route::post('/campaigns/select-winner/{uuid}', 'sweepStake');
            Route::post('/campaigns/participants/export', 'export');
        });
    });

    Route::controller(FormDataController::class)->group(function () {
        Route::get('/people/forms', 'index');
        Route::prefix('form')->group(function () {
            Route::get('/{form:reference_key}', 'show');
            Route::get('/{form:reference_key}/download', 'download');
            Route::put('/{form}/rename', 'rename');
            Route::delete('/{form}', 'delete');
            Route::delete('/{form:reference_key}/empty', 'deleteEmptyForm');
        });
        Route::delete('/form-content/{refKey}', 'deleteFormContent');
    });

    /**
     * * Integration Setting
     */
    // Route::controller(IntegrationController::class)->group(function () {
    //     Route::prefix('integration')->group(function () {
    //         Route::get('/setting', 'settings');
    //         Route::get('/setting/{type}', 'getIntegrationSettings');
    //     });
    //     Route::get('/get/available/integration', 'getAllIntegration');
    //     Route::get('/get/available/webinar/{refKey}', 'getAvailableWebinar');
    //     Route::post('/get/webinar/session', 'getWebinarSession');
    //     Route::post('/save/integration/settings', 'saveIntegrationSettings');
    //     Route::delete('/integration/setting/disconnect/{id}', 'disconnect');
    //     Route::delete('/delete/integration/{refKey}', 'deleteIntegrationSettings');
    // });

    /**
     * * Product Subscription
     */
    // Route::controller(ProductSubscriptionController::class)->group(function () {
    //     Route::get('/product/subscription/{id}', 'getProductSubscription');
    //     Route::get('delete/subscription/{id}', 'deleteProductSubscription');
    //     // Route::get('/product/subscription/dashboard', "showProductSubscriptionDashboard");
    //     Route::get('/product-subscription', "showProductSubscriptionDashboard");
    //     Route::post('/save/product-subscription/setting', "saveProductSubscriptionSetting");
    //     Route::get('/settings/product-subscription', 'getProductSubscriptionSetting');
    // });

    //*************************************Subscription**************************************** */

    Route::group(['middleware' => ['checkSubscription']], function () {
        //Subscription Related to Sign Up
        Route::get('/subscription/plan/create', 'SubscriptionController@viewSubPlanSelect')->withoutMiddleware([globalChecker::class]);
        Route::get('/subscription/form/create', 'SubscriptionController@viewSubFormDetail')->withoutMiddleware([globalChecker::class]);
    });

    Route::get('/subscription/plan/{type}', 'SubscriptionController@viewUpgradePlanSelect')->withoutMiddleware([globalChecker::class]);
    Route::post('/createSubscription', 'SubscriptionController@makeRecurringPayment')->withoutMiddleware([globalChecker::class]);
    Route::post('/retryInvoice', 'SubscriptionController@retryInvoiceWithNewPaymentMethod')->withoutMiddleware([globalChecker::class]);
    Route::get('/subscription/promo/eligibility', 'SubscriptionController@checkNewUserPromoEligibility');

    //*************************************Subscription**************************************** */

    //*************************************roles**************************************** */
    // Roles
    Route::get('/settings/role', 'RoleInvitationEmailController@getRoleInvitationPage')->middleware('can:assign-role,"assign-role"');
    Route::post('/role/send-invitation', 'RoleInvitationEmailController@sendInvitation');
    Route::post('/admin/changeAccount/{id}', function ($id) {
        Auth::user()->changeCurrentAccount($id);
        return redirect('/');
    });

    //*************************************roles**************************************** */

    //*************************************ONBOARDING PROCESS**************************************** */
    Route::get('/confirm/email', 'AccountController@emailConfirmation');
    Route::post('/account', ['uses' => 'AccountController@saveUserName']);
    // TODO: use better route naming (and controller method name)
    Route::post('/api/password', ['uses' => 'AccountController@saveApiAndPassword']);
    Route::get('/onboarding/loading/{accountId}', 'AccountController@loadingToDashboard');
    Route::post('/createShop', ['uses' => 'AccountController@afterVerifyCreateShop']);
    Route::post('/onboardingLoadingData', ['uses' => 'AccountController@onboardingLoadingData']);
    Route::put('/send/onboarding_email', 'AccountController@sendOnboardingEmail');

    Route::post('/onboarding/boarded', ['uses' => 'AccountController@boarded']);

    Route::middleware('CheckOnBoardingEmail')->group(function () {
        Route::get('/onboarding', 'AccountController@companyName');
    });
    Route::group(['middleware' => ['checkFirstName']], function () {
        Route::get('/onboarding/company', 'AccountController@companyName');
    });
    Route::group(['middleware' => ['checkOnBoarding']], function () {
        Route::get('/onboarding/salechannels', 'AccountController@saleChannelType');
    });
    Route::group(['middleware' => ['checkOnBoardingExpressSetup']], function () {
        Route::get('/onboarding/mini-store/setup', 'MiniStoreController@getMiniStoreInfo');
        Route::get('/onboarding/themes', 'TemplateController@getThemes');
        Route::get('/onboarding/product/{reference_key}', "UsersProductController@getProduct");
        Route::get('/onboarding/save', function () {
            return Inertia::render('onboarding/pages/Save');
        });
    });
    //to do: middleware for it
    Route::get('/onboarding/welcome', 'OnboardingController@onboardingWelcome');
    Route::group(['middleware' => ['checkApi']], function () {
        Route::get('/dashboard', 'AccountController@dashboard');
    });
    /************************************* END ONBOARDING PROCESS**************************************** */


    /*************************************PEOPLE PAGE***************************************************** */
    Route::get('/contacts', 'ProcessedContactController@allContacts');
    Route::get('/paginated/contacts', 'ProcessedContactController@getPaginatedContacts');
    Route::post('/people/bulkdelete', 'ProcessedContactController@bulkDelete');

    Route::post('/people/profile/{processedContact}/notes', 'NoteController@store');
    Route::put('/people/profile/{processedContact}/notes/{note}', 'NoteController@update');
    Route::delete('/people/profile/{processedContact}/notes/{note}', 'NoteController@destroy');

    // update address details
    Route::put('/people/profile/address', 'ProcessedAddressController@update');

    // update contact details
    Route::put('/people/profile/{processedContact}', 'ProcessedContactController@update');
    Route::post('/people/profile/addContact', 'ProcessedContactController@addContact');

    // people custom field related
    Route::get('/people/custom-fields', 'PeopleCustomFieldController@customField');
    Route::post('/people/profile/{processedContact}/customField', 'PeopleCustomFieldController@store');
    Route::post('/people/profile/newCustomField', 'PeopleCustomFieldController@addCustomFieldName');
    Route::put('/people/profile/{processedContact}/customFields', 'PeopleCustomFieldController@syncContactCustomFields');
    Route::post('/people/profile/deleteCustomField', 'PeopleCustomFieldController@deleteCustomField');
    Route::delete('/people/profile/customField/{customField}', 'PeopleCustomFieldController@destroy');
    Route::post('/people/profile/renameCustomField', 'PeopleCustomFieldController@rename');

    // people segments
    Route::get('/people/profile/{contactRandomId}/segments', 'SegmentController@contactSegmentNames');

    // filter people contacts (people & view segment page)
    Route::post('/people/filter', 'ProcessedContactController@filterContacts');
    /************************************* END PEOPLE PAGE**************************************** */


    //************************************* SEGMENT **************************************** */
    Route::get('/people/segments', 'SegmentController@index');
    Route::post('/segments', 'SegmentController@store');
    Route::get('/segments/{reference_key}', 'SegmentController@show');
    Route::delete('/segments/{id}', 'SegmentController@destroy');
    Route::put('/segments/{segment}', 'SegmentController@update');
    Route::put('/segments/{segment}/rename', 'SegmentController@renameSegment');
    Route::put('/segments/{segment}/synchronize', 'SegmentController@syncSocialMedia');
    //*************************************END SEGMENT PAGE **************************************** */


    //************************************* MY PROFILE AND ACCOUNT SETTING **************************************** */
    Route::get('/myprofile', 'MyProfileController@UserInfo');
    Route::get('/company/setting', 'MyProfileController@CompanyInfo');
    Route::get('/oauth/stripe/check', 'PaymentSettingController@checkStripeOuthConnection');
    Route::post('/footerupdate', 'AccountController@emailFooterSetting');
    Route::delete('/account/removeUser/{id}/{email}', 'AccountController@removeUser');

    //*************************************END MY PROFILE AND ACCOUNT SETTING **************************************** */

    //************************************* AFFILIATE MEMBER ****************************************************** */
    Route::get('/affiliate/members', 'AffiliateMemberController@allMembers');
    Route::get('/affiliate/members/commissions', 'AffiliateMemberController@allCommissions');
    Route::get('/affiliate/members/payouts', 'AffiliateMemberController@allPayouts');
    Route::put('/affiliate/members/participant', 'AffiliateMemberController@updateParticipant');
    Route::put('/affiliate/members/participant/promo', 'AffiliateMemberController@assignPromoCode');
    Route::put('/affiliate/members/participant/groups', 'AffiliateMemberController@addGroupsToParticipants');
    Route::delete('/affiliate/members/participant/groups', 'AffiliateMemberController@removeGroupsFromParticipants');
    Route::put('/affiliate/members/commission', 'AffiliateMemberController@updateCommission');
    Route::put('/affiliate/members/bulk/commissions', 'AffiliateMemberController@bulkUpdateCommissions');
    Route::put('/affiliate/members/commission/payout/{payout}', 'AffiliateMemberController@updateCommissionPayout');
    Route::get('/affiliate/members/groups', 'AffiliateMemberController@allGroups');
    Route::post('/affiliate/members/groups', 'AffiliateMemberController@createGroup');
    Route::put('/affiliate/members/groups/{group}', 'AffiliateMemberController@updateGroup');
    Route::delete('/affiliate/members/groups/{group}', 'AffiliateMemberController@destroyMemberGroup');

    // new v2 am
    Route::get('/affiliate/members/campaigns', 'AffiliateMemberController@allCampaigns');
    Route::post('/affiliate/members/campaigns', 'AffiliateMemberController@storeAffiliateMemberCampaign');
    Route::get('/affiliate/members/campaigns/create', 'AffiliateMemberController@showCreateCampaignPage');
    Route::get('/affiliate/members/campaigns/{reference_key}', 'AffiliateMemberController@editAffiliateMemberCampaign');
    Route::put('/affiliate/members/campaigns/{reference_key}', 'AffiliateMemberController@updateAffiliateMemberCampaign');
    Route::delete('/affiliate/members/campaigns/{campaign}', 'AffiliateMemberController@destroyCampaign');
    Route::get('/affiliate/members/conditions/types/values', 'AffiliateMemberController@getConditionTypeValues');


    Route::put('/affiliate/member/profile', 'AffiliateMemberController@updateMemberProfile');
    Route::get('/affiliate/members/{ref}', 'AffiliateMemberController@showAffiliateMemberProfile');
    //************************************* END AFFILIATE MEMBER ****************************************************** */


    //************************************* REPORT ****************************************************** */
    Route::get('/emails/report', 'EmailReportController@index');
    Route::get('/emails/{email}/report', 'EmailReportController@show');
    //************************************* END REPORT ****************************************************** */


    //************************************* TAG ****************************************************** */
    Route::get('/people/tags', 'TagsController@tags');
    Route::post('/newtag', ['uses' => 'TagsController@createTag']);
    Route::post('/rename/tag', ['uses' => 'TagsController@renameTag']);
    Route::post('/refresh/option', ['uses' => 'TagsController@refreshOption']);
    Route::post('/delete/tag', ['uses' => 'TagsController@deleteTag']);
    Route::post('/merge/tag', ['uses' => 'TagsController@mergeTag']);
    Route::post('/check/merge/tag', ['uses' => 'TagsController@checkMergeName']);
    Route::post('/add/tag', ['uses' => 'TagsController@addTag']);
    Route::post('/remove/tag', ['uses' => 'TagsController@removeTag']);
    Route::post('/selectTag', 'TagsController@selectTag');
    Route::post('/deleteSelectTag', 'TagsController@deleteSelectedTag');
    //************************************* END TAG ****************************************************** */

    //************************************* STORE CREDIT ****************************************************** */
    Route::post('/people/addStoreCredit', 'StoreCreditController@addStoreCredit');
    // Route::post('/people/checkExpiredCredits', 'StoreCreditController@checkExpiredCredits');
    //************************************* END STORE CREDIT ****************************************************** */

    // View people profile
    Route::delete('/people/{peopleId}/delete', 'ProcessedContactController@deletePeople');

    //******************************* EMAILS ******************************* */
    // emails general
    Route::put('/emails/{email}', 'EmailController@update');
    Route::put('/emails/{email}/name', 'EmailController@updateName');

    // emails
    Route::get('/emails', 'EmailController@index');
    Route::post('/emails/standard/create', 'EmailController@createStandardEmail');
    Route::post('/emails/automation', 'EmailController@createOrUpdateAutomationEmail');
    Route::get('/emails/standard/{email}/edit', 'EmailController@editStandardEmail');
    Route::delete('/emails/{email}/delete', 'EmailController@deleteEmail');
    Route::put('/emails/standard/{email}/send', 'SendEmailController@sendStandardEmail');
    Route::put('/emails/standard/{email}/send-test', 'SendEmailController@sendTestStandardEmail');
    Route::post('/emails/{email}/duplicate', 'EmailController@duplicateEmail');

    // email design (template)
    Route::get('/emails/design', 'EmailDesignController@index');
    Route::get('/emails/design/template/{emailDesign}/preview', 'EmailDesignController@previewTemplate');
    Route::post('/emails/design/template/create', 'EmailDesignController@createTemplate');

    // email design (builder)
    Route::get('/emails/{email}/design/{emailDesign}/edit', 'EmailDesignController@edit');
    Route::get('/emails/{email}/design/{emailDesign?}', 'EmailDesignController@show');
    Route::post('/emails/{email}/design/{emailDesign}', 'EmailDesignController@update');
    Route::get('/emails/{email}/design/{emailDesign}/preview', 'EmailDesignController@preview');
    Route::post('/emails/{email}/design/{emailDesign}/preview/session', 'EmailDesignController@putSessionForPreview');

    // email setup actions
    Route::post('/emails/sender/verify', 'EmailController@verifySenderDomain');
    Route::post('/emails/{email}/setup', 'EmailController@saveSetup');
    Route::post('/emails/{email}/sender', 'EmailController@saveSender');
    Route::post('/emails/{email}/segment', 'EmailController@saveSegment');
    Route::post('/emails/{email}/subject-preview', 'EmailController@saveSubjectPreview');
    Route::put('/emails/{email}/schedule', 'EmailController@updateSchedule');
    Route::put('/emails/{email}/cancel-schedule', 'EmailController@cancelSchedule');
    Route::post('/emails/{email}/rename', 'EmailController@renameEmail');

    // email settings
    Route::get('/emails/settings', 'EmailController@showSettings');
    Route::post('/emails/add-suppression', 'EmailController@addSuppressionEmail');
    Route::delete('/emails/suppression/{id}/{type}', 'EmailController@deleteSuppressionEmail');

    // redirect page after AWS email/domain verification
    Route::group(['prefix' => '/emails/verification'], function () {
        Route::get('success', 'SesMailsVerificationController@verificationSuccess');
        Route::get('failed', 'SesMailsVerificationController@verificationFailed');
    });
    //******************************* END EMAILS ******************************* */

    //******************************* SENDERS ******************************* */
    Route::get('/senders', 'SenderController@currentAccountSenders');
    Route::get('/senders/verified', 'SenderController@getVerifiedIdentity');
    Route::get('/senders/check', 'SenderController@checkSenderDomain');
    Route::get('/senders/check/ses', 'SenderController@checkSenderDomainOnSes');
    Route::post('/senders/verify', 'SenderController@sendHyperVerificationEmail');
    Route::get('/senders/verify/success', 'SenderController@verificationSuccess');
    Route::get('/senders/verify/{account}/{crypt}', 'SenderController@verifySenderDomain')->name('sender.verify');
    Route::get('/senders/{sender}/refresh', 'SenderController@refreshSenderStatus');
    Route::delete('/senders/{id}', 'SenderController@destroy');
    //******************************* END SENDERS ******************************* */

    //******************************* AUTOMATIONS ******************************* */
    Route::get('/automations', 'AutomationController@index');
    Route::post('/automations', 'AutomationController@create');
    Route::get('/automations/{reference_key}', 'AutomationController@show');
    Route::put('/automations/{reference_key}', 'AutomationController@update');
    Route::put('/automations/status/{reference_key}', 'AutomationController@updateStatus');
    Route::delete('/automations/{id}', 'AutomationController@destroy');

    // triggers
    Route::post('/automations/{reference_key}/triggers', 'AutomationTriggerController@store');
    Route::put('/automations/{reference_key}/triggers/{automationTrigger}', 'AutomationTriggerController@update');
    Route::delete('/automations/{reference_key}/triggers/{automationTrigger}', 'AutomationTriggerController@destroy');

    // steps
    Route::post('/automations/{reference_key}/steps', 'AutomationStepController@store');
    Route::post('/automations/{reference_key}/steps/delete', 'AutomationStepController@destroy');
    Route::put('/automations/{reference_key}/steps', 'AutomationStepController@update');
    //******************************* END AUTOMATIONS ******************************* */

    /**
     * * Report
     */
    Route::controller(ReportsController::class)->group(function () {
        Route::prefix('report')->group(function () {
            Route::get('/product', 'productReport');
            Route::get('/funnel', 'funnelReport');
            Route::get('/funnel/{funnel:reference_key}', 'funnelReportIndividual');
        });
    });

    Route::get('/reports/get/{funnel_id}', 'ReportsController@getFunnelReportData');
    Route::get('/report/sales', 'ReportsController@salesReportView');
    Route::get('/report/growth', 'ReportsController@growthReportPreview');
    Route::get('/report/segment', 'SegmentReportController@index');
    Route::get('/report/sales-channel', 'ReportsController@salesChannelReportView');
    Route::get('/report/referral', 'ReportsController@referralReport');
    Route::get('/report/referral/{refKey}', 'ReportsController@referralReportView');
    Route::get('/report/affiliate', 'ReportsController@affiliateReport');

    // some extra routes to get segment report data asynchronously
    Route::post('/report/segment/contacts', 'SegmentReportController@getSegmentContacts');

    //************************************************ Marketing ************************************************ */
    // Route::view('/marketing/social-proof', '/marketing/socialProofDashboard');
    Route::post('/create/notification', 'SocialProofController@createNotification');
    Route::get('/marketing/social-proof', 'SocialProofController@getAllNotification');
    Route::get('/notification/edit/{referenceKey}', 'SocialProofController@getSelectedNotification');
    Route::post('notification/get-reference-key/{id}', 'SocialProofController@getReferenceKey');
    Route::post('/notification/save-edit/{referenceKey}', 'SocialProofController@update');
    Route::post('/social-proof-save-global-setting/save-edit', 'SocialProofController@editSocialProofGlobalSetting');
    Route::delete('/notification/delete/{id}', 'SocialProofController@deleteNotification');
    Route::post('/notification/enable/{id}', 'SocialProofController@enableNotification');

    //************************************************ Product Recommendation ************************************************ */
    Route::get('/marketing/product-recommendation', 'ProductRecommendationController@index');
    Route::post('product-recommendation/status/update', 'ProductRecommendationController@update');

    Route::post('/import_parse', 'ImportController@parseImport')->name('import_parse');
    Route::get('/import_fields', 'ImportController@showImportFields');

    Route::get('/import_tag', 'ImportController@showTag');
    Route::post('/saveImportTag', 'ImportController@tagImportedContacts');
    // Route::post('/import_process','ImportController@processImport')->name('import_process');

    //************************************************ Import c ************************************************ */
    Route::post('/customFieldName', 'ImportController@checkDuplicateNameAndDataType');
    Route::post('/import/contacts', 'ImportController@saveImportContacts');

    //************************************ PRODUCTS ******************************* */

    Route::post('/addProduct', "UsersProductController@addProduct");
    Route::post('/deleteProduct', "UsersProductController@deleteProduct");
    Route::get('/product/duplicate/{refKey}', "UsersProductController@duplicateProduct");
    Route::get('/product/inventory', "UsersProductController@getProductInventory");
    Route::post('/save/product/inventory', "UsersProductController@saveProductInventory");

    //product category
    Route::get('/product/category', "CategoryController@getProductCategoryPage");
    Route::post('/product/category/add', "CategoryController@addCategory");
    Route::post('/bulk-products/category/{type}', "CategoryController@productsUpdateCategory");
    Route::post('/product/category/update/{id}', "CategoryController@updateCategory");
    Route::post('/product/edit-category-priority', "CategoryController@updateCategoryPriority");
    Route::delete('/product/category/delete/{id}', "CategoryController@deleteCategory");

    //product option
    Route::get('/product/customizations', "ProductOptionController@getAllProductOption");
    Route::get('/get/product/option', "ProductOptionController@getProductOption");
    Route::get('/product/option/{type}', "ProductOptionController@addProductOption");
    Route::post('/product/edit-customization-priority', "ProductOptionController@updateCustomizationPriority");
    Route::post('/product/option/save', "ProductOptionController@saveSharedProductOption");
    Route::post('/bulk-products/option/{type}', "ProductOptionController@productsUpdateOption");
    Route::get('/delete/option/{id}', "ProductOptionController@deleteSharedProductOption");
    Route::get('/get/exists/{type}', "ProductOptionController@getExistsOption");

    //product review
    Route::get('/product/reviews', "ProductReviewController@getProductReviews");
    Route::post('/changeReviewStatus/{id}', "ProductReviewController@changeReviewStatus");
    Route::post('/deleteReview/{id}', "ProductReviewController@deleteProductReview");

    //product badge
    Route::get('/product/badges', "ProductBadgeController@getProductBadges");
    Route::get('/product/badges/{urlId}', "ProductBadgeController@addProductBadgePage");
    Route::post('/product/add-product-badge', "ProductBadgeController@addProductBadge");
    Route::post('/product/edit-product-badge/{urlId}', "ProductBadgeController@editProductBadge");
    Route::post('/product/edit-badge-priority', "ProductBadgeController@editBadgePriority");
    Route::delete('/product-badge/delete/{id}', 'ProductBadgeController@deleteProductBadge');
    // Route::post('/deleteReview/{id}', "ProductReviewController@deleteProductReview");

    Route::get('/product/{reference_key}', "UsersProductController@getProduct");

    //************************************ END PRODUCTS ******************************* */

    //************************************** MINI STORE ************************************* */
    Route::get('/mini-store/setup', 'MiniStoreController@getMiniStoreInfo');
    Route::get('/mini-store/elements', 'MiniStoreController@getMiniStoreElements');
    Route::post('/mini-store/save-edit', 'MiniStoreController@editMiniStore');
    Route::post('/mini-store-checklist-header/save-edit', 'MiniStoreController@updateMiniStoreChecklistHeader');

    //************************************** END MINI STORE ************************************ */

    //************************************** DELYVA SERVICE ************************************* */
    Route::get('/delyva/setup', 'DelyvaController@getDelyvaApiInfo');
    Route::post('/delyva/save-edit', 'DelyvaController@editDelyvaApiInfo');

    //************************************** END DELYVA SERVICE ************************************ */

    //**************************************  Application ************************************* */
    Route::post('/sale-channel/save', 'ApplicationController@saveSaleChannel');
    //************************************** END Application ************************************ */

    //**************************************  Facebook Pixel ************************************* */
    Route::get('/facebook/setting', 'FacebookController@facebookPixelSetting');
    Route::post('/facebook/setting', 'FacebookController@facebookPixelSaveSetting');

    //************************************** END Facebook Pixel ************************************ */

    Route::get('/products', "UsersProductController@getProductDashboardPage");
    Route::get('/preview_template/{sectionType}/{encodedId}', 'TemplateController@previewTemplate');

    //* Templates
    Route::post('/user/template/save', 'TemplateController@saveUserTemplate');

    //************************************************ Order Control ************************************************ */

    // DEPRECATED
    Route::post('/order/details/{reference_key}/tracking/easyparcel', "OrderController@saveEasyParcelTracking");

    //*************************************SETTINGS**************************************** */
    Route::get('/settings/all',  'SettingController@index');
    Route::get('/settings/checkout',  'AccountController@getCheckoutSetting');
    Route::get('/settings/user', 'AccountController@getUserSettingPage')->middleware('can:assign-role,"assign-role"');
    Route::get('/settings/affiliate', 'AffiliateMemberController@showAffiliateSettings');
    Route::put('/settings/affiliate', 'AffiliateMemberController@updateAffiliateSettings');

    // social proof settings
    Route::get('/settings/social-proof', 'SocialProofController@getSocialProofNotificationSetting');
    //notification setting
    Route::get('/settings/notification', 'NotificationController@index');
    Route::post('/settings/notification/save', 'NotificationController@saveOrderEmailNotification');
    Route::post('/settings/notification/email/save', 'NotificationController@saveOrderNotificationEmail');
    // product review settings
    Route::get('/settings/product-review', 'ProductReviewController@getProductReviewSetting');
    Route::post('settings/product-review/edit', 'ProductReviewController@editProductReviewSetting');
    Route::post('settings/review-appearance/edit', 'ProductReviewController@editProductReviewAppearance');
    // checkout settings
    Route::get('/settings/checkout', 'CheckoutSettingsController@getCheckoutSetting');
    // legal settings
    Route::get('/settings/legal', 'LegalSettingsController@getLegalSetting');
    Route::get('/settings/legal/{type}', 'LegalSettingsController@getEditLegalSetting');
});

//************************************************ Shipping Control ************************************************ */

Route::get('/shipping/setting/deliverypickup', 'ShippingController@getDeliveryPickup');
Route::get('/shipping/form/{type}', 'ShippingController@showShippingSettingsForm');
Route::get('/apps/{type}', 'ShippingController@showShippingSetupForm');

Route::post('get-states-by-country', 'ShippingController@getState');



// Route::post('/shipping/setting')


//************************************************ Tax Control ************************************************ */
Route::post('/get/tax/settings', 'TaxController@checkTaxSetting');


/**
 * * Master Admin
 */
Route::group([
    'prefix' => '/master-admin',
    'as' => 'masterAdmin.',
    'namespace' => 'MasterAdmin',
], function () {
    Route::get('/', function () {
        return redirect(Auth::check() ? '/dashboard' : '/login');
    });

    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\LoginController@login');

    Route::middleware('auth:masterAdmin')->group(function () {
        Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

        Route::group([
            'prefix' => '/tables',
            'as' => 'table.',
        ], function () {
            Route::get('/{tableName}', 'TableController@showTable');
            Route::get('/{tableName}/add-record', 'TableController@showAddRecordForm');
            Route::post('/{tableName}/add-record/add', 'TableController@addRecord');
            Route::get('/{tableName}/record/{recordID}/edit-record', 'TableController@showEditRecordForm');
            Route::post('/{tableName}/record/{recordID}/edit-record/edit', 'TableController@editRecord');
        });
    });
});

/**
 * * Affliate Program for Hypershapes Subscription
 */
$domainURL = (app()->environment('local')) ? 'hypershapes.test' : ((app()->environment('staging')) ? 'salesmultiplier.asia' : 'hypershapes.com');
// dd($domainURL);
$subdomain = 'affiliate.' . $domainURL;

Route::domain($subdomain)->group(function () {
    Route::namespace('AffiliateAuth')->group(function () {
        //controller within the App\Http\Controller\AffiliateAuth namespace

        //GET ROUTE
        Route::redirect('/', '/dashboard');
        Route::get('login', 'AffiliateLoginController@showLoginForm')->name('affiliate.login');
        Route::get('/verify', 'AffiliateRegisterController@verify')->name('affiliate.verify');
        Route::get('/affiliate/verify', 'AffiliateRegisterController@verifyUser')->name('affiliate.verify.user');
        Route::get('register', 'AffiliateRegisterController@showRegisterForm');
        Route::get('forgot-password', 'AffiliateForgotController@showLinkRequestForm')->name('affiliate.forgot.password');
        Route::get('password/reset/{token}', 'AffiliateResetPasswordController@showResetForm')->name('affiliate.password.reset');

        //POST ROUTE
        Route::post('register', 'AffiliateRegisterController@register')->name('affiliate.register.submit');
        Route::post('login', 'AffiliateLoginController@login')->name('affiliate.login.submit');
        Route::get('logout', 'AffiliateLoginController@logout')->name('affiliate.logout');
        Route::post('forget/email', 'AffiliateForgotController@sendResetLinkEmail')->name('affiliate.password.email');
        Route::post('reset-password', 'AffiliateResetPasswordController@reset')->name('affiliate.password.reset.submit');
    });

    Route::group(['middleware' => ['AffiliateAuthentication']], function () {
        Route::get('dashboard', 'AffiliateController@viewDashboard')->name('affiliate.dashboard');
        Route::get('profile', 'AffiliateController@viewProfile')->name('affiliate.profile');
        Route::get('payouts', 'AffiliateController@viewPayouts')->name('affiliate.payment');
        Route::get('getCommissionDetail', 'AffiliateController@getCommissionDetail');
        Route::post('/profile/update', 'AffiliateController@updateProfileSetting');
        Route::post('/payout/update', 'AffiliateController@updatePayoutSetting');
        Route::post('/password/update', 'AffiliateController@updatePassword');
    });

    Route::get('/confirm/email', 'AffiliateController@emailConfirmation')->middleware('AffiliateCheckVerified');
    Route::post('/email/reset', 'AffiliateController@resendConfirmation');
});

/**
 * * Publish funnel, online store and mini store
 */
Route::group([
    'domain' => '{domain}',
    'middleware' => ['validateDomain', 'affiliateMemberStoreLastCookie', 'referralCampaignStoreLastCookie']
], function () {
    //* For published default sales channel page
    Route::get('/', 'BuilderController@showPublishPage');

    //* To generate sitemap
    Route::get('/sitemap.xml', 'BuilderController@generateSitemapIndex');
    Route::get('/sitemap_products.xml', 'BuilderController@generateSitemap');
    Route::get('/sitemap_categories.xml', 'BuilderController@generateSitemap');
    Route::get('/sitemap_pages.xml', 'BuilderController@generateSitemap');

    /**
     * * Builder - Form Element
     */
    Route::controller(FormDataController::class)->group(function () {
        Route::prefix('/form')->group(function () {
            Route::post('/check/exist', 'create');
            Route::post('/submit', "submit");
        });
    });

    Route::get('/categories/{category}/products/{path}', 'onlineStoreController@getProductInfoForDescription');
    Route::get('/products/all', "onlineStoreController@showProductList");
    Route::get('/categories/{path}', "onlineStoreController@showProductList");
    Route::get('/products/{path}', 'onlineStoreController@getProductInfoForDescription');
    Route::get('/shopping/cart', 'onlineStoreController@shoppingCart');

    //************************************************ Online Store (dashboard) ************************************************ */

    Route::get('/get/customer/detail', 'OnlineStoreCheckoutController@getCustomerDetail');

    // Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('onlineStoreApi', 'isCustomerAccountDisabled')->group(function () {
        // Route::get('/dashboard','EcommerceDashboardController@showDashboard')->middleware('checkReturnUrl');
        //account settings
        Route::get('/account/settings', 'EcommerceSettingsController@showAccountSettings');
        Route::post('/save/user/name', 'EcommerceSettingsController@saveUsername');
        Route::post('/save/password', 'EcommerceSettingsController@savePassword');
        //order
        Route::get('/orders/dashboard', 'EcommerceOrderController@showOrderDashboard');
        Route::get('/order/{refKey}', 'EcommerceOrderController@showOrders');
        //address book
        Route::get('/address/book', 'EcommerceAddressBookController@showAddressBook');
        Route::post('/save/address', 'EcommerceAddressBookController@saveAddressBook');
        //subscription
        Route::get('/subscriptions/dashboard', 'EcommerceSubscriptionController@showSubscriptionDashboard');
        //store credit
        Route::get('/store-credit', 'StoreCreditController@getCustomerStoreCreditPage');

        // affiliate member domain
        Route::get('/affiliates/domain', 'AffiliateMemberDashboardController@getAffiliateDomain');
        Route::get('/referral', 'EcommerceReferralController@index');
    });
    // });
    //get customer detail
    //************************************************ Online Store (subscription) ************************************************ */
    Route::post('/create/customer', 'OnlineStoreCheckoutController@createStripeCustomer');
    Route::post('/create/product/subscription', 'OnlineStoreCheckoutController@createProductSubscription');
    Route::get('/delete/product/subscription/{id}', 'OnlineStoreCheckoutController@deleteProductSubscription');
    Route::post('/retry/product/invoice', 'OnlineStoreCheckoutController@retryProductInvoice');
    Route::post('/product/subscrption/webhook', 'OnlineStoreCheckoutController@productSubscriptionWebhook');

    Route::group(['middleware' => ['validateAffiliateMemberDashboardDomain'], 'namespace' => 'AffiliateMemberAuth'], function () {
        Route::get('/affiliates/signup', 'RegisterController@showRegistrationForm');
        Route::post('/affiliates/signup', 'RegisterController@register');

        Route::get('/affiliates/login', 'LoginController@showLoginForm');
        Route::post('/affiliates/login', 'LoginController@login');

        Route::get('/affiliates/forget', 'ForgotPasswordController@showResetForm');
        Route::post('/affiliates/forget/email', 'ForgotPasswordController@sendResetLinkEmail');

        Route::get('/affiliates/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('affiliateMember.password.reset');
        Route::post('/affiliates/password/reset', 'ResetPasswordController@reset')->name('affiliateMember.password.reset.submit');
    });
    // this link is called on external store site, so it doesn't need that validateAffiliateMemberDashboardDomain'
    Route::put('/affiliates/visitor', 'AffiliateMemberController@updateUniqueVisitor');

    Route::middleware('validateAffiliateMemberDashboardDomain')->name('affiliateMember.')->group(function () {
        Route::namespace('AffiliateMemberAuth')->group(static function () {
            Route::get('/affiliates/logout', 'LoginController@logout');

            Route::get('/affiliates/confirm/email', 'VerificationController@emailConfirmation');
            Route::get('/affiliates/email/resend', 'VerificationController@resend');
            Route::get('/affiliates/email/verify', 'VerificationController@verify')->name('affiliates.member.verify');
        });

        // affiliate member dashboard
        Route::group(['middleware' => ['AffiliateMemberAuth'], 'controller' => AffiliateMemberDashboardController::class], function () {
            Route::get('/affiliates/dashboard', 'showDashboard');
            Route::get('/affiliates/campaigns', 'showCampaigns');
            Route::get('/affiliates/conversions', 'showConversions');
            Route::get('/affiliates/payouts', 'showPayouts');
            Route::post('/affiliates/payouts', 'requestPayout');
            Route::get('/affiliates/profile', 'showProfile');
            Route::put('/affiliates/profile', 'updateProfile');
            Route::put('/affiliates/profile/pw', 'updatePassword');
        });
    });

    /*-----------------------*/
    /*        Referral         */
    /*-----------------------*/
    Route::controller(EcommerceReferralController::class)->group(function () {
        Route::prefix('referral')->group(function () {
            Route::post('points', 'points');
            Route::post('user', 'isReferralUser');
            Route::post('click', 'clickSocialShare');
        });
    });

    /**
     * * Checkout
     */
    Route::controller(OnlineStoreCheckoutController::class)->group(function () {
        Route::prefix('checkout')->group(function () {
            Route::post('/info', 'getCheckoutInformation');
            Route::post('/shipping/check', 'checkShippingMethodAvailability');
            Route::post('/save/order', 'saveCheckoutOrder');
            Route::post('/update/payment/{status}', 'changePaymentStatus');
            Route::get('/status', 'getStatus');
            // Route::get('/{path}/{refKey?}', 'showCheckoutPages')->middleware('verifyCustomerAccount');
        });
    });

    Route::group(['middleware' => 'checkout'], function () {
        Route::controller(CheckoutController::class)->group(function () {
            Route::prefix('checkout')->group(function () {
                Route::post('/delivery/area', 'checkoutDeliveryArea');
                Route::post('/cart', 'checkoutCart')->withoutMiddleware('checkout');
                Route::get('/cart/update/{quantity}', 'checkoutCartItemQuantity');
                Route::post('/outOfStock', 'checkoutOutOfStock')->withoutMiddleware('checkout');
                Route::post('/shipping/option', 'checkoutShippingOption')->withoutMiddleware('checkout');
                Route::post('/form', 'checkoutForm');
                Route::post('/salesForm', 'checkoutTwoStepForm');
                Route::post('/shipping', 'checkoutShipping');
                Route::post('/order', 'checkoutOrder');
                Route::get('/order/process', 'checkoutOrderProcess')->middleware('checkoutGuard');
                Route::get('/{path}/{refKey?}', 'showCheckoutPages')->middleware('checkoutGuard');
            });
        });
    });

    /**
     * * Shipping
     */
    /*-----------------------*/
    /*      Easyparcel       */
    /*-----------------------*/
    Route::controller(EasyParcelController::class)->group(function () {
        Route::prefix('easyparcel')->group(function () {
            Route::post('/check/quotation', 'shippingRateChecking')->middleware('checkout');
        });
    });
    /*-----------------------*/
    /*       Lalamove        */
    /*-----------------------*/
    Route::controller(LalamoveController::class)->group(function () {
        Route::prefix('lalamove')->group(function () {
            Route::post('/check/quotation', 'quotationChecking')->middleware('checkout');
        });
    });
    /*-----------------------*/
    /*        Delyva         */
    /*-----------------------*/
    Route::controller(DelyvaController::class)->group(function () {
        Route::prefix('delyva')->group(function () {
            Route::post('/check/quotation', 'quotationCheck')->middleware('checkout');
            Route::get('/track/order', 'orderTracking');
        });
    });

    /**
     * * Payment
     */
    Route::controller(PaymentController::class)->group(function () {
        Route::get('/senangpay/redirect', 'senangpayRedirect');
        Route::prefix('ipay88')->group(function () {
            Route::post('/response', 'ipay88Response');
            Route::post('/callback', 'ipay88Callback');
        });
        Route::prefix('payments')->group(function () {
            // SenangPay payment
            Route::post('/get/hash', 'getHash');
            Route::get('senangpay/detail', 'getPaymentResponse');
            // Stripe payment
            Route::get('/stripe/paymentIntent', 'createStripePaymentIntent')->middleware('checkout');
            Route::get('/stripe/fpx/paymentIntent', 'createStripeFPXPaymentIntent')->middleware('checkout');
            // Stripe Fpx payment
            Route::post('/create/paymentIntent', 'createFPXPaymentIntent');
            Route::post('/checkout', "makeCheckout");

            Route::post('/oneClickCheckout', "makeOneClickCheckout");
            Route::post('/check/shipping/region', 'checkAvailableRegion');
        });

        // currency
        Route::get('/get/currency/{id}', 'getOrderCurrency');

        // two step form
        Route::get('/check/exists/{refKey}', "checkProductExists");
        Route::post('/check/storage', "getLocalStorage");
        Route::post('/getOrderSummery', "getOrderSummery");
        Route::get('/get/order/{refKey}', "getPreviousOrder");
        Route::get('/get/Product/{refKey}', "getProduct");
        Route::post('/check/shipping/region', 'checkAvailableRegion');
    });

    /**
     * * Orders
     */
    Route::controller(OrderController::class)->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('/{paymentRef}', 'findByPaymentRef');
            Route::post('/save', 'savePaymentDetail');
        });
    });

    /**
     * * Legal Policy
     */
    Route::controller(LegalSettingsController::class)->group(function () {
        Route::prefix('legal')->group(function () {
            Route::get('/{type}', 'showLegalPolicy');
        });
    });

    /**
     * * Promotion
     */
    Route::controller(PromotionController::class)->group(function () {
        Route::get('/marketing/promotions', 'viewAllPromotion')->name('promotion.all');
        Route::prefix('promotion')->group(function () {
            Route::get('/apply/{discountCode}', 'applyPromotion')->middleware('checkout');
            Route::delete('/delete/{promotionId}', 'deleteAppliedPromotion')->middleware('checkout');
            Route::post('/info', 'loadAllDiscount');
            Route::post('/discountCode', 'applyDiscount');
            Route::post('/store', 'storeDiscount');
        });
    });


    /**
     * * Products
     */
    Route::controller(UsersProductController::class)->group(function () {
        Route::prefix('product')->group(function () {
            Route::get('/getAllActiveProducts', 'getAllActiveProducts');
        });
    });
    Route::controller(ProductAccessController::class)->group(function () {
        Route::prefix('product')->group(function () {
            Route::get('/access-list/{productId}', 'getAccessList');
            Route::post('/access/add', 'addAccess');
            Route::get('/access/remove/{productId}', 'removeAccess');
        });
    });
    Route::controller(ProductCourseController::class)->group(function () {
        Route::prefix('course')->group(function () {
            Route::get('/students/{courseId}', 'getCourseStudents');
            Route::get('/save', 'saveCourse');
            Route::get('/people/remove/{courseId}', 'removePeople');
            Route::post('/add/student', 'addStudent');
        });
    });

    /**
     * * Currency
     */
    Route::controller(CurrencyController::class)->group(function () {
        Route::prefix('currency')->group(function () {
            Route::get('/get', 'getCurrency');
        });
    });

    /**
     * * Tax
     */
    Route::controller(TaxController::class)->group(function () {
        Route::prefix('tax')->group(function () {
            Route::get('/{taxCountry}/{taxState}', 'getTaxSetting');
        });
    });

    Route::group([
        'domain' => app()->environment() === 'production' ? 'hypershapes.com' : 'hypershapes.test'
    ], function () {
        Route::get('/pricing', 'BuilderController@showPricingPlan');
    });

    /**
     * * CRM Tracking
     */
    // Route::controller(OnlineStoreTrackingController::class)->group(function () {
    //     Route::prefix('tracking')->group(function () {
    //         Route::post('/visitor', 'create');
    //         Route::post('/activity', 'trackNewActivity');
    //         Route::post('/conversion', 'recordConversion');
    //         Route::get('/test', 'test');
    //     });
    //     Route::post('/update/customer/info', 'updateCustomerInfo');
    // });

    /**
     * * Catch-all route for publish page
     * ! always keep this route at the end of this file
     */
    Route::get('/{path}', 'BuilderController@showPublishPage');
});

/* eslint-disable prefer-destructuring */
/* eslint no-undefined: "error" */
/* global fbq */
import promotionsAPI from '@onlineStore/api/promotionsAPI.js';
import shippingsAPI from '@onlineStore/api/shippingsAPI.js';
import currencyAPI from '@onlineStore/api/currencyAPI.js';
import productsAPI from '@onlineStore/api/productsAPI.js';
import referralsAPI from '@onlineStore/api/referralsAPI.js';
import checkoutAPI from '@onlineStore/api/checkoutAPI.js';
import paymentsAPI from '@onlineStore/api/paymentsAPI.js';
import ordersAPI from '@onlineStore/api/ordersAPI';
import taxAPI from '@onlineStore/api/taxAPI.js';
import Axios from 'axios';
import clone from 'clone';
import Cookies from 'js-cookie';

export default {
    async fetchCurrency({ commit }) {
        return currencyAPI
            .index()
            .then((res) => {
                commit('setCurrency', {
                    currency: res.data,
                });
            })
            .catch((error) => {
                console.log(error);
            });
    },

    async fetchProducts({ commit }) {
        return productsAPI
            .index()
            .then((res) => {
                commit('setProducts', {
                    products: res.data.products,
                    categories: res.data.categories,
                });
            })
            .catch((error) => {
                console.log(error);
            });
    },
    async fetchSelectedProduct({ commit }, { host, pathname }) {
        return productsAPI
            .show(host, pathname)
            .then(
                ({
                    data: {
                        product,
                        companyName,
                        productRecommendationsTypes,
                        frequentlyBoughtProducts,
                    },
                }) => {
                    commit('setSelectedProduct', {
                        product: product ?? {},
                    });
                    if (productRecommendationsTypes?.length > 0) {
                        commit('setRecommendedProducts', {
                            categories: product.categories,
                            frequentlyBoughtProductIds:
                                frequentlyBoughtProducts,
                            types: productRecommendationsTypes,
                        });
                    }
                    const productDescription =
                        product?.productDescription?.replace(
                            /(<([^>]+)>)/gi,
                            ''
                        );
                    commit(
                        'shared/setMetaInfo',
                        {
                            title: `${product.productTitle} | ${companyName}`,
                            description: productDescription,
                            ogTitle: `${product.productTitle} | ${companyName}`,
                            ogDescription: productDescription,
                            ogUrl: `${host}/products/${pathname}`,
                            ogType: 'product',
                            ogSiteName: product.companyName,
                            ogImage: product.productImagePath,
                            twitterTitle: `${product.productTitle} | ${companyName}`,
                            twitterDescription: productDescription,
                            twitterUrl: `${host}/products/${pathname}`,
                            twitterImage: product.productImagePath,
                            canonicalLink: `${host}/products/${pathname}`,
                        },
                        { root: true }
                    );
                }
            )
            .catch((error) => {
                console.log(error);
            });
    },
    async initializeStoreDetails({ commit }, data) {
        commit('setProducts', {
            products: data.products,
            categories: data.categories,
        });
        commit('setStoreLocation', data.location);
        commit('setPreferences', data.preferences);
        commit('setCustomOrders', data.customOrders);
        commit('setStoreTitle', data.shopName);
        commit('setCurrency', {
            currency: data.currencyDetails,
        });
        // commit('builder/setThemeStyles', data.storeThemeVariables, {
        //     root: true,
        // });
        commit(
            'customerAccount/loginCustomerInfo',
            data.customerInfo?.processedContact,
            { root: true }
        );
        commit('customerAccount/setAddress', data.customerInfo?.address, {
            root: true,
        });
        commit('setSellerEmail', data.sellerEmail);
        commit('setPaymentMethods', data.paymentMethods);
    },
    async fetchCheckoutInfo({ dispatch, commit, getters, state, rootState }) {
        commit('setIsLoading', true);
        const pathname = window.location.pathname ?? '';
        return checkoutAPI
            .index(getters.isShippingRequired)
            .then(async ({ data }) => {
                if (!data.isActiveSaleChannel) {
                    alert(`Store page is deactivated by seller.`);
                    window.location.assign('/');
                }
                commit('setCartItems');
                if (
                    pathname !== '/checkout/outOfStock' &&
                    getters.hasOutOfStockProduct
                ) {
                    window.location.assign('/checkout/outOfStock');
                    return;
                }
                if (
                    pathname !== '/checkout/outOfStock' &&
                    state.cartItems.length > 0 &&
                    getters.subTotal < 2
                ) {
                    alert(
                        'Sorry, you cannot checkout with a total price lower than 2'
                    );
                    window.location.assign('/');
                } else {
                    commit('setHasDelyva', data.hasDelyva);
                    commit('setDelyvaDetail', data.delyvaDetail);
                    commit('setSelectedDeliveryHour');
                    const hasShipping =
                        data.hasShipping ||
                        data.hasEasyParcel ||
                        data.hasLalamove ||
                        data.hasDelyva;
                    commit('setHasShipping', hasShipping);
                    commit('setTaxSetting', data.taxSetting);
                    const isPayment =
                        pathname === '/checkout/payment' ||
                        rootState.miniStore.currentStep === 'payment';
                    dispatch('fetchShippingSetting', {
                        ...data,
                        isPayment,
                    }).finally(() => {
                        if (isPayment) {
                            commit(
                                'setProcessedContact',
                                data.processedContact
                            );
                            commit('setCashbacks', data.cashbacks);
                            dispatch('checkCashbackEligibility');
                        }
                    });
                }
                commit('setIsLoading', false);
            })
            .catch((err) => {
                commit('setIsLoading', false);
                console.log(err);
            });
    },
    async fetchCartItems({ commit }) {
        commit('setCartItems');
    },
    async fetchSelectedDeliveryHour({ commit }) {
        commit('setSelectedDeliveryHour');
    },
    /*eslint-disable */
    async fetchShippingSetting(
        { state, commit, getters, dispatch, rootState },
        {
            shippingMethod,
            hasEasyParcel,
            hasLalamove,
            hasDelyva,
            delyvaInfo,
            hasAutomationPromo,
            isPayment,
        }
    ) {
        const isOnlineStore = state.saleChannel === 'online-store';
        const isShippingRequiredForOS = ![
            '/checkout/outOfStock',
            '/checkout/information',
        ].includes(window.location.pathname);
        const isShippingRequiredForMS = ['shipping', 'payment'].includes(
            rootState.miniStore.currentStep
        );
        const isPromoRequired =
            hasAutomationPromo || localStorage.getItem('appliedPromotion');
        if (isOnlineStore ? isShippingRequiredForOS : isShippingRequiredForMS) {
            commit('setIsfetchingShipping', true);

            const selectedShipping = JSON.parse(
                localStorage.selectedShipping ?? '{}'
            );

            const checkRequirement = (hasService, serviceType) =>
                getters.isShippingRequired &&
                hasService &&
                (isPayment ? selectedShipping.method === serviceType : true);

            let customShipping = [];
            if (shippingMethod.length > 0) {
                customShipping = shippingMethod.map((m) => ({
                    ...m,
                    originalCharge: getters.shippingCharge(m),
                    convertCharge: getters.shippingCharge(m),
                }));
            }
            commit('setShippingMethods', { value: customShipping });

            const easyParcelPromise = checkRequirement(
                hasEasyParcel,
                'EasyParcel'
            )
                ? dispatch('fetchEasyParcel')
                : null;
            const lalamovePromise = checkRequirement(hasLalamove, 'Lalamove')
                ? dispatch('fetchLalamove')
                : null;
            const delyvaPromise = checkRequirement(hasDelyva, 'Delyva')
                ? dispatch('fetchDelyva', delyvaInfo)
                : null;
            return Promise.allSettled([
                easyParcelPromise,
                lalamovePromise,
                delyvaPromise,
            ]).then(async () => {
                dispatch('fetchSelectedShipping');
                commit('setIsfetchingShipping', false);
                if (isPromoRequired) await dispatch('fetchAllDiscount');
            });
        } else if (isPromoRequired) await dispatch('fetchAllDiscount');
    },
    /* eslint-enable */
    async fetchEasyParcel({ commit, getters }) {
        const customerInfo = JSON.parse(localStorage.getItem('formDetail'));
        const field = getters.isShippingRequired
            ? customerInfo.shipping
            : customerInfo.billing;

        try {
            const res = await shippingsAPI.getEasyParcel(
                field,
                getters.productWeightTotal
            );
            const { methods } = res.data;
            let easyParcelShippingMethods = [];

            if (methods.length > 0)
                easyParcelShippingMethods = methods.map((m) => ({
                    ...m,
                    id: m.service_id,
                    shipping_method: 'EasyParcel',
                    shipping_name: m.courier_name,
                    originalCharge: m.price,
                    convertCharge: m.price,
                }));

            if (easyParcelShippingMethods?.length > 0)
                commit('updateShippingMethods', easyParcelShippingMethods);
        } catch (err) {
            console.error(err);
        }
    },
    async fetchLalamove({ commit, state }) {
        const customerInfo = JSON.parse(localStorage.getItem('formDetail'));
        const address = customerInfo?.shipping
            ? {
                  street: customerInfo.shipping.address,
                  city: customerInfo.shipping.city,
                  state: customerInfo.shipping.state,
                  zip: customerInfo.shipping.zipCode,
                  country: customerInfo.shipping.country,
              }
            : null;
        const contact = {
            name: customerInfo?.customerInfo.fullName,
            phone: customerInfo?.shipping.phoneNumber.replace(/\D/g, ''),
        };

        try {
            const res = await shippingsAPI.getLalamove(address, contact);
            const { quotations } = res.data;

            const lalamoveShippingMethods = Object.keys(quotations).map(
                (serviceType) => {
                    const quotation = quotations[serviceType];

                    return {
                        ...quotation,
                        id: `lalamove${serviceType}`,
                        serviceType,
                        shipping_method: 'Lalamove',
                        shipping_name: `Lalamove - ${serviceType}`,
                        convertCharge: parseFloat(quotation.totalFee),
                        originalCharge: parseFloat(quotation.totalFee),

                        // add shipping address & contact details to store in lalamove_quotations later after checkout
                        address,
                        contact,
                    };
                }
            );
            if (lalamoveShippingMethods?.length > 0)
                commit('updateShippingMethods', lalamoveShippingMethods);
        } catch (err) {
            console.error(
                `Failed to load Lalamove shipping methods: ${err?.response?.data?.message}`
            );
        }
    },

    async fetchDelyva(
        { commit, getters, state } // delyvaInfo
    ) {
        const customerInfo = JSON.parse(localStorage.getItem('formDetail'));
        const address = customerInfo?.shipping
            ? {
                  street: customerInfo.shipping.address,
                  city: customerInfo.shipping.city,
                  state: customerInfo.shipping.state,
                  zip: customerInfo.shipping.zipCode,
                  country: customerInfo.shipping.country,
              }
            : null;
        const contact = {
            name: customerInfo?.customerInfo.fullName,
            phone: customerInfo?.shipping.phoneNumber.replace(/\D/g, ''),
        };
        const totalWeight = getters.productWeightTotal;
        try {
            const res = await shippingsAPI.getDelyva(
                address,
                contact,
                totalWeight
            );
            const delyvaServices = res.data;
            if (delyvaServices.errors.length !== 0) {
                console.log('Delyva Error Found!');
            }
            const filterDelyvaServices = delyvaServices.data.services.filter(
                (el) => el.itemType.includes('PARCEL')
            );

            let delyvaShippingMethods = [];
            delyvaShippingMethods = filterDelyvaServices.map((serviceType) => ({
                ...serviceType,
                serviceName: serviceType.service.serviceCompany.name,
                id: `delyva${serviceType.service.code}`,
                shipping_method: 'Delyva',
                shipping_name: serviceType.service.name,
                originalCharge: serviceType.price.amount,
                convertCharge: serviceType.price.amount,
                address,
                contact,
            }));
            // filter Next Day Delivery
            // TODO "Jingze: enable back this if NDD still being returned in live mode when the item type is FOOD"
            // fetchDelyva -> delyvaInfo has been comment
            // if (delyvaInfo.item_type === 'FOOD') {
            //     const forDeletion = ['NDD'];
            //     delyvaShippingMethods = delyvaShippingMethods.filter(
            //         (serviceType) =>
            //             !forDeletion.includes(serviceType.service.code)
            //     );
            // }
            if (delyvaShippingMethods?.length > 0)
                commit('updateShippingMethods', delyvaShippingMethods);
        } catch (err) {
            console.error(
                `Failed to load Delyva shipping methods: ${err.response?.data?.message}`
            );
        }
    },

    async checkoutPlaceOrder({ dispatch, getters, commit }) {
        commit('removeShippingPromo');
        const { hasOutOfStockProduct } = getters;

        if (!hasOutOfStockProduct) {
            dispatch('saveOrderAndCheckout');
        }
    },
    async fetchSelectedShipping({ state, commit }) {
        if (state.order?.shipping_method) {
            const selected = state.shippingMethods.find(
                (s) => s.shipping_name === state.order.shipping_method
            );
            commit('setSelectedShipping', selected);
            return;
        }
        if (localStorage.getItem('selectedShipping')) {
            const selectedShippingId = JSON.parse(
                localStorage.selectedShipping
            ).id;
            const selected = state.shippingMethods.find(
                (s) => s.id === selectedShippingId
            );
            if (selected) commit('setSelectedShipping', selected);
            else if (window.location.pathname === 'checkout/payment')
                window.location.assign('/checkout/shipping');
            else commit('setSelectedShipping', null);
        }
    },

    saveOrderAndCheckout({ state, getters, commit, dispatch }) {
        const formDetail = JSON.parse(localStorage.getItem('formDetail'));
        const { selectedShipping, appliedPromotion } = state;
        const { hasPhysicalProduct: isPhysical, grandTotal } = getters;
        let paymentRef;

        // reset payment_ref in localStorage first
        localStorage.setItem('payment_ref', '');

        // Used after redirect back from senangpay platform
        const cashback = {
            amount: getters.cashbackTotal,
            detail: state.cashbackDetail,
        };
        const storeCreditTotal = state.isCustomerSignedIn
            ? state.processedContactBalanceToBeUsed
            : 0;
        const usedCredit = state.isCustomerSignedIn
            ? state.processedContactBalanceToBeUsed
            : 0;

        Cookies.set('cashback', JSON.stringify(cashback));
        Cookies.set('storeCreditTotal', storeCreditTotal);
        Cookies.set('usedCredit', usedCredit);
        Cookies.set('promotion', JSON.stringify(appliedPromotion));

        ordersAPI
            .saveCheckoutOrder({
                url: window.location.host,
                accountId: state.cartItems[0]?.account_id,
                formDetail,
                paymentIntent: { amount: grandTotal * 100 },
                currency: state.currencyDetails,
                selectedShipping,
                isPhysical,
                unitPrice: getters.productPriceArray ?? [],
                unitTax: getters.productTaxArray ?? [], // TODO
                productDetail: state.cartItems ?? [],
                cartItem: state.cartItems,
                notes: localStorage.getItem('remark'),
                shippingCharge: getters.shippingFee,
                shippingMethod: selectedShipping,
                taxSetting: state.taxSetting ?? {},
                totalTax: getters.taxTotal ?? 0,
                shippingTax: getters.shippingTax ?? 0,
                subtotal: getters.subTotal ?? grandTotal,
                appliedPromotion,
                productTaxArray: getters.productTaxArray ?? [],

                // TODO
                deliveryHour:
                    localStorage.getItem('DELIVERYHOUR') != null
                        ? JSON.parse(localStorage.getItem('DELIVERYHOUR'))
                        : null,

                cashback: {
                    amount: getters.cashbackTotal,
                    detail: state.cashbackDetail,
                },
                storeCreditTotal: state.isCustomerSignedIn
                    ? state.processedContactBalanceToBeUsed
                    : 0,
                usedCredit: state.isCustomerSignedIn
                    ? state.processedContactBalanceToBeUsed
                    : 0,
                saleChannel: state.saleChannel,
            })
            .catch(() => {
                commit('setIsOSPlacingOrder', false);
                commit('setIsMSPlacingOrder', false);
            })
            .then(({ data }) => {
                // localStorage.setItem('id', data);
                paymentRef = data.payment_references;
                commit('setPaymentRef', paymentRef);
                localStorage.setItem('payment_ref', paymentRef);

                if (grandTotal === 0) {
                    ordersAPI
                        .changePaymentStatus('Success', {
                            paymentMethod: 'Store Credit',
                            status: 'Success',
                            url: window.location.host,
                            paymentRef,
                            cashback: {
                                amount: getters.cashbackTotal,
                                detail: state.cashbackDetail,
                            },
                            storeCreditTotal: state.isCustomerSignedIn
                                ? state.processedContactBalanceToBeUsed
                                : 0,
                            usedCredit: state.isCustomerSignedIn
                                ? state.processedContactBalanceToBeUsed
                                : 0,
                            appliedPromotion,
                        })
                        .then((res) => {
                            // Note: response.data.url is a url contains payment ref,
                            //       e.g. /checkout/success/12334455
                            dispatch('purchaseFB');
                            window.location.assign(res.data.url);
                        })
                        .catch(() => {
                            commit('setIsOSPlacingOrder', false);
                            commit('setIsMSPlacingOrder', false);
                        });
                    return;
                }

                // find out selected payment and perform click on
                // corresponding form
                const selectedPaymentMethod = state.paymentMethods.find(
                    (m) => m.checked
                );

                if (!selectedPaymentMethod) {
                    alert('Please select a payment method');
                    return;
                }

                // use DOM to simulate click on payment forms in PaymentMethodDetailContainer.vue
                // for further actions after this please refer to the component
                if (selectedPaymentMethod.name === 'stripe') {
                    document.getElementById('stripe-payment-button').click();
                }

                // Temporarily disabled
                else if (selectedPaymentMethod.name === 'senangPay') {
                    document.getElementById('senangPay-payment-form').submit();
                } else if (selectedPaymentMethod.name === 'stripe FPX') {
                    document.getElementById('fpx-button').click();
                }

                // temporarily disabled
                else if (selectedPaymentMethod.name === 'stripe subscription') {
                    document
                        .getElementById('subscription-submit-button')
                        .click();
                } else if (selectedPaymentMethod.name === 'manual payment') {
                    ordersAPI
                        .changePaymentStatus('Success', {
                            paymentMethod: 'manual payment',
                            status: 'Success',
                            url: window.location.host,
                            paymentRef,
                            cashback: {
                                amount: getters.cashbackTotal,
                                detail: state.cashbackDetail,
                            },
                            storeCreditTotal: state.isCustomerSignedIn
                                ? state.processedContactBalanceToBeUsed
                                : 0,
                            usedCredit: state.isCustomerSignedIn
                                ? state.processedContactBalanceToBeUsed
                                : 0,
                            appliedPromotion,
                        })
                        .then((res) => {
                            // Note: response.data.url is a url contains payment ref,
                            //       e.g. /checkout/success/12344555
                            dispatch('purchaseFB');
                            window.location.assign(res.data.url);
                        })
                        .catch(() => {
                            commit('setIsOSPlacingOrder', false);
                            commit('setIsMSPlacingOrder', false);
                        });
                }
            });
    },
    async fetchTaxSettings({ commit }) {
        const customerInfo = JSON.parse(localStorage.getItem('formDetail'));
        const hasShippingAddress = Object.keys(customerInfo.shipping).some(
            (key) => customerInfo.shipping[key] !== ''
        );
        const address = hasShippingAddress
            ? customerInfo.shipping
            : customerInfo.billing;

        if (!address.country || !address.state) {
            return null;
        }

        return taxAPI.index(address.country, address.state).then(({ data }) => {
            commit('setTaxSetting', data);
        });
    },
    async fetchAllDiscount({ commit, dispatch, getters, state }) {
        if (Object.keys(state.order).length > 0) {
            commit('setAppliedPromotion', state.order.order_discount);
            dispatch('calculateShippingPromo');
            return true;
        }
        commit('setAutomatedPromotion', []);
        return promotionsAPI
            .getAllDiscount(getters.promotionsInfo)
            .then(({ data }) => {
                dispatch('fetchDiscount', data.loadDiscount);
                dispatch('fetchProductDiscount', data.loadProductDiscount);
                dispatch('calculateShippingPromo');
                dispatch('checkCashbackEligibility');
            });
    },
    async fetchDiscount({ state, commit }, data) {
        if (data.automatedDiscount.length > 0) {
            commit('setAppliedPromotion', data.automatedDiscount);
        }
        if (data.loadDiscount.length > 0) {
            // commit('setAppliedPromotion', data.loadDiscount);
            data.loadDiscount.forEach((promo) => {
                if (promo.promotion.promotion_method === 'manual') {
                    const promoCategory = promo.promotion.promotion_category;
                    const indexOf = state.appliedPromotion
                        .map(
                            (discount) => discount.promotion.promotion_category
                        )
                        .indexOf(promoCategory);
                    if (indexOf === -1) {
                        commit('updateAppliedPromotion', {
                            promotion: promo,
                        });
                        if (promoCategory === 'Product') {
                            commit('appliedDiscountToProduct', promo);
                        }
                    } else {
                        commit('updateAppliedPromotion', {
                            promotion: promo,
                            index: indexOf,
                        });
                        if (promoCategory === 'Product') {
                            commit('removedDiscountFromProduct');
                            commit('appliedDiscountToProduct', promo);
                        }
                    }
                }
            });
        }
    },
    fetchProductDiscount({ commit, state }, data) {
        if (data.automatedDiscount.length > 0) {
            data.automatedDiscount.forEach((promo) => {
                if (promo.valid_status) {
                    const promoCategory = promo.promotion.promotion_category;
                    const indexOf = state.appliedPromotion
                        .map(
                            (discount) => discount.promotion.promotion_category
                        )
                        .indexOf(promoCategory);
                    if (indexOf === -1) {
                        commit('updateAppliedPromotion', {
                            promotion: promo,
                        });
                        if (promoCategory === 'Product') {
                            commit('appliedDiscountToProduct', promo);
                        }
                    }
                }
            });
        }
        commit('setAutomatedPromotion', data.automatedDiscount);
        if (data.loadDiscount.length > 0) {
            data.loadDiscount.forEach((promo) => {
                if (promo.promotion.promotion_method === 'manual') {
                    const promoCategory = promo.promotion.promotion_category;
                    const indexOf = state.appliedPromotion
                        .map(
                            (discount) => discount.promotion.promotion_category
                        )
                        .indexOf(promoCategory);
                    if (indexOf === -1) {
                        commit('updateAppliedPromotion', {
                            promotion: promo,
                        });
                        if (promoCategory === 'Product') {
                            commit('appliedDiscountToProduct', promo);
                        }
                    } else {
                        commit('updateAppliedPromotion', {
                            promotion: promo,
                            index: indexOf,
                        });
                        if (promoCategory === 'Product') {
                            commit('removedDiscountFromProduct');
                            commit('appliedDiscountToProduct', promo);
                        }
                    }
                }
            });
        }
    },

    storeDiscount({ state }) {
        const manualPromo = state.appliedPromotion.filter(
            (promo) => promo.promotion.promotion_method === 'manual'
        );
        if (manualPromo.length > 0) {
            localStorage.setItem(
                'appliedPromotion',
                JSON.stringify(manualPromo.map((m) => m.promotion.id))
            );
        } else {
            localStorage.removeItem('appliedPromotion');
        }
    },
    calculateShippingPromo: ({ commit, state, dispatch }) => {
        let hasShippingPromo = false;
        if (state.order?.order_discount?.length > 0) {
            hasShippingPromo =
                state.appliedPromotion?.filter(
                    (item) => item.promotion_category === 'Shipping'
                ).length > 0;
        } else {
            hasShippingPromo =
                state.appliedPromotion?.filter(
                    (item) =>
                        item.promotion.promotion_category === 'Shipping' &&
                        item.valid_status === true
                ).length > 0;
        }
        const allShippingMethod = clone(state.shippingMethods);
        let updatedShippingMethod = [];
        if (hasShippingPromo) {
            updatedShippingMethod = allShippingMethod.map((m) => ({
                ...m,
                convertCharge: 0,
            }));
        } else {
            updatedShippingMethod = allShippingMethod.map((m) => ({
                ...m,
                convertCharge: m.originalCharge,
            }));
        }
        commit('appliedDiscountToShippingMethods', updatedShippingMethod);
        dispatch('fetchSelectedShipping');
    },
    async checkCashbackEligibility({ commit, dispatch, getters, state }) {
        commit('setProcessedContactBalance', 0);
        const customer = state.processedContact;
        if (customer && Object.entries(customer).length !== 0) {
            let creditToBeUsed = getters.calculatePrice(
                customer.credit_balance / 100
            );
            if (getters.grandTotal <= creditToBeUsed) {
                creditToBeUsed = getters.grandTotal;
            }
            commit('setProcessedContactBalance', creditToBeUsed);
        }
        dispatch('getMostBenificialCashback', {
            cashbacks: state.cashbacks,
            customer,
        });
    },
    async getMostBenificialCashback(
        { commit, getters, state },
        { cashbacks, customer }
    ) {
        const cashbackArray = clone(cashbacks);
        let cashbackAmount = 0;
        const validCashbacks = cashbackArray.filter((c) =>
            c.salesChannel.some((s) => s.type === state.saleChannel)
        );
        const availableCashback = validCashbacks.filter(
            (c) =>
                getters.calculatePrice(c.min_amount / 100) <= getters.grandTotal
        );
        /*eslint-disable */
        availableCashback.map((m) => {
            const cashbackTotal = getters.formatPrice(
                getters.grandTotal *
                    (getters.formatPrice(m.cashback_amount / 100) / 100),
                false
            );
            m.total = cashbackTotal;
            if (m.capped_amount) {
                const cappedAmount = m.capped_amount / 100;
                m.total =
                    cashbackTotal > cappedAmount ? cappedAmount : cashbackTotal;
            }
            return m;
        });
        /* eslint-enable */
        /*eslint-disable */
        const sortedCashback = availableCashback.sort((a, b) =>
            b.total > a.total ? 1 : a.total > b.total ? -1 : 0
        );
        const selectedCashback = sortedCashback.find((cashback) => {
            if (cashback.for_all) {
                return true;
            }
            return cashback.contactIds.includes(customer.id);
        });
        cashbackAmount = getters.formatPrice(
            selectedCashback?.cashback_amount / 100
        );
        /* eslint-enable */
        commit('setCashbackAmount', cashbackAmount);
        commit('setCashbackDetail', selectedCashback);
    },

    /**
     * Generate secure hash primarily for senangPay
     */
    generateProductHash({ commit, getters, state }) {
        const productDetail = state.cartItems.reduce(
            (acc, cart) => `${acc}${cart.productTitle} x ${cart.qty}`,
            ''
        );
        const accountId = state.cartItems[0]?.account_id;
        const data = {
            detail: productDetail,
            amount: parseFloat(getters.grandTotal.toFixed(2)),
            orderId: accountId,
            accountId,
        };
        return paymentsAPI.getHash(data).then((response) => {
            commit('setHash', response.data);
        });
    },

    /**
     * Facebook Pixel & Conversion Api
     */

    async fetchInitialDataFB() {
        // fetch clientIp and browser
        if (!sessionStorage.getItem('ipAddress')) {
            let clientIp = null;

            await Axios.get('https://www.cloudflare.com/cdn-cgi/trace').then(
                (response) => {
                    [clientIp] = response.data
                        .substring(response.data.search('ip=') + 3)
                        .split('\n');
                }
            );
            sessionStorage.setItem('ipAddress', clientIp);
        }
        if (!sessionStorage.getItem('browser')) {
            // window.navigator.userAgentData
            //     .getHighEntropyValues([
            //         'architecture',
            //         'model',
            //         'platform',
            //         'platformVersion',
            //         'fullVersionList',
            //     ])
            //     .then((ua) => {
            //         console.log(ua,"ua");
            //         sessionStorage.setItem('browser', ua.brands[2].brand);
            //     });
            let browserType = '';
            if (
                (navigator.userAgent.indexOf('Opera') ||
                    navigator.userAgent.indexOf('OPR')) !== -1
            ) {
                browserType = 'Opera';
            } else if (navigator.userAgent.indexOf('Chrome') !== -1) {
                browserType = 'Chrome';
            } else if (navigator.userAgent.indexOf('Safari') !== -1) {
                browserType = 'Safari';
            } else if (navigator.userAgent.indexOf('Firefox') !== -1) {
                browserType = 'Firefox';
            } else if (
                navigator.userAgent.indexOf('MSIE') !== -1 ||
                !!document.documentMode === true
            ) {
                browserType = 'IE';
            } else {
                browserType = 'Unknown';
            }
            sessionStorage.setItem('browser', browserType);
        }
    },

    async serverTrackFB({ state }, event) {
        await Axios.post(
            `https://graph.facebook.com/v12.0/${state?.facebookPixel?.pixel_id}/events`,
            {
                access_token: `${state?.facebookPixel?.api_token}`,
                data: [
                    {
                        event_id: `${event.eventDetailName}.${event.time}`,
                        event_name: `${event.eventAction}`,
                        event_time: event.time,
                        user_data: {
                            client_ip_address:
                                sessionStorage.getItem('ipAddress'),
                            client_user_agent:
                                sessionStorage.getItem('browser'),
                        },
                        event_source_url: window.location.href,
                        ...event.data,
                    },
                ],
                test_event_code: 'TEST60938',
            }
        ).then((res) => {});
    },

    async pageViewFB({ state, dispatch }) {
        if (!state.isPublish && !state?.facebookPixel?.facebook_selected) {
            return;
        }
        const time = Math.floor(Date.now() / 1000);
        // fbq('track', 'PageView', {}, { eventID: `PageView.${time}` });
        // dispatch('serverTrackFB', {
        //     eventAction: 'PageView',
        //     eventDetailName: 'PageView',
        //     time,
        //     data: {},
        // });
    },

    async addToCartFB({ state, dispatch }) {
        if (!state.isPublish && !state?.facebookPixel?.facebook_selected)
            return;
        const selectedProductDetail = state.allProducts.find(
            (product) =>
                product.reference_key ===
                state.selectedCartProduct.reference_key
        );
        const time = Math.floor(Date.now() / 1000);
        const data = {
            content_ids: selectedProductDetail.SKU ?? selectedProductDetail.id,
            content_name: selectedProductDetail?.productTitle,
            value: Number(
                selectedProductDetail?.productPrice.replace(/[^0-9.-]+/g, '')
            ),
            currency: state.currencyDetails?.currency,
            content_type: 'product',
            contents: [
                {
                    id: selectedProductDetail?.id,
                    quantity: state.selectedCartProduct?.qty,
                },
            ],
        };
        // fbq(
        //     'track',
        //     'AddToCart',
        //     {
        //         ...data,
        //     },
        //     { eventID: `Cart.${time}` }
        // );
        // dispatch('serverTrackFB', {
        //     eventAction: 'AddToCart',
        //     eventDetailName: 'Cart',
        //     time,
        //     data,
        // });
    },

    async initialCheckoutFB({ state, getters, commit, dispatch }) {
        if (!state.isPublish && !state?.facebookPixel?.facebook_selected) {
            return;
        }
        commit('setCartItems'); // update total price

        const time = Math.floor(Date.now() / 1000);
        const data = {
            content_name: 'Checkout',
            content_ids: state?.cartItems.map((item) => item?.SKU ?? item?.id),
            content_type: 'product_group',
            num_items: getters.state.cartItemsQuantity,
            value: getters.subTotal,
            currency: state.currencyDetails?.currency,
            contents: state?.cartItems.map((item) => ({
                id: item?.SKU ?? item?.id,
                quantity: item?.qty,
            })),
        };

        // fbq(
        //     'track',
        //     'InitiateCheckout',
        //     {
        //         ...data,
        //     },
        //     { eventID: `InitiateCheckout.${time}` }
        // );
        // dispatch('serverTrackFB', {
        //     eventAction: 'InitiateCheckout',
        //     eventDetailName: 'InitiateCheckout',
        //     time,
        //     data,
        // });
    },

    async purchaseFB({ state, getters, dispatch }) {
        if (!state.isPublish && !state?.facebookPixel?.facebook_selected) {
            return;
        }
        const time = Math.floor(Date.now() / 1000);
        const data = {
            value: getters.grandTotal,
            currency: state?.currencyDetails.currency,
            content_type: 'product_group',
            content_ids: state?.cartItems.map((item) => item?.SKU ?? item?.id),
            contents: state?.cartItems.map((item) => ({
                id: item?.SKU ?? item?.id,
                quantity: item?.qty,
            })),
        };

        // fbq(
        //     'track',
        //     'Purchase',
        //     {
        //         ...data,
        //     },
        //     { eventID: `Purchase.${time}` }
        // );

        // dispatch('serverTrackFB', {
        //     eventAction: 'InitialCheckOut',
        //     eventDetailName: 'Purchase',
        //     time,
        //     data,
        // });
    },
    async productDetailFB({ state, dispatch }, product) {
        if (!state.isPublish && !state?.facebookPixel?.facebook_selected) {
            return;
        }

        const time = Math.floor(Date.now() / 1000);
        const data = {
            content_ids: product.SKU ?? product.id,
            content_name: product.productTitle,
            value: product.productPrice,
            currency: state.currencyDetails?.currency,
            content_type: 'product',
        };
        // fbq(
        //     'track',
        //     'ViewContent',
        //     {
        //         ...data,
        //     },
        //     { eventID: `ProductDetail.${time}` }
        // );

        // dispatch('serverTrackFB', {
        //     eventAction: 'ViewContent',
        //     eventDetailName: 'ProductDetail',
        //     time,
        //     data,
        // });
    },
    checkOutOfStock({ getters }) {
        let storedProduct = JSON.parse(
            localStorage.getItem('products') ?? '[]'
        );
        const isCheckoutPage = window.location.pathname.includes('checkout');
        if (!isCheckoutPage && getters.hasOutOfStockProduct) {
            const outOfStockProduct = JSON.parse(
                localStorage.getItem('outOfStockProduct')
            );
            storedProduct = storedProduct.filter(
                (item) =>
                    !outOfStockProduct.some(
                        (refKey) => refKey === item.reference_key
                    )
            );
            localStorage.setItem('products', JSON.stringify(storedProduct));
            localStorage.setItem('outOfStockProduct', '[]');
        }
    },

    async fetchReferralCampaigns({ commit }) {
        return referralsAPI
            .index()
            .then((res) => {
                commit('setReferralCampaigns', res.data);
            })
            .catch((error) => {
                console.log(error);
            });
    },

    async fetchReferralPoints({ state, commit }) {
        return referralsAPI
            .getReferralPoints(state.selectedReferralCampaign.id)
            .then(
                ({
                    data: {
                        points,
                        joined,
                        logData,
                        referOrderCount,
                        referSignUpCount,
                        isPurchased,
                        isRootUser,
                        isReferralUser,
                    },
                }) => {
                    commit('setReferralPoints', points);
                    commit('setReferralActionInfo', {
                        joined,
                        logs: logData,
                        orderCount: referOrderCount,
                        signUpCount: referSignUpCount,
                        isPurchased,
                        isRootUser,
                    });
                    commit('setIsReferralUser', isReferralUser);
                }
            );
    },
};

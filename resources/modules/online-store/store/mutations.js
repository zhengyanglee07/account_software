/* eslint no-param-reassign: "off" */

import clone from 'clone';

export default {
    setStoreTitle(state, storeTitle) {
        state.storeTitle = storeTitle;
    },
    setStoreLocation(state, location) {
        state.location = location;
    },
    setHasDelyva(state, hasDelyva) {
        state.hasDelyva = hasDelyva;
    },
    setDelyvaDetail(state, delyvaDetail) {
        state.delyvaDetail = delyvaDetail;
    },
    setPreferences(state, preferences) {
        state.preferences = preferences;
    },
    updatePreferences(state, { key, value }) {
        state.preferences[key] = value;
    },
    setIsLoading(state, isLoading) {
        state.isLoading = isLoading;
    },
    setCurrency(state, { currency }) {
        state.currencyDetails = clone(currency);
    },

    setProducts(state, { products, categories = [] }) {
        state.allProducts = [...products];
        state.allCategories = [...categories];
    },
    setSelectedProduct(state, { product }) {
        state.selectedProduct = { ...product };
    },
    setRecommendedProducts(
        state,
        {
            categories,
            frequentlyBoughtProductIds = {},
            recentlyViewProductIds = {},
            types,
        }
    ) {
        let recommendedCategoryProductIds = [];
        categories.forEach((category) => {
            recommendedCategoryProductIds = new Set([
                ...recommendedCategoryProductIds,
                ...state.allCategories
                    .find((el) => el.id === category)
                    .productsId.filter((el) => el !== state.selectedProduct.id),
            ]);
        });
        const recommendedProducts = (recommendedProductIds) =>
            [...state.allProducts].filter((e) =>
                [...recommendedProductIds].includes(e.id)
            );
        const recommendedIds = (type) => {
            switch (type) {
                case 'category':
                    return [...recommendedCategoryProductIds];
                case 'best-selling':
                    return state.allProducts
                        .filter((e) => e.id !== state.selectedProduct.id)
                        .sort((a, b) => b.saleQuantity - a.saleQuantity)
                        .map((p) => p.id);
                case 'recently-viewed':
                    return Object.values(recentlyViewProductIds);
                default:
                    return Object.values(frequentlyBoughtProductIds);
            }
        };
        const recommendedTypes = types;
        recommendedTypes.forEach((type) => {
            const recommendedProductIds = [...recommendedIds(type)];
            state.recommendedProducts = [
                ...state.recommendedProducts,
                {
                    type,
                    products: [
                        ...recommendedProducts(recommendedProductIds),
                    ].sort(
                        (a, b) =>
                            recommendedProductIds.indexOf(a.id) -
                            recommendedProductIds.indexOf(b.id)
                    ),
                },
            ];
        });
    },

    setSelectedOptions(state, { product, cart = null }) {
        const selected = { product: {} };
        const store = { products: [] };
        selected.product = product;
        store.products = JSON.parse(localStorage.getItem('products')) ?? [];
        const temp = store.products.find((e) => e.tempid === product.tempid);

        if (temp) {
            selected.product.qty = cart ? product.qty : temp.qty + product.qty;
            const index = store.products.findIndex(
                (e) => e.tempid === product.tempid
            );
            store.products[index] = selected.product;
            if (cart === 'remove') {
                store.products.splice(index, 1);
            }
        }

        if (product && !temp) {
            store.products.push(product);
        }
        state.selectedCartProduct = clone(product);

        const getCombinationIds = (item) => {
            const variantDetail = item.variant_details?.find(
                (d) => d.reference_key === item.variantRefKey
            );
            if (!variantDetail) return item.combinationIds ?? [];
            const tempIds = [];
            for (let i = 0; i < 5; i++) {
                if (variantDetail[`option_${i + 1}_id`])
                    tempIds.push(variantDetail[`option_${i + 1}_id`]);
            }
            return tempIds;
        };
        localStorage.setItem(
            'products',
            JSON.stringify(
                store.products.map((el) => ({
                    reference_key: el.reference_key,
                    hasVariant: el.hasVariant,
                    customOption:
                        el.customOption ?? el.customOptions?.map((m) => m.id),
                    customizations: el.customizations?.map((cus) => ({
                        id: cus.id,
                        index: cus.index,
                        label: cus.label,
                        values: cus.values?.map((val) => ({
                            ...val,
                            updatedAt:
                                val.updatedAt ??
                                el.customOptions
                                    ?.find((option) => option.id === cus.id)
                                    .inputs.find((input) => val.id === input.id)
                                    .updated_at,
                        })),
                    })),
                    tempid: el.tempid,
                    combinationIds: getCombinationIds(el),
                    variations: el.variations?.map((variant) => ({
                        ...variant,
                        id:
                            variant.id ??
                            el.variant_details?.find(
                                (d) => d.reference_key === el.variantRefKey
                            ).id,
                    })),
                    variantRefKey: el.variantRefKey ?? false,
                    qty: el.qty,
                }))
            )
        );
        state.cartItemsQuantity = store.products
            .filter((item) =>
                state.allProducts.some(
                    (el) =>
                        el.reference_key === item.reference_key &&
                        el.saleChannels.includes(
                            state.saleChannel === 'store'
                                ? 'online-store'
                                : state.saleChannel
                        )
                )
            )
            .reduce((accumulator, item) => accumulator + item.qty, 0);
    },
    setCartItems(state) {
        const store = { products: [] };
        store.products = JSON.parse(localStorage.getItem('products')) ?? [];
        const formatPrice = (price) =>
            parseFloat(price.toString().replace(/,/g, ''));
        const selectedProduct = [];
        store.products.forEach((product, itemIndex) => {
            let tempProduct = {};
            const item = state.allProducts.find(
                (el) => el.reference_key === product.reference_key
            );
            if (!item) {
                localStorage.setItem(
                    'products',
                    JSON.stringify(
                        store.products.filter((e, i) => i !== itemIndex)
                    )
                );
                return;
            }
            tempProduct = { ...product, ...item };
            const selectedVariantDetail = item?.variant_details?.find(
                (i) => i.reference_key === product.variantRefKey
            );
            // tempProduct.variations =
            const selectedCustomOptions = item?.custom_options?.filter((c1) =>
                product.customizations?.some((c2) => c1.id === c2.id)
            );
            const selectedCustomizations = product.customizations?.filter(
                (c1) => item?.custom_options?.some((c2) => c1?.id === c2.id)
            );
            let customizationsArray = null;
            let customizationPrice = 0;
            selectedCustomOptions?.forEach((custom, index) => {
                if (custom.is_total_Charge) {
                    customizationPrice += formatPrice(
                        custom.total_charge_amount
                    );
                }
                customizationsArray = custom.inputs?.filter((c1) =>
                    selectedCustomizations[index].values.some(
                        (c2) => c1.id === c2?.id && !c1.is_total_Charge
                    )
                );
                customizationPrice += customizationsArray.reduce(
                    (total, c) => total + formatPrice(c.single_charge),
                    0
                );
            });
            const variantPrice =
                selectedVariantDetail === undefined
                    ? null
                    : formatPrice(selectedVariantDetail?.price);
            tempProduct.variantImage = selectedVariantDetail?.image_url;
            tempProduct.productPrice =
                (variantPrice
                    ? formatPrice(variantPrice ?? 0)
                    : formatPrice(item?.productPrice)) +
                formatPrice(customizationPrice);
            tempProduct.netPrice = tempProduct.productPrice;
            tempProduct.customizations = product.customizations?.map((cus) => {
                const customOption = item?.custom_options?.find(
                    (option) => option.id === cus.id
                );
                return {
                    id: cus.id,
                    index: cus.index,
                    label: cus.label,
                    is_total_charge: customOption?.is_total_Charge,
                    total_charge_amount: customOption?.total_charge_amount,
                    values: cus.values?.map((val) => ({
                        ...val,
                        single_charge: customOption?.is_total_Charge
                            ? 0
                            : customOption?.inputs?.find(
                                  (input) => val.id === input.id
                              )?.single_charge,
                    })),
                };
            });
            selectedProduct.push(tempProduct);
        });
        state.cartItems = [...selectedProduct].filter((item) =>
            item.saleChannels.includes(
                state.saleChannel === 'store'
                    ? 'online-store'
                    : state.saleChannel
            )
        );
    },
    setCartItemsQuantity(state) {
        const saleChannel =
            state.saleChannel === 'store' ? 'online-store' : state.saleChannel;
        const store = { products: [] };
        store.products = JSON.parse(localStorage.getItem('products')) ?? [];
        state.cartItemsQuantity = store.products
            .filter((item) =>
                state.allProducts.some(
                    (el) =>
                        el.reference_key === item.reference_key &&
                        el.saleChannels.includes(saleChannel)
                )
            )
            .reduce((accumulator, item) => accumulator + item.qty, 0);
    },
    updateCartItems(state, { value, key = '', index = null }) {
        if (index) {
            state.cartItems[index][key] = value;
        } else {
            state.cartItems = value;
        }
    },
    setCheckoutCartItem(state) {
        const cartItems = [];
        state.orderDetail.forEach((order) => {
            const product = state.allProducts.find(
                (p) => p.id === order.users_product_id
            );
            if (product) {
                const cartItem = product;
                cartItem.customizations = JSON.parse(order.customization);
                cartItem.variations = JSON.parse(order.variant);
                cartItem.qty = order.quantity;
                cartItem.productPrice = order.unit_price;
                cartItem.image_url = order.image_url;
                cartItem.isDiscountApplied = order.is_discount_applied;
                cartItem.discount = JSON.parse(order.discount_details);
                cartItems.push({ ...cartItem });
            }
        });
        state.cartItems = cartItems;
    },
    updateScheduleDelivery(state, value) {
        if (value && Object.keys(value).length === 0) {
            localStorage.removeItem('DELIVERYHOUR');
        }
        state.selectedDeliveryHour = value;
    },
    setShippingMethods(state, { value, key = '', index = null }) {
        if (index) {
            state.shippingMethods[index][key] = value;
        } else {
            state.shippingMethods = value;
        }
    },
    updateShippingMethods(state, values) {
        state.shippingMethods = [...state.shippingMethods, ...values];
    },
    updateSelectedShipping(state, { key = '', value = null }) {
        state.selectedShipping[key] = value;
    },
    setHasShipping(state, hasShipping) {
        state.hasShipping = hasShipping;
    },
    setEasyParcel(state, { easyParcel }) {
        state.easyParcelShippingMethods = easyParcel;
    },
    setTaxSetting(state, setting) {
        state.taxSetting = { ...setting };
    },
    setSelectPaymentMethod(state, { data }) {
        if (!data) return;

        state.paymentMethods.forEach((paymentMethod) => {
            if (paymentMethod.id !== data.id) {
                paymentMethod.checked = false;
                paymentMethod.icon = 'down';
            }
        });
        const methodIdx = state.paymentMethods.findIndex(
            (m) => m.id === data.id
        );

        if (methodIdx === -1) {
            alert('Something wrong about payment methods, please contact us');
            return;
        }

        const { checked } = state.paymentMethods[methodIdx];

        state.paymentMethods[methodIdx].checked = !checked;
        if (checked) {
            state.paymentMethods[methodIdx].icon = 'up';
        } else {
            state.paymentMethods[methodIdx].icon = 'down';
        }
    },
    setAutomatedPromotion(state, automatedPromotion) {
        state.automatedPromotion = automatedPromotion;
    },
    updateAppliedPromotion(state, { promotion, index = null }) {
        if (index !== null) {
            state.appliedPromotion.splice(index, 1, promotion);
        } else {
            state.appliedPromotion.push(promotion);
        }
    },
    removeAppliedPromotion(state, { index }) {
        state.appliedPromotion.splice(index, 1);
    },
    removeShippingPromo(state, isTempRemove = true) {
        const deliveryHour = state.selectedDeliveryHour;
        const isSelectedPickup =
            Object.keys(deliveryHour).length > 0
                ? deliveryHour.type === 'pickup'
                : false;
        if (isSelectedPickup) {
            const indexOf = state.appliedPromotion
                .map((promo) => promo.promotion.promotion_category)
                .indexOf('Shipping');
            if (indexOf !== -1) {
                if (!isTempRemove && localStorage.getItem('appliedPromotion')) {
                    const storedPromo = JSON.parse(
                        localStorage.getItem('appliedPromotion')
                    );
                    const appliedPromo = storedPromo.filter(
                        (id) =>
                            id !== state.appliedPromotion[indexOf].promotion.id
                    );
                    localStorage.setItem(
                        'appliedPromotion',
                        JSON.stringify(appliedPromo)
                    );
                }
                state.appliedPromotion.splice(indexOf, 1);
            }
        }
    },

    setPaymentMethods(state, methods) {
        state.paymentMethods = methods;
    },
    appliedDiscountToProduct(state, data) {
        state.cartItems.forEach((item, index) => {
            state.cartItems[index].discount = [];
            state.cartItems[index].isDiscountApplied = false;

            data.promotionProducts.forEach((promo) => {
                if (item.id === promo.id) {
                    if (item.hasVariant) {
                        if (item.tempid === promo.variantCombinationID) {
                            state.cartItems[index].discount = promo;
                            state.cartItems[index].isDiscountApplied = true;
                        }
                    } else {
                        state.cartItems[index].discount = promo;
                        state.cartItems[index].isDiscountApplied = true;
                    }
                }
            });
        });
    },
    removedDiscountFromProduct(state) {
        state.cartItems.forEach((product, index) => {
            state.cartItems[index].discount = [];
            state.cartItems[index].isDiscountApplied = false;
        });
    },
    appliedDiscountToShippingMethods(state, shippingMethods) {
        state.shippingMethods = shippingMethods;
    },
    setCustomOrders(state, customOrders) {
        state.customOrders = customOrders;
    },
    setOrder(state, order) {
        const orderDetail = { ...order };
        const formProperty = [
            'name',
            'phoneNumber',
            'company_name',
            'address',
            'city',
            'country',
            'state',
            'zipcode',
        ];
        const shipping = {};
        const billing = {};
        formProperty.forEach((key) => {
            shipping[key] = order[`shipping_${key}`];
            if (key !== 'phoneNumber') billing[key] = order[`billing_${key}`];
        });
        orderDetail.customerDetail = {
            shipping,
            billing,
        };
        state.order = orderDetail;
    },
    setOrderDetail(state, orderDetail) {
        state.orderDetail = orderDetail;
    },
    setIsOrderSuccess(state, isSuccess) {
        state.isOrderSuccess = isSuccess;
    },
    setProcessedContact(state, detail) {
        state.processedContact = detail;
    },
    setProcessedContactBalance(state, balance) {
        state.processedContactBalanceToBeUsed = balance;
    },
    setCashbacks(state, cashbacks) {
        state.cashbacks = cashbacks;
    },
    setCashbackAmount(state, amount) {
        state.cashbackPercentage = amount;
    },
    setCashbackDetail(state, detail) {
        state.cashbackDetail = detail;
    },

    /**
     * General
     */
    setSalesChannel(state, channel) {
        state.saleChannel = channel ?? 'store';
    },

    /**
     * Mini Store
     */
    setMiniStoreDetails(state, details) {
        state.miniStoreDetails = details;
    },

    modifyCartItems(state, { type, value, index }) {
        state.cartItems[index][type] = value;
    },

    /**
     * Navigation
     */
    setMenuListArray(state, menus) {
        state.menuListArray = menus;
    },

    setPaymentRef(state, ref) {
        state.paymentRef = ref;
    },

    setIsOSPlacingOrder(state, isLoading) {
        state.isOSPlacingOrder = isLoading;
    },

    setIsMSPlacingOrder(state, isLoading) {
        state.isMSPlacingOrder = isLoading;
    },

    /**
     * Social Proof
     */
    setSocialProof(state, socialProof) {
        state.socialProof = socialProof;
    },

    /**
     * Loading state
     */
    setIsfetchingShipping(state, isFetchingShipping) {
        state.isFetchingShipping = isFetchingShipping;
    },

    setHash(state, h) {
        state.hash = h;
    },

    setFacebookPixel(state, { facebookPixel }) {
        state.facebookPixel = facebookPixel;
    },

    setIsPublish(state, { isPublish }) {
        state.isPublish = isPublish;
    },

    setClientIp(state, { clientIp }) {
        // console.log(clientIp);
        state.clientIp = clientIp;
    },

    setSellerEmail(state, value) {
        state.sellerEmail = value;
    },

    setReferralCampaigns(state, referralCampaigns) {
        state.referralCampaigns = referralCampaigns;
    },

    setFunnelProcessedContact(state, funnelProcessedContact) {
        state.funnelProcessedContact = funnelProcessedContact;
    },

    addMenuList(state, { name, link }) {
        const { menuListItem } = state;
        const payload = {
            id: '',
            name,
            link,
            refKey: '',
            elements: [],
        };

        // generate an ID
        // grabs the ID of the last element, then + 1
        payload.id =
            menuListItem.length > 0
                ? menuListItem[menuListItem.length - 1].id + 1
                : 0;

        // generate a random refKey
        const tempRefKeyList = [];
        menuListItem?.forEach((items) => {
            tempRefKeyList.push(items.refKey);
        });
        const generateKey = () => {
            const min = 100000000001;
            const max = 999999999999;
            let randomRefKey = 0;
            randomRefKey = parseInt(Math.random() * (max - min) + min);
            if (tempRefKeyList.includes(randomRefKey)) generateKey();
            payload.refKey = randomRefKey;
        };
        generateKey();
        state.menuListItem.push(payload);
    },

    deleteMenuList(state, index) {
        state.menuListItem.splice(index, 1);
    },

    updateMenuList(state, payload) {
        state.menuListArray = payload;
    },

    setMenuList(state, list) {
        state.menuListArray = list ?? [];
    },

    setMenuPages(state, pages) {
        state.menuAllPages = pages;
    },

    setMenuProducts(state, products) {
        state.menuAllProducts = products;
    },

    setMenuAllLegalPolicy(state, policies) {
        state.menuAllLegalPolicy = policies;
    },
    // Checkout
    setSelectedCartItems(state, cartItems) {
        state.cartItems = cartItems;
    },
    setIsPageLoading(state, isPageLoading) {
        state.isPageLoading = isPageLoading;
    },
    setLegalPolicy(state, legalPolicy) {
        state.legalPolicy = legalPolicy;
    },
    setAllTaxSettings(state, taxSetting) {
        state.allTaxSettings = taxSetting;
    },
    setFormDetail(state, formDetail) {
        state.formDetail = formDetail;
    },
    setAvailabelShippingApp(state, data) {
        const availableApp = [];
        if (data.hasEasyParcel) availableApp.push('easyparcel');
        if (data.hasLalamove) availableApp.push('lalamove');
        if (data.hasDelyva) availableApp.push('delyva');
        state.availableShippingApp = availableApp;
    },
    setAllShippingMethods(state, shippingMethods) {
        state.allShippingMethods = shippingMethods;
    },
    updateAllShippingMethods(state, shippingMethods) {
        state.allShippingMethods.push(...shippingMethods);
    },
    setSelectedShippingMethod(state, shippingMethod) {
        state.selectedShippingMethod = shippingMethod;
    },
    setSelectedDeliveryHour(state, deliverySchedule) {
        state.selectedDeliveryHour = clone(deliverySchedule);
    },
    setAllPaymentMethods(state, allPaymentMethods) {
        state.allPaymentMethods = allPaymentMethods;
    },
    setSelectedPaymentMethod(state, selectedPaymentMethod) {
        state.selectedPaymentMethod = selectedPaymentMethod;
    },
    setAppliedPromotion(state, appliedPromo) {
        state.appliedPromotion = appliedPromo;
    },
    applyDiscountToShipping(state) {
        if (state.allShippingMethods?.length === 0) return;
        state.allShippingMethods = state.allShippingMethods.map((m) => ({
            ...m,
            convertCharge: 0,
        }));
        if (Object.keys(state.selectedShippingMethod ?? {}).length)
            state.selectedShippingMethod.convertCharge = 0;
    },
    removeDiscountFromShipping(state) {
        if (state.allShippingMethods?.length === 0) return;
        state.allShippingMethods = state.allShippingMethods.map((m) => ({
            ...m,
            convertCharge: m.originalCharge,
        }));
        if (Object.keys(state.selectedShippingMethod ?? {}).length)
            state.selectedShippingMethod.convertCharge =
                state.selectedShippingMethod.originalCharge;
    },
    setCashback(state, cashback) {
        state.cashback = cashback;
    },
    setSelectedReferralCampaign(state, funnelSettings) {
        state.selectedReferralCampaign =
            state.referralCampaigns?.find(
                (e) => e.funnel_id === funnelSettings?.id && e.status
            ) ?? null;
    },
    setReferralPoints(state, points) {
        state.referralPoints = points;
    },
    setReferralActionInfo(state, info) {
        state.referralActionInfo = info;
    },
    setIsReferralUser(state, isReferralUser) {
        state.isReferralUser = isReferralUser;
    },
    setAllCategories(state, allCategories) {
        state.allCategories = allCategories;
    },
};

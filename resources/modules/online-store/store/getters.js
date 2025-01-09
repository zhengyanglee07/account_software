/* eslint-disable no-return-assign */
export default {
    state: (state) => state,

    hasPhysicalProduct: (state) =>
        state.cartItems.filter((c) => c.physicalProducts === 'on').length > 0,
    isTaxableProduct: (state) =>
        state.cartItems.filter((c) => c.isTaxable).length > 0,
    currency: (state) =>
        state.currencyDetails.currency === 'MYR'
            ? 'RM'
            : state.currencyDetails.currency,
    formatPrice:
        (state) =>
        (price, isDisplay = true) => {
            if (price === 0 || price === undefined || Number.isNaN(price))
                return 0;
            const {
                decimal_places: decimalPlaces,
                separator_type: separatorType,
                rounding,
            } = state.currencyDetails;
            let formatPrice = parseFloat(price);
            if (separatorType === ',') {
                formatPrice = parseFloat(price.toString().replace(/,/g, ''));
            } else if (separatorType === '.') {
                formatPrice = parseFloat(
                    price.toString().replace(/[^0-9.]+|\.(?=\d+\.\d+|$)/g, '')
                );
            }
            if (rounding && decimalPlaces !== 0)
                formatPrice = Math.ceil(formatPrice);
            formatPrice =
                decimalPlaces === 0
                    ? Math.floor(formatPrice).toFixed()
                    : formatPrice.toFixed(2);
            if (!isDisplay) return parseFloat(formatPrice);
            if (separatorType !== 'none')
                formatPrice = formatPrice.replace(
                    /\B(?=(\d{3})+\b)/g,
                    separatorType
                );
            return formatPrice;
        },
    calculatePrice:
        (state, getters) =>
        (price = 0, quantity = 1, isDisplay = false) => {
            if (Object.keys(state.currencyDetails ?? {}).length > 0) {
                const { isDefault, exchangeRate } = state.currencyDetails;
                let formattedPrice = getters.formatPrice(price, false);
                formattedPrice *= quantity;
                if (isDefault !== 1 && exchangeRate !== null)
                    formattedPrice *= exchangeRate;
                return getters.formatPrice(formattedPrice, isDisplay);
            }
            return 0;
        },
    subTotal: (state, getters) => {
        const cartItem = state.cartItems;
        let total = 0;
        cartItem.forEach((product) => {
            let productTotal = 0;
            if (product.isDiscountApplied) {
                productTotal = product.discount.valueAfterDiscount;
            } else {
                productTotal =
                    getters.formatPrice(product.productPrice, false) *
                    product.qty;
            }
            total += productTotal;
        });
        return getters.formatPrice(total, false);
    },
    cartSubTotal: (state) => {
        let total = 0;
        state.cartItems.forEach((product) => {
            total += parseFloat(product.productPrice);
        });
        return total;
    },
    productPriceArray: (state, getters) => {
        const priceArray = [];
        state.cartItems.forEach((product) => {
            const price = getters.calculatePrice(
                product.productPrice,
                product.qty,
                false
            );
            const afterCurrencyCoversionPrice = getters.formatPrice(
                price,
                false
            );
            priceArray.push(afterCurrencyCoversionPrice);
        });
        return priceArray;
    },
    productWeightTotal: (state) => {
        let totalWeight = 0;
        state.cartItems.forEach((product) => {
            let weight = 0;
            if (product.hasVariant) {
                weight = product.variant_details.find(
                    (p) => p.reference_key === product.variantRefKey
                )?.weight;
            } else {
                weight = product.weight;
            }
            totalWeight += parseFloat(weight) * product.qty;
        });
        return totalWeight;
    },
    shippingCharge: (state, getters) => (method) => {
        let charge = 0;
        const weight = getters.productWeightTotal;
        if (method.shipping_method === 'Based On Weight') {
            if (weight <= parseFloat(method.first_weight) && weight >= 0) {
                charge = method.first_weight_price;
            } else if (weight > parseFloat(method.first_weight)) {
                const additionalCharge =
                    ((weight - parseFloat(method.first_weight)) /
                        parseFloat(method.additional_weight)) *
                    parseFloat(method.additional_weight_price);
                charge =
                    parseFloat(method.first_weight_price) +
                    (Number.isNaN(additionalCharge) ? 0 : additionalCharge);
            }
        } else if (method.shipping_method === 'Flat Rate') {
            charge = method.per_order_rate;
        }
        return getters.formatPrice(charge, false);
    },
    shippingFee: (state, getters) => {
        if (state.isOrderSuccess)
            return getters.formatPrice(state.order.shipping);
        const fees =
            getters.validAppliedPromotion.filter(
                (promo) => promo.promotion.promotion_category === 'Shipping'
            ).length > 0
                ? 0
                : getters.formatPrice(
                      state.selectedShipping?.convertCharge ??
                          state.order?.shipping,
                      false
                  ) || 0.0;
        return fees;
    },
    calculateTax: (state, getters) => (price) => {
        const afterCurrencyCoversionPrice = getters.formatPrice(price, false);
        const taxRate = state.taxSetting.taxRate / 100;
        const afterTax = afterCurrencyCoversionPrice * taxRate;
        const tax = state.taxSetting.setting?.is_product_include_tax
            ? afterTax / (1 + taxRate)
            : afterTax;
        return getters.formatPrice(tax, false);
    },
    shippingTax: (state, getters) => {
        const shippingPromo =
            getters.validAppliedPromotion.filter(
                (promo) => promo.promotion.promotion_category === 'Shipping'
            ).length > 0;

        let tax = 0;
        if (
            !shippingPromo &&
            state.taxSetting.setting?.is_shipping_fee_taxable
        ) {
            tax = getters.calculateTax(getters.shippingFee);
        }
        return tax;
    },
    productTax: (state, getters) =>
        getters.formatPrice(
            // eslint-disable-next-line no-return-assign
            getters.productTaxArray.reduce((total, tax) => (total += tax), 0),
            false
        ) || 0.0,
    productTaxArray: (state, getters) => {
        const taxArray = [];
        state.cartItems.forEach((product) => {
            let afterProductDiscountPrice = 0;
            if (product.isDiscountApplied) {
                afterProductDiscountPrice = product.discount.valueAfterDiscount;
            } else {
                afterProductDiscountPrice = product.productPrice * product.qty;
            }

            const afterOrderDiscountPrice = getters.afterOrderDiscountPrice(
                getters.formatPrice(afterProductDiscountPrice, false)
            );

            const price = getters.calculatePrice(
                afterOrderDiscountPrice,
                1,
                false,
                true
            );

            const tax = product.isTaxable ? getters.calculateTax(price) : 0;
            taxArray.push(getters.formatPrice(tax, false));
        });
        return taxArray;
    },
    afterOrderDiscountPrice: (state, getters) => (price) => {
        let priceAfterDiscount = price;
        const appliedPromo = state.appliedPromotion.find(
            (p) => p.promotion.promotion_category === 'Order'
        );
        if (appliedPromo) {
            let discountPrice = 0;
            if (
                appliedPromo.promotion.promotion_type.order_discount_type ===
                'percentage'
            ) {
                discountPrice =
                    price *
                    (appliedPromo.promotion.promotion_type
                        .order_discount_value /
                        100);
            } else {
                const totalPrice = state.cartItems.reduce(
                    (total, item) => (total += item.productPrice * item.qty),
                    0
                );
                discountPrice =
                    (price / totalPrice) *
                    appliedPromo.promotion.promotion_type.order_discount_value;
            }
            priceAfterDiscount = price - discountPrice;
        }
        return getters.formatPrice(priceAfterDiscount, false);
    },
    taxTotal: (state, getters) =>
        getters.formatPrice(getters.productTax, false) +
        getters.formatPrice(getters.shippingTax, false),
    cashbackTotal: (state, getters) => {
        let cashbackTotal =
            getters.grandTotal * (parseFloat(state.cashbackPercentage) / 100);

        if (state.cashbackDetail?.capped_amount) {
            // eslint-disable-next-line no-unsafe-optional-chaining
            const cappedAmount = state.cashbackDetail?.capped_amount / 100;
            cashbackTotal =
                cashbackTotal > cappedAmount ? cappedAmount : cashbackTotal;
        }
        return getters.formatPrice(cashbackTotal, false);
    },
    grandTotal: (state, getters) => {
        let tax = 0;
        tax = state.taxSetting.setting?.is_product_include_tax
            ? 0
            : getters.taxTotal;

        const storeCredit = state.isCustomerSignedIn
            ? getters.formatPrice(state.processedContactBalanceToBeUsed, false)
            : 0;
        const grandTotal =
            getters.formatPrice(getters.totalAfterDiscount, false) +
            getters.formatPrice(getters.shippingFee, false) +
            getters.formatPrice(tax, false) -
            getters.formatPrice(storeCredit, false);
        return grandTotal <= 0 ? 0 : getters.formatPrice(grandTotal, false);
    },
    promotionsInfo: (state, getters) => {
        const customerInfo = JSON.parse(localStorage.getItem('formDetail'));
        const cartItem = state.cartItems;
        return {
            accountId: state.cartItems[0]?.account_id,
            customerEmail: customerInfo?.customerInfo.email,
            customerCountry: customerInfo?.shipping?.country,
            isPhysicalProduct: getters.hasPhysicalProduct,
            productArray: cartItem,
            currencyInfo: state.currencyDetails,
            customerInfo: customerInfo ?? null,
            subTotal: getters.subTotal,
            totalAfterDiscount: getters.subTotal,
            selectedShipping: state.selectedShipping ?? null,
        };
    },
    validAppliedPromotion: (state) => {
        const validPromo = state.appliedPromotion.filter(
            (promo) => promo.valid_status
        );
        return validPromo;
    },
    manualDiscount: (state, getters) => {
        const discount =
            getters.validAppliedPromotion
                .filter(
                    (promo) =>
                        promo.discountValue.type === 'manual' &&
                        promo.discountValue.category === 'Order'
                )
                .reduce(
                    (total, promo) => total + promo.discountValue.value,
                    0
                ) || 0;
        return getters.formatPrice(discount);
    },
    automatedDiscount: (state, getters) => {
        const discount =
            getters.validAppliedPromotion
                .filter(
                    (promo) =>
                        (promo.discountValue.type === 'automated' ||
                            promo.discountValue.type === 'automatic') &&
                        promo.discountValue.category === 'Order'
                )
                .reduce(
                    (total, promo) => total + promo.discountValue.value,
                    0
                ) || 0;
        return getters.formatPrice(discount, false);
    },
    totalDiscount: (state, getters) => {
        const total = getters.manualDiscount + getters.automatedDiscount;
        return getters.formatPrice(total, false);
    },
    totalAfterProductDiscount: (state, getters) => {
        let total = 0;
        state.cartItems.forEach((c) => {
            const price = c.productGrandTotal ?? c.productPrice * c.qty;
            if (c.isDiscountApplied) {
                total += c.discount.valueAfterDiscount;
            } else {
                total += price;
            }
        });
        return getters.formatPrice(total, false);
    },
    totalAfterDiscount: (state, getters) => {
        const totalAfterDiscount =
            getters.totalAfterProductDiscount - getters.totalDiscount;
        return totalAfterDiscount <= 0
            ? 0
            : getters.formatPrice(totalAfterDiscount, false);
    },
    hasOutOfStockProduct: (state) => {
        let hasOutOfStock = false;
        const storedProduct = JSON.parse(
            localStorage.getItem('products') ?? '[]'
        );
        const outOfStockArray = [];
        state.cartItems.forEach((item) => {
            const productDetail = storedProduct.find(
                (e) => e.reference_key === item.reference_key
            );
            const isActive = item.status === 'active';
            let isOutOfStock = false;
            let isVariantExists = true;
            let isCustomizationExists = true;
            let isVariantPurchasable = true;
            let isVariantOutOfStock = false;
            let isLatestCustomization = true;
            const isLatestCustomOption = item.custom_options?.every((option) =>
                productDetail.customOption?.some(
                    (optionId) => option.id === optionId
                )
            );
            const isProductTypeChanged =
                productDetail.hasVariant !== item.hasVariant;
            const isValidSalesChannel = item.saleChannels.includes(
                state.saleChannel
            );

            if (item.hasVariant) {
                const combIds = productDetail.combinationIds;
                const variant = (item.variant_details ?? []).find((v, i) =>
                    combIds?.every((combId, index) =>
                        combIds.includes(v[`option_${index + 1}_id`])
                    )
                );
                isVariantExists = !!variant;
                if (isVariantExists) {
                    isVariantOutOfStock =
                        !variant.is_selling && item.qty > variant.quantity;
                    isVariantPurchasable = variant.is_visible;
                }
            } else {
                isOutOfStock = !item.is_selling && item.qty > item.quantity;
            }

            if (productDetail.customizations) {
                productDetail.customizations.forEach((custom) => {
                    const selectedOption = item.custom_options
                        ?.find((c) => c.id === custom.id)
                        ?.inputs.find((input) =>
                            custom.values.some((v) => v.id === input.id)
                        );
                    if (selectedOption) {
                        const currentUpdatedAt = custom.values.find(
                            (val) => val.id === selectedOption.id
                        ).updatedAt;
                        const latestUpdatedAt = selectedOption.updated_at;
                        isLatestCustomization =
                            isLatestCustomization &&
                            new Date(currentUpdatedAt) >=
                                new Date(latestUpdatedAt);
                    } else isCustomizationExists = false;
                });
            }

            // Condition for all OS & MS pages
            const baseCondition =
                !isLatestCustomization ||
                !isLatestCustomOption ||
                !isVariantExists ||
                !isCustomizationExists ||
                !isVariantPurchasable ||
                isProductTypeChanged;

            // Condition for checkout pages only
            const conditionForCheckout =
                !isActive ||
                isOutOfStock ||
                isVariantOutOfStock ||
                !isValidSalesChannel;

            const isCheckoutPage =
                window.location.pathname.includes('checkout');
            if (
                baseCondition ||
                (isCheckoutPage ? conditionForCheckout : false)
            ) {
                outOfStockArray.push(item.reference_key);
                hasOutOfStock = hasOutOfStock || true;
            }
        });
        localStorage.setItem(
            'outOfStockProduct',
            JSON.stringify(outOfStockArray)
        );
        return hasOutOfStock;
    },
    isShippingRequired: (state, getters) => {
        const scheduleType = state.selectedDeliveryHour?.type;
        const isSelectedPickup = scheduleType === 'pickup';
        return !isSelectedPickup && getters.hasPhysicalProduct;
    },
    isPickupOnly: (state, getters) => {
        const { preferences } = state;
        const hasStorePickup = preferences?.is_enable_store_pickup;
        const hasDeliveryHour = preferences?.delivery_hour_type === 'custom';
        return getters.hasPhysicalProduct && hasStorePickup && !hasDeliveryHour;
    },

    isMiniStore: (state) => {
        return state.saleChannel === 'mini-store';
    },
    hasTaxableProduct: (state) =>
        state.cartItems.filter((c) => c.isTaxable).length > 0,
};

export default {
    emitPromoType: (state, promoType) => {
        state.promotionSetting.promotionType = promoType;
    },

    updatePromoError: (state, errors) => {
        state.errors = { ...errors };
    },

    updatePromoGeneral: (state, { type, value }) => {
        state.promotionGeneral[type] = value;
    },

    updateSetting: (state, { type, value, key, index = null }) => {
        if (index) state.promotionSetting[type][index][key] = value;
        else state.promotionSetting[type] = value;
    },

    updatePromoExtraCondition: (state, { type, value }) => {
        state.promotionExtraCondition[type] = value;
    },

    deleteSelectedCountries: (state, { index }) => {
        state.promotionSetting.selectedCountries.splice(index, 1);
    },

    deleteSelectedProduct: (state, { index, product }) => {
        const locatedProduct = state.allProducts.find((item) => {
            return item.product.id === product.product.id;
        });
        if (locatedProduct) {
            locatedProduct.product.isCheckedAll = false;
            if (locatedProduct.product.hasVariant) {
                locatedProduct.combinationVariation.forEach((combination) => {
                    combination.value.isChecked = false;
                });
            }
            locatedProduct.product.indeterminate = false;
        }
        state.promotionSetting.selectedProduct.splice(index, 1);
    },

    deleteErrorMessages: (state, { type }) => {
        if (type) delete state.errors[type];
        else state.errors = {};
    },

    loadPromoSetting: (state, setting) => {
        state.setting = { ...setting };
    },

    storeAvailableRegion: (state, setting) => {
        state.availableRegion = [...setting];
    },

    updateAvailableRegion: (state, { value, key, index }) => {
        state.availableRegion[index][key] = value;
    },
    setAllProducts: (state, products) => {
        state.allProducts = [...products];
    },
    updateCheckKey: (
        state,
        { value, key, combKey, checkKey, index = null, combIdx = null }
    ) => {
        if (combIdx !== null)
            state.allProducts[index][key][combIdx][combKey][checkKey] = value;
        else state.allProducts[index][key][checkKey] = value;
    },
    setCurrency: (state, currency) => {
        state.currency = currency;
    },
    setAllCategories: (state, category) => {
        state.allCategories = category;
    },
    updateSelectedCategory: (state, { type, value, index }) => {
        state.allCategories[index][type] = value;
    },
    deleteSelectedCategory: (state, { index, category }) => {
        const locatedCategory = state.allCategories.find(
            (item) => item.id === category.id
        );
        if (locatedCategory !== undefined) {
            locatedCategory.isChecked = false;
        }
        state.promotionSetting.selectedCategory.splice(index, 1);
    },
    updateShippingZone(state, zone) {
        state.availableRegion.push(zone);
    },
    updateAllSegments(state, segments) {
        state.allSegments = [...segments];
    },
    initializePromotionState(state) {
        Object.entries(state.initialState).forEach(([key, value]) => {
            state[key] = value;
        });
        Object.entries(state.initialPromotionSetting).forEach(
            ([key, value]) => {
                state.promotionSetting[key] = value;
            }
        );
    },
};

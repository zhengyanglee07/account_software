import { computed, onMounted, ref, inject } from 'vue';
import { useStore } from 'vuex';
import { useSetFormDetail } from '@onlineStore/hooks/useFormDetail.js';
import { useGetCartData } from '@onlineStore/hooks/useCart.js';
import { useSetManualShippingMethods } from '@onlineStore/hooks/useShipping.js';
import {
    usePromotionConstruct,
    useSetAppliedPromotion,
    useSetHasPromotion,
} from '@onlineStore/hooks/usePromotion.js';

let store = null;
let isFormDetailInitialized = false;

export function useInitializeTwoStepForm(data) {
    store = useStore();
    usePromotionConstruct();

    if (!isFormDetailInitialized) {
        useSetFormDetail(data.formDetail);
        isFormDetailInitialized = true;
    }
    store.commit('onlineStore/setSalesChannel', data.salesChannel);
    store.commit('onlineStore/setPreferences', data.preferences);
}

const isOutOfStock = ref(false);
export function useInitializeSecondStepFormDetail(data) {
    isOutOfStock.value = data.isOutOfStock;
    store.commit('onlineStore/setSelectedCartItems', data.selectedProduct);
    store.commit('onlineStore/setTaxSetting', data.taxSettings);
    useSetManualShippingMethods(data.manualShipping, data.selectedShipping);
    useSetHasPromotion(data.hasActivePromotion);
    useSetAppliedPromotion(data.promotion);
}

export function useGetTwoStepFormData() {
    const product = computed(() => useGetCartData().cartItems.value[0] ?? {});
    return {
        isOutOfStock,
        product,
    };
}

export function useSetSelectedProductInBuilder(settings) {
    store = store ?? useStore();
    const isInBuilderMode = computed(
        () => store.state.builder.mode === 'Builder'
    );

    if (isInBuilderMode.value) {
        const product = { ...settings?.selectedProduct };
        product.netPrice = parseFloat(product.productPrice);
        product.qty = 1;
        store.commit('onlineStore/setSelectedCartItems', [product]);
    }
}

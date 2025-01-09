import axios from '@services/axios.js';

export default {
    index(cartItems) {
        return axios.post('/check/inventory', {
            cartItem: cartItems.map((p) => ({
                ...p,
                productCombinationId: p.product_combination_id,
                variantCombinationId: p.variantRefKey,
                refKey: p.reference_key,
            })),
            accessTime:
                localStorage.getItem('accessTime') ??
                parseInt(new Date().getTime() / 1000, 10),
        });
    },
};

import axios from 'axios';

export default {
    index(params) {
        return axios.post('/checkout/info', params);
    },
    saveCartItems(cartItems) {
        return axios.post('/checkout/cart', { cartItems });
    },
};

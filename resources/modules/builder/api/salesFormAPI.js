import axios from 'axios';

export default {
    saveOrder(data) {
        return axios.post('/orders/save', data);
    },
    oneClickCheckout(data) {
        return axios.post('/payments/oneClickCheckout', data);
    },
    stripeAPI() {
        return axios.get('/payments/stripe/api');
    },
    salesFormCheckout(data) {
        return axios.post('/payments/checkout', data);
    },
    checkShippingRegion(data) {
        return axios.post('/payments/check/shipping/region', data);
    },
};

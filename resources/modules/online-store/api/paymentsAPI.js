import axios from 'axios';

export default {
    //* Senangpay
    getHash(data) {
        return axios.post('/payments/get/hash', data);
    },
    senangpay(paymentRef, extraParams) {
        return axios.get(
            `/payments/senangpay/detail${
                window.location.search
            }&payment_references=${
                paymentRef || localStorage.getItem('payment_ref')
            }&url=${window.location.host}${extraParams}`
        );
    },

    //* Stripe
    createStripePaymentIntent(isLanding) {
        return axios.get(
            `/payments/stripe/paymentIntent${
                isLanding ? '?isLanding=true' : ''
            }`
        );
    },

    //* Stripe FPX
    createStripeFPXPaymentIntent() {
        return axios.get('/payments/stripe/fpx/paymentIntent');
    },

    makeCheckout(data) {
        return axios.post('/payments/checkout', data);
    },
};

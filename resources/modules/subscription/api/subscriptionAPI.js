import axios from 'axios';

export default {
    update(data) {
        return axios.post('/subscription/plan/update', data);
    },
    saveSubscriptionDetail(data) {
        return axios.post('/subscription/detail/save', data);
    },
    getPromoEligibility(type) {
        return axios.get(`/subscription/promo/eligibility?type=${type}`);
    },
    validatePromoCode(promoCode) {
        return axios.post('/subscription/promoCode/validate', {
            promoCode,
        });
    },
    changePaymentMethod(paymentMethodId) {
        return axios.post('/subscription/paymentMethods/change', {
            paymentMethodId,
        });
    },
    saveCardDetail(paymentMethod, cardHolder) {
        return axios.post('/subscription/cardDetail/save', {
            paymentMethod,
            cardHolder,
        });
    },
    getSubscription(invoice) {
        return axios.post('/subscription/detail', {
            invoice,
        });
    },
    getRetryInvoice() {
        return axios.get('/subscription/retry/invoice');
    },
    saveRetryInvoice(data) {
        return axios.post('/subscription/save/retry/invoice', data);
    },
    deleteRetryInvoice() {
        return axios.delete('/subscription/delete/retry/invoice');
    },
    getYearlySubscriptionCoupon() {
        return axios.get('/subscription/get/yearly/subscription/coupons');
    },
};

import axios from 'axios';

export default {
    findByPaymentRef(paymentRef) {
        return axios.get(`/orders/${paymentRef}`);
    },
    saveCheckoutOrder(data) {
        return axios.post('/checkout/save/order', data);
    },
    changePaymentStatus(status, data) {
        return axios.post(`/checkout/update/payment/${status}`, data);
    },
    delyvaOrderTrack(params) {
        return axios.get('delyva/track/order', { params });
    },
};

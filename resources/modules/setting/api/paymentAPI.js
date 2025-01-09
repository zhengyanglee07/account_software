import axios from 'axios';

export default {
    update(paymentMethod) {
        return axios.post('/payment/settings/save', paymentMethod);
    },
};

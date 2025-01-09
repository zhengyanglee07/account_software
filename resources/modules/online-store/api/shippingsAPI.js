import axios from 'axios';

export default {
    getLalamove() {
        return axios.post('/lalamove/check/quotation');
    },
    getDelyva() {
        return axios.post('/delyva/check/quotation');
    },
    getEasyParcel() {
        return axios.post('/easyparcel/check/quotation');
    },
};

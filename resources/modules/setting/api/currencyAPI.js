import axios from 'axios';

export default {
    index() {
        return axios.get('/currency/all');
    },
    update(data) {
        return axios.post('/currency/setting/save', data);
    },
    delete(currencyId) {
        return axios.get(`/currency/delete/${currencyId}`);
    },
    defaultCurrency() {
        return axios.get('/currency/default');
    },
    updateRounding(rounding) {
        return axios.post('/currency/rounding/settings/save', {
            rounding,
        });
    },
    getLatestRate(currentCurrency, defaultCurrency) {
        return axios.post('/currency/latest/rate', {
            currentCurrency,
            defaultCurrency,
        });
    },
};

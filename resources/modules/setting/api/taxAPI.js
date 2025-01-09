import axios from 'axios';

export default {
    index() {
        return axios.get('/tax/detail');
    },
    update(isProductIncludeTax, isTaxIncludeShipping) {
        return axios.post('/tax/setting/save', {
            isProductIncludeTax,
            isTaxIncludeShipping,
        });
    },
    updateCountry(data) {
        return axios.post('/tax/setting/country/save', data);
    },
    delete(id) {
        return axios.delete(`/tax/setting/delete/${id}`);
    },
};

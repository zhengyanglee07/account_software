import axios from 'axios';

export default {
    update(data) {
        return axios.post('/checkout/settings/save', data);
    },
};

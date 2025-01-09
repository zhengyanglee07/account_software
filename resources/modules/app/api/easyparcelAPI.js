import axios from 'axios';

export default {
    index() {
        return axios.get('/easyparcel/settings');
    },
    update(apiKey) {
        return axios.post('/easyparcel/save', {
            apiKey,
        });
    },
};

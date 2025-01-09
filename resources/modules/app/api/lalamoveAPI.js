import axios from 'axios';

export default {
    index() {
        return axios.get('/lalamove/settings');
    },
    update(data) {
        return axios.post('/lalamove/save', data);
    },
};

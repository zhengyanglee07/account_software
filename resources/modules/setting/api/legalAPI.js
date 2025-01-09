import axios from 'axios';

export default {
    update(data) {
        return axios.post('/legal/setting/save', data);
    },
};

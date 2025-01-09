import axios from 'axios';

export default {
    update(data) {
        return axios.post('/location/setting/save', data);
    },
};

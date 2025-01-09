import axios from 'axios';

export default {
    index() {
        return axios.get('/currency/get');
    },
};

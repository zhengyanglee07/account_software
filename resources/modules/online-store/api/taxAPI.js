import axios from 'axios';

export default {
    index(country, state) {
        return axios.get(`/tax/${country}/${state}`);
    },
};

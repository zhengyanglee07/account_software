import axios from 'axios';

export default {
    logout(redirectUrl = '/checkout/information') {
        return axios.get(`/logout?redirectUrl=${redirectUrl}`);
    },
};

import axios from 'axios';

export default {
    orderUpdate(params, headers) {
        return axios.post(
            'https://api.delyva.app/v1.0/webhook',
            params,
            headers
        );
    },
    saveDetail(params) {
        return axios.post(`/delyva/save-edit`, params);
    },
};

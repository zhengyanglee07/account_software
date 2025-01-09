import axios from 'axios';

export default {
    create(funnelname) {
        return axios.post('/funnel/create', { funnelname });
    },
    updateStatus(apiKey) {
        return axios.post('/easyparcel/save', {
            apiKey,
        });
    },
    updateSettings(id, data) {
        return axios.put(`/funnel/${id}/update`, data);
    },
};

import axios from 'axios';

export default {
    create(id, nameArray) {
        return axios.post('/landing/create/from/template', {
            id,
            nameArray,
        });
    },
    update(landingId, data) {
        return axios.put(`/landing/update/status/${landingId}`, data);
    },
    duplicate(data) {
        return axios.post('/landing/duplicate', data);
    },
    delete(id) {
        return axios.delete(`/landing/delete/${id}`);
    },
    reorder(fromIndex, toIndex, funnelId) {
        return axios.post('/funnel/reorder/landing', {
            fromIndex,
            toIndex,
            funnelId,
        });
    },
};

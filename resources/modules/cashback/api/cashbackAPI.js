import axios from 'axios';

export default {
    create(data) {
        return axios.post('/cashback/create', data);
    },
    update(selectedId, data) {
        return axios.post(`/cashback/update/${selectedId}`, data);
    },
    delete(selectedId) {
        return axios.delete(`/cashback/delete/${selectedId}`);
    },
};

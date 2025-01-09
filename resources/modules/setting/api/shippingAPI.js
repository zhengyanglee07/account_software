import axios from 'axios';

export default {
    index() {
        return axios.get('/shipping/detail');
    },
    create(data) {
        return axios.post('/shipping/setting/save', data);
    },
    update(data) {
        return axios.post('/shipping/setting/update', data);
    },
    delete(id) {
        return axios.delete(`/shipping/setting/delete/${id}`);
    },
    updateDeliveryHour(data) {
        return axios.post('/shipping/setting/deliveryhour', data);
    },
    updateStorePickup(data) {
        return axios.post('/shipping/setting/storepickup', data);
    },
};

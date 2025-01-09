import axios from 'axios';

export default {
    update(data) {
        return axios.post('/billing/setting/save', data);
    },
    terminate() {
        return axios.get('/subscription/terminate');
    },
    reactiveSubscription() {
        return axios.post('/subscription/plan/update', {
            type: 'reactive',
        });
    },
};

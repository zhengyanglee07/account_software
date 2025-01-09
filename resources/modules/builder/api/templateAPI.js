import axios from 'axios';

export default {
    index() {
        return axios.get('/templates/all');
    },

    create(name, type, accountId, templateObject) {
        return axios.post('/templates/user', {
            name,
            type,
            accountId,
            templateObject: JSON.stringify(templateObject),
        });
    },

    delete(id) {
        return axios.delete(`/templates/user/${id}`);
    },

    update(obj) {
        return axios.put(`/templates/update/${obj?.id}`, obj);
    },

    uploadSnapshot(obj) {
        return axios.post('/templates/general/snapshot', obj);
    },
};

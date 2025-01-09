import axios from 'axios';

export default {
    updateOrCreate(postUrl, data) {
        return axios.post(postUrl, data);
    },

    createEmailTemplate() {
        return axios.post('/emails/template/create');
    },

    createEmailDesign(referenceKey, params) {
        return axios.post(`/emails/${referenceKey}/design/create`, params);
    },

    updateEmailTemplate(referenceKey, name, status) {
        return axios.put(`/emails/design/template/${referenceKey}/update`, {
            name,
            status,
        });
    },

    bulkEdit({ templateIds, status }) {
        return axios.put('/templates/bulk-update', {
            status,
            templateIds,
        });
    },

    delete(url) {
        return axios.delete(url);
    },
};

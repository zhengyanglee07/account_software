import axios from 'axios';

export default {
    index() {
        return axios.get('/images/all');
    },

    upload(formData) {
        return axios.post('/images/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },

    delete(id) {
        return axios.delete(`/images/delete/${id}`);
    },
};

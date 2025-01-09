import axios from 'axios';

export default {
    axiosPut(url) {
        return axios.put(url);
    },
    axiosDelete(url) {
        return axios.delete(url);
    },
    updatePreferences(data) {
        return axios.put('/online-store/update/preferences', data);
    },
    updateStatus(id, isPublished) {
        return axios.put(`/landing/update/status/${id}`, {
            is_published: isPublished,
        });
    },
    createMenu(menuTitle, newArray) {
        return axios.post('/online-store/menu/create', {
            menuTitle,
            newArray,
        });
    },
    updateMenu(data) {
        return axios.post('/online-store/menu/update', data);
    },

    deleteMenu(id) {
        return axios.delete(`/online-store/menu/${id}`);
    },
};

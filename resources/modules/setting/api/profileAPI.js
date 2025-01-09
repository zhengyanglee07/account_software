import axios from 'axios';

export default {
    updateProfile(firstName, lastName) {
        return axios.post('/profile/setting/save', {
            firstName,
            lastName,
        });
    },
    updatePassword(password) {
        return axios.post('/profile/password/change', password);
    },
};

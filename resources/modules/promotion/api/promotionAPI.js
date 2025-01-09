import axios from 'axios';

export default {
    update(data) {
        return axios.post('/promotion/save', data);
    },
    delete(id, type) {
        return axios.post('/promotion/delete', { id, type });
    },
    getProducts() {
        return axios.get('/product/getallproduct');
    },
    getCategories() {
        return axios.get('/product/getallcategory');
    },
    getSegments() {
        return axios.get('/promotion/getAllSegments');
    },
    getShippingRegion() {
        return axios.get('/promotion/getShippingRegion');
    },
};

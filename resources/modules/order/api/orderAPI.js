import axios from 'axios';

export default {
    post(url, data) {
        return axios.post(url, data);
    },
    update(data) {
        return axios.post('/orders/update', data);
    },
    updateOrderDetail(data) {
        return axios.post('/orders//update/details', data);
    },
    deleteOrderDetail(orders) {
        return axios.post('/orders/delete/details', {
            orders,
        });
    },
    getLatest() {
        axios.get('/orders?requestSource=axios');
    },
    markAsPaid(data) {
        return axios.post('/orders/markAsPaid', data);
    },
    updateFulfillment(data) {
        return axios.post('/orders/details/fulfillmentUpdate', data);
    },
    cancelFulfillment(data) {
        return axios.post('/orders/details/fulfillmentCancel', data);
    },
    checkShippingSettings() {
        return axios.get('/shipping/settings/check');
    },
    refund(data) {
        return axios.post('/orders/details/makeRefund', data);
    },

    /*
     * Easyparcel
     */
    easyparcelOrderStatus(orderId) {
        return axios.post('/easyparcel/check/order-status', { orderId });
    },
    easyparcelQuotation(data) {
        return axios.post('/easyparcel/check/quotation', data);
    },
    easyparcelFulfill(refKey, data) {
        return axios.post(`/orders/details/${refKey}/easyparcel/fulfill`, data);
    },

    /*
     * Lalamove
     */
    lalamoveOrder(deliveryId) {
        return axios.get(`/lalamove/check/orders/${deliveryId}`);
    },
    lalamoveDriverDetails(deliveryId, driverId) {
        return axios.get(
            `/lalamove/check/orders/${deliveryId}/drivers/${driverId}`
        );
    },
    lalamoveQuotation(data) {
        return axios.post('/lalamove/check/quotation', data);
    },
    lalamoveFulfill(refKey, data) {
        return axios.post(`/orders/details/${refKey}/lalamove/fulfill`, data);
    },

    /*
     * Delyva
     */
    delyvaQuotation(data) {
        return axios.post('/delyva/check/quotation', data);
    },
    delyvaFulfill(refKey, data) {
        return axios.post(`/delyva/orders/details/${refKey}/fulfill`, data);
    },
};

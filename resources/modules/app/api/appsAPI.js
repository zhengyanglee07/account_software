import axios from 'axios';

export default {
    saveMiniStore() {
        return axios.post('/apps/mini-store/setup');
    },

    updateSalesChannel(selectedSaleChannel, currentSaleChannel) {
        return axios.post('/apps/update/sale-channel', {
            selectedSaleChannel,
            currentSaleChannel,
        });
    },
    updateApps(selectedApp, currentChannel) {
        return axios.post('/apps/update/app', {
            selectedApp,
            currentChannel,
        });
    },
    updateFeature(selectedFeature) {
        return axios.get(`/apps/update/feature/${selectedFeature}`);
    },
};

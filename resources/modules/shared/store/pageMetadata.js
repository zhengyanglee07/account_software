export default {
    state() {
        return {
            storeDetails: {},
        };
    },

    mutations: {
        setStoreDetails(state, details) {
            state.storeDetails = details;
        },
    },

    namespaced: true,
};

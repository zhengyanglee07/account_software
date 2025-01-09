export default {
    loginCustomerInfo(state, info) {
        state.customerInfo = info;
    },
    setAddress(state, address) {
        state.address = address;
    },
    setRequireAccount(state, requireAccount) {
        state.requireAccount = requireAccount;
    },
};

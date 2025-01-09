export default {
    isCustomerAuthenticated: (state) => {
        return !!state.customerInfo;
    },
    hasCustomerAccount: (state) => {
        return state.requireAccount !== 'disabled';
    },
    isCustomerAccountRequired: (state) => {
        return state.requireAccount === 'required';
    },
};

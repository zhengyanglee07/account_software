export default {
    setState(state, { key, value }) {
        state[key] = { ...value };
    },

    updateState(state, { key, name, value }) {
        state[key][name] = value;
    },

    deleteErrorMessages(state, { type }) {
        if (type) delete state.errors[type];
        else state.errors = {};
    },

    setErrors(state, { key, value }) {
        state.errors[key] = value;
    },
};

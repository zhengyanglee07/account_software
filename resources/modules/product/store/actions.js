export default {
    fetchProductOption: ({ state, commit }, { productOptionArray }) => {
        commit('currentProductOption', productOptionArray);
    },
    fetchName: ({ state, commit }, { nameArray }) => {
        commit('currentName', nameArray);
    },
};

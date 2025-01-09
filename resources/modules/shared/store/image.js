import imagesAPI from '@shared/api/imagesAPI.js';

export default {
    state() {
        return {
            images: [],
            selectedImage: null,
        };
    },

    mutations: {
        setInitialImages(state, images) {
            state.images = images;
        },

        addNewImages(state, images) {
            state.images = [...state.images, ...images];
        },

        deleteImage(state, id) {
            const index = state.images.findIndex((img) => img.id === id);
            state.images.splice(index, 1);
        },
    },

    actions: {
        fetchImages({ commit }) {
            imagesAPI
                .index()
                .then((response) => {
                    commit('setInitialImages', response.data.images);
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        async imageUrlToBase64({ state }, imageUrl) {
            const data = await fetch(imageUrl);
            const blob = await data.blob();
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onload = () => {
                    const base64data = reader.result;
                    resolve(base64data);
                };
            });
        },
    },

    namespaced: true,
};

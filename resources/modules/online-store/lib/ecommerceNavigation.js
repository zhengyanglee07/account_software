import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

// TODO Darren: change to vuex
export const nested = {
    elements: [],
};

export const store = {
    menuItemList: [],
    latestId: 0,
    allPages: [],
    allProducts: [],
    tempRefKeyList: [],
};

export const mutations = {
    addMenuItem: ({ name, link, refKey }) => {
        store.latestId += 1;
        const min = 100000000001;
        const max = 999999999999;
        let randomRefKey = 0;
        do {
            randomRefKey = parseInt(Math.random() * (max - min) + min);
            if (!store.tempRefKeyList.includes(randomRefKey))
                store.tempRefKeyList.push(randomRefKey);
        } while (!store.tempRefKeyList.includes(randomRefKey));
        // console.log(refKey);
        if (refKey) {
            let index = nested.elements.findIndex(
                (elements) => elements.refKey === refKey
            );

            if (index !== -1)
                nested.elements.push({
                    id: store.latestId,
                    name,
                    link,
                    refKey: randomRefKey,
                    elements: [],
                });
            if (index === -1) {
                for (let i = 0; i < nested.elements.length; i++) {
                    index = nested.elements[i].elements.findIndex(
                        (elements) => elements.refKey === refKey
                    );
                    if (index !== -1) {
                        nested.elements[i].elements.push({
                            id: store.latestId,
                            name,
                            link,
                            refKey: randomRefKey,
                            elements: [],
                        });
                        break;
                    }
                }
            }

            if (index === -1) {
                for (let i = 0; i < nested.elements.length; i++) {
                    for (
                        let j = 0;
                        j < nested.elements[i].elements.length;
                        j++
                    ) {
                        index = nested.elements[i].elements[
                            j
                        ].elements.findIndex(
                            (elements) => elements.refKey === refKey
                        );
                        // console.log(index, 'run');
                        if (index !== -1) {
                            // console.log(nested.elements[i].elements[j].elements[index].name );
                            nested.elements[i].elements[j].elements.push({
                                id: store.latestId,
                                name,
                                link,
                                refKey: randomRefKey,
                                elements: [],
                            });
                            break;
                        }
                    }
                    if (index !== -1) {
                        break;
                    }
                }
            }
            return;
        }

        nested.elements.push({
            id: store.latestId,
            name,
            link,
            refKey: randomRefKey,
            elements: [],
        });
    },

    updateLatestId: () => {
        for (let i = 0; i < store.menuItemList.length; i++) {
            if (store.menuItemList[i + 1] === undefined) {
                break;
            }
            if (store.menuItemList[i].id < store.menuItemList[i + 1].id) {
                store.latestId = store.menuItemList[i + 1].id;
            }
        }
    },

    setMenuItemList: (newArray, menuTitle, menuId) => {
        store.menuItemList = JSON.parse(newArray ?? '[]');
        store.menuItemList = store.menuItemList ?? [];
        for (let i = 0; i < store.menuItemList.length; i++) {
            if (!store.menuItemList[i].elements) {
                store.menuItemList[i].elements = [];
                if (
                    store.menuItemList[i].link &&
                    !store.menuItemList[i].link.path
                ) {
                    const selectedProduct = store.allProducts.find(
                        (item) => item.id === store.menuItemList[i].link.id
                    );
                    if (!selectedProduct) {
                        store.menuItemList[i].link.path = '/products/undefined';
                    } else {
                        store.menuItemList[
                            i
                        ].link.path = `/products/${selectedProduct.path}`;
                    }
                }
            }
        }
        nested.elements = store.menuItemList;
        if (store.menuItemList.length !== 0) {
            if (store.menuItemList[0].elements.length === 0) {
                onlineStoreAPI
                    .updateMenu({
                        inputTitle: menuTitle,
                        menuItemArray: store.menuItemList,
                        id: menuId,
                    })
                    .then((response) => {
                        nested.elements = response.data.menu.menu_items;
                    });
            }
        }

        const min = 100000000001;
        const max = 999999999999;
        let randomRefKey = 0;

        if (nested.elements.length !== 0 && !nested.elements[0].refKey) {
            for (let i = 0; i < nested.elements.length; i++) {
                do {
                    randomRefKey = parseInt(Math.random() * (max - min) + min);
                    if (!store.tempRefKeyList.includes(randomRefKey))
                        store.tempRefKeyList.push(randomRefKey);
                } while (!store.tempRefKeyList.includes(randomRefKey));
                nested.elements[i].refKey = randomRefKey;
            }
        }
    },

    setAllPages: (newArray) => {
        store.allPages = newArray ?? [];
    },

    setAllProducts: (newArray) => {
        store.allProducts = newArray ?? [];
    },

    updateElements: (payload) => {
        nested.elements = payload;
    },
};

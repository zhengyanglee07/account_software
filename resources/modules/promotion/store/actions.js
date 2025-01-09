/*
|------------------------------------------------------------------
| perform asynchronous operations
| #important : always commit mutations to update state
|------------------------------------------------------------------
| function_name : ({state,commit,getters,dispatch }, payload) => {}
| state : allow us to access the same state object as
|         in current landing store instance
| commit : commit mutations to update state
| getters : call getters to get and manipulate state
| dispatch : call another actions
| payload : the data pass together with this.$store.dispatch
|------------------------------------------------------------------
| call by this.$store.dispatch('landing/function_name', payload)
| or by mapActions :
| methods : {
|      ...mapActions('landing', {
|          'name' : 'function_name',
|      })
| }
*/
import promotionAPI from '@promotion/api/promotionAPI.js';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

export default {
    loadPromotionSetting: async ({ state, commit }, setting) => {
        Object.entries(setting).forEach(([key, value]) => {
            commit('updateSetting', { type: key, value });
        });
    },
    loadSelected: ({ state, commit }) => {
        console.log('run loaded');
        let productPromise = null;
        let categoryPromise = null;
        let countryPromise = null;
        let segmentPromise = null;

        if (state.promotionSetting.selectedProduct.length > 0) {
            productPromise = new Promise((resolve, reject) => {
                promotionAPI
                    .getProducts()
                    .then((response) => {
                        console.log(response);
                        const selectedArr = [];
                        state.promotionSetting.selectedProduct.forEach(
                            (product) => {
                                // console.log('product',product);
                                const isFound = response.data.find(
                                    (item) =>
                                        item.product.id === product.productId
                                );
                                // console.log(isFound);
                                if (product.hasVariant) {
                                    // console.log(product.combinationVariant)
                                    product.combinationVariant.forEach(
                                        (comb) => {
                                            const findCombination =
                                                isFound.combinationVariation.find(
                                                    (combination) => {
                                                        const selectedCombination =
                                                            [
                                                                ...combination.combination_id,
                                                            ];
                                                        const allCombination = [
                                                            ...comb.combinationId,
                                                        ];
                                                        const arr1 =
                                                            selectedCombination
                                                                .slice()
                                                                .sort();
                                                        const arr2 =
                                                            allCombination
                                                                .slice()
                                                                .sort();
                                                        return (
                                                            arr1.length ===
                                                                arr2.length &&
                                                            arr1.every(
                                                                (
                                                                    value,
                                                                    index
                                                                ) => {
                                                                    return (
                                                                        value ===
                                                                        arr2[
                                                                            index
                                                                        ]
                                                                    );
                                                                }
                                                            )
                                                        );
                                                    }
                                                );
                                            if (findCombination !== undefined) {
                                                findCombination.value.isChecked = true;
                                            }
                                            console.log(findCombination);
                                        }
                                    );
                                }
                                if (isFound !== undefined) {
                                    selectedArr.push(isFound);
                                }
                            }
                        );
                        const selectedProduct = selectedArr.map((item) => {
                            return {
                                product: item.product,
                                combinationVariation:
                                    item.combinationVariation.filter(
                                        (combination) =>
                                            combination.value.isChecked
                                    ),
                            };
                        });

                        commit('updateSetting', {
                            type: 'selectedProduct',
                            value: selectedProduct,
                        });
                        resolve();
                    })
                    .catch((error) => {
                        console.log(error);
                        reject();
                    });
            });
        }

        if (state.promotionSetting.selectedCategory.length > 0) {
            categoryPromise = new Promise((resolve, reject) => {
                promotionAPI
                    .getCategories()
                    .then((response) => {
                        const selectedArr = [];
                        state.promotionSetting.selectedCategory.forEach(
                            (id) => {
                                const isFound = response.data.find(
                                    (category) => category.id === id
                                );
                                if (isFound !== undefined) {
                                    isFound.isChecked = true;
                                    isFound.isDisabled = true;
                                    selectedArr.push(isFound);
                                }
                            }
                        );
                        commit('updateSetting', {
                            type: 'selectedCategory',
                            value: selectedArr,
                        });
                        resolve();
                    })
                    .catch((error) => {
                        console.log(error);
                        reject();
                    });
            });
        }

        if (state.promotionSetting.selectedCountries.length > 0) {
            countryPromise = new Promise((resolve, reject) => {
                console.log('selected Countries');
                promotionAPI
                    .getShippingRegion()
                    .then((response) => {
                        const selectedArr = [];
                        state.promotionSetting.selectedCountries.forEach(
                            (id) => {
                                const isFound = response.data.find(
                                    (zone) => zone.id === id
                                );
                                // console.log(isFound,'Found');
                                if (isFound !== undefined) {
                                    isFound.checked = true;
                                    isFound.disabled = true;
                                    selectedArr.push(isFound);
                                }
                            }
                        );
                        commit('updateSetting', {
                            type: 'selectedCountries',
                            value: selectedArr,
                        });
                        resolve();
                    })
                    .catch((error) => {
                        reject();
                    });
            });
        }

        if (state.promotionSetting.targetValue.length > 0) {
            segmentPromise = new Promise((resolve, reject) => {
                promotionAPI
                    .getSegments()
                    .then((response) => {
                        const selectedArr = [];
                        state.promotionSetting.targetValue.forEach((id) => {
                            const isFound = response.data.find(
                                (segment) => segment.id === id
                            );
                            if (isFound !== undefined) {
                                selectedArr.push(isFound);
                            }
                        });
                        commit('updateSetting', {
                            type: 'targetValue',
                            value: selectedArr,
                        });
                        resolve();
                    })
                    .catch((error) => {
                        reject();
                    });
            });
        }

        return Promise.allSettled([
            productPromise,
            categoryPromise,
            countryPromise,
            segmentPromise,
        ]);
    },
    loadAllProduct: ({ state, commit }) => {
        promotionAPI
            .getProducts()
            .then((response) => {
                commit('setAllProducts', response.data);
                if (state.promotionSetting.selectedProduct.length > 0) {
                    state.promotionSetting.selectedProduct.forEach(
                        (product) => {
                            const indexOf = state.allProducts
                                .map((item) => item.product.id)
                                .indexOf(product.product.id);
                            const isFound = state.allProducts.find(
                                (item) => item.product.id === product.product.id
                            );
                            if (indexOf !== -1) {
                                if (product.product.hasVariant) {
                                    product.combinationVariation.forEach(
                                        (comb) => {
                                            isFound.combinationVariation.forEach(
                                                (combination, idx) => {
                                                    const allCombination = [
                                                        ...combination.combination_id,
                                                    ];
                                                    const selectedCombination =
                                                        [
                                                            ...comb.combination_id,
                                                        ];
                                                    const arr1 =
                                                        selectedCombination
                                                            .slice()
                                                            .sort();
                                                    const arr2 = allCombination
                                                        .slice()
                                                        .sort();
                                                    if (
                                                        arr1.length ===
                                                            arr2.length &&
                                                        arr1.every(
                                                            (value, index) =>
                                                                value ===
                                                                arr2[index]
                                                        )
                                                    ) {
                                                        commit(
                                                            'updateCheckKey',
                                                            {
                                                                index: indexOf,
                                                                combIdx: idx,
                                                                checkKey:
                                                                    'isChecked',
                                                                key: 'combinationVariation',
                                                                combKey:
                                                                    'value',
                                                                value: true,
                                                            }
                                                        );
                                                    }
                                                }
                                            );
                                        }
                                    );
                                    const totalIsChecked =
                                        isFound.combinationVariation.filter(
                                            (comb) => comb.value.isChecked
                                        ).length;
                                    const totalCombinationVariation =
                                        isFound.combinationVariation.length;
                                    if (
                                        totalIsChecked ===
                                        totalCombinationVariation
                                    ) {
                                        commit('updateCheckKey', {
                                            index: indexOf,
                                            value: true,
                                            key: 'product',
                                            checkKey: 'isCheckedAll',
                                        });
                                    } else {
                                        commit('updateCheckKey', {
                                            index: indexOf,
                                            value: true,
                                            key: 'product',
                                            checkKey: 'indeterminate',
                                        });
                                    }
                                } else {
                                    commit('updateCheckKey', {
                                        index: indexOf,
                                        value: true,
                                        key: 'product',
                                        checkKey: 'isCheckedAll',
                                    });
                                }
                            }
                        }
                    );
                }
            })
            .catch((error) => console.log(error));
    },

    setDateAccordingTimezone: async ({ state, commit }, timezoneStr) => {
        const start = dayjs(state.promotionSetting.startDate);
        const convertedStartDate = start
            .tz(timezoneStr)
            .format('YYYY-MM-DD h:mm a');
        commit('updateSetting', {
            type: 'startDate',
            value: convertedStartDate,
        });
        commit('updateSetting', {
            type: 'endDate',
            value: dayjs(
                new Date(new Date(convertedStartDate).setHours(23, 59))
            ).format('YYYY-MM-DD h:mm a'),
        });
        console.log('date updated.....');
    },
};

/* eslint-disable no-unused-expressions */
export default {
    initialOptionsState: (state) => {
        // Edit Type
        state.editingType = 'variant';

        // Customizations
        state.currentArray = [];
        state.inputOptions = [];
        state.errorOptions = [];
        state.inputErrors = [];
        state.existsName = [];
        state.deletedInputOptionsId = [];
        state.deletedInputId = [];
        state.deletedSharedInputOption = [];
        state.originalArray = [];
        state.customizationsIsValid = false;
        state.errorDisplayName = [];
        state.errorName = [];
        state.errorAtLeast = [];
        state.errorUpTo = [];
        state.errorTotalPrice = [];
        state.optionIndex = 0;
        state.optionValueIndex = 0;
        state.isCustomizationUpdated = false;
        state.isVariantUpdated = false;

        // Variants
        state.isProductEdited = false;
        state.variantOptionArray = [];
        state.validVariantOptions = [];
        state.deletedVariantIdBuffer = [];
        state.deletedVariantValueIdBuffer = [];
        state.deletedVariantIdForProductVariationBuffer = [];
    },

    emitCurrency: (state, currency) => {
        state.currency = currency;
    },

    updateEditingType: (state, type) => {
        state.editingType = type;
    },

    // ******************************** Customization *********************************** //

    emitCurrentArray: (state) => {
        state.currentArray = JSON.parse(JSON.stringify(state.inputOptions));
    },
    emitCurrentVariants: (state) => {
        state.validVariantOptions = JSON.parse(
            JSON.stringify(state.variantOptionArray)
        );
    },
    currentProductOption: (state, data) => {
        state.inputOptions = data;
    },
    currentProductVariant: (state, data) => {
        state.variantOptionArray = data;
    },
    currentName: (state, data) => {
        state.existsName = data;
    },
    resetDeletedArray: (state, data) => {
        state.deletedInputOptionsId = [];
        state.deletedInputId = [];
        state.deletedSharedInputOption = [];
    },
    resetInputOptionValues: (state, data) => {
        if (
            data.value === 'Text Area' ||
            data.value === 'Text Field' ||
            data.value === 'Number Field'
        ) {
            state.inputOptions[data.index].inputs.forEach((option) => {
                if (option.id !== undefined) {
                    state.deletedInputId.push(option.id);
                }
            });
            const inputs = [
                {
                    label: '',
                    option: 'One Color',
                    is_default: false,
                    type_of_single_charge: 'Default',
                    single_charge: 0.0,
                    value_1: '50',
                },
            ];
            state.inputOptions[data.index].inputs = inputs;
        }
        const inputValue = (type) => {
            switch (type) {
                case 'Color':
                    return '#000000';
                case 'Text Area':
                    return '50';
                case 'Text Field':
                    return '50';
                case 'Number Field':
                    return '10';
                default:
                    return null;
            }
        };
        state.inputOptions[data.index].inputs.forEach((option) => {
            option.value_1 = inputValue(data.value);
        });
    },
    reorderInputOptionValues: (state, data) => {
        state.inputOptions[data.index].inputs = data.values;
    },
    assignOptionIndex: (state, { optionIndex, optionValueIndex }) => {
        state.optionIndex = optionIndex;
        state.optionValueIndex = optionValueIndex;
    },
    pushSharedOptionArray: (state, datas) => {
        state.isCustomizationUpdated = true;
        state.inputOptions = state.inputOptions.filter((el) => !el.is_shared);
        datas.forEach((data) => {
            state.inputOptions.push(data);
        });
    },
    pushOptionArray: async (state, data) => {
        state.isCustomizationUpdated = true;
        const isShared = data.optionType === 'shared';
        const inputValue = (type) => {
            switch (type) {
                case 'Color':
                    return '#000000';
                case 'Text Area':
                    return '50';
                case 'Text Field':
                    return '50';
                case 'Number Field':
                    return '10';
                default:
                    return null;
            }
        };
        const inputOptions = {
            name: '',
            display_name: '',
            help_text: '',
            tool_tips: '',
            is_range: false,
            up_to: '',
            at_least: '',
            type: 'Text Field',
            is_required: false,
            is_shared: isShared,
            is_total_Charge: false,
            total_charge_amount: 0.0,
        };
        const inputs = {
            label: '',
            option: 'One Color',
            is_default: false,
            type_of_single_charge: 'Default',
            single_charge: 0.0,
            value_1: inputValue(data.valueType ?? inputOptions.type),
        };
        inputOptions.inputs = [inputs];

        if (data.type === 'inputs') {
            state.inputOptions[data.optionIndex].inputs.push(inputs);
        } else {
            state.inputOptions.push(inputOptions);
        }
    },
    checkError(state, data) {
        state.errorName = [];
        state.errorDisplayName = [];
        state.errorAtLeast = [];
        state.errorUpTo = [];
        state.errorTotalPrice = [];
        const optionErrors = [];
        const displayName = [];
        const inputErrors = [];
        state.inputOptions.forEach((option) => {
            displayName.push(option.display_name);
            // if (data.type === 'shared') {
            //     state.errorName.push(
            //         state.existsName.includes(option.name) ||
            //             option.name.trim() === ''
            //     );
            // }
            optionErrors.push(option.display_name.trim() === '');
            if (option.type === 'Checkbox' && option.is_range) {
                state.errorAtLeast.push(
                    !option.at_least || option.at_least === ''
                );
            }
            if (
                option.type === 'Checkbox' &&
                option.is_range &&
                (option.at_least || option.at_least !== '') &&
                option.up_to &&
                option.up_to !== ''
            ) {
                state.errorUpTo.push(
                    parseInt(option.up_to) <= parseInt(option.at_least)
                );
            }
            if (option.type === 'Checkbox') {
                state.errorTotalPrice.push(option.total_charge_amount === '');
            }
            option.inputs.forEach((input) => {
                input.priceError = false;
                if (option.type !== 'Checkbox' || !option.is_total_Charge) {
                    input.priceError = input.single_charge === '';
                    inputErrors.push(input.priceError);
                }
                if (
                    option.type !== 'Number Field' &&
                    option.type !== 'Text Field' &&
                    option.type !== 'Text Area'
                ) {
                    input.labelError = input.label?.trim() === '';
                    inputErrors.push(input.labelError);
                }
                if (
                    (option.type === 'Number Field' ||
                        option.type === 'Text Field' ||
                        option.type === 'Text Area') &&
                    input.is_default
                ) {
                    input.valueError =
                        input.value_1 === null || input.value_1?.trim() === '';
                    inputErrors.push(input.valueError);
                }
                if (option.type === 'Image') {
                    input.value_1 =
                        input.value_1 ||
                        'https://cdn.hypershapes.com/assets/product-default-image.png';
                }
                if (option.type === 'Color' || option.type === 'Image') {
                    input.valueError =
                        input.value_1 === null || input.value_1?.trim() === '';
                    inputErrors.push(input.valueError);
                }
            });
        });
        displayName.forEach((item, index) => {
            // let value = displayName.indexOf(item.trim()) === index;
            // if (item.trim() === '') {
            //     value = false;
            // }
            // if (value === true) {
            //     state.errorDisplayName.push(false);
            // }
            // if (value === false) {
            //     state.errorDisplayName.push(true);
            // }
            if (item.trim() === '') state.errorDisplayName.push(true);
            else state.errorDisplayName.push(false);
        });

        const errors = inputErrors.concat(
            state.errorName,
            state.errorAtLeast,
            state.errorUpTo,
            state.errorTotalPrice,
            state.errorDisplayName
        );
        // Cant find the errors then customizations is valid
        state.customizationsIsValid = !errors.find((error) => error);
    },
    deleteOptionArray: (state, data) => {
        state.isCustomizationUpdated = true;
        if (data.type === 'inputs') {
            state.deletedInputId.push(data.id);
            state.inputOptions[data.optionIndex].inputs.splice(data.index, 1);
        } else if (data.isShared === 1) {
            state.deletedSharedInputOption.push(data.id);
            state.inputOptions.splice(data.index, 1);
        } else {
            state.deletedInputOptionsId.push(data.id);
            state.inputOptions.splice(data.index, 1);
        }
    },
    inputOptionValue: (state, data) => {
        state.isCustomizationUpdated = true;
        if (data.object === 'is_default') {
            state.inputOptions[data.optionIndex].inputs.forEach(
                (element, index) => {
                    if (index !== data.index) {
                        element.is_default = false;
                    }
                }
            );
        }
        data.type === 'inputs'
            ? (state.inputOptions[data.optionIndex].inputs[data.index][
                  data.object
              ] = data.value)
            : (state.inputOptions[data.index][data.object] = data.value);

        if (data.object === 'display_name')
            state.errorDisplayName[data.index] = false;
        if (data.object === 'label')
            state.inputOptions[data.optionIndex].inputs[
                data.index
            ].labelError = false;
        if (data.object === 'single_charge')
            state.inputOptions[data.optionIndex].inputs[
                data.index
            ].priceError = false;
        if (data.object === 'value_1')
            state.inputOptions[data.optionIndex].inputs[
                data.index
            ].valueError = false;
        if (data.object === 'at_least') state.errorAtLeast[data.index] = false;
        if (data.object === 'up_to') state.errorUpTo[data.index] = false;
        if (data.object === 'total_charge_amount')
            state.errorTotalPrice[data.index] = false;
    },

    resetOptionArray: (state) => {
        state.errorName = [];
        state.errorDisplayName = [];
    },

    resetCustomizationErrors: (state) => {
        state.errorName = [];
        state.errorDisplayName = [];
        state.errorAtLeast = [];
        state.errorUpTo = [];
        state.errorTotalPrice = [];
    },

    // ******************************** Variant *********************************** //

    initiateVariants: (state, payload) => {
        state.variantOptionArray = payload.map((el) => ({
            ...el,
            valueArray: el.valueArray.map((e) => ({
                ...e,
                default: Boolean(e.default),
            })),
        }));
    },

    addVariantOptions: (state, payload) => {
        state.isVariantUpdated = true;
        state.variantOptionArray[payload].errorMessage = '';
        state.variantOptionArray[payload].valueArray.push({
            id: null,
            variant_value: '',
            default: false,
            color: '#000000',
            image_url:
                'https://cdn.hypershapes.com/assets/product-default-image.png',
            errorMessage: '',
        });
    },

    reorderVariantValues: (state, data) => {
        state.variantOptionArray[data.index].valueArray = data.values;
    },

    updateVariant: (state, { index, type, value }) => {
        state.isVariantUpdated = true;
        switch (type) {
            case 'name':
                state.variantOptionArray[index].name = value;
                break;
            case 'display_name':
                state.variantOptionArray[index].display_name = value;
                break;
            case 'errorMessage':
                state.variantOptionArray[index].errorMessage = value;
                break;
            case 'type':
                state.variantOptionArray[index].type = value;
                break;
            default:
                break;
        }
    },

    updateVariantValue: (state, { index, valueIndex, type, value }) => {
        state.isVariantUpdated = true;
        state.variantOptionArray[index].valueArray[valueIndex][type] = value;
    },

    swapVariantValue: (state, { index, values }) => {
        state.isVariantUpdated = true;
        state.variantOptionArray[index].valueArray = values;
    },

    addNewVariant: (state, { type, variant }) => {
        state.isVariantUpdated = true;
        if (state.variantOptionArray.length < 5) {
            if (type === 'new') {
                state.variantOptionArray.push({
                    id: null,
                    name: '',
                    display_name: '',
                    valueInput: null,
                    errorMessage: '',
                    type: 'button',
                    valueArray: [
                        {
                            id: null,
                            variant_value: '',
                            default: false,
                            color: '#000000',
                            image_url:
                                'https://cdn.hypershapes.com/assets/product-default-image.png',
                            errorMessage: '',
                        },
                    ],
                    is_shared: false,
                });
            } else {
                state.variantOptionArray.push(variant);
            }
        }
    },

    deleteVariantByIndex: (state, payload) => {
        state.isVariantUpdated = true;
        state.variantOptionArray.splice(payload, 1);
    },

    deleteVariantValueByIndex: (state, { index, valueIndex }) => {
        state.isVariantUpdated = true;
        state.variantOptionArray[index].valueArray.splice(valueIndex, 1);
    },

    clearAllVariantErrors: (state, payload) => {
        state.variantOptionArray.forEach((variant) => {
            variant.errorMessage = '';
            if (variant.valueArray.length !== 0) {
                variant.valueArray.forEach((value) => {
                    value.errorMessage = '';
                });
            }
        });
    },

    clearAllVariantBuffers: (state, payload) => {
        state.deletedVariantIdBuffer = [];
        state.deletedVariantValueIdBuffer = [];
        state.deletedVariantIdForProductVariationBuffer = [];
    },

    checkDefault: (state, { count, index }) => {
        const { length } = state.variantOptionArray[count].valueArray;

        if (state.variantOptionArray[count].valueArray[index].default) {
            state.variantOptionArray[count].valueArray[index].default = false;
        } else {
            for (let i = 0; i < length; i++) {
                if (i === index) {
                    state.variantOptionArray[count].valueArray[
                        i
                    ].default = true;
                } else {
                    state.variantOptionArray[count].valueArray[
                        i
                    ].default = false;
                }
            }
        }
    },

    addDeletedVariant: (state, { type, value }) => {
        state.isVariantUpdated = true;
        switch (type) {
            case 1:
                state.deletedVariantIdBuffer.push(value);
                break;
            case 2:
                state.deletedVariantValueIdBuffer.push(value);
                break;
            case 3:
                state.deletedVariantIdForProductVariationBuffer.push(value);
                break;
            default:
                break;
        }
    },

    updateProductEditStatus: (state, payload) => {
        state.isProductEdited = payload;
    },

    // ******************************** subscription *********************************** //

    initialSavedSubscriptionArray: (state, subscriptionArray) => {
        state.savedSubscriptionArray = JSON.parse(
            JSON.stringify(subscriptionArray)
        );
    },
    initialSubscriptionArray: (state, id) => {
        const subscription =
            state.savedSubscriptionArray.find((subs) => subs.id === id) ?? null;
        state.subscriptionArray =
            subscription === null
                ? []
                : JSON.parse(JSON.stringify([subscription]));
    },
    addSubscriptionOption: (state) => {
        state.count += 1;
        state.subscriptionDefault.id = `add-${state.count}`;
        state.subscriptionDefault.currency = state.currency;
        state.subscriptionArray.push(
            JSON.parse(JSON.stringify(state.subscriptionDefault))
        );
    },
    deleteSubscriptionOption: (state, index) => {
        state.subscriptionArray.splice(index, 1);
    },
    deleteSelectedSubscriptionOption: (state, id) => {
        state.savedSubscriptionArray.forEach((subscription, index) => {
            if (subscription.id === id)
                state.savedSubscriptionArray.splice(index, 1);
        });
    },
    inputSubscriptionOption: (state, { index, type, value }) => {
        state.subscriptionArray[index].changes = true;
        state.subscriptionArray[index].errorMessage = false;
        state.subscriptionArray[index][type] = value;
    },
    saveSubscriptionOption: (state) => {
        eventBus.$emit('checkError');
        state.subscriptionArray.forEach((subscription) => {
            subscription.errorMessage = true;
        });
        if (!state.error) {
            state.savedSubscriptionArray.forEach((subscription, index) => {
                if (subscription.id === state.subscriptionArray[0].id)
                    state.savedSubscriptionArray.splice(index, 1);
            });
            state.savedSubscriptionArray.push(
                JSON.parse(JSON.stringify(state.subscriptionArray[0]))
            );
            $('#add-subscription-modal').modal('hide');
        }
        state.error = false;
    },
    checkSubscriptionError: (state, invalid) => {
        if (invalid) state.error = true;
    },
};

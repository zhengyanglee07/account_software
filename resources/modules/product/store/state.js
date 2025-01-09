export default () => ({
    currency: '',

    // Edit Type
    editingType: 'variant',

    // Customizations
    currentArray: [],
    inputOptions: [],
    errorOptions: [],
    inputErrors: [],
    existsName: [],
    deletedInputOptionsId: [],
    deletedInputId: [],
    deletedSharedInputOption: [],
    originalArray: [],
    customizationsIsValid: false,
    errorDisplayName: [],
    errorName: [],
    errorAtLeast: [],
    errorUpTo: [],
    errorTotalPrice: [],
    optionIndex: 0,
    optionValueIndex: 0,
    isCustomizationUpdated: false,
    isVariantUpdated: false,

    // Variants
    isProductEdited: false,
    variantOptionArray: [],
    validVariantOptions: [],
    deletedVariantIdBuffer: [],
    deletedVariantValueIdBuffer: [],
    deletedVariantIdForProductVariationBuffer: [],

    // subscription
    subscriptionDefault: {
        id: null,
        price_id: '',
        display_name: '',
        description: '',
        currency: '',
        amount: 0,
        interval_count: 1,
        interval: 'month',
        expiration: 'never_expire',
        expiration_cycle: 0,
        errorMessage: false,
        type: 'none',
        capped_at: null,
        discount_rate: 0,
    },
    subscriptionArray: [],
    savedSubscriptionArray: [],
    error: false,
    count: 0,
});

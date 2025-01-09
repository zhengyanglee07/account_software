export default {
    /**
     * Shipping Settings
     */
    delivery: {
        deliveryHourType: 'default',
        deliveryHours: [],
        disableDate: [],
        preOrderDay: 7,
        isPreperationTime: false,
        isLimitOrder: false,
        preperationValue: 0,
        isDaily: true,
        isSameTime: true,
    },
    pickup: {
        isEnableStorePickup: false,
        deliveryHours: [],
        disableDate: [],
        preOrderDay: 7,
        isPreperationTime: false,
        isLimitOrder: false,
        preperationValue: 0,
        isDaily: true,
        isSameTime: true,
    },
    isLoading: true,
    isExpressSetup: false,
    isSelfDeliveryLoading: false,
    error: null,

    /**
     * Currency Settings
     */
    currencyDetails: {},
    specialCurrency: [
        'BIF',
        'CLP',
        'DJF',
        'GNF',
        'JPY',
        'KMF',
        'KRW',
        'MGA',
        'PYG',
        'RWF',
        'UGX',
        'VND',
        'VUV',
        'XAF',
        'XOF',
        'XPF',
    ],
};

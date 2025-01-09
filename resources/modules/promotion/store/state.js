import dayjs from 'dayjs';

const getDefaultPromotionSettings = () => {
    return {
        promotionType: '',
        promoCode: '',
        promoTitle: '',
        displayTitle: '',
        discountType: 'orderBased',
        discountValueType: 'percentage',
        discountValue: 0,
        discountCap: '',
        discountCategory: 'Order',
        minimumType: 'none',
        minimumValue: 0,
        isExpiryDate: false,
        startDate: dayjs(new Date().toLocaleString()).format(
            'YYYY-MM-DD hh:mm a'
        ),
        endDate: dayjs(
            new Date(new Date().toLocaleString()).setHours(23, 59)
        ).format('YYYY-MM-DD hh:mm a'),
        countryType: 'all',
        targetCustomerType: 'all',
        targetValue: [],
        storeUsageLimitType: 'unlimited',
        storeUsageValue: '',
        customerUsageLimitType: 'unlimited',
        customerUsageValue: '',
        selectedCountries: [],
        selectedProduct: [],
        selectedCategory: [],
        // selectedSegment:[],
        minimumQuantity: 1,
        productDiscountType: 'all-product',
    };
};

const getDefaultState = () => {
    return {
        errors: {},
        currency: '',
        isLoading: false,
        allProducts: [], // all product for browse product modal
        allCategories: [], // all category for browse category modal
        availableRegion: [],
        allSegments: [], // all segments
    };
};

export default {
    initialState: getDefaultState(),
    initialPromotionSetting: getDefaultPromotionSettings(),
    ...getDefaultState(),
    promotionSetting: getDefaultPromotionSettings(),
};

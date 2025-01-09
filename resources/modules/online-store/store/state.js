export default () => ({
    //* store
    storeTitle: '',
    location: {},
    sellerEmail: {},

    //* people
    formDetail: {},
    processedContact: {},
    processedContactBalanceToBeUsed: 0,
    funnelProcessedContact: {},

    //* products
    allProducts: [],
    allCategories: [],
    cartItems: [],
    cartItemsQuantity: 0,
    currencyDetails: {},
    selectedProduct: {},
    selectedCartProduct: {},
    recommendedProducts: [],

    //* preferences
    preferences: {},

    //* shippings
    availableShippingApp: [],
    allShippingMethods: [],
    selectedShippingMethod: {},
    selectedShipping: {},

    //* delivery
    selectedDeliveryHour: {},

    // tax
    allTaxSettings: [],
    taxSetting: {},

    //* promotions
    appliedPromotion: [],
    automatedPromotion: [],
    productPromotion: [],

    //* cashback
    cashback: null,
    cashbacks: [],
    cashbackPercentage: 0,
    cashbackDetail: null,
    isCustomerSignedIn: true,

    //* payments
    allPaymentMethods: [],
    selectedPaymentMethod: null,
    paymentMethods: [],
    paymentRef: '', // ref obtained during checkout process

    //* order
    customOrders: [],
    order: {},
    orderDetail: [],
    isOrderSuccess: false,

    //* sale channel => mini-store, online-store, funnel
    saleChannel: 'online-store',

    //* mini store details
    miniStoreDetails: {},

    //* loading
    isPageLoading: true,
    isLoading: true,
    isFetchingShipping: false,

    //* navigations
    menuListArray: [],
    menuListItem: [],
    menuAllPages: [],
    menuAllProducts: [],
    menuAllLegalPolicy: [],

    //* social proof
    socialProof: {},
    isOSPlacingOrder: false,
    isMSPlacingOrder: false,

    //* facebook pixel
    facebookPixel: {},
    clientIp: '',
    hash: '',
    isPublish: false,

    //* referral campaigns
    isReferralUser: false,
    referralCampaigns: [],
    selectedReferralCampaign: null,
    referralPoints: 0,
    referralActionInfo: {
        points: 0,
        joined: false,
        logs: null,
        signUpCount: 0,
        orderCount: 0,
        isRootUser: false,
    },

    //* legal policy
    legalPolicy: [],
});

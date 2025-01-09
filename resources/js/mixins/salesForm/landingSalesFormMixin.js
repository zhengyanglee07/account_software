import { mapGetters, mapState, mapMutations } from 'vuex';
import Cookies from 'js-cookie';

export default {
    props: {
        settings: Object,
        styles: Object,
        id: [String, Number],
    },
    computed: {
        ...mapState('landing', {
            // elementOnEdit : state => state.onEdit,
            // responsiveMode : state => state.responsiveMode,
            // isTwoStepFormOnEdit : state => state.onEditTitle,
            status: (state) => state.status,
            landingSettings: (state) => state.landing_settings,
            accountId: (state) => state.landing_settings.account_id,
        }),
        ...mapState('settings', ['currencyDetails']),
        ...mapGetters('settings', ['calculatePrice', 'formatPrice']),
        singleProductPrice() {
            return this.calculatePrice(this.product.productPrice);
        },
        shippingTax() {
            const shipping = this.taxSetting.setting.is_shipping_fee_taxable
                ? this.shippingFee * (this.taxSetting.taxRate / 100)
                : 0;
            return this.calculatePrice(shipping);
        },
        productTax() {
            let tax = 0.0;
            if (
                this.product.isTaxable === 1 &&
                this.taxSetting.taxRate !== ''
            ) {
                const taxRate = parseFloat(this.taxSetting.taxRate) / 100;
                const productPrice = this.singleProductPrice * this.quantity;
                const totalTax = productPrice * taxRate;
                tax = this.taxSetting.setting.is_product_include_tax
                    ? totalTax / (1 + taxRate)
                    : totalTax;
            }
            return this.formatPrice(tax, false);
        },
        calculateTax() {
            // gabriel recheck
            return this.shippingTax + this.productTax;
        },
        totalPrice() {
            const productPrice = this.singleProductPrice * this.quantity;
            const tax = this.taxSetting.setting.is_product_include_tax
                ? 0
                : this.productTax;
            const totalTax = tax + this.shippingTax;
            const grandTotal =
                productPrice + parseFloat(totalTax) + this.shippingFee;
            return grandTotal;
        },
    },
    data() {
        return {
            orderDetail: {
                currency: null,
                exchangeRate: null,
            },
            product: {
                id: null,
                productTitle: null,
                productPrice: 0.0,
                productImagePath: null,
                hasVariant: 0,
            },
            taxSetting: {
                taxName: '',
                taxRate: '',
                setting: {},
            },
            quantity: 1,
        };
    },
    methods: {
        ...mapMutations('settings', ['initialCurrencyDetails']),
        getOrderDetail() {
            window.axios
                .get(`/get/currency/${Cookies.get('formId')}`)
                .then(({ data }) => {
                    data.isDefault = 0;
                    data.rounding = 0;
                    data.prefix =
                        data.currency === 'MYR' ? 'RM' : data.currency;
                    this.initialCurrencyDetails(data);
                });
        },
        getProductDetail() {
            window.axios.get(`/get/Product/${this.refKey}`).then((response) => {
                this.product = response.data.product;
                this.getTaxSetting();
            });
        },
        getTaxSetting() {
            if (this.customerDetail) {
                const customerDetail =
                    this.product.physicalProducts === 'on'
                        ? this.customerDetail.shipping
                        : this.customerDetail.billing;
                if (
                    Object.keys(customerDetail).length > 0 &&
                    customerDetail.country !== '' &&
                    customerDetail.state !== ''
                ) {
                    window.axios
                        .post('/get/tax/settings', {
                            taxCountry: customerDetail.country,
                            taxState: customerDetail.state,
                            accountId: this.accountId,
                        })
                        .then((response) => {
                            // console.log(response.data)
                            this.taxSetting.taxName = response.data.taxName;
                            this.taxSetting.taxRate = response.data.taxRate;
                            this.taxSetting.setting = response.data.setting;
                        });
                }
            }
        },
        checkProductExists() {
            if (this.refKey !== null) {
                window.axios
                    .get(`/check/exists/${this.refKey}`)
                    .then((response) => {
                        if (!response.data)
                            this.settings.selectedProduct[0] = null;
                        this.getProductDetail();
                    });
            }
        },
        trackUniquePageView() {
            window.axios.post('/update/uniquePage/view', {
                pageName: this.productRefKey,
                isFunnel: true,
                landingPath: this.landingSettings.path,
            });
        },
    },
    watch: {
        customerDetail: {
            handler(newValue) {
                if (
                    newValue !== null &&
                    this.type !== 'button' &&
                    this.status !== 'On Edit'
                ) {
                    this.getTaxSetting();
                }
            },
            deep: true,
        },
    },
    mounted() {
        if (
            Cookies.get('hyper_unique_view') === undefined &&
            this.productId !== null &&
            this.productId !== undefined &&
            this.status === 'Publish'
        )
            this.trackUniquePageView();
        if (Cookies.get('formId') !== undefined) this.getOrderDetail();
    },
};

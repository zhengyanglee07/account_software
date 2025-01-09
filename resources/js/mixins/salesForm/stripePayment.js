import { mapGetters, mapMutations, mapState, mapActions } from 'vuex';
import Cookies from 'js-cookie';
import axios from 'axios';
import salesFormAPI from '@builder/api/salesFormAPI.js';

export default {
    name: 'checkoutPayment',
    data() {
        return {
            stripe: '',
            card: '',
            isRegionAvailable: '',
            shippingFee: 0,
        };
    },
    computed: {
        ...mapState('landing', {
            landingSettings: (state) => state.landing_settings,
            status: (state) => state.status,
            responsiveMode: (state) => state.responsiveMode,
        }),
        ...mapState('settings', ['currencyDetails']),

        ...mapGetters('landing', ['edit_mode']),

        ...mapGetters('settings', ['calculatePrice']),
        ...mapState('ecommerce', {
            stripePaymentAPI: (state) => state.stripePaymentAPI,
        }),
        stripeStyle() {
            return {
                base: {
                    color: '#32325d',
                    fontFamily: 'Arial, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#32325d',
                    },
                },
                invalid: {
                    fontFamily: 'Arial, sans-serif',
                    color: '#fa755a',
                    iconColor: '#fa755a',
                },
            };
        },

        subtotal() {
            return this.productPrice * this.quantity;
        },
        productPrice() {
            return this.calculatePrice(parseFloat(this.product.productPrice));
        },

        isPhysicalProduct() {
            return this.settings.selectedProduct[0].physicalProducts === 'on';
        },

        outOfStock() {
            if (this.status === 'On Edit') return false;
            const stock = this.product.quantity;
            const continueSelling = this.product.is_selling;
            return (
                (stock < this.quantity && !continueSelling) ||
                !this.product.selectedSaleChannels?.includes('funnel')
            );
        },
    },
    methods: {
        ...mapActions('ecommerce', ['actionAfterSubmit']),
        checkAvailableRegion() {
            document.querySelector(`#${this.name.button}`).disabled = false;
            salesFormAPI
                .checkShippingRegion({
                    account_id: this.landingSettings.account_id,
                    customerDetail: this.customerDetail,
                })
                .then((response) => {
                    this.isRegionAvailable = response.data.isRegionAvailable;
                    if (
                        !response.data.isRegionAvailable &&
                        this.isPhysicalProduct &&
                        document.querySelector(`#${this.name.error}`)
                            .textContent === ''
                    ) {
                        document.querySelector(
                            `#${this.name.error}`
                        ).textContent = 'Sorry. Out of zone';
                        document.querySelector(
                            `#${this.name.button}`
                        ).disabled = true;
                    }
                    if (
                        response.data.isRegionAvailable &&
                        this.isPhysicalProduct
                    ) {
                        this.shippingFee = this.calculatePrice(
                            response.data.shippingFee
                        );
                        document.querySelector(
                            `#${this.name.error}`
                        ).textContent = '';
                    }
                });
        },
        intializeStripeElement() {
            this.stripe = Stripe(this.stripePaymentAPI.publishable_key);
            const elements = this.stripe.elements();
            // initail stripe elements style
            this.card = elements.create('card', {
                style: this.stripeStyle,
                hidePostalCode: true,
            });
            // Stripe injects an iframe into the DOM
            this.card.mount(`#${this.name.element}`);
            this.card.on('change', (event) => {
                // Disable the Pay button if there are no card details in the Element
                if (!this.outOfStock) {
                    document.querySelector(`#${this.name.button}`).disabled =
                        event.empty;
                    document.querySelector(`#${this.name.error}`).textContent =
                        event.error ? event.error.message : '';
                }
            });
            const form = document.getElementById(this.name.form);
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                axios
                    .post('/update/customer/info', {
                        visitorId: Cookies.get('visitor_id'),
                        customerInfo: this.customerDetail.customerInfo,
                        isTracking: false,
                        channel: 'funnel',
                    })
                    .then((response) => {
                        if (
                            this.status !== 'On Edit' &&
                            this.stripePaymentAPI !== null
                        )
                            this.checkCondition();
                    })
                    .catch(({ response: { data } }) => {
                        this.$toast.error('Error', data.message);
                    });
                // Complete payment when the submit button is clicked
            });
        },
        checkCondition() {
            if (
                this.stripePaymentAPI === null ||
                this.selectedProduct === null ||
                parseFloat(this.totalPrice) < 2
            ) {
                alert('Settings is incomplete');
            } else {
                this.checkAvailableRegion();
                if (
                    (this.isRegionAvailable || !this.isPhysicalProduct) &&
                    !this.outOfStock
                )
                    this.payByStripe();
            }
        },
        payByStripe() {
            this.loading(true);
            // fetch to server side
            salesFormAPI
                .salesFormCheckout({
                    productPrice: this.totalPrice * 100,
                    customerDetail: this.customerDetail.customerInfo,
                    accountId: this.landingSettings.account_id,
                    currency: this.currency,
                })
                .catch((error) => {
                    this.showError(error);
                })
                .then((response) => {
                    const { clientSecret } = response.data;
                    this.stripe
                        .confirmCardPayment(clientSecret, {
                            payment_method: {
                                card: this.card,
                            },
                        })
                        .then((result) => {
                            if (result.error) {
                                this.showError(
                                    result.error ? result.error.message : ''
                                );
                                return;
                            }
                            this.saveOrder(
                                result.paymentIntent,
                                response.data.customerId
                            );
                        });
                });
        },
        saveOrder(paymentIntent, customerId) {
            Cookies.remove('formId');
            salesFormAPI
                .saveOrder({
                    type: 'sales-form',
                    landingSettings: this.landingSettings,
                    customerDetail: this.customerDetail,
                    paymentIntent,
                    productDetail: this.product,
                    productQuantity: this.quantity,
                    customerId,
                    currency: this.currency,
                    exchangeRate: this.currencyDetails.exchangeRate,
                    totalTax: this.calculateTax,
                    taxSetting: this.taxSetting,
                    shippingFee: this.shippingFee,
                    productPrice: this.productPrice,
                    fId: Cookies.get('formId'),
                    subtotal: this.subtotal,
                })
                .then((response) => {
                    const orid = Cookies.get('orid');
                    const cookiesData =
                        orid !== undefined
                            ? `${orid}-${response.data.paymentReference}`
                            : response.data.paymentReference;
                    Cookies.set('orid', cookiesData);
                    sessionStorage.removeItem('cId');
                    Cookies.set('cId', true, { expires: 1 });
                    localStorage.id = response.data.contactRandomId;
                    if (Cookies.get('formId') === undefined) {
                        Cookies.set('formId', response.data.paymentReference, {
                            expires: 1,
                        });
                    }
                    if (
                        this.settings.actionAfterSubmit[0] ===
                        'Next Step In Funnel'
                    ) {
                        this.actionAfterSubmit({
                            status: this.status,
                            landingId: this.landingSettings.id,
                        });
                        this.loading(false);
                    } else {
                        window.location.replace(this.settings.redirectURL[0]);
                        this.loading(false);
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        showError(errorMsgText) {
            this.loading(false);
            document.querySelector('#submit-form-button').disabled = false;
            const errorMsg = document.querySelector('#two-step-card-errors');
            errorMsg.textContent = errorMsgText;
            setTimeout(function () {
                errorMsg.textContent = '';
            }, 4000);
        },
        loading(isLoading) {
            // Show a spinner on payment submission
            if (isLoading) {
                // Disable the button and show a spinner
                document.querySelector(`#${this.name.form}`).disabled = true;
                document
                    .querySelector(`#${this.name.spinner}`)
                    .classList.remove('hidden');
                document
                    .querySelector(`#${this.name.buttonText}`)
                    .classList.add('hidden');
            } else {
                document
                    .querySelector(`#${this.name.spinner}`)
                    .classList.add('hidden');
                document
                    .querySelector(`#${this.name.buttonText}`)
                    .classList.remove('hidden');
            }
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
                    this.checkAvailableRegion();
                }
            },
            deep: true,
        },
    },
    mounted() {
        // if(this.customerDetail && this.type !=='button')this.checkAvailableRegion();
        if (this.stripePaymentAPI && this.product && this.type !== 'button')
            this.intializeStripeElement();
    },
};

export default {
    inject: ['currencyArray'],
    data() {
        return {
            numberInputField: '',
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
    },
    computed: {
        parseTestingInput() {
            return JSON.stringify(this.numberInputField);
        },
    },
    methods: {
        specialCurrencyCalculation(price = 0, currency = null) {
            const formatCurrency = currency === 'RM' ? 'MYR' : currency;
            if (!this.currencyArray || this.currencyArray.length === 0)
                return 0;
            const currencyArray = this.currencyArray.find((currencies) => {
                return currency === null
                    ? Boolean(currencies.isDefault) === true
                    : currencies.currency === formatCurrency;
            });
            let formatPrice = parseFloat(price);
            if (
                formatPrice === 0 ||
                formatPrice === undefined ||
                Number.isNaN(formatPrice)
            )
                return 0;
            const {
                decimal_places: decimalPlaces,
                separator_type: separatorType,
                rounding,
            } = currencyArray;
            // if (rounding && decimal_places != 0)
            //     formatPrice = Math.ceil(formatPrice);
            formatPrice =
                decimalPlaces === 0
                    ? Math.floor(formatPrice).toFixed()
                    : formatPrice.toFixed(2);
            if (separatorType !== 'none')
                formatPrice = formatPrice.replace(
                    /\B(?=(\d{3})+\b)/g,
                    separatorType
                );
            return formatPrice;
        },
        isSpecialCurrency(currency) {
            return this.specialCurrency.includes(currency);
        },
    },
    watch: {
        parseTestingInput(newValue) {
            const { currency, decimal_places: decimalPlaces } =
                this.currencyArray ?? {};
            if (this.specialCurrency.includes(currency) || decimalPlaces) {
                document.querySelectorAll('.price').forEach((input) => {
                    if (input.value !== 0 && input.value != null)
                        input.value = Math.floor(input.value);
                    input.step = '1';
                    input.addEventListener('input', (e) =>
                        e.target.value.replace(/[^0-9]*/g, '')
                    );
                });
            }
        },
    },
    updated() {
        this.$nextTick(() => {
            this.numberInputField = document.querySelectorAll('.price');
        });
    },
};

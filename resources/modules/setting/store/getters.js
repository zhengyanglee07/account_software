export default {
    /**
     * Currency Settings
     */
    calculatePrice:
        (state, getters) =>
        (price = 0, quantity = 1, isDisplay = false, isExchange = true) => {
            if (state.currencyDetails.length !== 0) {
                const { isDefault, exchangeRate } = state.currencyDetails;
                price = getters.formatPrice(price, false);
                price *= quantity;
                if (isDefault !== 1 && exchangeRate !== null)
                    price *= exchangeRate;
                return getters.formatPrice(price, isDisplay);
            }
            return 0;
        },
    formatPrice:
        (state) =>
        (price, isDisplay = true) => {
            if (price === 0 || price === undefined || Number.isNaN(price))
                return 0;
            const {
                decimal_places: decimalPlaces,
                separator_type: separatorType,
                rounding,
            } = state.currencyDetails;
            let formatPrice = parseFloat(price);
            if (rounding && decimalPlaces !== 0)
                formatPrice = Math.ceil(formatPrice);
            formatPrice =
                decimalPlaces === 0
                    ? Math.floor(formatPrice).toFixed()
                    : formatPrice.toFixed(2);
            if (!isDisplay) return parseFloat(formatPrice);
            if (separatorType !== 'none')
                formatPrice = formatPrice.replace(
                    /\B(?=(\d{3})+\b)/g,
                    separatorType
                );
            return formatPrice;
        },
    currency: (state) => {
        return state.currencyDetails ? state.currencyDetails.prefix : '';
    },
};

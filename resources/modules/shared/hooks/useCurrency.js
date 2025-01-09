import { inject } from 'vue';

export default function convertPrice(currency, price) {
    const currencyArray = inject('currencyArray');
    const formatCurrency = currency === 'RM' ? 'MYR' : currency;
    if (!currencyArray || currencyArray.length === 0)
        throw Error("Parameter 'currencyArray' can not be empty");

    const selectedCurrency = currencyArray.find((currencies) => {
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
        return '0.00';
    const { decimal_places: decimalPlaces, separator_type: separatorType } =
        selectedCurrency;

    formatPrice =
        decimalPlaces === 0
            ? Math.floor(formatPrice).toFixed()
            : formatPrice.toFixed(2);
    if (separatorType !== 'none')
        formatPrice = formatPrice.replace(/\B(?=(\d{3})+\b)/g, separatorType);
    return formatPrice;
}

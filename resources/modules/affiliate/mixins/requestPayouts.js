export default {
    props: {
        minimumPayout: {
            type: Number,
            default: () => 100,
        },
        availableCommissions: {
            type: Number,
            default: () => 0,
        },
        defaultCurrency: {
            type: String,
            default: '$',
        },
    },
};

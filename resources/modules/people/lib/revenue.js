export const calculateOrdersRevenue = (orders) =>
    orders.reduce(
        (acc, o) =>
            // acc + parseFloat(o.total) / (parseFloat(o.exchange_rate) || 1), // some legacy exchange rate on order will be null
            acc + parseFloat(o.total),
        0
    );

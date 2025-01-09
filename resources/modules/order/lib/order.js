const getOrderProductsTotalWeight = (orderDetails) =>
    orderDetails.reduce((total, od) => {
        console.log(orderDetails, 'orderDetails');
        // usersProduct.weight is a fallback for manual & legacy order (March 2020)
        const currentProductWeight =
            parseFloat(od.weight) || parseFloat(od.users_product?.weight || 0);

        return total + currentProductWeight;
    }, 0.0);

export default getOrderProductsTotalWeight;

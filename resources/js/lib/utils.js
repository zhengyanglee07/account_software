export const convertToLowercase = (item) => {
    return typeof item === 'string' ? item.toLowerCase() : item;
};

export const sort = (a, b, order) => {
    return order === 1
        ? a > b
            ? 1
            : b > a
            ? -1
            : 0
        : a < b
        ? 1
        : b < a
        ? -1
        : 0;
};

export const sortIdsByDescending = (records) => {
    return records.sort((a, b) => a.id - b.id).reverse();
};

/**
 * * Turn any string into camelCase
 * @param str : String
 * @return String
 */
export const camelizeString = (str) => {
    if (typeof str !== 'string') return str;
    return str
        .toLowerCase()
        .replace(/[^a-zA-Z0-9]+(.)/g, (m, chr) => chr.toUpperCase());
};

/**
 * Capitalize string
 * @param str : String
 * @returns String
 */
export const capitalize = (str) => {
    if (typeof str !== 'string') return str;
    return str.charAt(0).toUpperCase() + str.toLowerCase().slice(1);
};

/**
 * Turn string into snake case
 * @param str : String
 * @returns String
 */
export const kebabCaseLize = (str) =>
    (str ?? '').toLowerCase().replace(/ /g, '-');

/**
 * Get the value of query string in current module src
 * @param string queryParam
 * @returns
 */
export const getParamValue = (url, queryParam) =>
    new URL(url).searchParams.get(queryParam);

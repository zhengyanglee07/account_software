/**
 * Parse a string d to Date object, using the format yyyy-mm-dd.
 * d format must be (yyyy)-(mm)-(dd). Separator can be anything,
 * e.g. / or -
 *
 * @param {string} d
 * @returns {Date}
 */
export const parseDate = (d) => {
    const year = parseInt(d.slice(0, 4));
    const month = parseInt(d.slice(5, 7));
    const day = parseInt(d.slice(8, 10));
    return new Date(year, month - 1, day);
};

/**
 * Get first day Date object of provided date.
 * month param is optional to increase/decrease month
 *
 * @param date
 * @param month
 * @returns {Date}
 */
export const getFirstDay = (date, month = 0) =>
    new Date(date.getFullYear(), date.getMonth() + month, 1);

/**
 * Get last day Date object of provided date.
 * month param is optional to increase/decrease month
 *
 * Note: different with first day, month = 1 in here indicates current month
 *
 * @param date
 * @param month
 * @returns {Date}
 */
export const getLastDay = (date, month = 1) =>
    new Date(date.getFullYear(), date.getMonth() + month, 0);

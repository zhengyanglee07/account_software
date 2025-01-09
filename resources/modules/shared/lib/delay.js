/**
 * Simple es6 delay/sleep func
 *
 * @param ms
 * @returns {Promise<unknown>}
 */
export const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

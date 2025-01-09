/* eslint-disable import/prefer-default-export */
/**
 * Show validation err message from Laravel backend in axios
 * catch closure.
 *
 * The validation err data should and ONLY came from Laravel
 * own validation mechanism (e.g. $request->validate or similar).
 * Else you format properly the error response yourself
 *
 * Note: not tested in JQuery ajax, most probably it only works
 *       on axios
 *
 * @param error
 */
export const validationFailedNotification = (error) => {
    // status 422 to ensure that the error obtained is coming
    // from Laravel validation, though it's not 100%
    if (error.response && error.response.status === 422) {
        const { errors } = error.response.data;

        Object.keys(errors).forEach((field) => {
            errors[field].forEach((errMsg) => {
                window.toast.error('Error', errMsg);
            });
        });
    }
};

import partial from 'lodash/partial';
import './vanillaToast.script.js';
import './toast.style.css';

const ToastPlugin = {
    install(app, globalOptions = {}) {
        /**
         * UPDATE 05/2021:
         * $.Toast is replaced with vanilla JS. I named it window._Toast.
         * You shouldn't use window._Toast under any circumstances, please
         * use window.toast instead if you need to access this globally
         *
         * -------------------------------------------------------------
         *
         * Wrapper for ANY toast lib to use, currently it's $.Toast
         * If want to change to another toast lib in the future,
         * just convert the $.Toast in this function into another toast
         * implementation
         *
         * @param type Type of the toast (can be success, warning, info, error)
         * @param title Title of the toast
         * @param message Message of the toast
         * @param options Custom options to provide on call
         */
        function toast(type, title, message, options = {}) {
            /* eslint no-underscore-dangle: 0 */
            window._Toast(title, message, type, {
                ...globalOptions,
                ...options,
            });
        }

        // assign to window object (for easier usage in blade file)
        // Note: shouldn't be always doing this, but this is unavoidable
        // window.toast = {
        //     info: partial(toast, 'info'),
        //     success: partial(toast, 'success'),
        //     warning: partial(toast, 'warning'),
        //     error: partial(toast, 'error'),
        //     notice: partial(toast, 'notice'),
        //     toast,
        // };

        const toastPlugin = {
            info: partial(toast, 'info'),
            success: partial(toast, 'success'),
            warning: partial(toast, 'warning'),
            error: partial(toast, 'error'),
            notice: partial(toast, 'notice'),
            toast,
        };

        app.config.globalProperties.$toast = toastPlugin;
        app.provide('$toast', toastPlugin);
    },
};

export default ToastPlugin;

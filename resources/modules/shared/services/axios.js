import Axios from 'axios';

const baseURL = import.meta.env.PROD
    ? `https://${import.meta.env.VITE_APP_HOST}`
    : 'http://localhost:8000';

const axios = Axios.create({
    baseURL: `${baseURL}/api/v1`,
    timeout: import.meta.env.DEV ? 18000 : 8000,
    withCredentials: true,
});

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Intercept before sending HTTP requests
axios.interceptors.request.use(
    (response) =>
        /**
         * you can process config here
         */
        response,
    (error) => Promise.reject(error)
);

// Intercept after received HTTP requests
axios.interceptors.response.use(
    (response) =>
        /**
         * Any status code that lie within the range of 2xx
         * cause this function to trigger
         */
        response,
    (error) => {
        if (error.response && error.response.data) {
            // const code = error.response.status
            // const msg = error.response.data.message
            // ElMessage.error(`Code: ${code}, Message: ${msg}`)
            const { method, url, baseURL: baseApiUrl } = error.config;
            const { status, statusText } = error.response;
            const { message, file, line } = error.response.data;
            console.log(
                '\x1b[31m%s\x1b[0m',
                `
            [Axios Error]`
            );
            console.error(
                '\x1b[35m%s\x1b[0m',
                `
            ${method.toUpperCase()} ${baseApiUrl}${url}
            Status: ${status} - ${statusText}
            Error: 
                message: ${message}
                file: ${file}
                line: ${line}
            `
            );
            // method, path, status, statusText, config,
            // error.data
        } else {
            // ElMessage.error(`${error}`)
        }
        return Promise.reject(error);
    }
);
export default axios;

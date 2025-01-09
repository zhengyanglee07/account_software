import axios from 'axios';

export default {
    index() {
        return axios.get('/product/getAllActiveProducts');
    },
    show(domain, pathname) {
        // console.log('*(*(*(*(*(****************************$', domain, pathname);
        const domainName = import.meta.env.DEV ? 'localhost' : domain;
        const productPath = import.meta.env.DEV
            ? pathname
            : decodeURIComponent(pathname);
        return axios.get(`/${domainName}/products/${productPath}`);
    },
};

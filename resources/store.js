import { createStore } from 'vuex';
//* Module's store
import builder from '@builder/store/index.js';
import settings from '@setting/store/index.js';
import promotions from '@promotion/store/index.js';
import orders from '@order/store/index.js';
import product from '@product/store/index.js';
import automations from '@automation/store/index.js';
import image from '@shared/store/image.js';
import onlineStore from '@onlineStore/store/index.js';
import people from '@people/store/index.js';
import pageMetadata from '@shared/store/pageMetadata.js';
import customerAccount from '@customerAccount/store/index.js';

export default createStore({
    modules: {
        builder,
        settings,
        promotions,
        orders,
        product,
        image,
        onlineStore,
        people,
        pageMetadata,
        automations,
        customerAccount,
    },
    strict: process.env.NODE_ENV !== 'production',
});

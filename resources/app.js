import { createApp, h, defineAsyncComponent } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';

import './sass/app.scss';
import 'bootstrap';

//* Modals
import VueJqModal from '@shared/components/VueJqModal.vue';
import LimitModal from '@shared/components/LimitModal.vue';
import DeleteConfirmationModal from '@shared/components/DeleteConfirmationModal.vue';
import ModalTemplate from '@shared/components/ModalTemplate.vue';
import SettingsDeleteModal from '@shared/components/SettingsDeleteModal.vue';

/**
 * * Local Plugins
 */
import ToastPlugin from '@plugins/vtoast';

import vueCountryRegionSelect from 'vue3-country-region-select';

import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

import VueClickAway from 'vue3-click-away';

import VueSocialSharing from 'vue-social-sharing';

/**
 * * Services
 */
import Axios from 'axios';
import eventBus from '@services/eventBus.js';

/**
 * * Layout
 */
import Layout from '@shared/layout/BaseLayout.vue';

/**
 * * Vuex store
 */
import store from './store.js';

window.axios = Axios;
window.eventBus = eventBus;

createInertiaApp({
    resolve: async (name) => {
        // eslint-disable-next-line import/no-dynamic-require, global-require
        const pages = import.meta.glob('./modules/*/pages/*.vue');
        const page = (await pages[`./modules/${name}.vue`]()).default;
        if (name === 'automation/pages/AutomationBuilder') return page;
        page.layout = page.layout ?? Layout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        /**
         * * Plugins
         */
        app.use(plugin);
        app.use(store);
        app.use(vueCountryRegionSelect);
        app.use(VueClickAway);
        app.use(VueSocialSharing);
        app.use(ToastPlugin, {
            has_icon: true,
            position_class: 'toast-top-right',
            fullscreen: false,
            timeout: 4000,
            sticky: false,
            rtl: false,
        });

        /**
         * TODO register component in asynchronous way
         * const ComponentNameInPascalCase = defineAsyncComponent(() =>
         *     import('@module/components/ComponentNameInPascalCase.vue')
         * );
         * app.component('ComponentNameInPascalCase', ComponentNameInPascalCase);
         */
        //* Layouts
        app.component('Link', Link);

        //* Modals
        app.component('VueJqModal', VueJqModal);
        app.component('LimitModal', LimitModal);
        app.component('DeleteConfirmationModal', DeleteConfirmationModal);
        app.component('ModalTemplate', ModalTemplate);
        app.component('SettingsDeleteModal', SettingsDeleteModal);

        //* Global plugin components
        app.component('VSelect', vSelect);

        /**
         * * Base Components
         */
        const BaseFormInput = defineAsyncComponent(() =>
            import('@shared/components/BaseFormInput.vue')
        );
        app.component('BaseFormInput', BaseFormInput);

        const BaseFormCheckBox = defineAsyncComponent(() =>
            import('@shared/components/BaseFormCheckBox.vue')
        );
        app.component('BaseFormCheckBox', BaseFormCheckBox);

        const BaseFormRadio = defineAsyncComponent(() =>
            import('@shared/components/BaseFormRadio.vue')
        );
        app.component('BaseFormRadio', BaseFormRadio);

        const BaseFormSelect = defineAsyncComponent(() =>
            import('@shared/components/BaseFormSelect.vue')
        );
        app.component('BaseFormSelect', BaseFormSelect);

        const BaseCard = defineAsyncComponent(() =>
            import('@shared/components/BaseCard.vue')
        );
        app.component('BaseCard', BaseCard);

        const BaseFormGroup = defineAsyncComponent(() =>
            import('@shared/components/BaseFormGroup.vue')
        );
        app.component('BaseFormGroup', BaseFormGroup);

        const BaseButton = defineAsyncComponent(() =>
            import('@shared/components/BaseButton.vue')
        );
        app.component('BaseButton', BaseButton);

        const BaseButtonGroup = defineAsyncComponent(() =>
            import('@shared/components/BaseButtonGroup.vue')
        );
        app.component('BaseButtonGroup', BaseButtonGroup);

        const BaseFormSwitch = defineAsyncComponent(() =>
            import('@shared/components/BaseFormSwitch.vue')
        );
        app.component('BaseFormSwitch', BaseFormSwitch);

        const BaseFormTelInput = defineAsyncComponent(() =>
            import('@shared/components/BaseFormTelInput.vue')
        );
        app.component('BaseFormTelInput', BaseFormTelInput);

        const BaseImageUpload = defineAsyncComponent(() =>
            import('@shared/components/BaseImageUpload.vue')
        );
        app.component('BaseImageUpload', BaseImageUpload);

        const BaseMultiSelect = defineAsyncComponent(() =>
            import('@shared/components/BaseMultiSelect.vue')
        );
        app.component('BaseMultiSelect', BaseMultiSelect);

        const BaseSearchSelect = defineAsyncComponent(() =>
            import('@shared/components/BaseSearchSelect.vue')
        );
        app.component('BaseSearchSelect', BaseSearchSelect);

        const BaseColorPicker = defineAsyncComponent(() =>
            import('@shared/components/BaseColorPicker.vue')
        );
        app.component('BaseColorPicker', BaseColorPicker);

        const BaseDropdownOption = defineAsyncComponent(() =>
            import('@shared/components/BaseDropdownOption.vue')
        );
        app.component('BaseDropdownOption', BaseDropdownOption);

        const BaseModal = defineAsyncComponent(() =>
            import('@shared/components/BaseModal.vue')
        );
        app.component('BaseModal', BaseModal);

        const BaseDropdown = defineAsyncComponent(() =>
            import('@shared/components/BaseDropdown.vue')
        );
        app.component('BaseDropdown', BaseDropdown);

        const BaseTab = defineAsyncComponent(() =>
            import('@shared/components/BaseTab.vue')
        );
        app.component('BaseTab', BaseTab);

        const BaseImagePreview = defineAsyncComponent(() =>
            import('@shared/components/BaseImagePreview.vue')
        );
        app.component('BaseImagePreview', BaseImagePreview);

        const BaseFormCountrySelect = defineAsyncComponent(() =>
            import('@shared/components/BaseFormCountrySelect.vue')
        );
        app.component('BaseFormCountrySelect', BaseFormCountrySelect);

        const BaseFormRegionSelect = defineAsyncComponent(() =>
            import('@shared/components/BaseFormRegionSelect.vue')
        );
        app.component('BaseFormRegionSelect', BaseFormRegionSelect);

        const BaseFormTextarea = defineAsyncComponent(() =>
            import('@shared/components/BaseFormTextarea.vue')
        );
        app.component('BaseFormTextarea', BaseFormTextarea);

        const BaseBadge = defineAsyncComponent(() =>
            import('@shared/components/BaseBadge.vue')
        );
        app.component('BaseBadge', BaseBadge);

        const BaseDatePicker = defineAsyncComponent(() =>
            import('@shared/components/BaseDatePicker.vue')
        );
        app.component('BaseDatePicker', BaseDatePicker);

        const BasePopover = defineAsyncComponent(() =>
            import('@shared/components/BasePopover.vue')
        );
        app.component('BasePopover', BasePopover);

        const BaseChart = defineAsyncComponent(() =>
            import('@shared/components/BaseChart.vue')
        );
        app.component('BaseChart', BaseChart);

        /**
         * * Base Layout
         */
        const BasePageLayout = defineAsyncComponent(() =>
            import('@shared/layout/BasePageLayout.vue')
        );
        app.component('BasePageLayout', BasePageLayout);

        const BaseSettingLayout = defineAsyncComponent(() =>
            import('@shared/layout/BaseSettingLayout.vue')
        );
        app.component('BaseSettingLayout', BaseSettingLayout);

        const PictureElement = defineAsyncComponent(() =>
            import('@shared/components/PictureElement.vue')
        );
        app.component('PictureElement', PictureElement);

        /**
         * * Modals
         */
        const TemplateNameModal = defineAsyncComponent(() =>
            import('@builder/components/TemplateNameModal.vue')
        );
        app.component('TemplateNameModal', TemplateNameModal);

        /**
         * * Onboarding
         */
        const OnboardingFormLayout = defineAsyncComponent(() =>
            import('@onboarding/layout/OnboardingFormLayout.vue')
        );
        app.component('OnboardingFormLayout', OnboardingFormLayout);

        const SelectionCard = defineAsyncComponent(() =>
            import('@onboarding/components/OnboardingSelectionCard.vue')
        );
        app.component('SelectionCard', SelectionCard);

        /**
         * * Order
         */
        const OrderHeader = defineAsyncComponent(() =>
            import('@order/components/OrderHeader.vue')
        );
        app.component('OrderHeader', OrderHeader);

        const OrderHeaderLayout = defineAsyncComponent(() =>
            import('@order/layout/OrderHeaderLayout.vue')
        );
        app.component('OrderHeaderLayout', OrderHeaderLayout);

        const DisplayProduct = defineAsyncComponent(() =>
            import('@order/components/OrderDisplayProduct.vue')
        );
        app.component('DisplayProduct', DisplayProduct);

        /**
         * * Customer Account
         */
        const EcommerceAccountLayout = defineAsyncComponent(() =>
            import('@customerAccount/layout/EcommerceAccountLayout.vue')
        );
        app.component('EcommerceAccountLayout', EcommerceAccountLayout);

        /**
         * * Shared
         */

        const BaseDatatable = defineAsyncComponent(() =>
            import('@shared/components/BaseDatatable.vue')
        );
        app.component('BaseDatatable', BaseDatatable);

        const TextEditor = defineAsyncComponent(() =>
            import('@shared/components/TextEditor.vue')
        );
        app.component('TextEditor', TextEditor);

        //* Skeleton Loader
        const SkeletonLoader = defineAsyncComponent(() =>
            import('@shared/components/SkeletonLoader.vue')
        );
        app.component('SkeletonLoader', SkeletonLoader);

        //* Currency
        const CurrencySelect = defineAsyncComponent(() =>
            import('@shared/components/CurrencySelect.vue')
        );
        app.component('CurrencySelect', CurrencySelect);

        const StepNode = defineAsyncComponent(() =>
            import('@automation/components/BuilderStepNode.vue')
        );
        app.component('StepNode', StepNode);

        const NestedDraggable = defineAsyncComponent(() =>
            import('@shared/components/NestedDraggable.vue')
        );
        app.component('NestedDraggable', NestedDraggable);

        /**
         * * Global accessible properties
         */
        app.config.globalProperties.$axios = Axios;

        app.mount(el);
    },
});

// Add a response interceptor
window.axios.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        const { status } = error.response;
        const { data } = error.response;
        if (status === 422 && data.message.includes("You've Reach The Limit")) {
            const dataJSON = JSON.parse(data.message ?? '{}');
            if (dataJSON.exceed_limit)
                eventBus.$emit('open-limit-modal', {
                    showModal: dataJSON.exceed_limit,
                    modalTitle: dataJSON.modal_title,
                    context: dataJSON.context ?? null,
                    customContext: dataJSON.custom_context ?? null,
                    upgradeButton: dataJSON.upgradeButton,
                    subscriptionDetail: dataJSON.subscription ?? null,
                });
        }
        return Promise.reject(error);
    }
);

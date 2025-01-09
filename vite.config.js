import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const path = require('path');

export default defineConfig({
    build: {
        chunkSizeWarningLimit: 1000,
    },
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        laravel(['resources/app.js']),
    ],
    server: {
        host: true,
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),

            '@assets': '/resources/js/assets',
            '@components': '/resources/js/components',
            '@layouts': '/resources/js/layout',
            '@lib': '/resources/js/lib',
            '@mixins': '/resources/js/mixins',
            '@pages': '/resources/js/pages',
            '@plugins': '/resources/js/plugins',
            '@store': '/resources/js',
            '@services': '/resources/modules/shared/services',
            //* Modules
            '@shared': '/resources/modules/shared',
            '@funnel': '/resources/modules/funnel',
            '@product': '/resources/modules/product',
            '@builder': '/resources/modules/builder',
            '@app': '/resources/modules/app',
            '@template': '/resources/modules/template',
            '@setting': '/resources/modules/setting',
            '@subscription': '/resources/modules/subscription',
            '@order': '/resources/modules/order',
            '@promotion': '/resources/modules/promotion',
            '@onlineStore': '/resources/modules/online-store',
            '@onboarding': '/resources/modules/onboarding',
            '@automation': '/resources/modules/automation',
            '@productRecommendaiton':
                '/resources/modules/product-recommendation',
            '@cashback': '/resources/modules/cashback',
            '@people': '/resources/modules/people',
            '@socialProof': '/resources/modules/social-proof',
            '@affiliate': '/resources/modules/affiliate',
            '@report': '/resources/modules/report',
            '@auth': '/resources/modules/auth',
            '@customerAccount': '/resources/modules/customer-account',
            '@email': '/resources/modules/email',
            '@referral': '/resources/modules/referral',
            '@popup': '/resources/modules/popup',
            '@hypershapesAffiliate': '/resources/modules/hypershapes-affiliate',
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    $base-font-family: 'Lato', sans-serif;
                    $base-font-color : #202930;
                    $base-font-size: 14px;
                    $base-background: #f6f8f9;

                    $button-font-size: 12px;
                    $responsive-base-font-size: 12px;
                    $page-title-font-size : 20px;
                    $mobile-align-left-padding: 16px;

                    $h-primary: #009EF7;
                    $h-primary-hover: #0095E8;
                    $h-primary-text: #ffffff;

                    $h-secondary: #E4E6Ef;
                    $h-secondary-hover: #B5B5C3;
                    $h-secondary-color: #3F4254;
                    $h-secondary-buttercup: #f6bb12;
                    $h-secondary-pictonBlue: #43bdef;
                    $h-secondary-red: #ea1914;
                    $h-secondary-themePurple: #7766F7;

                    $h-section: #7239EA;
                    $h-column: #50CD89;
                    $h-element: #009EF7;

                    $sm-display: 576px;
                    $md-display: 768px;
                    $lg-display: 1024px;

                    $table-border-color: #ced4da;
                `,
            },
        },
    },
});

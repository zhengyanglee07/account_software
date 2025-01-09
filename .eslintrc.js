module.exports = {
    env: {
        browser: true,
        es2022: true,
        node: true,
    },
    extends: [
        'eslint:recommended',
        'airbnb-base',
        'plugin:vue/vue3-recommended',
        'prettier',
        'plugin:storybook/recommended',
    ],
    globals: {
        Atomics: 'readonly',
        SharedArrayBuffer: 'readonly',
        $: true,
    },
    parserOptions: {
        ecmaVersion: 2022,
        sourceType: 'module',
    },
    plugins: ['vue'],
    rules: {
        // 'linebreak-style': ['error', 'unix'],
        'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'import/extensions': ['error', 'ignorePackages'],
        'import/no-extraneous-dependencies': [
            'off',
            {
                devDependencies: false,
            },
        ],
        // 'no-param-reassign': [
        //     'error',
        //     {
        //         props: true,
        //         ignorePropertyModificationsFor: [
        //             'acc',
        //             'accumulator',
        //             'designAccumulator',
        //         ],
        //     },
        // ],

        'vue/component-name-in-template-casing': [
            'error',
            'PascalCase',
            {
                registeredComponentsOnly: false,
                ignores: [],
            },
        ],
        'no-case-declarations': 'off',
        'no-useless-escape': 'off',
        // add window. prefix when using browser gloabl variables
        'no-undef': 'warn',
        // avoid error caused by global variables like axios
        'no-unused-vars': 'off',
        'global-require': 'off',
        'no-param-reassign': 'off',
        radix: 'off',
        'no-plusplus': [
            'error',
            {
                allowForLoopAfterthoughts: true,
            },
        ],
        'vue/multi-word-component-names': 0,
    },
    settings: {
        'import/resolver': {
            alias: {
                map: [
                    ['@assets', './resources/js/assets'],
                    ['@components', './resources/js/components'],
                    ['@layouts', './resources/js/layout'],
                    ['@lib', './resources/js/lib'],
                    ['@mixins', './resources/js/mixins'],
                    ['@pages', './resources/js/pages'],
                    ['@plugins', './resources/js/plugins'],
                    ['@store', './resources/js'],
                    ['@services', './resources/modules/shared/services'],
                    //* Modules
                    ['@shared', './resources/modules/shared'],
                    ['@funnel', './resources/modules/funnel'],
                    ['@app', './resources/modules/app'],
                    ['@template', './resources/modules/template'],
                    ['@setting', './resources/modules/setting'],
                    ['@subscription', './resources/modules/subscription'],
                    ['@order', './resources/modules/order'],
                    ['@promotion', './resources/modules/promotion'],
                    ['@onlineStore', './resources/modules/online-store'],
                    ['@product', './resources/modules/product'],
                    ['@builder', './resources/modules/builder'],
                    ['@onboarding', './resources/modules/onboarding'],
                    ['@automation', './resources/modules/automation'],
                    [
                        '@productRecommendation',
                        './resources/modules/product-recommendation',
                    ],
                    ['@cashback', './resources/modules/cashback'],
                    ['@people', './resources/modules/people'],
                    ['@socialProof', './resources/modules/social-proof'],
                    ['@affiliate', './resources/modules/affiliate'],
                    [
                        '@customerAccount',
                        './resources/modules/customer-account',
                    ],
                    ['@email', './resources/modules/email'],
                    ['@emailBuilder', './resources/modules/email-builder'],
                    ['@auth', './resources/modules/auth'],
                    ['@referral', './resources/modules/referral'],
                    ['@popup', './resources/modules/popup'],
                    [
                        '@hypershapesAffiliate',
                        './resources/modules/hypershapes-affiliate',
                    ],
                ],
                extensions: ['.js', '.vue'],
            },
        },
    },
};

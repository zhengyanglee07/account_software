export default {
    showPeopleDesc: true,

    // original (mostly from db)
    contacts: [],
    tags: [],
    usersProducts: [],
    condition: {},
    customFieldNames: [],
    funnels: [],
    ecommercePages: [],
    forms: [],
    marketingEmailStatuses: ['Subscribed', 'Unsubscribed', 'Bounced'],

    // checked
    checkedContactIds: [],

    /**
     * sample conditionFilters array taken from people filter
     *
        [
            // "Or" condition
            [
                // "And" condition
                {
                    id: 1,
                    name: 'Total Sales',
                    error: true,

                    // subConditions contain all conditions from ONE filter
                    subConditions: [
                        {
                            key: 'is greater than or equal to',
                            value: '1',
                        },
                        {
                            key: 'in the last',
                            value: 'false',
                        },
                    ],
                },

                // "And" condition
                {
                    id: 2,
                    name: 'Tags',
                    error: true,

                    subConditions: [
                        {
                            key: 'have',
                            value: ['t1'],
                        },
                    ],
                },
            ],

            // "Or" condition
            [
                {
                    id: 3,
                    name: 'Custom Field',
                    error: true,

                    subConditions: [
                        {
                            key: 'have no',
                            value: ['t2'],
                        },
                    ],
                },
            ],

            // "Or" condition
            [
                {
                    id: 4,
                    name: 'Tags',
                    error: true,

                    subConditions: [
                        {
                            key: 'have',
                            value: ['t1'],
                        },
                    ],
                },
            ],
        ],

     Note: array above is just served as a rough example, please
           double check the current filter conditions array yourself.
           Maybe somehow the structure might change and this doc
           is not updated
    */
    conditionFilters: [
        [
            // initial condition filter
            {
                id: 1,
                name: '',
                error: true,
                subConditions: [],
            },
        ],
    ],
    conditionFiltersShowErrors: false,
};

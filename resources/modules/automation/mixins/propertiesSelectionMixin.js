/**
 * This mixin is used in automation modals' content, the
 * submit form and purchase product content.
 */
export default {
    props: ['value'],
    computed: {
        properties: {
            get() {
                return this.reducer(this.value);
            },
            set(newProperties) {
                this.$emit('input', newProperties);
            },
        },
    },
    methods: {
        /**
         * Note: should be overridden by caller
         *
         * Reduce properties used in v-select. For details refer to
         * vue-select docs, :reduce props
         *
         * @param {object} obj
         * @returns {object}
         */
        reducer(obj) {
            return obj;
        },
    },
    mounted() {
        if (!this.options) {
            throw new Error(
                'Please define an options, either in data or computed'
            );
        }

        // primarily for newly created trigger to provide a default properties,
        // for example "Any form" for submit_form trigger,
        // since new trigger should have a null/empty properties
        if (!this.value) {
            const defaultProperties = this.reducer(this.options[0]);
            this.$emit('input', defaultProperties);
        }
    },
};

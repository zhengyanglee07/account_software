import { mapState } from 'vuex';

export default {
    props: {
        conditionId: [String, Number],
        orIndex: Number,
    },
    computed: {
        ...mapState('people', ['conditionFiltersShowErrors']),
    },
    methods: {
        showError(modelName) {
            return (
                this.conditionFiltersShowErrors && this.v$[modelName]?.$invalid
            );
        },
    },
};

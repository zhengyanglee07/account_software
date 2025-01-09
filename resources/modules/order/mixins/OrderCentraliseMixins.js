import { mapMutations, mapState } from 'vuex';
import { VueTelInput } from 'vue-tel-input';

export default {
    components: {
        VueTelInput,
    },
    data() {
        return {
            bindProps: {
                enabledCountryCode: false,
                defaultCountry: 'Malaysia',
                validCharactersOnly: true,
                placeholder: '',
                mode: 'international',
                name: 'phoneNo',
                required: true,
                inputOptions: {
                    showDialCode: false,
                },
            },
        };
    },
    computed: {},
    methods: {
        ...mapMutations('orders', [
            'setState',
            'deleteErrorMessages',
            'updateState',
            'setErrors',
        ]),
    },
    mounted() {
        console.log('order mixin');
    },
};

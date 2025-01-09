import { mapActions, mapMutations, mapGetters, mapState } from 'vuex';
import { Modal } from 'bootstrap';

export default {
    computed: {
        ...mapState('promotions', {
            errors: 'errors',
            setting: 'promotionSetting',
            availableRegion: 'availableRegion',
            currency: 'currency',
            allCategories: 'allCategories',
            allProducts: 'allProducts',
            allSegments: 'allSegments',
        }),
    },
    data() {
        return {
            modal: null,
            isModalShowed: false,
        };
    },
    watch: {
        errors: {
            handler() {},
            deep: true,
        },
    },
    methods: {
        ...mapMutations('promotions', {
            updateStateSetting: 'updateSetting',
            deleteErrorMessages: 'deleteErrorMessages',
        }),
        triggerModal(elemId) {
            if (this.isModalShowed) return;
            const modalElem = document.getElementById(elemId);
            this.modal = new Modal(modalElem);
            this.modal.show();
            this.isModalShowed = true;
            modalElem.addEventListener('hidden.bs.modal', () => {
                this.isModalShowed = false;
            });
        },

        updateSetting(type, value, key, index) {
            this.updateStateSetting({ type, value, key, index });
        },
        clearError(event) {
            this.deleteErrorMessages({ event });
        },
        hasError(name) {
            return Object.prototype.hasOwnProperty.call(this.errors, name);
        },
        validateNumber(event) {
            const code = event.keyCode;
            return (
                (code === 69 ||
                    code === 189 ||
                    code === 187 ||
                    code === 109 ||
                    code === 107) &&
                event.preventDefault()
            );
        },
        validateInteger(event) {
            const code = event.keyCode;
            return (
                (code === 69 ||
                    code === 189 ||
                    code === 187 ||
                    code === 109 ||
                    code === 107 ||
                    code === 110 ||
                    code === 190) &&
                event.preventDefault()
            );
        },
    },
};

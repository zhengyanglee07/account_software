import { mapState, mapMutations } from 'vuex';
import { Modal } from 'bootstrap';
import BaseModal from '@shared/components/BaseModal.vue';

export default {
    components: {
        BaseModal,
    },
    data() {
        return {
            modalId: '', // placeholder, remember to replace this in your modal component
        };
    },
    computed: mapState('automations', ['modal']),
    methods: {
        ...mapMutations('automations', ['resetModal']),
        closeModal() {
            const modalInstance = Modal.getInstance(
                document.getElementById(this.modalId)
            );
            if (modalInstance) {
                modalInstance.hide();
            }
            new Modal(document.getElementById(this.modalId)).hide();
        },

        checkModalState(expectedType) {
            const { type, data: stepData } = this.modal;

            if (type !== expectedType)
                throw new Error(
                    `Type is not the same with expected type: ${expectedType}`
                );

            if (!stepData)
                throw new Error(
                    'Please provide at least an empty object for data in modal state'
                );
        },

        // to be overridden (or ignore if you don't want to use this in your modal)
        showBsModalListener(e) {},

        /**
         * to be overridden (or ignore if you don't want to use this in your modal)
         * You can choose to put something like resetModal in here to cleanup
         * previous modal state
         *
         * The reason why this listener is not default to reset modal state is,
         * because not all modals want to reset state after closing, e.g. AddStepModal.
         * Hence, I will let the mixin user to override this by itself
         */
        hiddenBsModalListener(e) {},
    },
    mounted() {
        const modal = document.getElementById(this.modalId);

        modal.addEventListener('show.bs.modal', this.showBsModalListener);
        modal.addEventListener('hidden.bs.modal', (e) => {
            if (e.target.id === 'add-sender-email-modal') return;
            this.hiddenBsModalListener(e);
        });
    },
};

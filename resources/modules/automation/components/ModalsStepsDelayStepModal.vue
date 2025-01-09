<template>
  <BaseModal
    :modal-id="modalId"
    title="Edit delay"
    manual
    no-footer
  >
    <DelayStepModalContent
      v-model="delay"
      @close-modal="closeModal"
    />
  </BaseModal>
</template>

<script>
import { deepSearch } from '@shared/lib/deep.js';
import { mapState } from 'vuex';
// import VueJqModal from '@components/VueJqModal.vue';
import modalDataMixin from '@automation/mixins/modalDataMixin';
import DelayStepModalContent from '@automation/components/ModalsStepsDelayStepModalContent.vue';

const getDefaultDelay = () => ({
  duration: 1,
  unit: 'minute',
});

export default {
  name: 'DelayStepModal',
  components: {
    // VueJqModal,
    DelayStepModalContent,
  },
  mixins: [modalDataMixin],
  data() {
    return {
      modalId: 'automation-delay-step-modal',
      delay: getDefaultDelay(),
    };
  },
  computed: {
    ...mapState('automations', ['steps']),
  },
  methods: {
    loadSavedDelay(stepId) {
      const step = deepSearch(this.steps, 'id', (k, v) => v === stepId);

      this.delay = {
        ...step.properties,
      };
    },

    // overridden methods from mixin
    showBsModalListener() {
      const {
        data: { id: stepId },
      } = this.modal;
      this.checkModalState('step');

      // new delay
      if (!stepId) return;
      this.loadSavedDelay(stepId);
    },
    hiddenBsModalListener() {
      this.resetModal();

      // don't forget to reset also local state
      this.delay = getDefaultDelay();
    },
  },
};
</script>

<style scoped></style>

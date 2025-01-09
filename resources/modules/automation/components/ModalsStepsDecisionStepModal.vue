<template>
  <BaseModal
    :modal-id="modalId"
    title="Edit Decision"
    size="xl"
    manual
    no-footer
  >
    <DecisionStepModalContent
      v-model="decision"
      @close-modal="closeModal"
    />
  </BaseModal>
</template>

<script>
import { mapState } from 'vuex';
import cloneDeep from 'lodash/cloneDeep';
// import VueJqModal from '@components/VueJqModal.vue';
import modalDataMixin from '@automation/mixins/modalDataMixin.js';
import DecisionStepModalContent from '@automation/components/ModalsStepsDecisionStepModalContent.vue';
import { deepSearch } from '@shared/lib/deep.js';

const getDefaultDecision = () => ({
  filters: [],
});

export default {
  name: 'DecisionStepModal',
  components: {
    // VueJqModal,
    DecisionStepModalContent,
  },
  mixins: [modalDataMixin],
  data() {
    return {
      modalId: 'automation-decision-step-modal',
      decision: getDefaultDecision(),
    };
  },
  computed: {
    ...mapState('automations', ['steps']),
  },
  methods: {
    loadSavedDecision(stepId) {
      const step = deepSearch(this.steps, 'id', (k, v) => v === stepId);
      this.decision = cloneDeep(step.properties);
    },

    // overridden methods from mixin
    showBsModalListener() {
      const {
        data: { id: stepId },
      } = this.modal;
      this.checkModalState('step');

      // new delay
      if (!stepId) return;
      this.loadSavedDecision(stepId);
    },
    hiddenBsModalListener() {
      this.resetModal();

      // don't forget to reset also local state
      this.decision = getDefaultDecision();
    },
  },
};
</script>

<style scoped></style>

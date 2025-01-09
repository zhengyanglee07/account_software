<template>
  <BaseModal
    :modal-id="modalId"
    title="Edit Action"
    manual
    no-footer
    size="xl"
  >
    <ActionStepModalContent
      :selected-action="selectedAction"
      @update-selected-action="handleUpdateSelectedAction"
      @close-modal="closeModal"
    />
  </BaseModal>
</template>

<script>
import { deepSearch } from '@shared/lib/deep.js';
import { mapState } from 'vuex';
import modalDataMixin from '@automation/mixins/modalDataMixin.js';
import ActionStepModalContent from '@automation/components/ModalsStepsActionStepModalContent.vue';
import { Modal } from 'bootstrap';
import eventBus from '@shared/services/eventBus.js';

export default {
  name: 'ActionStepModal',
  components: {
    ActionStepModalContent,
  },
  mixins: [modalDataMixin],
  data() {
    return {
      modalId: 'automation-action-step-modal',
      selectedAction: null,
    };
  },
  computed: {
    ...mapState('automations', ['steps', 'stepActions']),
    selectedActionType() {
      return this.selectedAction && this.selectedAction.type;
    },
  },
  mounted() {
    eventBus.$on('step-action-changed', () => {
      this.showBsModalListener();
    });
  },
  methods: {
    closeModal() {
      Modal.getInstance(
        document.getElementById('automation-action-step-modal')
      ).hide();
    },
    // overridden methods from mixin
    showBsModalListener() {
      const {
        data: { id: stepId },
      } = this.modal;
      this.checkModalState('step');

      // new action
      if (!stepId) return;

      const step = deepSearch(this.steps, 'id', (k, v) => v === stepId);

      const stepAction = this.stepActions.find(
        (stepActionFind) => stepActionFind.kind === step.kind
      );
      this.selectedAction = { ...stepAction };

      if (this.selectedAction.kind === 'automationSendEmailAction') {
        eventBus.$emit('email-modal-updated');
      }
    },
    hiddenBsModalListener() {
      this.resetModal();

      // clean up local states
      this.selectedAction = null;
    },

    handleUpdateSelectedAction(action) {
      this.selectedAction = { ...action };
    },
  },
};
</script>

<style scoped lang="scss"></style>

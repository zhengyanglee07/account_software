<template>
  <div
    class="modal-body row px-lg-17"
    style="padding-bottom: 0 !important"
  >
    <ActionSelector v-model="localSelectedAction" />
  </div>
  <ActionStepEmailModalContent
    v-if="selectedActionKind === 'automationSendEmailAction'"
    :is-add-step-modal="isAddStepModal"
    @close-modal="handleCloseModal"
    @back="$emit('back', $event)"
  />

  <ActionStepTagModalContent
    v-else-if="
      selectedActionKind === 'automationAddTagAction' ||
        selectedActionKind === 'automationRemoveTagAction'
    "
    :selected-action-kind="selectedActionKind"
    :is-add-step-modal="isAddStepModal"
    @close-modal="handleCloseModal"
    @back="$emit('back', $event)"
  />
  <button
    v-show="false"
    id="automation-add-step-action-modal-close-button"
    type="button"
    data-bs-dismiss="modal"
    aria-label="Close"
  />
</template>

<script>
import ActionSelector from '@automation/components/ModalsActionSelector.vue';
import ActionStepEmailModalContent from '@automation/components/ModalsStepsActionStepEmailModalContent.vue';
import ActionStepTagModalContent from '@automation/components/ModalsStepsActionStepTagModalContent.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'ActionStepModalContent',
  components: {
    ActionStepTagModalContent,
    ActionSelector,
    ActionStepEmailModalContent,
  },
  props: {
    selectedAction: Object,
    isAddStepModal: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    localSelectedAction: {
      get() {
        return this.selectedAction;
      },
      set(newSelectedAction) {
        this.$emit('update-selected-action', newSelectedAction);
      },
    },

    selectedActionKind() {
      return this.localSelectedAction?.kind;
    },
  },
  methods: {
    handleCloseModal() {
      this.$emit('close-modal');
    },
  },
};
</script>

<style scoped lang="scss"></style>

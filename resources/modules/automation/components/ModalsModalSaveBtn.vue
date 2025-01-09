<template>
  <button
    type="button"
    class="primary-small-square-button modal-save-btn"
    :disabled="saving"
    @click="handleSave"
  >
    <div
      v-if="saving"
      class="spinner--white-small"
    >
      <div class="loading-animation loading-container">
        <div class="shape shape1" />
        <div class="shape shape2" />
        <div class="shape shape3" />
        <div class="shape shape4" />
      </div>
    </div>
    <!-- <i v-if="saving" class="fas fa-circle-notch fa-spin pe-0"></i> -->
    <span v-else>Save</span>
  </button>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'ModalSaveBtn',
  props: {
    saving: Boolean,
  },
  methods: {
    handleSave() {
      this.$emit('handle-save');
      const modalInstance = Modal.getInstance(
        document.getElementById('automation-add-step-modal')
      );
      if (modalInstance) {
        modalInstance.hide();
        return;
      }
      new Modal(document.getElementById('automation-add-step-modal')).hide();
    },
  },
};
</script>

<style scoped lang="scss">

.modal-save-btn {
  min-width: 62px; // the approx width with 13px "Save"  word
  text-align: center;
}
</style>

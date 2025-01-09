<template>
  <BaseModal
    title="Add Social Proof"
    modal-id="add-social-proof-notification-modal"
  >
    <BaseFormGroup
      label="Create a name for your social proof notification"
      :error-message="showErr ? 'Name is required' : ''"
    >
      <BaseFormInput
        v-model="notificationName"
        placeholder="Notification 1, Notification 2, etc."
        @input="clearError"
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton @click="createNotification">
        Save
      </BaseButton>
    </template>
  </BaseModal>
  <VueJqModal
    v-if="false"
    modal-id="add-social-proof-notification-modal"
    :small="true"
  >
    <template #title>
      Create New Notification
    </template>
    <template #body>
      <label
        for="add-new-notification-input"
        class="m-container__text"
      >
        Create a name for your social proof notification
      </label>

      <input
        id="add-new-notification-input"
        v-model="notificationName"
        type="text"
        class="m-container__input firstInput"
        :class="{
          'error-border': showErr,
        }"
        @input="clearError"
      >

      <span
        v-if="showErr"
        class="m-container__text"
      >
        <p class="error">Field is required</p>
      </span>
    </template>

    <template #footer>
      <button
        type="button"
        class="cancel-button"
        data-bs-dismiss="modal"
      >
        Cancel
      </button>
      <button
        class="primary-small-square-button"
        @click="createNotification"
      >
        Create
      </button>
    </template>
  </VueJqModal>
</template>

<script>
/* eslint no-unused-expressions: 1 */
import { Modal } from 'bootstrap';
import BaseModal from '@shared/components/BaseModal.vue';

export default {
  componetns: {
    BaseModal,
  },
  data() {
    return {
      notificationName: '',
      showErr: false,
    };
  },

  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(
        'add-social-proof-notification-modal'
      );
      modalEl.addEventListener('hidden.bs.modal', () => {
        this.notificationName = '';
        this.showErr = false;
      });
    }, 1000);
  },

  methods: {
    clearError() {
      this.showErr = false;
    },

    createNotification() {
      this.showErr = this.notificationName?.trim() === '';
      if (this.showErr) return;
      axios
        .post('/create/notification', {
          name: this.notificationName,
        })
        .then((response) => {
          Modal.getInstance(
            document.getElementById('add-social-proof-notification-modal')
          ).hide();
          const referenceKey = response.data.reference_key;
          this.$inertia.visit(`/notification/edit/${referenceKey}`);
        });
    },
  },
};
</script>

<style scoped lang="scss">
.tag-error {
  position: absolute;
  bottom: -1.6rem;
}
</style>

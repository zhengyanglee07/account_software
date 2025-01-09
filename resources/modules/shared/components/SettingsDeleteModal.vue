<template>
  <VueJqModal
    :modal-id="modalId"
    :scrollable="false"
    :small="true"
    data_backdrop="static"
  >
    <template #title>
      Delete Setting
    </template>
    <template #custom-close-btn>
      <span
        type="button"
        class="close-button"
        aria-label="Close"
        @click="closeDeleteModal"
      >
        &times;
      </span>
    </template>
    <template #body>
      <!-- ERROR -->
      <p
        v-if="deletionError"
        class="m-container__text--error"
      >
        Oops! Something Went Wrong.
      </p>
      <p
        v-if="deletionError"
        class="m-container__text"
      >
        You have to enter "DELETE".
      </p>
      <p
        v-if="!deletionError"
        class="m-container__text--bold"
      >
        Are you sure?
      </p>
      <p
        v-if="!deletionError"
        class="m-container__text"
      >
        You won't be able to revert this.
      </p>
      <input
        v-model="deleteText"
        class="m-container__input form-control firstInput"
        type="text"
        placeholder="Enter &quot;DELETE&quot;"
        @keydown.enter="save"
      >
    </template>
    <template #footer>
      <button
        v-show="false"
        id="setting-delete-modal-close-button"
        type="button"
        data-bs-dismiss="modal"
        aria-label="Close"
      />
      <span
        class="cancel-button"
        @click="closeDeleteModal"
      >Cancel</span>
      <button
        id="deleteConfirmationButton"
        class="primary-small-square-button"
        @click="save"
      >
        Save
      </button>
    </template>
  </VueJqModal>
</template>

<script>
export default {
  name: 'SettingDeleteModal',

  props: ['modalId'],

  data() {
    return {
      deleteText: '',
      deletionError: false,
    };
  },

  methods: {
    save() {
      if (this.deleteText === 'DELETE') {
        this.deleteText = '';
        this.$emit('save-button');
      } else {
        this.$toast.error('Error', 'Something wrong. Please try again later.');
        this.deletionError = true;
      }
    },
    closeDeleteModal() {
      this.$emit('close-button');
    },
  },
};
</script>

<style></style>

<template>
  <BaseModal
    :title="`Terminate ${title}`"
    :modal-id="modalId"
  >
    <h2
      v-if="deletionError"
      class="text-danger"
    >
      Oops! Something Went Wrong.
    </h2>
    <h2 v-else>
      Are you sure?
    </h2>

    <BaseFormGroup
      :label="
        !deletionError
          ? `${displayLabel} ${date}`
          : `You have to enter 'TERMINATE'`
      "
      class="mt-5"
    >
      <BaseFormInput
        id="terminate-confirmation"
        v-model="deleteText"
        type="text"
        placeholder="Enter &quot;TERMINATE&quot;"
        @keyup.enter="eventOnSave"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="eventOnSave">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
export default {
  name: 'DeleteConfirmationModal',
  props: {
    title: {
      type: String,
      required: true,
    },
    modalId: {
      type: String,
      default: 'delete-modal',
    },
    date: {
      type: String,
      default: null,
    },
    displayLabel: {
      type: String,
      default: 'Your subscription will be terminate on',
    },
  },

  data() {
    return {
      deleteText: null,
      deletionError: false,
    };
  },

  methods: {
    eventOnCancel() {
      this.$emit('cancel');
      this.resetData();
    },

    eventOnSave() {
      if (this.deleteText === 'TERMINATE') {
        this.$emit('save');
        document.getElementById(`${this.modalId}-close-button`)?.click();
        this.resetData();
      } else {
        this.deletionError = true;
      }
    },

    resetData() {
      Object.assign(this.$data, this.$options.data.call(this));
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BaseModal
    title="Add Automation"
    modal-id="create-new-automation-modal"
  >
    <BaseFormGroup label="Automation Name">
      <BaseFormInput
        id="name"
        v-model="name"
        placeholder="Automation Name"
        name="name"
        type="text"
      />
      <span
        v-if="showError && v$.name.required.$invalid"
        class="text-danger"
      >
        Name is required
      </span>
    </BaseFormGroup>
    <template #footer>
      <BaseButton @click="createAutomation">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  name: 'CreateNewAutomationModal',
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      name: '',
      showError: false,
      showLimitModal: false,
      creatingAutomation: false,
    };
  },
  validations: {
    name: {
      required,
    },
  },
  methods: {
    clearError() {
      this.showError = false;
    },

    async createAutomation() {
      this.clearError();

      if (this.v$.$invalid) {
        this.showError = true;
        return;
      }

      const { name } = this;

      this.creatingAutomation = true;
      try {
        const res = await this.$axios.post('/automations', { name });
        const referenceKey = res.data.reference_key;
        window.location.href = `/automations/${referenceKey}`;
      } catch (err) {
        // generic error message
        if (err.response.status !== 422) {
          console.error(err);

          this.$toast.error(
            'Error',
            'Unexpected error occurs, failed to create automation.'
          );
          return;
        }
        if (err.response.data.exceed_limit) {
          this.showLimitModal = err.response.data.exceed_limit;
        }

        // showing validation error if response code is 422
        validationFailedNotification(err);
      } finally {
        this.creatingAutomation = false;
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

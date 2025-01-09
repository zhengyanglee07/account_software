<template>
  <BaseModal
    title="Add Email"
    :modal-id="modalId"
  >
    <p>Send an one-time email</p>
    <BaseFormGroup label="Enter a name">
      <BaseFormInput
        v-model="email"
        type="text"
        @input="clearError"
      />
      <template
        v-if="showError"
        #error-message
      >
        {{ v$.email.required.$invalid ? 'Email name is required' : errMsg }}
      </template>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="creatingEmail"
        @click="createStandardEmail"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { router } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'CreateNewEmailModal',
  props: {
    modalId: String,
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      show: false,
      email: '',

      // validations
      errMsg: '',
      showError: false,
      creatingEmail: false,
    };
  },
  validations: {
    email: {
      required,
    },
  },

  mounted() {
    // const modal = $(`#${this.modalId}`);
    // modal.on('hidden.bs.modal', this.handleHiddenBsModal);
  },
  methods: {
    closeModal() {
      this.show = false;
    },
    clearError() {
      this.showError = false;
      this.errMsg = '';
    },
    createStandardEmail() {
      this.clearError();

      const { email } = this;

      if (!email) {
        this.showError = true;
        return;
      }

      this.creatingEmail = true;
      axios
        .post('/emails/standard/create', {
          email,
        })
        .then(({ data: { reference_key: refKey } }) => {
          this.creatingEmail = false;
          const modalInstance = Modal.getInstance(
            document.getElementById(this.modalId)
          );
          if (modalInstance) {
            modalInstance.hide();
          }
          new Modal(document.getElementById(this.modalId)).hide();
          router.visit(`/emails/standard/${refKey}/edit`);
        })
        .catch((err) => {
          console.error(err);

          this.errMsg = err.response.data.message;
          this.showError = true;
        })
        .finally(() => {
          this.creatingEmail = false;
        });
    },

    handleHiddenBsModal() {
      this.email = '';
      this.clearError();
    },
  },
};
</script>

<style scoped lang="scss">
.email-err-msg {
  position: absolute;
  left: 0;
}

.nav-link {
  color: $h-primary !important;
  border-bottom: 1px solid $h-primary !important;
}
</style>

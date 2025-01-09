<template>
  <BaseModal
    title="Email Name"
    :modal-id="modalId"
  >
    <BaseFormGroup label="Change your email name">
      <BaseFormInput
        id="email-name"
        v-model="name"
        name="name"
        type="text"
      />
      <template
        v-if="showErr && v$.name.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="updatingName"
        @click="updateEmailName"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { Modal } from 'bootstrap';
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  name: 'EmailSetupNameModal',
  props: {
    modalId: String,
    email: {
      type: Object,
      required: true,
    },
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      name: '',
      showErr: false,
      modal: null,
      updatingName: false,
    };
  },
  validations: {
    name: {
      required,
    },
  },
  computed: {
    emailRefKey() {
      return this.email.reference_key;
    },
  },
  watch: {
    email() {
      this.name = this.email.name;
    },
  },
  mounted() {
    this.modal = new Modal(document.getElementById(this.modalId));
  },
  methods: {
    clearError() {
      this.showErr = false;
    },
    cancelEditEmailName() {
      this.name = this.email.name;
    },
    updateEmailName() {
      this.clearError();

      if (this.v$.$invalid) {
        this.showErr = true;
        return;
      }

      this.updatingName = true;
      const { name } = this;
      this.$axios
        .put(`/emails/${this.emailRefKey}/name`, {
          name,
        })
        .then(() => {
          this.$toast.success('Success', 'Successfully updated email name.');

          this.$emit('update-email-name', name);
          this.modal.hide();
        })
        .catch((error) => {
          if (error.response.status === 422) {
            validationFailedNotification(error);
            return;
          }

          // generic error
          this.$toast.error('Error', 'Unknown error occurs');
        })
        .finally(() => {
          this.updatingName = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

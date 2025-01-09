<template>
  <div class="w-100">
    <div class="w-100">
      <BaseFormGroup
        label="Current Password"
        label-for="old-password"
        required
      >
        <BaseFormInput
          id="old-password"
          v-model="form.oldPassword"
          type="password"
          placeholder="Enter Old Password"
          required
        >
          <template #append>
            <button
              class="cleanButton p-0"
              @click="showOrHidePassword('old-password')"
            >
              <i class="fas fa-eye" />
            </button>
          </template>
        </BaseFormInput>
        <template
          v-if="v$.form.oldPassword.required.$invalid && showError"
          #error-message
        >
          This field is required.
        </template>
      </BaseFormGroup>
      <BaseFormGroup
        label="New Password"
        label-for="new-password"
        required
      >
        <BaseFormInput
          id="new-password"
          v-model="form.newPassword"
          type="password"
          placeholder="Enter New Password"
          required
        >
          <template #append>
            <button
              class="cleanButton p-0"
              @click="showOrHidePassword('new-password')"
            >
              <i class="fas fa-eye" />
            </button>
          </template>
        </BaseFormInput>
        <template
          v-if="
            (v$.form.newPassword.required.$invalid ||
              v$.form.newPassword.minLength.$invalid ||
              v$.form.newPassword.valid.$invalid) &&
              showError
          "
          #error-message
        >
          {{
            v$.form.newPassword.required.$invalid
              ? 'This field is required'
              : 'Password must have 8 characters long with combination of letter(s) and number(s)'
          }}
        </template>
      </BaseFormGroup>
      <BaseFormGroup
        label="Confirm New Password"
        label-for="confirm-password"
        required
      >
        <BaseFormInput
          id="confirm-password"
          v-model="form.confirmPassword"
          type="password"
          placeholder="Confirm New Password"
          required
        >
          <template #append>
            <button
              class="cleanButton p-0"
              @click="showOrHidePassword('confirm-password')"
            >
              <i class="fas fa-eye" />
            </button>
          </template>
        </BaseFormInput>
        <template
          v-if="
            (v$.form.confirmPassword.required.$invalid ||
              form.confirmPassword !== form.newPassword) &&
              showError
          "
          #error-message
        >
          {{
            v$.form.confirmPassword.required.$invalid
              ? 'This field is required'
              : 'Confirm new password must be same as new password'
          }}
        </template>
      </BaseFormGroup>
    </div>
    <div class="col-md-8 w-100">
      <div class="d-flex justify-content-end pt-0">
        <BaseButton
          type="theme-primary"
          :disabled="isSaving"
          @click="updatePassword"
        >
          Save
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import useVuelidate from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';

export default {
  name: 'AccountSettingsResetPassword',
  setup() {
    return { v$: useVuelidate() };
  },

  data() {
    return {
      form: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: '',
      },
      showError: false,
      isSaving: false,
    };
  },

  validations: {
    form: {
      oldPassword: {
        required,
        minLength: minLength(8),
      },
      newPassword: {
        required,
        minLength: minLength(8),
        valid(val) {
          const containNumbers = /[0-9]/.test(val);
          const containLetters = /[a-zA-Z]/.test(val);
          return containNumbers && containLetters;
        },
      },
      confirmPassword: {
        required,
      },
    },
  },

  methods: {
    showOrHidePassword(elementId) {
      const element = document.getElementById(elementId);
      element.type = element.type === 'password' ? 'text' : 'password';
    },
    updatePassword() {
      this.showError = false;
      if (
        this.v$.$invalid ||
        this.form.confirmPassword !== this.form.newPassword
      ) {
        this.showError = true;
        return;
      }
      this.isSaving = true;
      axios
        .post('/save/password', {
          oldPassword: this.form.oldPassword,
          newPassword: this.form.newPassword,
        })
        .then(({ data: { status } }) => {
          if (status === 'success')
            this.$toast.success('Success', 'Successfully updated password');
          else this.$toast.error('Error', 'Fail to update password');
        })
        .finally(() => {
          this.isSaving = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
:deep(.form-group) {
  padding-right: 0 !important;
  padding-left: 0 !important;
}
:deep(.card .card-footer) {
  padding: 0.5rem 1rem !important;
}
</style>

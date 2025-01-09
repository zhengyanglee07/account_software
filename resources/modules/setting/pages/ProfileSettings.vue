<template>
  <BasePageLayout
    page-name="Profile Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Personal Profile">
      <template #description>
        <p>Hypershapes will use this information to contact you</p>
      </template>
      <template #content>
        <BaseFormGroup label="Email address">
          <BaseFormInput
            id="profile-email"
            :model-value="state.email"
            type="email"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="First Name"
          :error-message="
            validatePersonalData('firstName') ? 'Fill in the blank' : ''
          "
          col="6"
        >
          <BaseFormInput
            id="profile-first-name"
            v-model="state.firstName"
            type="text"
            placeholder="Enter First Name"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Last Name"
          :error-message="
            validatePersonalData('lastName') ? 'Fill in the blank' : ''
          "
          col="6"
        >
          <BaseFormInput
            id="profile-last-name"
            v-model="state.lastName"
            type="text"
            placeholder="Enter Last Name"
            required
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="savePersonal">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Change Password">
      <template #content>
        <BaseFormGroup
          label="Verify Password"
          :error-message="state.currentPasswordErr"
        >
          <BaseFormInput
            id="verify-password"
            v-model="state.oldPassword"
            type="password"
            placeholder="Enter Old Password"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Change Password"
          :error-message="state.newPasswordErr"
          col="6"
        >
          <BaseFormInput
            id="new-password"
            v-model="state.newPassword"
            type="password"
            placeholder="New Password"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Confirm New Password"
          :error-message="state.passwordConfirmationErr"
          col="6"
        >
          <BaseFormInput
            id="reenter-password"
            v-model="state.newPasswordConfirmation"
            type="password"
            placeholder="Reenter Password"
            required
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton
          :disabled="state.updatingPassword"
          @click="updatePassword"
        >
          {{ state.updatingPassword ? 'Saving...' : 'Save' }}
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import useVuelidate from '@vuelidate/core';
import { required, sameAs } from '@vuelidate/validators';
import { onMounted, ref, computed, reactive, watch, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import profileAPI from '@setting/api/profileAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  user: { type: Object, default: () => {} },
});

const state = reactive({
  email: '',
  firstName: '',
  lastName: '',
  oldPassword: '',
  newPassword: '',
  newPasswordConfirmation: '',
  currentPasswordErr: '',
  newPasswordErr: '',
  passwordConfirmationErr: '',
  hasPersonalError: false,
  updatingPassword: false,
  showValidationErrors: false,
});

const personalDataRules = {
  firstName: { required },
  lastName: { required },
};

const passwordRules = {
  oldPassword: { required },
  newPassword: { required },
  newPasswordConfirmation: { required },
};

const v1$ = useVuelidate(personalDataRules, state);
const v2$ = useVuelidate(passwordRules, state);

const validatePersonalData = (model) =>
  state.showValidationErrors && v1$.value[model].required.$invalid;

const validatePassword = (model, type) => {
  if (model === 'newPasswordConfirmation' && type === 'sameAsPassword')
    return state.newPassword !== state.newPasswordConfirmation;
  return v2$.value[model][type].$invalid;
};

const savePersonal = () => {
  state.showValidationErrors = v1$.value.$invalid;
  if (state.showValidationErrors) return;
  profileAPI.updateProfile(state.firstName, state.lastName).then(() => {
    $toast.success('Success', 'Successfully updated profile settings.');

    setTimeout(() => {
      router.reload();
    }, 700);
  });
};

const clearPassword = () => {
  state.oldPassword = '';
  state.newPassword = '';
  state.newPasswordConfirmation = '';
};

const clearPasswordError = () => {
  state.currentPasswordErr = '';
  state.newPasswordErr = '';
  state.passwordConfirmationErr = '';
};

const updatePassword = async () => {
  clearPasswordError();
  switch (true) {
    case validatePassword('oldPassword', 'required'):
      state.currentPasswordErr = 'Please fill in the blank';
      break;
    case validatePassword('newPassword', 'required'):
      state.newPasswordErr = 'Please fill in the blank';
      break;
    case validatePassword('newPasswordConfirmation', 'required'):
      state.passwordConfirmationErr = 'Please fill in the blank';
      break;
    case validatePassword('newPasswordConfirmation', 'sameAsPassword'):
      state.passwordConfirmationErr = 'Password does not match.';
      break;
    default:
      break;
  }

  if (
    v2$.value.$invalid ||
    validatePassword('newPasswordConfirmation', 'sameAsPassword')
  )
    return;
  state.updatingPassword = true;
  try {
    await profileAPI.updatePassword({
      current_password: state.oldPassword,
      password: state.newPassword,
      password_confirmation: state.newPasswordConfirmation,
    });

    clearPassword();

    $toast.success('Success', 'Password Update Successfully');
  } catch (err) {
    const errors = err.response?.data?.errors;

    if (errors?.current_password) {
      [state.currentPasswordErr] = errors.current_password;
      return;
    }

    [state.newPasswordErr] = errors.password;
    [state.passwordConfirmationErr] = errors.password;
  } finally {
    state.updatingPassword = false;
  }
};

onMounted(() => {
  state.email = props.user.email;
  state.firstName = props.user.firstName;
  state.lastName = props.user.lastName;
});
</script>

<style scoped lang="scss"></style>

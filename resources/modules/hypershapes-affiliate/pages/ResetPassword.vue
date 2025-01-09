<template>
  <Head title="Reset Password" />
  <!-- <div class="row">
          <div style="text-align: center; width: 100%">
            <img
              src="@shared/assets/media/hypershapes-logo.png"
              class="onboarding-logo"
            >
          </div>
        </div> -->
  <!-- <div class="container">
      <Head title="Reset Password" />
      <div class="resetPassContainer">
        <div class="row">
          <div style="text-align: center; width: 100%">
            <img
              src="@shared/assets/media/hypershapes-logo.png"
              class="onboarding-logo"
            >
          </div>
        </div> -->
  <!-- <div class="row justify-content-center">
          <div class="forgot-password-page">
            <img
              src="@shared/assets/media/hypershapes-logo.png"
              class="onboarding-logo"
            > -->
  <h1
    class="forgot-password-page__main-title h-two"
    style="text-align: center; margin: 36px 100px 9.75px 100px"
  >
    Setup New Password
  </h1>
  <div
    class="inputContainer__row"
    style="text-align: center; font-weight: 500"
  >
    <span
      class="p-two"
      style="
        cursor: default;
        padding-right: 5px;
        font-size: 16.25px;
        color: #b5b5c3;
      "
    >Already reset your password ?</span>
    <Link
      id="kt_login_signup"
      href="/login"
      class="inputContainer__redirect p-two"
      style="font-size: 16.25px"
    >
      Sign in here
    </Link>

    <div class="inputCol w-100">
      <form @submit.prevent="submit">
        <input
          type="hidden"
          name="token"
          :value="token"
        >
        <div
          class="inputContainer"
          style="margin: 0 auto"
        >
          <!-- <div class="inputContainer__row">
                  <label
                    for="email"
                    class="inputContainer__label h-five w-100"
                  >Email Address</label>
                  <input
                    id="email"
                    type="email"
                    class="inputContainer__input p-two"
                    name="email"
                    :value="email"
                    required
                    autocomplete="email"
                    autofocus
                    disabled
                  > -->

          <!-- <span class="invalid-feedback p-two" role="alert">{{ $message }}</span> -->
          <!-- </div> -->
          <div class="inputContainer__row">
            <div class="inputContainer__row text-start">
              <BaseFormGroup
                label="Password"
                description="Use 8 or more characters with a mix of letters and numbers"
                style="margin-top: 22.75px"
              >
                <BaseFormInput
                  id="password"
                  v-model="form.password"
                  style="margin-bottom: 5px"
                  type="password"
                  name="password"
                />
                <template
                  v-if="errors.password"
                  #error-message
                >
                  {{ errors.password }}
                </template>
              </BaseFormGroup>
            </div>
            <!-- <label
                    for="password"
                    class="inputContainer__label h-five w-100"
                  >Password</label>
                  <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="inputContainer__input p-two"
                    name="password"
                    required
                    autocomplete="new-password"
                  >
                  <span
                    v-if="errors.password"
                    class="error-message error-font-size"
                    style="color: red"
                    v-text="errors.password"
                  /> -->
            <!-- @error('password')
                                  <span class="invalid-feedback p-two" role="alert">{{ $message }}</span>
                              @enderror -->
          </div>

          <div class="inputContainer__row">
            <div class="inputContainer__row text-start">
              <BaseFormGroup label="Confirm Password">
                <BaseFormInput
                  id="password-confirm"
                  v-model="form.password_confirmation"
                  type="password"
                  name="password_confirmation"
                />
                <template
                  v-if="errors.password"
                  #error-message
                >
                  {{ errors.password }}
                </template>
              </BaseFormGroup>
            </div>
            <!-- <label
                    for="password-confirm"
                    class="inputContainer__label h-five w-100"
                  >Confirm Password</label>
                  <input
                    id="password-confirm"
                    v-model="form.password_confirmation"
                    type="password"
                    class="inputContainer__input p-two"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                  > -->
          </div>

          <div class="inputContainer__row mb-0 d-flex justify-content-center">
            <BaseButton
              is-submit
              @click="handleClickEvent"
            >
              Submit
            </BaseButton>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import AuthenticateLayout from '@hypershapesAffiliate/layouts/AuthLayout.vue';

export default {
  layout: AuthenticateLayout,
};
</script>

<script setup>
/* eslint-disable */
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
/* eslint-enable */

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const errors = computed(() => usePage().props.errors);

const submit = () => {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Login" />

  <h4
    class="loginOrSignUp__main-title h-two"
    style="text-align: center; font-size: 22.75px"
  >
    Sign In to Hypershapes
  </h4>
  <div
    class="inputContainer__row"
    style="text-align: center; margin-bottom: 32.5px; font-weight: 500"
  >
    <span
      class="p-two"
      style="color: #b5b5c3; font-size: 16.25px"
    >New Here? &nbsp;</span>
    <Link
      id="kt_login_signup"
      href="/register"
      class="inputContainer__redirect p-two"
      style="font-size: 16.25px"
    >
      Create an account
    </Link>
  </div>
  <div class="loginInputContainer w-100">
    <form @submit.prevent="submit">
      <div class="form-group row">
        <div style="width: 100%">
          <div
            v-if="status"
            class="alert alert-success"
            role="alert"
            style="font-size: 14px"
          >
            {{ status }}
          </div>
          <div class="inputContainer">
            <div class="inputContainer__row text-start">
              <BaseFormGroup label="Email">
                <BaseFormInput
                  id="email"
                  v-model="form.email"
                  type="email"
                  name="email"
                />
                <template
                  v-if="errors.email"
                  #error-message
                >
                  {{ errors.email }}
                </template>
              </BaseFormGroup>
            </div>
            <div class="inputContainer__row text-start">
              <BaseFormGroup
                label="Password"
                class="password-text"
              >
                <template #label-row-end>
                  <BaseButton
                    type="link"
                    class="inputContainer__forgot-password p-two"
                    style="color: #009ef7; display: inline-block"
                    href="/forgot-password"
                  >
                    Forgot Password ?
                  </BaseButton>
                </template>

                <BaseFormInput
                  id="password"
                  v-model="form.password"
                  type="password"
                  name="password"
                />
              </BaseFormGroup>
            </div>
          </div>
        </div>
      </div>
      <div
        class="form-group row inputContainer__row"
        style="justify-content: center"
      >
        <BaseFormGroup>
          <BaseFormCheckBox
            id="remember-me"
            v-model="form.remember"
            name="remember"
          >
            Keep me logged in
          </BaseFormCheckBox>
        </BaseFormGroup>

        <div class="inputContainer__row mb-0 d-flex justify-content-center">
          <BaseButton
            type="primary"
            style="width: 100%"
            is-submit
          >
            Log In
          </BaseButton>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import AuthenticateLayout from '@auth/layout/AuthenticateLayout.vue';

export default {
  layout: AuthenticateLayout,
};
</script>

<script setup>
/* eslint-disable */

import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BaseFormInput from '@shared/components/BaseFormInput.vue';
import BaseFormGroup from '@shared/components/BaseFormGroup.vue';
import BaseButton from '@shared/components/BaseButton.vue';
/* eslint-enable */

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const errors = computed(() => usePage().props.errors);

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
.password-text .form-label {
  width: 100px;
}
</style>

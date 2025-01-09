<template>
  <Head title="Forgot Password" />
  <!-- <img
        src="@shared/assets/media/hypershapes-logo.png"
        class="onboarding-logo"
      > -->
  <!-- <div class="row">
        <div class="d-block d-sm-none">
          <img
            src="@shared/assets/media/hypershapes-logo.png"
            style="margin: 15px 0; height: 50px"
          >
        </div>
      </div> -->
  <h4
    class="forgot-password-page__main-title h-two"
    style="text-align: center; font-size: 22.75px"
  >
    Forgot Password?
  </h4>
  <h5
    style="
      color: #b5b5c3;
      text-align: center;
      margin-bottom: 32.5px;
      font-size: 16.25px;
      font-weight: 500;
    "
  >
    Enter your email to reset your password.
  </h5>
  <div class="inputCol w-100">
    <div
      v-if="status"
      class="alert alert-success"
      role="alert"
      style="font-size: 14px"
    >
      {{ status }}
    </div>
    <form @submit.prevent="submit">
      <div class="inputContainer">
        <div class="inputContainer__row text-start">
          <!-- <label class="inputContainer__label h-five w-100">
                Email
              </label> -->
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
          <!-- <input
                id="email"
                v-model="form.email"
                type="email"
                class="inputContainer__input p-two is-invalid"
                name="email"
                required
                autofocus
                autocomplete="email"
              > -->
          <!-- <div
                v-if="false"
                style="width: 100%; margin-bottom: 20px"
              >
                <span
                  class="error-message error-font-size"
                  role="alert"
                >message</span>
              </div> -->
        </div>
        <div class="inputContainer__row mb-0 d-flex justify-content-center">
          <BaseButton
            type="primary"
            style="margin: 0.5rem"
            is-submit
            @click="triggerSomeEventOnClick"
          >
            Submit
          </BaseButton>

          <!-- <button
                type="submit"
                class="primary-square-button"
              >
                Submit
              </button> -->
          <!-- <Link
                href="/login"
                style="margin:auto 0 auto 20px; color: #202930; "
                class="cancel-button p-two"
              >
                Cancel
              </Link> -->
          <Link href="/login">
            <BaseButton
              type="light-primary"
              style="margin: 0.5rem"
            >
              Cancel
            </BaseButton>
          </Link>
        </div>
      </div>
    </form>
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
import { router, Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BaseFormInput from '@shared/components/BaseFormInput.vue';
import BaseFormGroup from '@shared/components/BaseFormGroup.vue';
import BaseButton from '@shared/components/BaseButton.vue';
/* eslint-enable */

defineProps({
  status: String,
});

const form = useForm({
  email: '',
});

const errors = computed(() => usePage().props.errors);

const submit = () => {
  form.post('/forget/email');
};
</script>

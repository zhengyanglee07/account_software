<template>
  <Head title="Verify Email" />
  <div class="text-center">
    <!-- <p class="h-two">
            Verify Email
          </p> -->

    <div class="verify-content p-two">
      <h1
        class="verify-content__text"
        style="line-height: 1.5; margin-top: 20px"
      >
        We have sent an email to <br>
        {{ email }}
      </h1>
      <h5
        class="verify-content__text mt-5"
        style="
          font-size: 16.25px;
          line-height: 1.5;
          color: #b5b5c3;
          font-weight: 500;
        "
      >
        Click the button in the email to confirm your <br>
        address and activate your account.
      </h5>
      <h5
        class="verify-content__text m-3"
        style="
          font-size: 16.25px;
          line-height: 1.5;
          color: #b5b5c3;
          font-weight: 500;
        "
      >
        Didn't get your email?
        <br>
        Check your spam, junk or promotion folder.
      </h5>
    </div>

    <div
      class="inputContainer__footer"
      style="margin-top: 40px"
    >
      <form
        class="d-inline"
        @submit.prevent="submit"
      >
        <BaseButton
          is-submit
          @click="handleClickEvent"
        >
          Resend Email
        </BaseButton>
        <!-- <button
                type="submit"
                class="primary-white-button"
                style="margin-right: 20px; white-space: nowrap"
              >
                Resend Email
              </button> -->
      </form>

      <!-- <form
              class="d-inline"
              @submit.prevent="logout"
            >
              <button
                type="submit"
                class="primary-square-button"
              >
                Back to login
              </button>
            </form> -->
    </div>
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
import { computed } from 'vue';
import { router, Head, Link, useForm } from '@inertiajs/vue3';
/* eslint-enable */

const props = defineProps({
  status: String,
  email: String,
});

const form = useForm({});

const submit = () => {
  form.post('/email/verification-notification');
};

const logout = () => {
  router.post('/logout');
};

const verificationLinkSent = computed(
  () => props.status === 'verification-link-sent'
);
</script>

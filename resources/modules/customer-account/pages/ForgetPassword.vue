<template>
  <div class="row">
    <div class="onboarding-title customer-account-h-two">
      Forgot Password
    </div>
  </div>
  <form @submit.prevent="forgetPassword">
    <div class="row d-flex justify-content-center">
      <div class="onboarding-container">
        <div
          v-if="status"
          class="alert"
          :class="status.class"
          role="alert"
          style="font-size: 14px"
        >
          {{ status.message }}
        </div>
        <BaseFormGroup
          label="Email Address"
          label-for="email"
        >
          <BaseFormInput
            id="email"
            v-model="form.email"
            type="email"
            name="email"
            required
          />
        </BaseFormGroup>
        <div class="text-center">
          <BaseButton
            is-submit
            class="mb-5"
            type="theme-primary"
          >
            Reset Password
          </BaseButton>
          <p class="text-center mb-0">
            Remember it ?&nbsp;&nbsp;<Link href="/login">
              Log In Here!
            </Link>
          </p>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  layout: PublishLayout,
  props: {
    status: {
      type: Object,
      required: false,
    },
  },
  setup() {
    const form = useForm({
      email: '',
    });

    const forgetPassword = () => {
      form.post('/forgot/email');
    };

    return {
      form,
      forgetPassword,
    };
  },
};
</script>

<style scoped lang="scss">
.error-message {
  color: red;
  font-size: 12px;
}

.toast-message {
  margin: 20px 0 !important;
}

.onboarding-title {
  padding-left: 0;
  padding-right: 0;
  margin: 8px 0px;
}

.onboarding-container {
  background-color: transparent;
  box-shadow: none;
  margin: 0px;
}

.onboarding {
  align-items: flex-start;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  overflow: auto;
  display: flex;
  justify-content: center;

  &-title {
    font-size: 28px;
    text-align: center;
    width: 100%;
  }

  &-container {
    width: 480px;
    background-color: white;
    box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 3%);
    border-radius: 0.25rem;
    padding: 1.25rem !important;
    margin: 10px 0;
    position: relative;
    /* min-height: 500px; */
  }
}

@media (min-width: 768px) {
  .onboarding-container {
    background-color: white;
    box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);
    border: 1px solid lightgray;
    padding: 30px !important;
    margin: 10px 0;
    min-width: 50%;
  }
}

.company-logo {
  padding-left: 0;
  padding-right: 0;
  margin-bottom: 36px;
}

.input-section {
  margin-top: 20px;
  margin-bottom: 0px;
}

@media (min-width: 768px) {
  .input-section {
    margin-bottom: 20px !important;
  }
}

label {
  margin-bottom: 0px !important;
}

.form-control {
  margin-top: 8px;
  min-height: 36px !important;
  padding: 10px;
  font-size: 12px !important;
}

.input-group-text {
  line-height: 1.6 !important;
  background-color: white !important;
  border-radius: 0 0.25rem 0.25rem 0;
  margin-top: 8px;
}

.login-button {
  margin: 20px 0 0 0;
}

.container-footer {
  margin-top: 0px;
}

@media (min-width: 768px) {
  .logo-responsize-size {
    height: 56px !important;
    width: auto !important;
  }
}

/* typo */
.customer-account-h-two {
  font-size: 25px !important;
  font-weight: 900 !important;
  font-family: 'Inter', sans-serif !important;
}
.customer-account-h-five {
  font-size: 14px !important;
  font-weight: 700 !important;
  font-family: 'Roboto', sans-serif !important;
}

.customer-account-p-three {
  font-size: 12px !important;
  font-weight: 400 !important;
  font-family: 'Roboto', sans-serif !important;
}
</style>

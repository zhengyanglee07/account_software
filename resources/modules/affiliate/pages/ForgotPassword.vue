<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          Reset Password
        </div>
      </div>
      <form @submit.prevent="forgetPassword">
        <div class="row d-flex justify-content-center">
          <div class="onboarding-container">
            <div
              v-if="status"
              class="alert alert-success"
              role="alert"
              style="font-size: 14px"
            >
              {{ status }}
            </div>

            <BaseFormGroup
              for="email"
              label="Email Address"
            >
              <BaseFormInput
                id="email"
                v-model="form.email"
                type="email"
                name="email"
                required
                autocomplete="email"
                autofocus
              />
            </BaseFormGroup>
            <!-- @error('email')
                  <span class="invalid-feedback p-two" role="alert">{{ $message }}</span>
              @enderror -->

            <div class="row justify-content-center">
              <BaseButton
                class="mb-5"
                is-submit
              >
                Reset Password
              </BaseButton>
              <p class="text-center mb-0">
                Remember it? &nbsp;
                <Link
                  href="/affiliates/login"
                  class="h_link customer-account-p-three"
                >
                  Sign In Here !
                </Link>
              </p>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  layout: PublishLayout,
  props: {
    account: {
      type: Object,
      default: () => ({}),
    },
    status: {
      type: String,
      default: () => null,
    },
  },
  setup() {
    const form = useForm({
      email: '',
    });

    const forgetPassword = () => {
      form.post('/affiliates/forget/email');
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
    border: 1px solid lightgray;
    border-radius: 0.25rem;
    padding: 30px !important;
    margin: 10px 0;
    min-width: 50%;
    position: relative;
    /* min-height: 500px; */
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
  text-align: center;
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

<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          Reset Password
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="onboarding-container">
          <form @submit.prevent="submit">
            <BaseFormGroup label="Email Address">
              <BaseFormInput
                id="email"
                type="email"
                name="email"
                :model-value="email"
                required
                autocomplete="email"
                autofocus
                disabled
              />
            </BaseFormGroup>
            <BaseFormGroup label="Password">
              <BaseFormInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'password' : 'text'"
                name="password"
                required
                autocomplete="new-password"
              >
                <template #append>
                  <BaseButton
                    id="show-password"
                    type=""
                    @click="showPassword = !showPassword"
                  >
                    <i :class="`fa fa-${showPassword ? 'eye' : 'eye-slash'}`" />
                  </BaseButton>
                </template>
              </BaseFormInput>
            </BaseFormGroup>
            <BaseFormGroup label="Confirm Password">
              <BaseFormInput
                id="password-confirm"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'password' : 'text'"
                name="password_confirmation"
                required
                autocomplete="new-password"
              >
                <template #append>
                  <BaseButton
                    id="show-password"
                    type=""
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i
                      :class="`fa fa-${
                        showConfirmPassword ? 'eye' : 'eye-slash'
                      }`"
                    />
                  </BaseButton>
                </template>
              </BaseFormInput>
              <template
                v-if="errors.password"
                #error-message
              >
                {{ errors.password }}
              </template>
            </BaseFormGroup>

            <div class="row justify-content-center">
              <BaseButton
                class="mb-5"
                is-submit
              >
                Reset Password
              </BaseButton>
              <p class="text-center mb-0">
                Remember it ?&nbsp;&nbsp;
                <Link href="/affiliates/login">
                  Sign in Here !
                </Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'ResetPassword',
  layout: PublishLayout,
  props: {
    token: {
      type: String,
      default: null,
    },
    email: {
      type: String,
      default: () => '',
    },
  },

  setup(props) {
    const form = useForm({
      token: props.token,
      email: props.email,
      password: '',
      password_confirmation: '',
    });

    const showPassword = ref(true);
    const showConfirmPassword = ref(true);

    const errors = computed(() => usePage().props.errors);

    const submit = () => {
      form.post('/affiliates/password/reset', {
        onFinish: () => form.reset('password', 'password_confirmation'),
      });
    };

    return {
      form,
      submit,
      showPassword,
      showConfirmPassword,
      errors,
    };
  },
};
</script>

<style lang="scss" scoped>
:deep(.input-group-text) {
  padding: 0px;
}

.error-message {
  color: red;
  font-size: 12px;
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

.vue-tel-input:deep(.vti__input) {
  font-size: 12px;
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

.cleanButton {
  background-color: transparent;
  border: none;
  padding: 5px;
  cursor: pointer;
  text-decoration: none;
}

.cleanButton:hover,
.cleanButton:active,
.cleanButton:focus {
  color: $h-primary;
  outline: none;
  cursor: pointer;
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

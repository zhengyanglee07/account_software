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
            <input
              type="hidden"
              name="token"
              :value="token"
            >
            <input
              type="hidden"
              name="account_id"
              :value="accountId"
            >
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
                disabled
              />
            </BaseFormGroup>
            <BaseFormGroup
              label="Password"
              label-for="password"
            >
              <BaseFormInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'password' : 'text'"
                name="password"
                required
              >
                <template #append>
                  <BaseButton
                    size="sm"
                    type=""
                    @click="showPassword = !showPassword"
                  >
                    <i :class="`fa fa-${showPassword ? 'eye' : 'eye-slash'}`" />
                  </BaseButton>
                </template>
              </BaseFormInput>
            </BaseFormGroup>
            <BaseFormGroup
              label="Confirm Password"
              label-for="password-confirm"
            >
              <BaseFormInput
                id="password-confirm"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'password' : 'text'"
                name="password_confirmation"
                required
              >
                <template #append>
                  <BaseButton
                    size="sm"
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
            <div class="text-center">
              <BaseButton
                class="mb-5"
                is-submit
              >
                Reset Password
              </BaseButton>
              <p class="mb-5 text-center">
                Remember it ?&nbsp;&nbsp;<Link href="/login">
                  Log in Here!
                </Link>
              </p>
              <Link
                href="/forgot"
                class="d-flex w-100 flex-center mb-0"
              >
                Forgot Password?
              </Link>
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
      required: false,
    },
    email: {
      type: String,
      required: false,
    },
    accountId: {
      type: String,
      required: false,
    },
    companyLogo: {
      type: String,
      required: false,
    },
    pageName: {
      type: String,
      required: false,
    },
  },

  setup(props) {
    // const props = defineProps({
    //   email: String,
    //   token: String,
    //   accountId: String,
    //   companyLogo: String,
    //   pageName: String,
    // });
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
      form.post('/password/reset', {
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

<style scoped lang="scss">
:deep(.input-group-text) {
  padding: 0px;
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

.input-group-append {
  margin-top: 8px;
}

.input-group-text {
  line-height: 1.6 !important;
  background-color: white !important;
  border-radius: 0 0.25rem 0.25rem 0;
}

@media (min-width: 767px) {
  .input-group-text {
    line-height: 1.8 !important;
  }
}

.form-check {
  margin-top: 20px;
  min-height: 0.5rem !important;
}

.login-button {
  margin: 20px 0 0 0;
}

.container-footer {
  margin-top: 0px;
}

@media (min-width: 768px) {
  .logo-responsive-size {
    height: 56px !important;
    width: auto !important;
  }
}

.form-check {
  padding-left: 0;
}

.welcome-statement {
  font-size: 30px !important;
  font-weight: 900 !important;
  font-family: 'Inter', sans-serif !important;
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
</style>

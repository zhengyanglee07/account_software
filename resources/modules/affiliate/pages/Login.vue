<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          <!-- <img
            v-if="account?.company_logo"
            :src="account?.company_logo"
            class="affOnboarding__logo"
            :alt="`Logo of ${account?.company}`"
          >
          <h1 v-else>
            {{ account?.company }}
          </h1> -->
          Log In
        </div>
      </div>

      <div class="row d-flex justify-content-center">
        <div class="onboarding-container">
          <form @submit.prevent="login">
            <div
              v-if="status"
              class="alert alert-success"
              role="alert"
              style="font-size: 14px"
            >
              {{ status }}
            </div>
            <BaseFormGroup
              label="Email"
              label-for="exampleInputEmail1"
            >
              <BaseFormInput
                id="exampleInputEmail1"
                v-model="form.email"
                name="email"
                type="email"
                aria-describedby="emailHelp"
                placeholder="Enter email"
                required
              />
              <template
                v-if="errors.email"
                #error-message
              >
                {{ errors.email }}
              </template>
            </BaseFormGroup>

            <BaseFormGroup
              label="Password"
              label-for="exampleInputPassword1"
            >
              <BaseFormInput
                id="exampleInputPassword1"
                v-model="form.password"
                name="password"
                :type="isPasswordTypePassword ? 'password' : 'text'"
                placeholder="Password"
                required
              >
                <template #append>
                  <BaseButton
                    id="show-password"
                    size="sm"
                    type=""
                    @click="isPasswordTypePassword = !isPasswordTypePassword"
                  >
                    <i
                      :class="`fa fa-${
                        isPasswordTypePassword ? 'eye' : 'eye-slash'
                      }`"
                    />
                  </BaseButton>
                </template>
              </BaseFormInput>
              <!-- <div
                v-if="errorMessage.password"
                style="width: 100%; margin-bottom: 12px"
              >
                <span class="error-message error-font-size" role="alert">
                  {{ errorMessage.password[0] }}
                </span>
              </div> -->
            </BaseFormGroup>

            <BaseFormGroup>
              <BaseFormCheckBox
                id="exampleCheck1"
                v-model="form.remember"
                name="remember"
              >
                Keep me logged in
              </BaseFormCheckBox>
            </BaseFormGroup>
            <div class="row justify-content-center">
              <BaseButton
                class="mb-5"
                is-submit
              >
                Log In
              </BaseButton>
              <p class="mb-5 text-center">
                Not affiliate member yet?&nbsp;&nbsp;
                <Link href="/affiliates/signup">
                  Create an Account
                </Link>
              </p>
              <Link
                href="/affiliates/forget"
                class="d-flex w-100 flex-center customer-account-p-three"
              >
                Forget Password?
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'Login',
  layout: PublishLayout,
  props: {
    account: {
      type: Object,
      default: () => ({}),
    },
  },
  setup() {
    const form = useForm({
      email: '',
      password: '',
      remember: false,
    });

    const isPasswordTypePassword = ref(true);

    const errors = computed(() => usePage().props.errors);

    const login = () => {
      form.post('/affiliates/login', {
        onFinish: () => form.reset('password'),
      });
    };

    return {
      form,
      login,
      isPasswordTypePassword,
      errors,
    };
  },
};
</script>
<style scoped lang="scss">
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

.customer-account-p-three {
  font-size: 12px !important;
  font-weight: 400 !important;
  font-family: 'Roboto', sans-serif !important;
}
</style>

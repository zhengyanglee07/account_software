<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          Sign Up
        </div>
      </div>

      <div class="row d-flex justify-content-center">
        <div class="onboarding-container">
          <div class="flash-message w-100" />
          <form @submit.prevent="register">
            <BaseFormGroup label="Full Name">
              <BaseFormInput
                v-model="form.fullName"
                name="fullName"
                type="text"
                required
                placeholder="Enter full name"
              />
              <template
                v-if="errors.fullName"
                #error-message
              >
                {{ errors.fullName }}
              </template>
            </BaseFormGroup>
            <BaseFormGroup label="Phone Number">
              <BaseFormTelInput
                v-model="form.phoneNumber"
                required
              />
            </BaseFormGroup>
            <BaseFormGroup
              label="Email"
              label-for="emailInput"
            >
              <BaseFormInput
                id="emailInput"
                v-model="form.email"
                name="email"
                type="email"
                placeholder="Enter email"
                required
              />
              <template
                v-if="errors.email || status"
                #error-message
              >
                {{ errors.email || status }}
              </template>
            </BaseFormGroup>
            <BaseFormGroup
              label="Password"
              label-for="passwordInput"
            >
              <BaseFormInput
                id="passwordInput"
                v-model="form.password"
                name="password"
                :type="isPasswordTypePassword ? 'password' : 'text'"
                placeholder="Password"
                required
              >
                <template #append>
                  <BaseButton
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
                type="theme-primary"
                is-submit
              >
                Sign Up
              </BaseButton>
              <p class="text-center mb-0">
                Already have an account?&nbsp;&nbsp;<Link href="/login">
                  Log In Now!
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
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'Register',

  layout: PublishLayout,
  props: {
    affiliateId: {
      type: Number,
      required: false,
    },
    status: {
      type: String,
      required: false,
    },
  },

  setup() {
    const form = useForm({
      fullName: '',
      phoneNumber: '',
      email: '',
      password: '',
    });

    const isPasswordTypePassword = ref(true);

    const errors = computed(() => usePage().props.errors);

    const register = () => {
      form.post(`/signup/${window.location.host}`, {
        onFinish: (data) => {
          if (data === 'register_successful')
            router.visit('/email/verification');
        },
      });
    };

    return {
      form,
      register,
      isPasswordTypePassword,
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

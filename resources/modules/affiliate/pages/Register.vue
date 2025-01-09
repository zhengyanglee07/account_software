<template>
  <div class="onboarding">
    <div class="w-100">
      <div class="row">
        <div class="onboarding-title customer-account-h-two">
          <!-- <img
            v-if="companyLogo"
            :src="companyLogo"
            class="affOnboarding__logo"
            :alt="`Logo of ${companyName}`"
          >
          <h1 v-else>
            {{ companyName }}
          </h1> -->
          <h2 class="affOnboarding__title h-two">
            Sign up as {{ companyName }}'s affiliate member now!
          </h2>
        </div>
      </div>
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
          <div class="row">
            <BaseFormGroup
              label="First Name"
              col="md-6"
            >
              <BaseFormInput
                id="firstName"
                v-model="firstName"
                type="text"
                name="firstName"
              />
            </BaseFormGroup>
            <BaseFormGroup
              label="Last Name"
              col="md-6"
            >
              <BaseFormInput
                id="lastName"
                v-model="lastName"
                type="text"
                name="lastName"
                @input="clearValidationErrors"
              />
              <template
                v-if="showValidationErrors && v$.lastName.required.$invalid"
                #error-message
              >
                Last Name is required
              </template>
            </BaseFormGroup>
          </div>

          <div class="row">
            <BaseFormGroup label="Email">
              <BaseFormInput
                id="email"
                v-model="email"
                type="email"
                name="email"
                required
                autocomplete="off"
                autofocus
                @input="clearValidationErrors"
              />
              <BaseFormGroup
                v-if="showValidationErrors && v$.email.required.$invalid"
              >
                <template #error-message>
                  Email is required
                </template>
              </BaseFormGroup>

              <BaseFormGroup
                v-else-if="showValidationErrors && v$.email.email.$invalid"
              >
                <template #error-message>
                  Email format is invalid
                </template>
              </BaseFormGroup>

              <!-- TODO: email existed msg for affiliate member account -->
              <!--            <div class="inputContainer__row" v-if="emailMessage !== ''">-->
              <!--              <span-->
              <!--                v-if="emailMessage !== ''"-->
              <!--                style="font-size: 12px"-->
              <!--                :class="[isEmailExisted ? 'failed' : 'success']"-->
              <!--              >-->
              <!--                {{ emailMessage }}-->
              <!--              </span>-->
              <!--            </div>-->
            </BaseFormGroup>
          </div>

          <div class="row">
            <BaseFormGroup label="Password">
              <BaseFormInput
                id="password"
                v-model="password"
                type="password"
                required
                autocomplete="new-password"
                name="password"
                @input="clearValidationErrors"
              >
                <template #append>
                  <BaseButton
                    size="sm"
                    type=""
                    @click="showPassword"
                  >
                    <i
                      id="aff-signup-eye"
                      class="fas fa-eye"
                    />
                  </BaseButton>
                </template>
              </BaseFormInput>
              <BaseFormGroup
                v-if="
                  showValidationErrors &&
                    v$.password.$invalid &&
                    !v$.password.minLength.$invalid
                "
              >
                <template #error-message>
                  Password is required
                </template>
              </BaseFormGroup>
              <BaseFormGroup
                v-if="showValidationErrors && v$.password.minLength.$invalid"
              >
                <template #error-message>
                  Password must be at least 8 characters
                </template>
              </BaseFormGroup>
            </BaseFormGroup>
          </div>
          <div class="row justify-content-center">
            <BaseButton
              class="mb-5"
              is-submit
              :disabled="signingUp"
              @click="signUp"
            >
              <span v-if="!signingUp">Sign Up</span>
              <span v-else>
                <i class="fas fa-circle-notch p-0 m-0 fa-spin" />
              </span>
            </BaseButton>
            <p class="text-center mb-0">
              Already have an account ?&nbsp;
              <Link :href="loginLink">
                Sign In Now !
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required, email, minLength } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { validationFailedNotification } from '@shared/lib/validations.js';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  layout: PublishLayout,
  props: {
    account: {
      type: Object,
      default: () => ({}),
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },

  data() {
    return {
      email: '',
      firstName: '',
      lastName: '',
      password: '',
      signingUp: false,
      isEmailExisted: false,
      isCheckingEmail: false,
      token: null,

      showValidationErrors: false,
    };
  },
  validations: {
    email: {
      required,
      email,
    },
    password: {
      required,
      minLength: minLength(8),
    },
    lastName: {
      required,
    },
  },
  computed: {
    // Note: this url probably wont work locally, test this in staging if you want
    companyLogo() {
      return this.account.company_logo;
    },
    companyName() {
      return this.account.store_name;
    },

    loginLink() {
      return `/affiliates/login`;
    },
    signUpLink() {
      const via = new URLSearchParams(window?.location.search).get('via');
      return `/affiliates/signup${via ? `?via=${via}` : ''}`;
    },
  },

  mounted() {
    this.token = new URLSearchParams(window?.location.search).get('token');
  },
  methods: {
    clearValidationErrors() {
      this.showValidationErrors = false;
    },

    async signUp() {
      this.showValidationErrors = this.v$.$invalid;
      // console.log(this.v$)
      // return

      if (this.showValidationErrors) {
        return;
      }

      this.signingUp = true;

      try {
        await this.$axios.post(this.signUpLink, {
          first_name: this.firstName,
          last_name: this.lastName,
          email: this.email,
          password: this.password,
          token: this.token,
        });

        this.$toast.success('Success', 'Sign Up Successfully.');

        this.$inertia.visit('/affiliates/confirm/email');
      } catch {
        this.password = '';

        // if (error.response.status !== 422) {
        //   this.$toast.error(
        //     'Error',
        //     `Something went wrong, ${error.response.data.message}`
        //   );
        //   return;
        // }

        // validationFailedNotification(error);
      } finally {
        this.signingUp = false;
      }
    },

    showPassword() {
      const element = document.getElementById('password');
      const eye = document.getElementById('aff-signup-eye');
      if (element.type === 'password') {
        element.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
      } else {
        element.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
      }
    },
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
.affiliate-account-h-two {
  font-size: 25px !important;
  font-weight: 900 !important;
  font-family: 'Inter', sans-serif !important;
}
.affiliate-account-h-five {
  font-size: 14px !important;
  font-weight: 700 !important;
  font-family: 'Roboto', sans-serif !important;
}

.affiliate-account-p-three {
  font-size: 12px !important;
  font-weight: 400 !important;
  font-family: 'Roboto', sans-serif !important;
}
</style>

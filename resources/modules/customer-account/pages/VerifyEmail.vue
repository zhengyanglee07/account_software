<template>
  <div class="onboarding">
    <div class="w-100 row">
      <div class="company-logo text-center">
        <img
          v-if="companyLogo"
          :src="companyLogo"
          style="height: 42px; width: auto"
          class="logo-responsive-size"
        >
        <img
          v-else
          src="@shared/assets/media/hypershapes-logo.png"
          style="height: 42px; width: auto"
          class="logo-responsive-size"
        >
      </div>

      <div class="onboarding-title customer-account-h-two mb-5">
        Verify Email
      </div>

      <div>
        <div class="d-flex justify-content-center text-center">
          <div>
            <p class="mb-5">
              We have sent an email to {{ email }}
            </p>
            <p class="mb-5">
              Click the link in the email to confirm your address and activate
              your account.
            </p>
            <div class="mb-5">
              Didn't get your email?
              <br>
              Check your spam, junk or promotion folder.
            </div>
          </div>
        </div>
      </div>

      <div class="text-center justify-content-center">
        <form
          class="m-5 d-inline"
          method="get"
          action="/email/reset"
        >
          <BaseButton is-submit>
            Resend email
          </BaseButton>
        </form>
        <BaseButton
          type="link"
          href="/login"
        >
          Back to login
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  layout: PublishLayout,

  props: {
    email: {
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
  setup() {
    const redirectToLogin = () => {
      window.location.pathname = '/login';
    };

    const logout = () => {
      axios
        .get('/logout')
        .then(() => redirectToLogin())
        .catch((error) => console.log(error));
    };

    return { logout };
  },
};
</script>

<style scoped lang="scss">
.onboarding-container {
  background-color: transparent;
  box-shadow: none;
  padding: 0 !important;
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
    padding: 1rem 4rem !important;
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

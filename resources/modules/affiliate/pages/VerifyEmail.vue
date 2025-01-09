<template>
  <div class="onboarding">
    <div class="w-100 row">
      <div
        v-if="companyLogo"
        class="company-logo text-center"
      >
        <img
          :src="companyLogo"
          style="height: 42px; width: auto"
          class="logo-responsive-size"
        >
        <!-- <img
          v-else
          src="@shared/assets/media/hypershapes-logo.png"
          style="height: 42px; width: auto"
          class="logo-responsive-size"
        > -->
        <!-- <h1 v-else>
          {{ account?.company }}
        </h1> -->
      </div>

      <div class="onboarding-title customer-account-h-two">
        Verify Email
      </div>

      <div class="row d-flex justify-content-center">
        <div class="onboarding-container">
          <div class="text-center">
            <div>
              <p class="mb-5">
                We have sent an email to {{ email }}
              </p>
              <p class="mb-5">
                Click the link in the email to confirm your address and activate
                your affiliate account.
              </p>
              <div class="mb-5">
                Didn't get your email?
                <br>
                Check your spam, junk or promotion folder.
              </div>
            </div>
            <form
              class="m-5 d-inline"
              method="get"
              action="/affiliates/email/resend"
            >
              <BaseButton is-submit>
                Resend email
              </BaseButton>
            </form>
            <BaseButton
              type="link"
              href="/affiliates/login"
            >
              Back to login
            </BaseButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  layout: PublishLayout,

  props: {
    email: {
      type: String,
      default: () => '',
    },
    account: {
      type: Object,
      default: () => ({}),
    },
  },
};
</script>

<style scoped lang="scss">
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

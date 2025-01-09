<template>
  <BasePageLayout
    page-name="User Role Settings"
    back-to="/settings/user"
    is-setting
  >
    <SettingPageHeader
      title="Invite User"
      :previous-button-u-r-l="'/settings/user'"
      previous-button-link-title="Back To User Settings"
    />
    <BaseSettingLayout title="Administrator">
      <template #description>
        Handle everything except subscription and payment.
      </template>
      <template #content>
        <BaseFormGroup
          label="User Email"
          label-for="email"
        >
          <BaseFormInput
            id="email"
            v-model="email"
            type="text"
            placeholder="Email Address"
          />
          <span
            v-if="showEmailError"
            class="text-danger"
          >
            {{ errorMessage }}
          </span>
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton @click="sendInvitation">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
export default {
  props: {
    accountRandomId: String,
    existingEmails: Array,
  },

  data() {
    return {
      email: '',
      showEmailError: false,
      errorMessage: '',
      isLoading: false,
      limitError: false,
    };
  },
  methods: {
    sendInvitation() {
      if (this.email === '') {
        this.showEmailError = true;
        this.errorMessage = 'Email cannot be empty!';
        return;
      }
      const isEmailValid = this.validateEmail(this.email);
      if (!isEmailValid) {
        this.errorMessage = 'Email is invalid';
        this.showEmailError = true;
        return;
      }
      if (this.existingEmails.includes(this.email)) {
        if (this.email === this.existingEmails[0]) {
          this.errorMessage =
            'The following user are yourself, the owner to this account!';
          this.showEmailError = true;
          return;
        }
        this.errorMessage =
          'The following user already an admin to your account or it is invited as the admin to your account!';
        this.showEmailError = true;
        return;
      }
      this.isLoading = true;

      axios
        .post('/role/send-invitation', {
          email: this.email,
          randomId: this.accountRandomId,
          role: 'admin',
        })
        .then(({ data }) => {
          //   this.isLoading = false;
          this.$toast.success('Success', 'Invitation Email Sent.');
        })
        .catch((e) => {
          console.log('Error', 'Something Went Wrong.');
          this.limitError = true;
        })
        .finally(() => {
          this.isLoading = false;
        })
        .then(() => {
          if (!this.limitError) window.location.href = '/settings/user';
        });
    },
    validateEmail(email) {
      const re =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
    },
    clearError() {
      this.showEmailError = false;
      this.errorMessage = '';
    },
  },
};
</script>

<style lang="scss" scoped></style>

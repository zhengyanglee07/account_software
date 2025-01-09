<template>
  <BasePageLayout
    page-name="Profile Settings"
    is-setting
  >
    <BaseSettingLayout title="Personal Profile">
      <template #description>
        Basic profile information
      </template>

      <template #content>
        <BaseFormGroup
          label="First Name"
          col="md-6"
          required
          :error-message="firstNameError ? 'First name is required' : ''"
        >
          <BaseFormInput
            v-model="firstName"
            type="text"
            @input="firstNameError = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Last Name"
          col="md-6"
          required
          :error-message="lastNameError ? 'Last name is required' : ''"
        >
          <BaseFormInput
            v-model="lastName"
            type="text"
            @input="lastNameError = false"
          />
        </BaseFormGroup>

        <BaseFormGroup label="Email Address">
          <BaseFormInput
            type="email"
            :model-value="emailAddress"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup label="Paypal Email">
          <BaseFormInput
            v-model="paypalEmail"
            type="email"
          />
        </BaseFormGroup>

        <BaseFormGroup label="Full Address">
          <BaseFormInput
            v-model="address"
            type="text"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="City"
          col="md-6"
        >
          <BaseFormInput
            v-model="city"
            type="text"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Country"
          col="md-6"
        >
          <BaseFormCountrySelect
            id="country"
            v-model="country"
            type="text"
            :country="country"
            top-country="Malaysia"
            country-name
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="State"
          col="md-6"
        >
          <BaseFormRegionSelect
            id="state"
            v-model="state"
            type="text"
            :country="country"
            :region="state"
            placeholder="Select State"
            country-name
            region-name
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Postcode"
          col="md-6"
        >
          <BaseFormInput
            v-model="zipcode"
            type="text"
          />
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton
          class="me-2 mb-2"
          :disabled="savingProfile"
          @click="saveProfile"
        >
          <span v-if="!savingProfile">Save</span>
          <span v-else>
            <i class="fas fa-circle-notch fa-spin pe-0" />
          </span>
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Reset Password">
      <template #content>
        <BaseFormGroup
          label="Current Password"
          required
        >
          <BaseFormInput
            v-model="currentPassword"
            type="password"
            @input="clearPasswordErrs"
          />
          <template #error-message>
            {{ currentPasswordErr }}
          </template>
        </BaseFormGroup>

        <BaseFormGroup
          label="New Password"
          required
        >
          <BaseFormInput
            v-model="newPassword"
            type="password"
            @input="clearPasswordErrs"
          />
          <template #error-message>
            {{ newPasswordErr }}
          </template>
        </BaseFormGroup>

        <BaseFormGroup
          label="Confirm New Password"
          required
        >
          <BaseFormInput
            v-model="confirmNewPassword"
            type="password"
            @input="clearPasswordErrs"
          />
          <template #error-message>
            {{ confirmNewPasswordErr }}
          </template>
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton
          class="me-2 mb-2"
          :disabled="savingPassword"
          @click="savePassword"
        >
          <span v-if="!savingPassword">Save</span>
          <span v-else>
            <i class="fas fa-circle-notch fa-spin pe-0" />
          </span>
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
/* eslint-disable camelcase */
import AffiliateLayout from '@shared/layout/AffiliateLayout.vue';

export default {
  layout: AffiliateLayout,
  props: {
    member: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      firstName: '',
      lastName: '',
      emailAddress: '',
      paypalEmail: '',
      address: '',
      city: '',
      country: '',
      state: '',
      zipcode: '',

      currentPassword: '',
      newPassword: '',
      confirmNewPassword: '',

      currentPasswordErr: '',
      newPasswordErr: '',
      confirmNewPasswordErr: '',

      savingProfile: false,
      savingPassword: false,

      firstNameError: false,
      lastNameError: false,
    };
  },
  mounted() {
    this.firstName = this.member.first_name;
    this.lastName = this.member.last_name;
    this.emailAddress = this.member.email;
    this.paypalEmail = this.member.paypal_email;
    this.address = this.member.address;
    this.city = this.member.city;
    this.country = this.member.country;
    this.state = this.member.state;
    this.zipcode = this.member.zipcode;
  },
  methods: {
    async saveProfile() {
      this.savingProfile = true;
      this.firstNameError = this.firstName.trim() === '';
      this.lastNameError = this.lastName.trim() === '';
      if (this.firstNameError || this.lastNameError) {
        this.savingProfile = false;
        this.$toast.warning('Warning', 'Please fill in all required fields');
        return;
      }

      try {
        await this.$axios.put('/affiliates/profile', {
          id: this.member.id,
          first_name: this.firstName,
          last_name: this.lastName,
          paypal_email: this.paypalEmail,
          address: this.address,
          city: this.city,
          country: this.country,
          state: this.state,
          zipcode: this.zipcode,
        });
        this.$toast.success('Success', 'Successfully update profile');
        this.$inertia.visit('/affiliates/profile', { replace: true });
      } catch (err) {
        this.$toast.error('Error', 'Failed to save profile');
      } finally {
        this.savingProfile = false;
      }
    },

    async savePassword() {
      const current_password = this.currentPassword;
      const password = this.newPassword;
      const password_confirmation = this.confirmNewPassword;
      const requiredErrMsg = 'Field is required';

      if (!current_password) {
        this.currentPasswordErr = requiredErrMsg;
        return;
      }

      if (!password) {
        this.newPasswordErr = requiredErrMsg;
        return;
      }

      if (!password_confirmation) {
        this.confirmNewPasswordErr = requiredErrMsg;
        return;
      }

      if (password !== password_confirmation) {
        this.confirmNewPasswordErr = 'Password mismatch';
        return;
      }

      this.savingPassword = true;

      try {
        await this.$axios.put('/affiliates/profile/pw', {
          current_password,
          password,
          password_confirmation,
        });

        this.$toast.success('Success', 'Successfully updated password');
      } catch (err) {
        const { errors } = err.response.data;

        if (errors.current_password) {
          const [cp] = errors.current_password;
          this.currentPasswordErr = cp;
          return;
        }

        const [pw] = errors.password;
        this.newPasswordErr = pw;
        this.confirmNewPasswordErr = pw;
      } finally {
        this.savingPassword = false;
      }
    },

    clearPasswordErrs() {
      this.currentPasswordErr = '';
      this.newPasswordErr = '';
      this.confirmNewPasswordErr = '';
    },
  },
};
</script>

<style scoped lang="scss"></style>

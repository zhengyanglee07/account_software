<template>
  <BasePageLayout
    page-name="Affiliate Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Affiliate Member System">
      <template #description>
        Change the default behaviour of your affiliate member system.
      </template>

      <template #content>
        <BaseFormGroup label="Automatically approve affiliate">
          <BaseFormSwitch v-model="localSettings.auto_approve_affiliate" />
        </BaseFormGroup>
        <BaseFormGroup label="Automatically approve commission">
          <BaseFormSwitch v-model="localSettings.auto_approve_commission" />
        </BaseFormGroup>
        <BaseFormGroup
          v-if="localSettings.auto_approve_commission"
          label="Auto commission approval period"
          :error-message="
            v$.localSettings.auto_approval_period.$invalid
              ? 'Value must be at least 1'
              : ''
          "
        >
          <BaseFormInput
            v-model="localSettings.auto_approval_period"
            type="number"
            min="1"
            placeholder="Enter a number"
          >
            <template #append>
              day(s)
            </template>
          </BaseFormInput>
        </BaseFormGroup>
        <BaseFormGroup
          label="Automatic add as an affiliate when someone signs up a customer account"
        >
          <BaseFormSwitch
            v-model="localSettings.auto_create_account_on_customer_sign_up"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Minimum payout"
          :error-message="
            v$.localSettings.minimal_payout.$invalid
              ? 'Value must be at least 1'
              : ''
          "
        >
          <BaseFormInput
            v-model="localSettings.minimal_payout"
            type="number"
            min="1"
            placeholder="Enter a number"
          >
            <template #prepend>
              {{ currency === 'MYR' ? 'RM' : currency }}
            </template>
          </BaseFormInput>
        </BaseFormGroup>
        <BaseFormGroup label="Lifetime commission">
          <BaseFormSwitch v-model="localSettings.enable_lifetime_commission" />
        </BaseFormGroup>
        <BaseFormGroup
          label="Cookie expiration time"
          :error-message="
            v$.localSettings.cookie_expiration_time.$invalid
              ? 'Value must be at least 1'
              : ''
          "
        >
          <BaseFormInput
            v-model="localSettings.cookie_expiration_time"
            type="number"
            min="1"
            placeholder="Enter a number"
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton
          :disabled="savingSettings"
          @click="saveSettings"
        >
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import { minValue, required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'AffiliateMemberSettings',

  props: {
    settings: {
      type: Object,
      default: () => ({}),
    },
    currency: {
      type: String,
      required: true,
    },
  },

  setup() {
    return { v$: useVuelidate() };
  },

  data() {
    return {
      localSettings: {
        auto_approve_affiliate: true,
        auto_approve_commission: true,
        auto_approval_period: 30,
        auto_create_account_on_customer_sign_up: true,
        minimal_payout: 100,
        enable_lifetime_commission: false,
        cookie_expiration_time: 90,
      },

      savingSettings: false,
    };
  },
  validations: {
    localSettings: {
      auto_approval_period: {
        minValue: minValue(1),
        required,
      },
      minimal_payout: {
        minValue: minValue(1),
        required,
      },
      cookie_expiration_time: {
        minValue: minValue(1),
        required,
      },
    },
  },
  mounted() {
    this.localSettings = {
      auto_approve_affiliate: Boolean(this.settings.auto_approve_affiliate),
      auto_approve_commission: Boolean(this.settings.auto_approve_commission),
      auto_create_account_on_customer_sign_up: Boolean(
        this.settings.auto_create_account_on_customer_sign_up
      ),
      auto_approval_period: parseFloat(this.settings.auto_approval_period),
      minimal_payout: parseFloat(this.settings.minimal_payout),
      enable_lifetime_commission: Boolean(
        this.settings.enable_lifetime_commission
      ),
      cookie_expiration_time: parseFloat(this.settings.cookie_expiration_time),
    };
  },
  methods: {
    async saveSettings() {
      if (this.v$.$invalid) {
        this.$toast.warning(
          'Warninig',
          'Please fix your errors before saving.'
        );
        return;
      }

      this.savingSettings = true;

      try {
        await axios.put('/settings/affiliate', {
          id: this.settings.id,
          ...this.localSettings,
        });

        this.$toast.success('Success', 'Successfully saved affiliate settings');
      } catch (err) {
        if (err.response.status !== 422) {
          this.$toast.error('Error', 'Failed to save settings');
          return;
        }
        console.log(err.response);
        const { data } = err.response;
        const { errors } = data;
        Object.keys(errors).forEach((field) => {
          errors[field].forEach((errMsg) => {
            this.$toast.warning('Warning', errMsg);
          });
        });
      } finally {
        this.savingSettings = false;
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

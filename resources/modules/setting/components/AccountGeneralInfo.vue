<template>
  <div class="w-100">
    <div class="w-100">
      <!-- <div class="setting-page__content-oneline">
        <SettingLabel title="Account Name (For Internal Use)" />
        <input
          id="accountName"
          v-model="localAccountName"
          type="text"
          class="setting-page__form-input form-control p-two"
          name="accountName"
          placeholder="Enter Account Name"
          required
        >

        <span
          v-if="errAccount"
          class="error-message error-font-size"
        >
          Please fill in the account name
        </span>
      </div> -->

      <BaseFormGroup label="Timezone">
        <BaseMultiSelect
          v-model="localTimezone"
          :options="timezones"
        />

        <span
          v-if="errTimezone"
          class="error-message error-font-size"
        >
          Please select a timezone
        </span>
      </BaseFormGroup>
    </div>

    <div class="col-md-8 w-100">
      <div class="d-flex justify-content-end pt-0">
        <BaseButton @click="saveAccount()">
          Save
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import { timezones } from '@shared/lib/timezone.js';

export default {
  name: 'AccountGeneralInfo',
  props: {
    accountName: String,
    currency: String,
    timezone: String,
  },
  data() {
    return {
      value: '',
      timezones,
      localAccountName: '',
      localCurrency: '',
      localTimezone: '',
      errAccount: false,
      errCurrency: false,
      errTimezone: false,
    };
  },
  mounted() {
    // save props to local state for v-model bindings
    // since we can't change props directly
    this.localAccountName = this.accountName;
    this.localCurrency = this.currency;
    this.localTimezone = this.timezone;
  },
  methods: {
    saveAccount() {
      const timezone = this.localTimezone;

      this.errTimezone = !timezone;

      if (!timezone) return;

      this.$axios
        .put('/account/timezone/update', {
          timezone,
        })
        .then(({ data }) => {
          this.$toast.success('Success', data.message);
        })
        .catch((err) => console.error(err));
    },
  },
};
</script>

<style scoped lang="scss"></style>

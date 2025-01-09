<template>
  <BasePageLayout is-setting>
    <BaseSettingLayout title="Tracking Code">
      <template #description>
        Google Analytics allows your to track amount of traffic your store gets,
        where the traffic comes from, demographic information of your visitors,
        and more.
        <BaseButton
          type="link"
          href="https://analytics.google.com/analytics/web"
          is-open-in-new-tab
          rel="noopener noreferrer"
        >
          Login to your Google Analytics account here.
        </BaseButton>
      </template>
      <template #content>
        <BaseFormGroup label="Head Tracking Code">
          <BaseFormTextarea
            id="tracking-header"
            v-model="preferences.ga_header"
            rows="5"
            placeholder="Paste your code from Google Analytics here"
          />
        </BaseFormGroup>
        <BaseFormGroup label="Body Tracking Code">
          <BaseFormTextarea
            id="tracking-header"
            v-model="preferences.ga_bodytop"
            rows="5"
            placeholder="Paste your code from Google Analytics here"
          />
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Affiliate Badge">
      <template
        v-if="!canDisableBadges"
        #description
      >
        Upgrade to Triangle plan to disable affiliate badge.
        <BaseButton
          type="link"
          href="/subscription/plan/upgrade"
          is-open-in-new-tab
        >
          Upgrade Now
        </BaseButton>
      </template>
      <template #content>
        <BaseFormGroup label="Affiliate Badge">
          <BaseFormCheckBox
            id="show-affiliate"
            v-model="preferences.has_affiliate_badge"
            :value="1"
            :disabled="!canDisableBadges"
          >
            Show affiliate badge at the bottom right of online store and mini
            store pages
          </BaseFormCheckBox>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <template #footer>
      <BaseButton
        :disabled="loading"
        @click="updatePreferences"
      >
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { usePage } from '@inertiajs/vue3';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

const props = defineProps({
  dbPreferences: {
    type: Object,
    default: () => {},
  },
  timeZone: {
    type: String,
    default: 'Asia/Kuala_Lumpur',
  },
});

const loading = ref(false);
const preferences = ref({ ...props.dbPreferences });

const canDisableBadges = computed(() => {
  return !usePage().props.permissionData.featureForPaidPlan.includes(
    'can-disabled-badge'
  );
});

const $toast = inject('$toast');

const updatePreferences = () => {
  loading.value = true;
  onlineStoreAPI
    .updatePreferences({
      ...preferences.value,
      canDisableBadges: canDisableBadges.value,
    })
    .then((response) => {
      const { status, message, updatedPreferences } = response.data;
      preferences.value = updatedPreferences;
      $toast.success(status, message);
    })
    .catch((error) => {
      $toast.error(
        'Failed',
        'Unexpected Error Occured. Please contact our support for help'
      );
      throw new Error(error);
    })
    .finally(() => {
      loading.value = false;
    });
};
</script>

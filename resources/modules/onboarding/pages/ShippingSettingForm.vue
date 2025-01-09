<template>
  <div>
    <ShippingTypeSettings
      v-if="type === 'scheduled-delivery'"
      type="delivery"
      :time-zone="timeZone"
      onboarding
    />

    <ShippingTypeSettings
      v-if="type === 'pickup'"
      type="pickup"
      :time-zone="timeZone"
      :location="location"
      onboarding
    />

    <AddNewShipping
      v-if="type === 'self-delivery'"
      :submit-url="submitUrl(type)"
      onboarding
    />

    <EasyParcelSettings
      v-if="type === 'easyparcel'"
      :submit-url="submitUrl(type)"
      class="w-100"
      onboarding
    />
    <LalamoveSettings
      v-if="type === 'lalamove'"
      :submit-url="submitUrl(type)"
      onboarding
    />
    <DelyvaSettings
      v-if="type === 'delyva'"
      :submit-url="submitUrl(type)"
      onboarding
    />

    <div class="w-100 d-flex justify-content-between">
      <BaseButton
        type="link"
        :href="redirectionLink[type]?.backUrl"
      >
        <i class="me-2 fa-solid fa-arrow-left" />
        Back
      </BaseButton>
      <div>
        <BaseButton
          v-if="redirectionLink[type]?.skipUrl"
          type="link"
          :href="redirectionLink[type]?.skipUrl"
          class="me-3"
        >
          Skip
          <i class="ms-2 fa-solid fa-arrow-right" />
        </BaseButton>
        <BaseButton @click="submit">
          Submit
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import ShippingTypeSettings from '@setting/components/ShippingTypeSettings.vue';
import AddNewShipping from '@setting/pages/AddNewShipping.vue';
import EasyParcelSettings from '@app/pages/EasyParcelSettings.vue';
import LalamoveSettings from '@app/pages/LalamoveSettings.vue';
import DelyvaSettings from '@app/pages/DelyvaPage.vue';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import eventBus from '@services/eventBus.js';

export default {
  components: {
    AddNewShipping,
    EasyParcelSettings,
    LalamoveSettings,
    DelyvaSettings,
    ShippingTypeSettings,
  },
  layout: OnboardingLayout,
  props: {
    type: { type: String, default: '' },
    location: { type: Object, default: () => {} },
    shippingZone: { type: Array, default: () => [] },
    timeZone: { type: String, default: 'Asia/Singapore' },
  },
  computed: {
    redirectionLink() {
      const isOSOnboarding =
        localStorage?.selectedSalesChannel === 'online-store';
      const shippingUrl = {
        backUrl: '/onboarding/shippings',
        skipUrl: isOSOnboarding
          ? null
          : '/onboarding/shipping/scheduled-delivery',
        submitUrl: isOSOnboarding
          ? '/onboarding/save'
          : '/onboarding/shipping/scheduled-delivery',
      };
      return {
        'scheduled-delivery': {
          backUrl: '/onboarding/shippings',
          skipUrl: '/onboarding/shipping/pickup',
        },
        pickup: {
          backUrl: '/onboarding/shipping/scheduled-delivery',
          skipUrl: null,
        },
        'self-delivery': shippingUrl,
        easyparcel: shippingUrl,
        lalamove: shippingUrl,
        delyva: shippingUrl,
      };
    },
  },
  methods: {
    ...mapActions('settings', ['initializePickupDelivery']),
    submit() {
      eventBus.$emit(`${this.type}-submit`);
    },
    submitUrl(type) {
      return this.redirectionLink[type]?.submitUrl;
    },
  },
  mounted() {
    if (['scheduled-delivery', 'pickup'].includes(this.type))
      this.initializePickupDelivery();
  },
};
</script>

<style lang="scss" scoped>
.setting-page {
  justify-content: center !important;
  align-items: center !important;
}

:deep(.card-body) {
  margin-left: 1rem !important;
}

:deep(.form-group) {
  padding: 0px !important;
}
</style>

<template>
  <SubscriptionUpsellBanner subscription-page />
  <div class="subscripton-container container">
    <div
      v-if="props.type !== 'create'"
      :class="`text-${permissionData.showLimitModal ? 'end' : 'start'}`"
    >
      <BaseButton
        v-if="!permissionData.showLimitModal && !isAccountDeactive"
        type="link"
        href="/billing/setting"
        class="back-button"
      >
        <i class="me-2 fas fa-chevron-left" />
        Back To Subscription
      </BaseButton>

      <form
        v-else
        id="logout-form"
        :action="props.logoutLink"
        method="POST"
      >
        <input
          type="hidden"
          name="_token"
          :value="csrf"
        >
        <BaseButton
          type="link"
          :href="props.logoutLink"
          is-submit
          @click="localStorage?.clear()"
        >
          Logout
        </BaseButton>
      </form>
    </div>

    <img
      src="@shared/assets/media/hypershapes-logo.png"
      class="hypershapes_logo"
    >

    <SubscriptionSignup
      :current-plan="props.currentSubscriptionPlan"
      :subscription-plan="props.allSubscriptionPlan"
      :type="props.type"
      :production="props.env"
      :credit-card="creditCard"
      :promo-remaining-hours="props.promoRemainingHours"
      :is-exceed-limit="permissionData.showLimitModal"
    />
  </div>

  <LimitModal :permission-data="permissionData" />
</template>

<script>
import Layout from '@subscription/layout/SubscriptionLayout.vue';
import SubscriptionUpsellBanner from '@subscription/components/SubscriptionUpsellBanner.vue';
import SubscriptionSignup from '@subscription/components/SubscriptionSignup.vue';
import { reactive, computed, useAttrs } from 'vue';

export default {
  layout: Layout,
};
</script>

<script setup>
const attrs = useAttrs();

const props = defineProps({
  logoutLink: { type: String, default: null },
  currentSubscriptionPlan: { type: Object, default: () => {} },
  allSubscriptionPlan: { type: Array, default: () => [] },
  type: { type: String, default: 'create' },
  env: { type: String, default: 'production' },
  creditCard: { type: Object, default: () => {} },
  promoRemainingHours: { type: Number, default: null },
});

const state = reactive({
  csrf: document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content'),
});

const permissionData = computed(() => attrs.permissionData);

const head = document.getElementsByTagName('HEAD')[0];
const link = document.createElement('link');
link.rel = 'stylesheet';
link.href = 'https://fonts.googleapis.com/css?family=Roboto&display=swap';
head.appendChild(link);

const isAccountDeactive = computed(() =>
  ['canceled', 'incomplete', 'incomplete_expired'].includes(
    attrs.account.subscription_status
  )
);
</script>

<style lang="scss" scoped>
.hypershapes_logo {
  width: 26%;
}

@media screen and (max-width: 415px) {
  .hypershapes_logo {
    width: 70%;
  }
  .subscripton-container {
    padding: 0;
  }
  .back-button {
    margin-left: 2.5rem;
    margin-bottom: 1.25rem;
  }
}

.subscripton-container {
  padding-top: 50px;
  text-align: center;
}
</style>

<template>
  <div
    class="d-flex justify-content-center align-items-center flex-column full-height"
  >
    <div class="h2 font-weight-bold mb-4 mobile-title">
      {{ `Your ${selectedSaleChannel} is live now` }}
    </div>
    <img
      class="mobile-image"
      src="https://media.hypershapes.com/images/onboarding/undraw_in_sync_re_jlqd.svg"
      alt="Mini Store is Live Now"
      width="450"
    >
    <div>
      <div class="view-store-button">
        <BaseButton
          :href="`https://${currentDomain.domain}`"
          is-open-in-new-tab
        >
          View Store
        </BaseButton>

        <BaseButton
          type="link"
          class="mt-6"
          href="/dashboard"
        >
          Enter dashboard
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';

export default {
  layout: OnboardingLayout,
  props: ['domain'],
  data() {
    return {
      currentDomain: {},
      selectedSaleChannel: 'mini store',
    };
  },

  mounted() {
    this.currentDomain = this.domain;
    const params = new URL(document.location).searchParams;
    const saleChannel = params.get('saleChannel');
    if (saleChannel)
      this.selectedSaleChannel =
        saleChannel === 'online-store' ? 'online store' : 'mini store';
  },
};
</script>

<style scoped lang="scss">
body {
  height: 100%;
  margin: 0;
}

.full-height {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  overflow: auto;
  background: white;
}

.view-store-button {
  padding: 50px 0;
  margin-top: auto;
  display: grid;
  text-align: center;
}

@media (max-width: 415px) {
  .mobile-title {
    font-size: 28px;
    max-width: 90%;
    text-align: center;
  }
  .mobile-image {
    width: 80%;
  }
  .mobile-button {
    font-size: 14px !important;
  }
}

.primary-small-square-button {
  background-color: $h-primary !important;
  width: 150px;
  height: 35px;
  margin-bottom: 15px;
}

.enter-dashboard:hover {
  text-decoration: underline;
}

a:link {
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
</style>

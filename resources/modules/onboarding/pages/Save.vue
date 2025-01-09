<template>
  <div
    class="d-flex justify-content-center align-items-center flex-column full-height"
  >
    <img
      class="image"
      src="https://media.hypershapes.com/images/onboarding/ezgif.com-gif-maker.gif"
      alt="create record"
      width="400"
    >
    <div style="margin-top: 15px">
      <div
        id="progress"
        :style="{ '--attrsVal': `${(100 / 7) * completedApiCount}%` }"
      />
    </div>
    <div class="h-five mt-4 mobile-progress">
      {{
        redirecting
          ? 'Your records are saved. Redirecting...'
          : `1 out of 1 completed`
      }}
    </div>
    <div
      class="mt-2 mobile-text"
      style="font-size: 11px; color: #808285a6"
    >
      Please do not close this tab or browser
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';

export default {
  name: 'OnboardingSave',
  layout: OnboardingLayout,

  data() {
    return {
      completedApiCount: 0,
      redirecting: false,
      saleChannel: 'mini-store',
    };
  },
  mounted() {
    this.saleChannel = Cookies.get('saleChannel') ?? 'mini-store';
    this.storeExpressSetup();
    this.triggerOnDemandSSL();
  },
  methods: {
    resolved(result) {
      console.log('Resolved');
      this.completedApiCount += 1;
    },
    rejected(result) {
      console.error(result);
    },
    storeExpressSetup() {
      let onlineStoreThemes;
      let onboardingSetup;
      let paymentSettings;
      let shippingSettings;
      let deliverySettings;
      let pickupSettings;
      const { selectedSalesChannel } = localStorage;

      if (localStorage.onlineStoreThemes)
        onlineStoreThemes = JSON.parse(localStorage.onlineStoreThemes);
      if (localStorage.onboardingSetup)
        onboardingSetup = JSON.parse(localStorage.onboardingSetup);
      let promise1 = null;
      if (selectedSalesChannel === 'online-store') {
        promise1 = Promise.resolve(
          localStorage.onlineStoreThemes
            ? axios.post(`/online-store/createNewTheme`, {
                newObject: onlineStoreThemes.newObject,
              })
            : true
        ).then(this.resolved, this.rejected);
      } else {
        promise1 = Promise.resolve(
          localStorage.onboardingSetup
            ? axios.post(`/mini-store/save-edit`, {
                name: onboardingSetup.name,
                description: onboardingSetup.description,
                image: onboardingSetup.image,
                isEnabledDeliveryChecker: true,
              })
            : true
        ).then(this.resolved, this.rejected);
      }
      const promise2 = Promise.resolve(
        localStorage.onboardingProduct
          ? axios.post('/addProduct', {
              type: 'new',
              details: JSON.parse(localStorage.onboardingProduct),
            })
          : true
      ).then(this.resolved, this.rejected);
      if (localStorage.paymentSettings)
        paymentSettings = JSON.parse(localStorage.paymentSettings);
      const promise3 = Promise.resolve(
        localStorage.paymentSettings
          ? axios.post('/payment/settings/save', {
              ...paymentSettings,
              enable_fpx: paymentSettings.enable_fpx ?? 0,
            })
          : true
      ).then(this.resolved, this.rejected);
      if (localStorage.shippingSettings)
        shippingSettings = JSON.parse(localStorage.shippingSettings);
      const shippingAPI =
        shippingSettings?.link === 'delyva'
          ? '/delyva/save-edit'
          : shippingSettings?.link;
      const promise4 = Promise.resolve(
        localStorage.shippingSettings
          ? axios.post(shippingAPI, shippingSettings)
          : true
      ).then(this.resolved, this.rejected);
      if (localStorage.deliverySettings)
        deliverySettings = JSON.parse(localStorage.deliverySettings);
      const promise5 = Promise.resolve(
        localStorage.deliverySettings
          ? axios.post('/shipping/setting/deliveryhour', {
              ...deliverySettings,
            })
          : true
      ).then(this.resolved, this.rejected);
      if (localStorage.pickupSettings)
        pickupSettings = JSON.parse(localStorage.pickupSettings);
      const promise6 = Promise.resolve(
        localStorage.pickupSettings
          ? axios.post('/shipping/setting/storepickup', {
              ...pickupSettings,
            })
          : true
      ).then(this.resolved, this.rejected);
      const promise7 = Promise.resolve(axios.post('/onboarding/boarded')).then(
        this.resolved,
        this.rejected
      );
      Promise.allSettled([
        promise1,
        promise2,
        promise3,
        promise4,
        promise5,
        promise6,
        promise7,
      ])
        .then(() => {
          localStorage.clear();
          this.redirecting = true;
          const toDeleteCookiesData = ['onboarding', 'saleChannel'];
          toDeleteCookiesData.forEach((data) => {
            Cookies.remove(data);
          });
        })
        .finally(() => {
          setTimeout(() => {
            this.$inertia.visit('/onboarding/welcome');
          }, 3000);
        });
    },

    async triggerOnDemandSSL() {
      this.$axios.get('/domain/on-demand/ssl').catch((err) => {
        throw new Error(err);
      });
    },
  },
};
</script>

<style lang="scss" scoped>
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

.image {
  margin-bottom: 20px;
  margin-top: -70px;

  @media (max-width: 415px) {
    width: 100%;
  }
}

#progress {
  background: #e0e2e5c7;
  border-radius: 13px;
  height: 10px;
  width: 300px;
}

#progress:after {
  content: '';
  display: block;
  background: #5c36ff;
  width: var(--attrsVal);
  height: 100%;
  border-radius: 9px;
}

@media (max-width: 415px) {
  .mobile-progress {
    font-size: 18px !important;
  }
  .mobile-text {
    font-size: 14px !important;
  }
}
</style>

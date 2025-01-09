<template>
  <div class="settings_whole">
    <div class="settings_container">
      <div class="row">
        <div
          v-for="(setting, index) in allSettings"
          :key="index"
          class="col-md-3"
        >
          <Link :href="setting.link">
            <BaseCard
              no-body-margin
              class="min-h-125px mt-0 mb-6"
            >
              <div
                class="d-flex justify-content-start align-items-center w-100 px-5 ps-10"
              >
                <img
                  class="w-25px me-6"
                  :src="
                    settingIcons[
                      `/resources/modules/setting/assets/media/${setting.iconName}.svg`
                    ]?.default
                  "
                  :alt="setting.title"
                  :style="
                    setting.iconStyle
                      ? setting.iconStyle
                      : 'transform: scale(4.5)'
                  "
                >
                <p
                  style="width: calc(100% - 30px)"
                  class="text-start m-0 fs-4 text-gray-800"
                >
                  {{ setting.title }} Settings
                </p>
              </div>
            </BaseCard>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    permission: { type: Object, default: () => {} },
    env: { type: String, default: 'production' },
  },
  data() {
    return {
      settingIcons: [],
      allSettings: [
        {
          title: 'Account',
          link: '/account/setting',
          iconName: 'account-settings',
        },
        {
          title: 'Domain',
          link: '/domain/settings',
          iconName: 'domain-settings',
        },
        {
          title: 'Shipping',
          link: '/shipping/settings',
          iconName: 'shipping-settings',
        },
        {
          title: 'Payment',
          link: '/payment/settings',
          iconName: 'payment-settings',
        },
        {
          title: 'Legal',
          link: '/settings/legal',
          iconName: 'legal-settings',
          iconStyle: 'transform: scale(1.8)',
        },
        {
          title: 'Checkout',
          link: '/settings/checkout',
          iconName: 'checkout-settings',
          iconStyle: 'transform: scale(2)',
        },
        {
          title: 'Location',
          link: '/location/settings',
          iconName: 'location-settings',
        },
        {
          title: 'Email',
          link: '/emails/settings',
          iconName: 'email-settings',
        },
        {
          title: 'Currency',
          link: '/currency/settings',
          iconName: 'currency-settings',
        },
        {
          title: 'Tax',
          link: '/tax/settings',
          iconName: 'tax-settings',
        },
        {
          title: 'Notification',
          link: '/settings/notification',
          iconName: 'notification',
          iconStyle: 'transform: scale(2)',
        },
        // {
        //   title: 'Social Proof',
        //   link: '/settings/social-proof',
        //   iconName: 'notification-setting',
        // },
        // {
        //   title: 'Product Review',
        //   link: '/settings/product-review',
        //   iconName: 'product-review-settings',
        //   iconStyle: 'transform: scale(2)',
        // },
        {
          title: 'Affiliate',
          link: '/settings/affiliate',
          iconName: 'product-subscription-settings',
          iconStyle: 'transform: scale(4.5)',
        },
        // {
        //   title: 'Theme',
        //   link: '/settings/theme',
        //   iconName: 'checkout-settings',
        //   iconStyle: 'transform: scale(1.1);',
        // },
        // {
        //   title: 'Integration',
        //   link: '/integration/setting',
        //   iconName: 'integration-settings',
        //   iconStyle: 'transform: scale(0.8)',
        // },
      ],
    };
  },
  mounted() {
    const invalidSettings = this.env === 'production' ? ['Integration'] : [];

    if (!this.permission.assignRole) invalidSettings.push('User');
    if (!this.permission.editPayment) invalidSettings.push('Payment');
    this.allSettings = this.allSettings.filter(
      (e) => !invalidSettings.includes(e.title)
    );

    this.settingIcons = import.meta.glob(
      '/resources/modules/setting/assets/media/*.svg',
      { eager: true }
    );
  },
};
</script>

<style></style>

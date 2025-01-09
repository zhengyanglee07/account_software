<template>
  <AddNewPaymentMethod
    :payment-method="paymentMethod"
    :type="type"
    :return-url="returnUrl"
    :stripe-client-id="stripeClientId"
    :account-name="accountName"
    onboarding
  />
</template>

<script>
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import AddNewPaymentMethod from '@setting/pages/AddNewPaymentMethod.vue';

export default {
  components: {
    AddNewPaymentMethod,
  },
  layout: OnboardingLayout,
  props: {
    paymentMethod: { type: Object, default: () => {} },
    type: { type: String, default: '' },
    returnUrl: { type: Object, default: () => {} },
    stripeClientId: { type: String, default: '' },
    accountName: { type: String, default: '' },
  },
};
</script>

<style></style>

<template>
  <OnboardingFormLayout
    title="Step 3: Choose a Payment Method"
    back-url="/onboarding/product/create"
    skip-url="/onboarding/shippings"
  >
    <template #description>
      Your customers will pay for their order via the payment method you choose
      here
    </template>

    <template #content>
      <div class="row mt-10">
        <div
          v-for="paymentDetail in paymentDetails"
          :key="paymentDetail.type"
          class="col-lg-6"
        >
          <OnboardingSelectionCard
            :is-active="selectedPaymentMethods === paymentDetail.type"
            @click="selectedPaymentMethods = paymentDetail.type"
          >
            <template #image>
              <img
                alt="img"
                :src="paymentDetail.image"
                class="image-style"
                style="width: 110px"
              >
            </template>
            <template #title>
              {{ paymentDetail.title }}
            </template>
            <template #description>
              {{ paymentDetail.description }}
            </template>
          </OnboardingSelectionCard>
        </div>
      </div>
    </template>

    <template #submit-button>
      <BaseButton @click="redirect()">
        Continue <i class="ms-2 fa-solid fa-arrow-right-long" />
      </BaseButton>
    </template>
  </OnboardingFormLayout>
</template>

<script>
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import stripeIcon from '@setting/assets/media/stripe-logo.jpg';
import manualPaymentIcon from '@setting/assets/media/manual-payment-icon.jpg';
import OnboardingSelectionCard from '@onboarding/components/OnboardingSelectionCard.vue';

export default {
  components: { OnboardingSelectionCard },
  layout: OnboardingLayout,
  props: {
    paymentMethods: {
      type: Array,
      required: true,
    },
    env: {
      type: String,
      default: 'production',
    },
  },

  data() {
    return {
      selectedPaymentMethods: 'stripe',
      paymentDetails: [
        {
          title: 'Stripe',
          type: 'stripe',
          link: '/onboarding/payment/setup/new/stripe',
          description:
            'To accept payment via Credit Card, Debit Card or FPX (Malaysia only, need extra setup)',
          image: stripeIcon,
        },
        {
          title: 'Manual Payment',
          type: 'manual payment',
          link: '/onboarding/payment/setup/new/manual payment',
          description:
            'To accept the payment outside of your store, such as cash on delivery, bank transfer, e-wallet and etc',
          image: manualPaymentIcon,
        },
      ],
    };
  },

  methods: {
    redirect() {
      const selectedMethod = this.paymentDetails.find(
        (e) => e.type === this.selectedPaymentMethods
      );
      this.$inertia.visit(selectedMethod.link);
    },
  },
};
</script>

<style lang="scss" scoped>
.img-container {
  width: 100px;
  height: 100px;
  object-fit: cover;
}

.main-title {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 100%;
  margin-bottom: 20px;

  &__title {
    height: 50px;
  }
}
</style>

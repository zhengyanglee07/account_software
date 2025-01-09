<template>
  <div class="sub-title-container__box px-0">
    <h4
      class="sub-title-container__title h-two"
      style="font-size: 19.5px !important; text-align: left"
    >
      Step 4: Choose a Way to Deliver the Order
    </h4>
    <p
      class="sub-title-container__sub-title p-two p-0"
      style="
        font-size: 13.975px;
        color: #a1a5b7;
        text-align: left;
        font-weight: 500;
      "
    >
      You will deliver the items purchased by your customer via the method you
      choose here
    </p>
  </div>
  <div class="row mt-10">
    <div
      v-for="selectedShippingMethod in selectedShippingMethods"
      :key="selectedShippingMethod.type"
      class="col-lg-6"
    >
      <OnboardingSelectionCard
        :is-active="selectedShipping === selectedShippingMethod.type"
        style="height: 235px"
        @click="selectedShipping = selectedShippingMethod.type"
      >
        <template #image>
          <img
            alt="img"
            :src="selectedShippingMethod.image"
            class="image-style"
            style="width: 130px"
          >
        </template>
        <template #title>
          {{ selectedShippingMethod.title }}
        </template>
        <template #description>
          {{ selectedShippingMethod.description }}
        </template>
      </OnboardingSelectionCard>
    </div>
  </div>
  <BaseButton
    style="float: right; margin: 10px 0"
    @click="redirect()"
  >
    Continue <i class="ms-2 fa-solid fa-arrow-right-long" />
  </BaseButton>
</template>

<script>
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import delyvaIcon from '@shared/assets/media/Delyva-Logo.svg';
import selfDeliveryIcon from '@shared/assets/media/self-delivery.svg';
import OnboardingSelectionCard from '@onboarding/components/OnboardingSelectionCard.vue';

export default {
  components: { OnboardingSelectionCard },
  layout: OnboardingLayout,
  data() {
    return {
      selectedShipping: 'delyva',
      selectedShippingMethods: [
        {
          title: 'Delyva',
          type: 'delyva',
          image: delyvaIcon,
          link: '/onboarding/shipping/setup/delyva',
          description:
            'One-stop delivery solutions provider for businesses of any size. Easily fulfil orders and get any kind of delivery done with just a few clicks.',
        },
        {
          title: 'Lalamove',
          type: 'lalamove',
          image:
            'https://s3.ap-southeast-1.amazonaws.com/media.hypershapes.com/images/lalamove-app.png',
          link: '/onboarding/shipping/setup/lalamove',
          description:
            'A leading on-demand delivery technology company, connecting millions of users around the world with delivery drivers in their local area.',
        },
        {
          title: 'EasyParcel',
          type: 'easyparcel',
          image:
            'https://s3.ap-southeast-1.amazonaws.com/media.hypershapes.com/images/easyparcel-app.webp',
          link: '/onboarding/shipping/setup/easyparcel',
          description:
            'Online booking platform for courier services. Discounted delivery rate for all.',
        },

        {
          title: 'Self Delivery',
          type: 'self delivery',
          image: selfDeliveryIcon,
          link: '/onboarding/shipping/setup/self-delivery',
          description:
            'Set up the regions where you want to ship and how much you charge for shipping at checkout',
        },
      ],
    };
  },
  methods: {
    redirect() {
      const selectedMethod = this.selectedShippingMethods.find(
        (e) => e.type === this.selectedShipping
      );
      this.$inertia.visit(selectedMethod.link);
    },
  },
};
</script>

<style lang="scss" scoped>

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

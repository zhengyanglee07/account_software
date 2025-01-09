<template>
  <BaseCard :class="type === 'update' ? '' : 'bg-light'">
    <BaseFormGroup
      v-if="type !== 'update'"
      label="Name On Card"
      required
      :error-message="!cardHolderExists ? 'This field is required' : ''"
    >
      <BaseFormInput
        id="subscription-card-holder-name"
        v-model="cardHolder"
        :disabled="hasCreditCard"
        type="text"
      />
    </BaseFormGroup>

    <BaseFormGroup
      v-if="type !== 'update'"
      class="text-start"
      label="Promo Code"
    >
      <BaseFormInput
        id="subscription-promo-code"
        v-model="promoCode"
        type="text"
      >
        <template #append>
          <BaseButton
            type="link"
            size="sm"
            :disabled="promoCode === '' || isVerfyingPromoCode"
            is-submit
            @click="validatePromoCode"
          >
            Verify
          </BaseButton>
        </template>
      </BaseFormInput>
      <p
        :class="`${
          errorCode === 'Promo Code Not Available'
            ? 'text-danger'
            : 'text-success'
        }`"
      >
        {{ errorCode }}
      </p>
    </BaseFormGroup>

    <BaseFormGroup label="Card Number">
      <SubscriptionPayment
        v-if="!hasCreditCard"
        :type="type"
        :card-holder="cardHolder"
        :production="production"
        :selected-plan="selectedPlan"
        :promo-code="promoCode"
        :promo-code-type="promoCodeType"
      />
      <div
        v-else
        class="d-flex form-control justify-content-between"
        style="background-color: #eff2f5"
      >
        <span>{{ `**** **** **** ${creditCard.last_4_digit}` }}</span>
        <span>{{ creditCard.expire_date }}</span>
      </div>
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
import SubscriptionPayment from '@subscription/components/SubscriptionPayment.vue';
import subscriptionAPI from '@subscription/api/subscriptionAPI.js';
import eventBus from '@services/eventBus.js';

export default {
  name: 'SubscriptionForm',
  components: {
    SubscriptionPayment,
  },
  props: ['type', 'production', 'selectedPlan', 'creditCard'],
  data() {
    return {
      cardHolder: '',
      promoCode: '',
      errorCode: '',
      cardHolderExists: true,
      isVerfyingPromoCode: false,
    };
  },
  computed: {
    hasCreditCard() {
      return Object.keys(this.creditCard ?? {}).length > 0;
    },
    promoCodeType() {
      return this.errorCode === 'Promo Code Available' ? 'success' : 'fail';
    },
    formTitle() {
      return this.type === 'create'
        ? 'Subscription Form'
        : 'Update Credit Card Detail Form';
    },
  },
  watch: {
    cardHolder(newValue) {
      if (newValue === '') this.cardHolderExists = false;
      else this.cardHolderExists = true;
    },
  },
  mounted() {
    this.cardHolder = this.creditCard?.card_holder_name;
  },

  methods: {
    validatePromoCode() {
      this.isVerfyingPromoCode = true;
      subscriptionAPI
        .validatePromoCode(this.promoCode)
        .then((response) => {
          this.isVerfyingPromoCode = false;
          this.errorCode = response.data.status;
          eventBus.$emit(
            'subscription-coupon',
            response.data.promoCode?.coupon
          );
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isVerfyingPromoCode = false;
        });
    },
  },
};
</script>

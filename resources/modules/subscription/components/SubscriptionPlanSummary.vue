<template>
  <form
    v-if="!isPublish"
    id="subscription-form"
    @submit.prevent
  >
    <BaseModal
      no-header
      modal-id="plan-summary"
      :size="type !== 'update' ? 'xl' : 'sm'"
    >
      <h1 class="mb-3">
        {{ modalHeader }}
      </h1>

      <div
        v-if="type !== 'update'"
        class="d-flex"
      >
        <div class="nav-group nav-group-outline mx-auto my-5">
          <BaseButton
            type="active btn-active-secondary"
            color="gray-400"
            class="me-3"
            :active="!subscriptionPlanInterval"
            @click="subscriptionPlanInterval = false"
          >
            Monthly
          </BaseButton>
          <BaseButton
            type="active btn-active-secondary"
            color="gray-400"
            :active="subscriptionPlanInterval"
            @click="subscriptionPlanInterval = true"
          >
            Yearly
          </BaseButton>
        </div>
      </div>

      <div class="row">
        <div :class="summaryModalContentClass">
          <BaseButton
            v-for="subscription in selectedSubscription"
            :key="subscription.id"
            class="w-100 mb-6"
            type="outline btn-outline-dashed btn-active-primary"
            :active="subscription.id === selectedPlan.id"
            :disabled="
              $parent.getSubscriptionPriceId(subscription) <=
                currentPlan.subscription_plan_price_id && type !== 'create'
            "
            @click="$emit('selectedPlan', subscription)"
          >
            <div
              class="subscription-plan-nav d-flex align-items-center text-start mb-3 w-100"
            >
              <BaseFormGroup
                col="1"
                class="me-3"
              >
                <BaseFormRadio
                  :id="`subscription-plan-${subscription.id}`"
                  :model-value="subscription.id === selectedPlan.id"
                  class="form-check-success"
                  :value="true"
                />
              </BaseFormGroup>
              <BaseFormGroup col="6">
                <h2
                  :class="{ 'text-white': subscription.id === selectedPlan.id }"
                >
                  {{ subscription.plan }}
                  <BaseBadge
                    v-if="subscription.extra.badgeText"
                    :text="subscription.extra.badgeText"
                    type="light-success"
                  />
                </h2>
                <p
                  class="fw-bold mb-0"
                  style="font-size: 12px"
                >
                  {{ subscription.extra.description }}
                </p>
              </BaseFormGroup>
              <BaseFormGroup col="5">
                <span class="fw-bolder">$ </span>
                <span class="fs-3x fw-bolder">
                  {{
                    parseInt(
                      hasDiscount(subscription)
                        ? subscription.discountPrice
                        : subscription.price
                    )
                  }}
                </span>
                <span
                  v-if="hasDiscount(subscription)"
                  class="ms-1 text-decoration-line-through"
                >
                  {{ parseInt(subscription.price) }}
                </span>
                <span class="fs-7 opacity-50">
                  /
                  <span>
                    {{
                      subscription.subscription_plan_type === 'monthly'
                        ? 'Month'
                        : 'Year'
                    }}
                  </span>
                </span>
              </BaseFormGroup>
            </div>
          </BaseButton>
        </div>
        <div :class="summaryModalContentClass">
          <BaseDatatable
            v-if="type !== 'update'"
            title="subscription"
            class="bg-light text-start text-uppercase mb-5"
            :table-headers="tableHeaders"
            :table-datas="[selectedPlan]"
            no-header
            no-action
            no-sorting
          >
            <template #cell-total="{ row: { price } }">
              $ {{ price }}
            </template>
          </BaseDatatable>
          <SubscriptionFormDetail
            :type="type"
            :show-form="showForm"
            :credit-card="creditCard"
            :production="production"
            :selected-plan="selectedPlan"
          />
        </div>
      </div>

      <template #footer>
        <BaseButton
          id="submit-button"
          :disabled="isStripePaymentProcessing"
          is-submit
          @click="subscribe"
        >
          <span
            v-if="isStripePaymentProcessing"
          ><i
            class="fas fa-spinner fa-pulse"
          /></span>
          <span v-else>{{ buttonText }}</span>
        </BaseButton>
      </template>
    </BaseModal>
  </form>
</template>

<script>
import SubscriptionFormDetail from '@subscription/components/SubscriptionFormDetail.vue';
import subscriptionAPI from '@subscription/api/subscriptionAPI.js';
import eventBus from '@services/eventBus.js';

export default {
  name: 'SubscriptionPlanSummary',
  components: {
    SubscriptionFormDetail,
  },
  props: [
    'selectedPlan',
    'planInterval',
    'currentPlan',
    'subscriptionPlan',
    'type',
    'production',
    'creditCard',
    'isPublish',
  ],
  emits: ['changePlanInterval', 'selectedPlan'],
  data() {
    return {
      isEligibleForNewUserPromo: false,
      isStripePaymentProcessing: false,
      tableHeaders: [
        { name: 'Plan', key: 'plan' },
        { name: 'Interval', key: 'subscription_plan_type' },
        { name: 'Total', key: 'total', custom: true },
      ],
    };
  },
  computed: {
    isCreditCard() {
      return Object.keys(this.creditCard ?? {}).length > 0;
    },
    summaryModalContentClass() {
      return `col-md-${this.type !== 'update' ? '6' : '12'}`;
    },
    selectedSubscription() {
      const { permissionDetail } = this.$page.props.permissionData;
      const peopleLimit = {};
      Object.entries(permissionDetail).forEach(([plan, detail]) => {
        Object.values(detail).forEach((e) => {
          if (e.slug === 'add-people') {
            peopleLimit[plan] = e.max;
          }
        });
      });
      if (this.type === 'update') return [];
      const extraData = {
        Free: {
          badgeText: null,
          description:
            'Support entrepreneurs who wish to receive orders online',
        },
        Square: {
          badgeText: null,
          description:
            'Designed for businesses to grow online faster with all marketing features',
        },
        Triangle: {
          badgeText: 'Most Popular',
          description:
            'Best for SMEs to quickly multiply their sales with referral & affiliate marketing',
        },
        Circle: {
          badgeText: null,
          description:
            'Ideal for brands who want to scale and receive massive orders online',
        },
      };
      return this.subscriptionPlan.map((m) => ({
        ...m,
        ...m.subscription_plan_price.find(
          (e) =>
            e.subscription_plan_type ===
            (this.subscriptionPlanInterval ? 'yearly' : 'monthly')
        ),
        discountPrice: this.$parent.getSubscriptionPrice(
          m.subscription_plan_price
        ),
        extra: extraData[m.plan],
      }));
    },
    subscriptionPlanInterval: {
      get() {
        return this.planInterval;
      },
      // setter
      set(val) {
        this.$emit('changePlanInterval', val);
      },
    },
    selectedPlanId() {
      return this.selectedPlan.subscription_plan_id;
    },
    modalHeader() {
      return this.type === 'update' ? 'Change Credit Card' : 'Upgrade a Plan';
    },
    modalTitle() {
      return this.type === 'update' ? 'Credit Card' : 'Plan Summary';
    },
    buttonText() {
      return this.type === 'update' ? 'Change' : 'Upgrade Plan';
    },
    showForm() {
      return (
        (!this.isCreditCard && this.selectedPlan.plan !== 'Free') ||
        (this.selectedPlan.plan !== 'Free' &&
          this.currentPlan.status === 'canceled')
      );
    },
  },
  async mounted() {
    eventBus.$on('stripe-payment-processing', (isLoading) => {
      this.isStripePaymentProcessing = isLoading;
    });

    try {
      const res = await subscriptionAPI.getPromoEligibility(this.type);
      this.isEligibleForNewUserPromo = res.data.isEligible;
    } catch (err) {
      /* some errors here */
    }
  },
  methods: {
    hasDiscount(subscription) {
      return parseInt(subscription.price) !== subscription.discountPrice;
    },
    hideSubscriptionModal() {
      eventBus.$emit('hide-modal-plan-summary');
    },
    subscribe() {
      if (this.type === 'create') this.createSubscription();
      else this.upgradeSubscription();
    },
    createSubscription() {
      if (this.selectedPlanId === 1) {
        subscriptionAPI
          .saveSubscriptionDetail({
            subscriptionDetail: null,
            subscriptionPlanId: this.selectedPlanId,
          })
          .then((response) => {
            this.hideSubscriptionModal();
            window.location.href = '/onboarding';
          })
          .catch((error) => {
            // console.log('error',error)
            this.$toast.error('Error', 'Fail Subscribe');
          });
      }
    },
    upgradeSubscription() {
      if (this.isCreditCard) {
        subscriptionAPI
          .update({
            type: 'upgrade',
            selectedPlan: this.selectedPlan,
            promo: this.isEligibleForNewUserPromo,
          })
          .then((response) => {
            this.hideSubscriptionModal();
            this.$toast.success('Successful', 'Successful Upgraded');
            window.location.href = '/billing/setting';
          })
          .catch((error) => {
            this.$toast.error('Error', 'Fail Upgraded');
          });
      }
    },
  },
};
</script>

<style scoped>
.subscription-plan-nav .form-group {
  margin-bottom: 0 !important;
}

.subscription-plan-nav .form-group:last-child {
  text-align: end;
}
</style>

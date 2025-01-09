<template>
  <div class="w-100">
    <!-- Counter here, in days & hours -->
    <div
      v-if="promoRemainingHours"
      class="mt-4"
    >
      <p
        class="mb-0"
        style="font-size: 3rem"
      >
        Promotion ends in
      </p>
      <div class="d-flex justify-content-center">
        <p class="countdown-clock">
          {{ countDownDays }} Days
        </p>
        <p class="ms-3 countdown-clock">
          {{ countDownHours }} Hours
        </p>
      </div>
    </div>

    <div
      class="sticky-top plan-interval-nav"
      :style="`background-color: ${isPublish ? '#FFF' : '#eff2f5'}`"
    >
      <div class="h-100 d-flex justify-content-center align-items-center">
        <div class="nav-group nav-group-outline">
          <BaseButton
            type="active btn-active-secondary"
            color="gray-400"
            class="me-3"
            :active="!planInterval"
            @click="planInterval = false"
          >
            Monthly
          </BaseButton>
          <BaseButton
            type="active btn-active-secondary"
            color="gray-400"
            :active="planInterval"
            @click="
              planInterval = true;
              isHiddenMobileCard = !isHiddenMobileCard;
            "
          >
            Yearly
          </BaseButton>
        </div>
      </div>
    </div>

    <div
      v-for="interval in ['monthly', 'yearly']"
      :key="interval"
      class="my-10 h-100"
    >
      <div
        v-if="interval === 'monthly' ? !isHiddenMobileCard : isHiddenMobileCard"
        class="row g-10 px-5 h-100"
      >
        <div
          v-for="(subscription, index) in subscriptionPlan"
          :key="index"
          class="col-xl-3 rounded-3 plan-card"
        >
          <BaseCard class="h-100 p t-15 rounded">
            <h1 class="fw-boldest">
              {{ subscription.plan }} plan
            </h1>
            <div class="text-center py-5">
              <span class="text-primary">$ </span>
              <span class="fs-3x fw-bolder text-primary">
                {{ getSubscriptionPrice(subscription.subscription_plan_price) }}
                <span
                  v-if="hasDiscount(subscription)"
                  class="h3 text-muted text-decoration-line-through"
                >
                  {{
                    parseInt(
                      subscription.subscription_plan_price[planInterval ? 1 : 0]
                        .price
                    )
                  }}
                </span>
              </span>
              <span
                class="fs-7 fw-bold opacity-50"
              >/
                <span>
                  {{ planInterval ? 'Year' : 'Month' }}
                </span>
              </span>
              <div
                v-if="
                  planInterval &&
                    subscription.plan !== 'Free' &&
                    hasDiscount(subscription)
                "
                class="popular-badge-wrap"
              >
                <span
                  class="popular-badge h-five text-uppercase fw-bold"
                >80% DISCOUNT</span>
              </div>
            </div>

            <div
              v-for="(limit, limitIndex) in planLimitMobileVersion"
              :key="limitIndex"
              class="text-start d-flex justify-content-between mb-5"
            >
              <div v-if="limit.data[index] === 'yes'">
                <span class="fw-bold fs-6 text-gray-800 flex-grow-1 pe-3">
                  Disable Hypershapes Badges
                </span>
              </div>
              <div v-else-if="limit.data[index] !== 'no'">
                <span
                  class="fw-bold fs-6 text-gray-800 flex-grow-1 pe-3"
                >{{ limit.data[index] }} {{ limit.columnName }}
                </span>
              </div>

              <img
                v-if="limit.data[index] !== 'no'"
                src="@subscription/assets/media/tick.svg"
                width="20"
                :alt="limit.data[index]"
              >
            </div>
            <BaseButton
              type="link"
              class="w-100"
              size="sm"
              @click="scrollToAnchorPoint('moreFeatures')"
            >
              More Features
            </BaseButton>
            <BaseButton
              v-if="isPublish"
              :href="registerLink"
              is-open-in-new-tab
              size="sm"
              class="mt-3 w-100"
            >
              Signup Now
            </BaseButton>
            <BaseButton
              v-else-if="
                getSubscriptionPriceId(subscription) >
                  currentPlan.subscription_plan_price_id || type === 'create'
              "
              data-bs-toggle="modal"
              data-bs-target="#plan-summary"
              size="sm"
              class="mt-3 w-100"
              @click="selectSubscriptionPlan(subscription)"
            >
              {{ buttonText }}
            </BaseButton>
          </BaseCard>
        </div>
      </div>
    </div>

    <div
      ref="moreFeatures"
      class="plan-table"
    >
      <table class="table table-hover">
        <thead class="sticky-top">
          <!-- Desktop Plan Table Header -->
          <tr class="table-light">
            <th class="fw-bolder">
              Plan Name
            </th>
            <th
              v-for="(subscription, index) in subscriptionPlan"
              :key="index"
              class="position-relative"
            >
              <div
                v-if="
                  planInterval &&
                    subscription.plan !== 'Free' &&
                    hasDiscount(subscription)
                "
                class="ribbon"
              >
                <strong>80% DISCOUNT</strong>
              </div>
              <h1>{{ subscription.plan }}</h1>
              <div>
                <span class="text-primary">$ </span>
                <span class="fs-3x fw-bolder text-primary">
                  {{
                    getSubscriptionPrice(subscription.subscription_plan_price)
                  }}
                </span>

                <span
                  v-if="hasDiscount(subscription)"
                  class="h3 text-muted ps-2 text-decoration-line-through"
                >
                  {{
                    parseInt(
                      subscription.subscription_plan_price[planInterval ? 1 : 0]
                        .price
                    )
                  }}
                </span>
                <p class="text-muted fw-bold">
                  per {{ planInterval ? 'year' : 'month' }}
                </p>
                <BaseButton
                  v-if="isPublish"
                  :href="registerLink"
                  is-open-in-new-tab
                  class="mt-3"
                >
                  Signup Now
                </BaseButton>
                <BaseButton
                  v-else-if="
                    checkSubscription(subscription) || type === 'create'
                  "
                  data-bs-toggle="modal"
                  data-bs-target="#plan-summary"
                  class="mb-3"
                  @click="selectSubscriptionPlan(subscription)"
                >
                  {{ buttonText }}
                </BaseButton>
              </div>
            </th>
          </tr>
          <!-- Mobile Plan Table Header -->
          <tr class="table-light">
            <th class="fw-bolder">
              Plan Name
            </th>
            <th
              v-for="(subscription, index) in subscriptionPlan"
              :key="index"
            >
              <b>{{ subscription.plan }}</b>
              <div>
                <b>
                  ${{
                    getSubscriptionPrice(subscription.subscription_plan_price)
                  }}
                </b>
                <span
                  class="text-muted"
                >/{{ planInterval ? 'year' : 'month' }}
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(limit, index) in planLimit"
            :key="index"
            :class="limit.data === '' ? 'table-light' : ''"
          >
            <td
              :class="`text-capitalize text-start p-auto ${
                limit.data === '' ? 'fw-bolder' : ''
              }`"
              :colspan="limit.data === '' ? 5 : ''"
            >
              {{ limit.columnName }}
            </td>
            <td
              v-for="(data, dataIndex) in limit.data.slice(0, 4)"
              :key="dataIndex"
            >
              <div v-if="data !== 'yes' && data !== 'no'">
                {{ data }}
              </div>
              <img
                v-else
                width="20"
                :src="
                  icons[
                    `/resources/modules/subscription/assets/media/${data}.png`
                  ]?.default
                "
                :alt="data"
              >
            </td>
          </tr>
          <tr v-if="isPublish">
            <td />
            <td
              v-for="index in 4"
              :key="index"
            >
              <BaseButton
                :href="registerLink"
                is-open-in-new-tab
                class="mt-3"
              >
                Signup Now
              </BaseButton>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <SubscriptionPlanSummary
      :selected-plan="selectedPlan"
      :plan-interval="planInterval"
      :current-plan="currentPlan"
      :subscription-plan="subscriptionPlan"
      :type="type"
      :is-publish="isPublish"
      :production="production"
      :credit-card="creditCard"
      @changePlanInterval="planInterval = !planInterval"
      @selectedPlan="selectSubscriptionPlan"
    >
      <!-- v-if="selectedPlanId" -->
    </SubscriptionPlanSummary>
  </div>
</template>

<script>
import SubscriptionPlanSummary from '@subscription/components/SubscriptionPlanSummary.vue';
import planLimit from '@subscription/lib/planLimit.js';
import planLimitMobileVersion from '@subscription/lib/planLimitMobileVersion.js';
import eventBus from '@services/eventBus.js';
import clone from 'clone';

export default {
  name: 'SubscriptionSignup',
  components: {
    SubscriptionPlanSummary,
  },
  props: {
    isPublish: { type: Boolean, default: false },
    currentPlan: { type: Object, default: () => {} },
    subscriptionPlan: { type: Array, default: () => [] },
    type: { type: String, default: 'upgrade' },
    production: { type: String, default: 'production' },
    creditCard: { type: Object, default: () => {} },
    promoRemainingHours: { type: String, default: '' },
    isExceedLimit: { type: Boolean, default: false },
  },

  data() {
    return {
      isHiddenMobileCard: false,
      planInterval: true,
      selectedPlan: '',
      showLimitModal: false,
      propContext: '',
      planLimit,
      planLimitMobileVersion,
      countDownDays: 0,
      countDownHours: 0,
      coupons: {},
      registerLink: 'https://my.hypershapes.com/register',
      icons: [],
    };
  },
  computed: {
    buttonText() {
      if (this.type === 'create') return 'Subscribe Now';
      return this.type === 'upgrade' ? 'Upgrade Now' : 'Downgrade Now';
    },
  },
  mounted() {
    this.icons = import.meta.glob(
      '/resources/modules/subscription/assets/media/*.png',
      { eager: true }
    );
    eventBus.$on('subscription-coupon', (val) => {
      this.coupons = val;
      const currentSubscription = this.subscriptionPlan.find(
        (e) => e.plan === this.selectedPlan.plan
      );
      if (currentSubscription) this.selectSubscriptionPlan(currentSubscription);
    });
    this.countDownDays = Math.floor((this.promoRemainingHours || 0) / 24);
    this.countDownHours = Math.floor((this.promoRemainingHours || 0) % 24);
  },
  methods: {
    hasDiscount({ id }) {
      return this.planInterval && this.coupons?.valid && id > 1;
    },
    checkSubscription(subscription) {
      const currentPlanId = this.currentPlan.subscription_plan_price_id;
      const planId =
        this.isExceedLimit && currentPlanId % 2 !== 0
          ? currentPlanId + 1
          : currentPlanId;
      return this.getSubscriptionPriceId(subscription) > planId;
    },
    selectSubscriptionPlan(subscription) {
      const { subscription_plan_price: subscriptionPlanPrice } = subscription;
      const interval = this.planInterval ? 'yearly' : 'monthly';
      const pricing = clone(
        subscriptionPlanPrice.find(
          (price) => price.subscription_plan_type === interval
        )
      );
      pricing.price = this.getSubscriptionPrice(subscriptionPlanPrice);
      this.selectedPlan = { plan: subscription.plan, ...pricing };
    },
    getSubscriptionPriceId(subscription) {
      const { subscription_plan_price: subscriptionPlanPrice } = subscription;
      if (subscription.plan === 'Free') {
        const freePlan = subscriptionPlanPrice.find(
          (plan) => plan.subscription_plan_type === 'monthly'
        );
        return freePlan.id;
      }
      const interval = this.planInterval ? 'yearly' : 'monthly';
      const pricing = subscriptionPlanPrice.find(
        (price) => price.subscription_plan_type === interval
      );
      return pricing.id;
    },
    getSubscriptionPrice(price) {
      const interval = this.planInterval ? 'yearly' : 'monthly';
      const filteredSubscriptionPlan = price.find(
        (e) => e.subscription_plan_type === interval
      );

      const {
        amount_off: amountOff,
        percent_off: percentOff,
        valid,
      } = this.coupons ?? {};
      const subscriptionPrice = parseInt(filteredSubscriptionPlan.price);
      if (
        interval === 'monthly' ||
        !valid ||
        (this.currentPlan.subscription_plan_id > 1 &&
          this.currentPlan.subscription_plan_price?.subscription_plan_type ===
            'yearly')
      )
        return subscriptionPrice;
      if (amountOff) return parseInt(subscriptionPrice - amountOff);
      if (percentOff)
        return parseInt(subscriptionPrice * ((100 - percentOff) / 100));
      return subscriptionPrice;
    },
    scrollToAnchorPoint(refName) {
      const el = this.$refs[refName];
      el.scrollIntoView({ behavior: 'smooth' });
    },
  },
};
</script>

<style lang="scss" scoped>
.desktop-version {
  display: inline-block;
}
.mobile-mode {
  display: none;
}

.plan-table {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 5rem;
  border-radius: 0.475rem;
  box-shadow: 0 0.5rem 1.5rem 0.5rem rgb(0 0 0 / 8%);
  background-color: #fff;
}

table {
  margin-top: 2.5rem;
}

thead tr:nth-child(2) {
  display: none;
}

thead th:not(:first-child) {
  padding-left: 0;
  padding-right: 0;
}

tbody {
  overflow: auto;
  background-color: #fff;
}

th {
  vertical-align: top;
  padding: 50px 0 15px 0;
}

th:first-child,
td:first-child {
  vertical-align: middle;
  text-align: start;
  padding-left: 1rem;
}

th,
td {
  border-left: 2px solid #fff;
  border-right: 2px solid #fff;
}

th:not(:first-child):not(:first-child) {
  text-align: center;
  text-transform: uppercase;
}

table .sticky-top {
  top: 70px;
}
.plan-interval-nav {
  height: 70px;
}

.nav-group {
  background-color: #fff;
}

@media screen and (max-width: 415px) {
  .desktop-version {
    display: none;
  }
  .mobile-mode {
    display: block;
  }
  .plan-card {
    padding-right: 2.5rem;
    padding-left: 2.5rem;
  }

  thead {
    position: initial;
  }

  thead tr:nth-child(1) {
    display: none;
  }
  thead tr:nth-child(2) {
    display: table-row;
  }

  th,
  td {
    border: none;
    padding: 0.75rem 0.25rem;
  }

  th:not(:first-child):not(:first-child) {
    text-transform: none;
  }

  .plan-table {
    padding: 0;
    box-shadow: none;
  }

  .ribbon {
    display: none;
  }
}

.ribbon {
  position: absolute;
  top: 0;
  width: 100%;
  font-size: 11px;
  color: white;
  background-color: #7239ea;
  padding: 5px 21px;
}

.popular-badge-wrap {
  position: absolute;
  width: 100%;
  height: 188px;
  top: -9px;
  left: 11px;
  overflow: hidden;

  &::before,
  &::after {
    content: '';
    position: absolute;
  }

  &::before {
    width: 40px;
    height: 8px;
    right: 75px;
    background: #7239ea;
    border-radius: 8px 8px 0px 0px;
  }

  &::after {
    width: 8px;
    height: 40px;
    right: 0px;
    top: 75px;
    background: #7239ea;
    border-radius: 0px 8px 8px 0px;
  }
}

.popular-badge {
  width: 170px;
  height: 30px;
  line-height: 30px;
  position: absolute;
  top: 30px;
  right: -50px;
  z-index: 2;
  overflow: hidden;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
  border: 1px dashed white;
  box-shadow: 0 0 0 3px, #7239ea 0px 21px 5px -18px rgba(0, 0, 0, 0.6);
  background: #7239ea;
  text-align: center;
  color: white;
  font-size: 10px !important;
  padding-right: 15px;
}

.card-shadow {
  box-shadow: 0px 0px 10px 0px rgb(0 0 0 / 8%);
}
</style>

<!-- eslint-disable vue/no-useless-template-attributes -->
<template>
  <div>
    <BaseCard
      has-header
      title="General"
    >
      <ReportStatisticCard
        col="3"
        title="Unique Clicks"
        :data="uniqueClickLogs.length"
        title-class="text-gray-800 fw-bolder fs-3.5 mb-2"
      />
      <ReportStatisticCard
        col="3"
        title="Sign Ups"
        :data="affiliateDetail.total_referrals"
        title-class="text-gray-800 fw-bolder fs-3.5 mb-2"
      />
      <ReportStatisticCard
        col="3"
        title="Free Accounts"
        :data="affiliateDetail.current_free_user"
        title-class="text-gray-800 fw-bolder fs-3.5 mb-2"
      />
      <ReportStatisticCard
        col="3"
        title="Paid Accounts"
        :data="affiliateDetail.current_paid_account"
        title-class="text-gray-800 fw-bolder fs-3.5 mb-2"
      />
    </BaseCard>
    <BaseCard
      has-header
      title="Commissions"
    >
      <ReportStatisticCard
        v-for="detail in details"
        :key="detail.title"
        :title="detail.title"
        :data="`$ ${detail.value}`"
        title-class="text-gray-800 fw-bolder fs-3.5 mb-2"
        col="3"
      />
    </BaseCard>
    <div class="row">
      <div class="col-xl-8">
        <BaseCard
          has-header
          title="Affiliate Links"
        >
          <BaseFormGroup
            label="Share your affiliate referral links to your friends and followers"
          >
            <BaseFormInput
              :id="`affiliate-link`"
              :model-value="
                `https://${
                  environment === 'staging'
                    ? 'affiliate-test.hypershapes.com'
                    : 'hypershapes.com'
                }` +
                  '?affiliate-id=' +
                  affiliateUser.affLinkCode
              "
              readonly
            >
              <template #append>
                <i
                  :id="`affiliate_clipboard`"
                  class="copy-btn fas fa-link hoverable"
                  :data-clipboard-target="`#affiliate-link`"
                  data-bs-toggle="custom-tooltip"
                  data-bs-placement="bottom"
                  data-bs-title="Copied to Clipboard!"
                  title="Copied to Clipboard!"
                />
              </template>
            </BaseFormInput>
            <div class="text-muted fs-7 mt-1">
              You will earn commissions when someone subscribes to Hypershapes
              paid plan through your affiliate link.
              <!-- Refer to the commission rate
              <a
                href="/affiliates/campaigns"
                class="text-primary"
                style="cursor: 'pointer'"
              >here</a> -->
            </div>
          </BaseFormGroup>
        </BaseCard>
      </div>
      <div class="col-xl-4">
        <BaseCard
          title="Sublines Overview"
          :style="{ height: '200px' }"
          has-header
        >
          <div
            v-for="(el, i) in levels?.length ? levels : [[], []]"
            :key="i"
          >
            <div class="d-flex flex-stack m-2">
              <span class="text-muted fw-bold fs-6"> Level {{ i + 1 }} </span>
              <span class="text-muted fw-bold fs-6">
                {{ el?.length }} sublines
              </span>
            </div>
            <div
              v-if="(levels?.length || 1) - 1 !== i"
              class="separator separator-dashed my-3"
            />
          </div>
        </BaseCard>
      </div>
    </div>
    <BaseCard>
      <BaseTab :tabs="tabs">
        <template #tab-history>
          <BaseDatatable
            no-action
            no-header
            :table-headers="historyHeaders"
            :table-datas="referralHistoryLogs"
            custom-description="No have any transaction history records yet."
          />
        </template>
        <template #tab-status>
          <BaseDatatable
            no-action
            no-header
            :table-headers="statusHeaders"
            :table-datas="referralStatus"
            custom-description="No have any status records yet."
          />
        </template>
      </BaseTab>
    </BaseCard>
  </div>
</template>

<script>
import ReportStatisticCard from '@email/components/ReportStatisticCard.vue';
import Clipboard from 'clipboard';
import NavLayout from '@hypershapesAffiliate/layouts/NavLayout.vue';
import dayjs from 'dayjs';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: { ReportStatisticCard },
  layout: NavLayout,
  props: {
    affiliateUser: {
      type: Object,
      default: () => ({}),
    },
    affiliateLog: {
      type: Array,
      default: () => [],
    },
    affiliateDetail: {
      type: Object,
      default: () => ({}),
    },
    affiliateReferrals: {
      type: Array,
      default: () => [],
    },
    domainUrl: {
      type: String,
      default: () => '',
    },
    affiliateCommissionLogs: {
      type: Array,
      default: () => [],
    },
    environment: {
      type: String,
      default: () => '',
    },
    detailCards: {
      type: Array,
      default: () => [],
    },
    levels: {
      type: Array,
      default: () => [],
    },
    uniqueClickLogs: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      baseURL: 'hypershapes.com',
      historyHeaders: [
        { name: 'Date', key: 'date' },
        { name: 'Referral No', key: 'referral_no' },
        { name: 'Name', key: 'name' },
        { name: 'Commission', key: 'commission' },
        { name: 'Commission status', key: 'commission_status' },
        { name: 'Refer By', key: 'refer_from' },
        { name: 'Payout Countdown', key: 'countdown' },
      ],
      statusHeaders: [
        { name: 'Referral No', key: 'referral_no' },
        { name: 'Name', key: 'name' },
        { name: 'Plan', key: 'plan' },
        { name: 'Status', key: 'status' },
      ],
      details: this.detailCards,
      isActive: false,
      tableName: 'history',
      tabs: [
        { label: 'History', target: 'history' },
        { label: 'Status', target: 'status' },
      ],
    };
  },
  computed: {
    referralHistoryLogs() {
      return this.affiliateCommissionLogs.map((item) => {
        const countdown = dayjs(item.credited_date).diff(
          dayjs(Date.now()),
          'day'
        );
        return {
          date: dayjs(item.paid_date).format('DD/MM/YYYY'),
          refer_from:
            item?.affiliate_referral?.refer_from_affiliate.id ===
            this.affiliateUser.id
              ? 'Me'
              : item?.affiliate_referral?.refer_from_affiliate.fullName,
          referral_no: item?.affiliate_referral?.referral_unique_id,
          name: item?.affiliate_referral?.referral_name,
          commission: `$ ${item.commission}`,
          commission_status: item.commission_status,
          countdown:
            countdown <= 0 ? 'Available for payout' : `${countdown} day(s)`,
        };
      });
    },

    referralStatus() {
      return this.affiliateReferrals.map((item) => {
        const referralSubscription = this.affiliateLog.find((data) => {
          return data.referral_unique_id === item.referral_unique_id;
        });
        return {
          referral_no: item.referral_unique_id,
          name: item.referral_name,
          plan:
            // eslint-disable-next-line no-nested-ternary
            item.subscription_id && referralSubscription !== undefined
              ? referralSubscription.plan.includes('Free')
                ? `${referralSubscription.plan}`
                : `${referralSubscription.plan} ( ${this.capitalize(
                    item.subscription.subscription_plan_type
                      .subscription_plan_type
                  )} )`
              : 'No plan',
          status: item.referral_status,
          isActive: item.status !== 'cancelled',
        };
      });
    },
  },

  mounted() {
    setTimeout(() => {
      const cb = new Clipboard('.copy-btn');

      cb.on('success', (e) => {
        e.clearSelection();
      });
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('.fa-link')
      );
      bootstrap?.then(({ Tooltip }) => {
        const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
          const tooltip = new Tooltip(tooltipTriggerEl, {
            trigger: 'click',
          });
          tooltipTriggerEl.onmouseleave = () => {
            tooltip.hide();
          };
          return tooltip;
        });
      });
    }, 1000);
  },

  methods: {
    capitalize(s) {
      if (typeof s !== 'string') return '';
      return s.charAt(0).toUpperCase() + s.slice(1);
    },
  },
};
</script>

<style scoped lang="scss">
.col-8 {
  width: 66.66666667%;
  @media (max-width: 980px) {
    width: 100%;
  }
}
.col-4 {
  width: 33.33333333%;
  @media (max-width: 980px) {
    width: 100%;
  }
}
</style>

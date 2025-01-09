<template>
  <BasePageLayout :page-name="'Affiliate Report'">
    <template #left>
      <BaseCard
        has-header
        has-toolbar
        title="General"
      >
        <template #toolbar>
          <BaseDatePicker
            v-model="dateRange"
            placeholder="Select date range"
            range
            value-type="date"
            :disabled-date="disableAfterToday"
            @change="handleDatesChange"
            @clear="clearDates"
          />
        </template>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(clicks)?.length ?? 0"
            text_title="Clicks"
            class="h-100"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="
              getFilteredData(orders)
                ?.map((e) => e.processed_contact_id)
                ?.filter((x, i, a) => a.indexOf(x) == i)?.length ?? 0
            "
            :text_title="`Customers`"
            class="h-100"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(orders)?.length ?? 0"
            :text_title="`Orders`"
            class="h-100"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="
              getFilteredData(orders)
                ?.reduce((p, c) => (p?.sale ?? 0) + (+c?.sale ?? 0), 0)
                .toFixed(2) ?? 0
            "
            :text_title="`Total Sales (${defaultCurrency})`"
          />
        </div>
      </BaseCard>
      <BaseCard
        has-header
        title="Commissions"
        has-toolbar
        :toolbar-at-end="false"
      >
        <template #toolbar>
          <BaseButton
            type="link"
            href="/affiliate/members/commissions"
          >
            <i class="fa-solid fa-square-up-right" />
          </BaseButton>
        </template>
        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="commissionsInfo?.totalCommissions?.toFixed(2) ?? 0.0"
            :text_title="`Total Commissions (${defaultCurrency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="commissionsInfo?.pendingCommissions?.toFixed(2) ?? 0.0"
            :text_title="`Pending Commissions (${defaultCurrency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="commissionsInfo?.approvedCommissions?.toFixed(2) ?? 0.0"
            :text_title="`Approved Commissions (${defaultCurrency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="commissionsInfo?.withdrawnCommissions?.toFixed(2) ?? 0.0"
            :text_title="`Withdrawn Commissions (${defaultCurrency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="
              commissionsInfo?.disapprovedCommissions?.toFixed(2) ?? 0.0
            "
            :text_title="`Disapproved Commissions (${defaultCurrency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="commissionsInfo?.disapprovedPayouts?.toFixed(2) ?? 0.0"
            :text_title="`Disapproved Payouts (${defaultCurrency})`"
            class="h-100"
          />
        </div>
      </BaseCard>
    </template>
    <template #right>
      <BaseCard
        has-header
        has-toolbar
        title="Affiliate"
        :toolbar-at-end="false"
      >
        <template #toolbar>
          <BaseButton
            type="link"
            href="/affiliate/members"
          >
            <i class="fa-solid fa-square-up-right" />
          </BaseButton>
        </template>
        <div class="d-flex flex-stack m-2">
          <span class="text-muted fw-bold fs-6"> Total </span>
          <span class="text-muted fw-bold fs-6">
            {{ affiliates.filter((el) => el.status)?.length ?? 0 }}
          </span>
        </div>
        <div class="separator separator-dashed my-3" />
        <div class="d-flex flex-stack m-2">
          <span class="text-muted fw-bold fs-6"> Approved </span>
          <span class="text-muted fw-bold fs-6">
            {{
              affiliates.filter((el) => el.status === 'approved')?.length ?? 0
            }}
          </span>
        </div>
        <div class="separator separator-dashed my-3" />
        <div class="d-flex flex-stack m-2">
          <span class="text-muted fw-bold fs-6"> Disapproved </span>
          <span class="text-muted fw-bold fs-6">
            {{
              affiliates.filter((el) => el.status === 'disapproved')?.length ??
                0
            }}
          </span>
        </div>
        <div class="separator separator-dashed my-3" />
        <div class="d-flex flex-stack m-2">
          <span class="text-muted fw-bold fs-6"> Pending </span>
          <span class="text-muted fw-bold fs-6">
            {{
              affiliates.filter((el) => el.status === 'pending')?.length ?? 0
            }}
          </span>
        </div>
        <div class="separator separator-dashed my-3" />
      </BaseCard>
    </template>
  </BasePageLayout>
</template>

<script setup>
import dayjs from 'dayjs';
import cloneDeep from 'lodash/cloneDeep';
import { ref, computed } from 'vue';
import AffiliateCard from '@shared/components/AffiliateCard.vue';

const startDate = '2019-01-01';
const endDate = dayjs().$d;

const props = defineProps({
  commissions: {
    type: Array,
    default: () => [],
  },
  orders: {
    type: Array,
    default: () => [],
  },
  clicks: {
    type: Array,
    default: () => [],
  },
  affiliates: {
    type: Array,
    default: () => [],
  },
  defaultCurrency: {
    type: String,
    default: 'MYR',
  },
  commissionsInfo: {
    type: Object,
    default: () => {},
  },
});

const dateRange = ref([]);
const datesBetweenRange = computed(() => {
  let dates = [];
  let theDate = dayjs(dateRange.value[0] ?? '2019-01-01').format('YYYY-MM-DD');
  while (theDate < dayjs(dateRange.value[1] ?? dayjs()).format('YYYY-MM-DD')) {
    dates = [theDate, ...dates];
    theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
  }
  return [dayjs(endDate).format('YYYY-MM-DD'), ...dates];
});

const clearDates = () => {
  dateRange.value = [];
};

const disableAfterToday = (date) => {
  return date > new Date(new Date().setHours(0, 0, 0, 0));
};

const handleDatesChange = (newDates) => {
  const startingDate = newDates[0];
  const endingDate = newDates[1];
  this.dateRange = [startingDate, endingDate];
};

const getFilteredData = (data) => {
  const filteredData = data.filter((d) =>
    datesBetweenRange.value.includes(
      new Date(d?.created_at).toLocaleDateString('sv-SE')
    )
  );
  return filteredData;
};
</script>

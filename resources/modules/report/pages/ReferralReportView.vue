<template>
  <BasePageLayout
    :page-name="campaign.title"
    back-to="/report/referral"
  >
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
            :quantity="getFilteredData(participants)?.length ?? 0"
            :text_title="`Participants`"
          />
        </div>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(referralSocialShares)?.length ?? 0"
            :text_title="`Referred Clicks`"
          />
        </div>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="
              getFilteredData(participants)?.filter(
                (el) => el.refer_by !== null
              )?.length ?? 0
            "
            :text_title="`Referred Leads`"
          />
        </div>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(referralPageClickLogs)?.length ?? 0"
            :text_title="`Page Views`"
          />
        </div>
      </BaseCard>
      <BaseCard
        has-header
        :title="`All ${participants.length} Participants`"
      >
        <BaseDatatable
          no-action
          max-height="300px"
          :table-headers="tableHeaders"
          :table-datas="tableDatas"
          title="participant"
          custom-description="This campaign has no participants yet."
        />
      </BaseCard>
    </template>

    <template #right>
      <BaseCard
        has-header
        title="Shares"
      >
        <div
          v-for="(share, index) in shares"
          :key="index"
          class="col-xl-4 my-0 mb-3"
        >
          <BaseFormInput
            class="input-group-sm"
            :model-value="share.logs.length"
            readonly
            disabled
            :color="getIconColor(share.name)"
          >
            <template #prepend>
              <i
                :class="`${getIconClass(share.name)}`"
                :style="{ color: '#fff' }"
              />
            </template>
          </BaseFormInput>
        </div>
      </BaseCard>
      <BaseCard
        v-if="inviterActions?.length"
        has-header
        title="Inviter Actions"
      >
        <div
          v-for="(action, index) in inviterActions"
          :key="index"
          class="col-xl-12 my-0 mb-3"
        >
          <div class="card statistic-card hoverable card-xl-stretch h-100">
            <div class="card-body">
              <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                {{ action.title }} <br>
              </div>
              <div class="fw-bold text-gray-800">
                {{ action.rate }}
              </div>
            </div>
          </div>
        </div>
      </BaseCard>
      <BaseCard
        v-if="inviteeActions?.length"
        has-header
        title="Invitee Actions"
      >
        <div
          v-for="(action, index) in inviteeActions"
          :key="index"
          class="col-xl-12 my-0 mb-3"
        >
          <div class="card statistic-card hoverable card-xl-stretch h-100">
            <div class="card-body">
              <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                {{ action.title }} <br>
              </div>
              <div class="fw-bold text-gray-800">
                {{ action.rate }}
              </div>
            </div>
          </div>
        </div>
      </BaseCard>
      <BaseCard
        has-header
        title="Rewards"
      >
        <div
          v-for="(reward, index) in rewards"
          :key="index"
          class="col-xl-12 my-0 mb-3"
        >
          <div class="card statistic-card hoverable card-xl-stretch h-100">
            <div class="card-body">
              <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                {{ reward.title }} <br>
              </div>
              <div class="fw-bold text-gray-800">
                {{ reward.rate }}
              </div>
            </div>
          </div>
        </div>
      </BaseCard>
    </template>
  </BasePageLayout>
</template>

<script setup>
import dayjs from 'dayjs';
import { ref, computed } from 'vue';
import AffiliateCard from '@shared/components/AffiliateCard.vue';
import _ from 'lodash';

const startDate = '2019-01-01';
const endDate = dayjs().$d;

const dateRange = ref([]);
const props = defineProps({
  campaign: {
    type: Object,
    default: () => {},
  },
  actions: {
    type: Array,
    default: () => [],
  },
  rewards: {
    type: Array,
    default: () => [],
  },
  participants: {
    type: Array,
    default: () => [],
  },
  referralSocialShares: {
    type: Array,
    default: () => [],
  },
  referralPageClickLogs: {
    type: Array,
    default: () => [],
  },
});

const tableHeaders = ref([
  { name: 'Email', key: 'email' },
  {
    name: 'Name',
    key: 'name',
  },
  { name: 'Points', key: 'point' },
  {
    name: 'Referral',
    key: 'referCount',
  },
  {
    name: 'Share',
    key: 'shareCount',
  },
]);

const getIconClass = (type) => {
  switch (type) {
    case 'facebook':
      return 'fa-brands fa-facebook-f';
    case 'email':
      return 'fa-solid fa-envelope';
    case 'messenger':
      return 'fa-brands fa-facebook-messenger';
    case 'copy':
      return 'far fa-copy';
    default:
      return `fa-brands fa-${type}`;
  }
};
const getIconColor = (type) => {
  switch (type) {
    case 'facebook':
      return '#1877f2';
    case 'twitter':
      return '#1da1f2';
    case 'whatsapp':
      return '#25d366';
    case 'linkedin':
      return '#0077b5';
    case 'telegram':
      return '#0088cc';
    case 'messenger':
      return '#0084ff';
    case 'email':
      return '#ea4335';
    case 'copy':
      return '#183153';
    default:
      return null;
  }
};

const clearDates = () => {
  dateRange.value = [];
};

const disableAfterToday = (date) => {
  return date > new Date(new Date().setHours(0, 0, 0, 0));
};

const handleDatesChange = (newDates) => {
  const startingDate = newDates[0];
  const endingDate = newDates[1];
  dateRange.value = [startingDate, endingDate];
};

const datesBetweenRange = computed(() => {
  let dates = [];
  // const [startDate, endDate] = this.dateRange;
  let theDate = dayjs(dateRange.value[0] ?? '2019-01-01').format('YYYY-MM-DD');
  while (theDate < dayjs(dateRange.value[1] ?? dayjs()).format('YYYY-MM-DD')) {
    dates = [theDate, ...dates];
    theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
  }
  return [dayjs(endDate).format('YYYY-MM-DD'), ...dates];
});

const getFilteredData = (data) => {
  const filteredData = data?.filter((d) =>
    datesBetweenRange.value.includes(
      new Date(d?.created_at).toLocaleDateString('sv-SE')
    )
  );
  return filteredData;
};

const socials = computed(() => {
  const groupBy = _.groupBy(props.referralSocialShares, 'type');
  return Object.fromEntries(Object.entries(groupBy));
});

const shares = computed(() => {
  const socialShares = props.campaign.social_networks;
  if (props.campaign.share_email_enabled) socialShares.push({ name: 'email' });
  socialShares.push({ name: 'copy' });
  return socialShares?.map((el) => ({
    ...el,
    logs: Object.keys(socials.value).some((key) => key === el.name)
      ? socials.value[el.name]
      : [],
  }));
});

const tableDatas = computed(() => {
  return props.participants.map((e) => ({
    ...e,
    name: e.name.trim() ? e.name : '-',
  }));
});

const inviterActions = computed(() => {
  return props.actions.filter((el) => el.referralType === 'inviter') ?? [];
});

const inviteeActions = computed(() => {
  return props.actions.filter((el) => el.referralType === 'invitee') ?? [];
});
</script>

<style scoped lang="scss">
.statistic-card {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
}
</style>

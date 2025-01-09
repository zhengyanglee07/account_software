<template>
  <BasePageLayout
    is-setting
    :page-name="emailName"
    back-to="/emails"
  >
    <div class="row">
      <div class="col-8">
        <BaseCard
          has-header
          title="General"
        >
          <ReportStatisticCard
            title="Total Sent"
            :data="sent"
          />
          <ReportStatisticCard
            title="Delivery Rate"
            :data="calculateRate(delivered, sent) + '%'"
            :append="delivered + '/' + sent"
          />
          <ReportStatisticCard
            title="Open Rate"
            :data="calculateRate(opened, delivered) + '%'"
            :append="opened + '/' + delivered"
          />
          <ReportStatisticCard
            class="pt-3"
            title="Click Rate"
            :data="calculateRate(clicked, delivered) + '%'"
            :append="clicked + '/' + delivered"
          />
          <ReportStatisticCard
            class="pt-3"
            title="Bounce Rate"
            :data="calculateRate(bounced, sent) + '%'"
            :append="bounced + '/' + sent"
          />
          <ReportStatisticCard
            class="pt-3"
            title="Unsubscribe Rate"
            :data="calculateRate(unsubscribed, delivered) + '%'"
            :append="unsubscribed + '/' + delivered"
          />
        </BaseCard>
      </div>
      <div class="col-4 mb-6">
        <BaseCard
          has-header
          title="Details"
          class="h-100"
        >
          <div>
            <div class="email-detail-row">
              <span>Name:</span>
              <span> {{ emailName }}</span>
            </div>
            <div class="email-detail-row">
              <span>Recipients:</span>
              <span> {{ recipientDesc }}</span>
            </div>
            <div class="email-detail-row">
              <span>Subject:</span>
              <span> {{ subject }}</span>
            </div>
            <div class="email-detail-row">
              <span>Sent Date:</span>
              <span>{{ formattedSentDate ?? '-' }}</span>
            </div>
          </div>
        </BaseCard>
      </div>
    </div>
    <BaseCard
      has-header
      no-body-margin
      title="Recipient"
    >
      <BaseDatatable
        title="recipient"
        no-action
        no-hover
        :table-headers="tableHeaders"
        :table-datas="tableData"
        custom-description="Recipients will be shown here after the email is sent."
      >
        <template #cell-openedAt="{ row: { openedAt } }">
          <span>
            {{ openedAt ? convertedDateTime(openedAt) : '-' }}
          </span>
        </template>
        <template #cell-clickedAt="{ row: { clickedAt } }">
          <span>
            {{ clickedAt ? convertedDateTime(clickedAt) : '-' }}
          </span>
        </template>
      </BaseDatatable>
    </BaseCard>
  </BasePageLayout>
</template>

<script>
import ReportStatisticCard from '@email/components/ReportStatisticCard.vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone'; // dependent on utc plugn
import customParseFormat from 'dayjs/plugin/customParseFormat';
import eventBus from '@services/eventBus.js';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

export default {
  name: 'EmailIndividualReport',
  components: {
    ReportStatisticCard,
  },
  props: {
    sent: {
      type: [String, Number],
      default: '',
    },
    opened: {
      type: [String, Number],
      default: '',
    },
    clicked: {
      type: [String, Number],
      default: '',
    },
    bounced: {
      type: [String, Number],
      default: '',
    },
    unsubscribed: {
      type: [String, Number],
      default: '',
    },
    tableData: {
      type: [Object, Array],
      default: () => [],
    },
    emailName: {
      type: String,
      default: '',
    },
    recipientDesc: {
      type: String,
      default: '',
    },
    subject: {
      type: String,
      default: '',
    },
    sentDate: {
      type: String,
      default: '',
    },
    timezone: {
      type: String,
      default: 'Asia/Kuala_Lumpur',
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: 'Email Address',
          key: 'emailAddress',
        },
        {
          name: 'Name',
          key: 'name',
        },
        {
          name: 'Status',
          key: 'status',
        },
        {
          name: 'Open At',
          key: 'openedAt',
          custom: true,
        },
        {
          name: 'Click At',
          key: 'clickedAt',
          custom: true,
        },
      ],
    };
  },
  computed: {
    delivered() {
      return this.sent - this.bounced;
    },
    formattedSentDate() {
      return dayjs(this.sentDate)
        .tz(this.timezone)
        .format('MMMM D, YYYY [at] h:mm a');
    },
  },
  methods: {
    convertedDateTime(date) {
      return dayjs(date).tz(this.timezone).format('MMMM D, YYYY [at] h:mm a');
    },
    calculateRate(val1, val2) {
      const divide = val1 / val2;

      return divide ? (divide * 100).toFixed(2) : 0;
    },
  },
};
</script>

<style scoped>
:deep(.form-group) {
  margin-bottom: 0px !important;
}

.email-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0rem;
  border-bottom: dashed 1px #e6e9f1;
}

.email-detail-row span:first-child {
  font-weight: 600;
}
</style>

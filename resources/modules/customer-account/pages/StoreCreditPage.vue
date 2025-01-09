<template>
  <EcommerceAccountLayout>
    <template #content>
      <div class="d-flex">
        <BaseCard class="col-6">
          <div class="statistic-card card-xl-stretch">
            <h1>
              {{ currency }}
              {{
                specialCurrencyCalculation(
                  processedContact.credit_balance / 100
                )
              }}
            </h1>
            <p class="text-dark">
              Total Available Credits
            </p>
          </div>
        </BaseCard>
        <BaseCard class="col-6">
          <div class="statistic-card card-xl-stretch">
            <h1>
              {{ currency }}
              {{ specialCurrencyCalculation(totalSpent / 100) }}
            </h1>
            <p class="text-dark">
              Total Credit Spent
            </p>
          </div>
        </BaseCard>
      </div>

      <BaseDatatable
        v-if="storeCredits.length"
        title="store credit"
        :table-headers="tableHeaders"
        :table-datas="storeCredits"
        no-action
        no-header
      >
        <template
          #cell-credit="{
            row: { credit_type, credit_amount, balance, expire_date },
          }"
        >
          <span v-if="credit_type !== 'Set'">
            {{ credit_type !== 'Deduct' ? '+' : '-' }}
            {{ currency }}
            {{ specialCurrencyCalculation(credit_amount) }}
          </span>
          <span v-if="credit_type === 'Set'">
            {{ Math.sign(balance) == 1 ? '+' : '-' }}
            {{ currency }}
            {{ specialCurrencyCalculation(balance) }}
          </span>
          <div
            v-if="credit_type !== 'Deduct'"
            class="fw-bolder"
          >
            Expire at: {{ formatDate(expire_date) }}
          </div>
        </template>
      </BaseDatatable>
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import PublishLayout from '@builder/layout/PublishLayout.vue';
import EcommerceAccountLayout from '@customerAccount/layout/EcommerceAccountLayout.vue';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(timezone);

export default {
  components: {
    EcommerceAccountLayout,
  },
  mixins: [specialCurrencyCalculationMixin],
  layout: PublishLayout,
  props: {
    user: { type: Object, default: () => {} },
    processedContact: { type: Object, default: () => {} },
    storeCredits: { type: Array, default: () => [] },
    selectedCurrency: { type: String, default: '' },
    totalSpent: { type: Number, default: 0 },
  },
  data() {
    return {
      currency: '',
      tableHeaders: [
        { name: 'Date', key: 'created_at', isDateTime: true },
        { name: 'Content', key: 'reason' },
        { name: 'Credit', key: 'credit', custom: true },
      ],
    };
  },
  computed: {
    accountTimezone() {
      return this.$page.props.timezone ?? 'Asia/Kuala_Lumpur';
    },
  },
  mounted() {
    this.currency = this.selectedCurrency;
    if (this.currency === 'MYR') this.currency = 'RM';
  },
  methods: {
    formatDate(date) {
      return dayjs(String(date))
        .tz(this.accountTimezone)
        .format('MMMM D, YYYY [at] h:mm a');
    },
  },
};
</script>

<style scoped>
.statistic-card {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
  padding: 1.5rem;
}
</style>

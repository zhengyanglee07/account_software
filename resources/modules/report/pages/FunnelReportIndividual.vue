<template>
  <BasePageLayout
    is-setting
    :page-name="`Funnel ${funnel.funnel_name}`"
    back-to="/report/funnel"
  >
    <BaseFormGroup
      class="w-300px me-4"
      label="Date Range: "
    >
      <BaseDatePicker
        v-model="dateRange"
        placeholder="Select date range"
        value-type="date"
        range
        :disabled-date="disableDatesAfterToday"
        @clear="dateRange = []"
      >
        <template #input>
          <div class="form-control">
            {{
              `${dayjs(dateRange[0]).format('D MMM YY')} ~ ${dayjs(
                dateRange[1]
              ).format('D MMM YY')}`
            }}
          </div>
        </template>
      </BaseDatePicker>
    </BaseFormGroup>

    <BaseChart
      v-model:metricType="metricType"
      :metric-type-options="metricTypeOptions"
      :metric-total="metric()"
      :date-range="dateRange"
      :datasets="chartDatasets"
      @datesUpdated="datesInChart = $event"
      @groupByUpdated="groupBy = $event"
    />

    <BaseCard>
      <ReportStatisticCard
        title="Total Sales"
        :data="metric('Total Sales')"
      />
      <ReportStatisticCard
        title="Orders"
        :data="metric('Orders')"
      />
      <ReportStatisticCard
        title="AOV"
        :data="metric('AOV')"
      />
      <ReportStatisticCard
        title="Opted In"
        :data="metric('Opted In')"
      />
      <ReportStatisticCard
        title="Customers"
        :data="metric('Customers')"
      />
    </BaseCard>

    <BaseCard
      has-header
      no-body-margin
      title="All Pages"
    >
      <BaseDatatable
        title="page"
        no-action
        no-header
        no-hover
        :table-headers="tableHeaders"
        :table-datas="tableDatas"
      />
    </BaseCard>
  </BasePageLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ReportStatisticCard from '@email/components/ReportStatisticCard.vue';
import clone from 'clone';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import isBetween from 'dayjs/plugin/isBetween';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(isBetween);

const props = defineProps({
  funnel: {
    type: Object,
    required: true,
  },
  landingPages: {
    type: Array,
    default: () => [],
  },
  orders: {
    type: Array,
    default: () => [],
  },
  forms: {
    type: Array,
    default: () => [],
  },
  pageViews: {
    type: Array,
    default: () => [],
  },
  timezone: {
    type: Object,
    required: true,
  },
});

const defaultCurrency = computed(() => {
  const currency = usePage().props.account?.currency ?? 'MYR';
  return currency === 'MYR' ? 'RM' : currency;
});

const tableHeaders = [
  {
    name: 'Name',
    key: 'name',
  },
  {
    name: 'Unique Views',
    key: 'unique-views',
  },
  {
    name: 'Opted In',
    key: 'opt-in',
  },
  {
    name: 'Orders',
    key: 'orders',
  },
  {
    name: `Total Sales (${defaultCurrency.value})`,
    key: 'total-sales',
  },
];

const dateRange = ref([]);
const groupBy = ref('Day');
const metricType = ref('Total Sales');
const datesInChart = ref([]);
const metricTypeOptions = [
  'Total Sales',
  'Orders',
  'AOV',
  'Opted In',
  'Customers',
];

const convertToAccountTimezone = (dateTime) => {
  return dayjs(dateTime)?.tz(props.timezone);
};

const isBetweenSelectedDateRange = (dateTime) => {
  if (dateRange.value.length === 0) return true;
  const [startDate, endDate] = dateRange.value;
  return convertToAccountTimezone(dateTime).isBetween(
    convertToAccountTimezone(startDate),
    convertToAccountTimezone(endDate)
  );
};

const chartDatasets = computed(() => {
  let source = [];
  switch (metricType.value) {
    case 'Opted In':
      source = props.forms.reduce((acc, curr) => {
        if (curr.form_contents.length > 0) {
          curr.form_contents.forEach((e) => acc.push(e));
        }
        return acc;
      }, []);
      break;
    default:
      source = clone(props.orders);
  }

  const isInitialDataAnArray = ['Customers', 'Opted In'].includes(
    metricType.value
  );

  return datesInChart.value.map((date) => {
    let totalSales = 0;
    let totalOrder = 0;
    const data = source.reduce(
      (acc, curr) => {
        if (
          dayjs(curr.created_at).isSame(
            dayjs(date.value),
            groupBy.value.toLowerCase()
          )
        ) {
          switch (metricType.value) {
            case 'Total Sales':
              acc += +curr.total;
              break;
            case 'Orders':
              acc += 1;
              break;
            case 'AOV':
              totalSales += +curr.total;
              totalOrder += 1;
              break;
            case 'Customers':
              acc.push(curr.processed_contact_id);
              break;
            case 'Opted In':
              acc.push(curr.reference_key);
              break;
            default:
          }
        }
        return acc;
      },
      isInitialDataAnArray ? [] : 0
    );
    if (isInitialDataAnArray) {
      return new Set([...data]).size;
    }
    if (metricType.value === 'AOV') {
      return totalSales / (totalOrder || 1);
    }
    return data;
  });
});

const tableDatas = ref([]);
const totalSales = ref(0);
const totalOrders = ref(0);
const totalOptedIn = ref(0);
const totalCustomers = ref([]);

const resetStatistics = () => {
  totalSales.value = 0;
  totalOrders.value = 0;
  totalOptedIn.value = 0;
  totalCustomers.value = [];
};

watch(
  dateRange,
  () => {
    resetStatistics();
    tableDatas.value = props.landingPages.map((page) => {
      const { id: pageId, name: pageName } = page;

      const pageViews = props.pageViews.filter((pageView) => {
        return (
          isBetweenSelectedDateRange(pageView.created_at) &&
          +pageView.value === +pageId
        );
      });

      const pageOptIns = props.forms.reduce((acc, form) => {
        if (form.landing_id !== pageId) return acc;
        const submissionBetweenSelectedDateRange = form.form_contents.reduce(
          (nestedAcc, submission) => {
            const { reference_key: refKey, created_at: createdAt } = submission;
            if (
              !nestedAcc.includes(refKey) &&
              isBetweenSelectedDateRange(createdAt)
            ) {
              nestedAcc.push(refKey);
            }
            return nestedAcc;
          },
          []
        );
        acc += submissionBetweenSelectedDateRange.length;
        return acc;
      }, 0);

      const pageOrders = props.orders.filter((order) => {
        return (
          isBetweenSelectedDateRange(order.created_at) &&
          +order.landing_id === +pageId
        );
      });

      const pageTotalSales = pageOrders.reduce((acc, order) => {
        acc += +order.total;
        return acc;
      }, 0);

      pageOrders.forEach(({ processed_contact_id: contactId }) => {
        if (!totalCustomers.value.includes(contactId)) {
          totalCustomers.value.push(contactId);
        }
      });

      totalSales.value += pageTotalSales;
      totalOrders.value += pageOrders.length;
      totalOptedIn.value += pageOptIns;

      return {
        name: pageName,
        orders: pageOrders.length,
        'opt-in': pageOptIns,
        'unique-views': pageViews.length,
        'total-sales': pageTotalSales.toFixed(2),
      };
    });
  },
  {
    deep: true,
    immediate: true,
  }
);

const averageOrderValue = computed(() => {
  return (totalSales.value / (totalOrders.value || 1)).toFixed(2);
});

const metric = (type = metricType.value) => {
  let metricData = 0;
  switch (type) {
    case 'Total Sales':
      metricData = `${defaultCurrency.value} ${totalSales.value.toFixed(2)}`;
      break;
    case 'Orders':
      metricData = totalOrders.value;
      break;
    case 'AOV':
      metricData = `${defaultCurrency.value} ${averageOrderValue.value}`;
      break;
    case 'Customers':
      metricData = totalOptedIn.value;
      break;
    case 'Opted In':
      metricData = totalCustomers.value.length;
      break;
    default:
  }
  return metricData;
};

const disableDatesAfterToday = (date) => {
  return date > new Date(new Date().setHours(0, 0, 0, 0));
};

onMounted(() => {
  const date = new Date();
  date.setTime(date.getTime() - 31 * 24 * 3600 * 1000);
  dateRange.value = [date, new Date()];
});
</script>

<template>
  <Component
    :is="chartWrapperAttributes.is"
    v-bind="chartWrapperAttributes"
  >
    <template #header>
      <div class="d-flex flex-column">
        <h6 class="text-gray-700">
          {{ metricType }}
        </h6>
        <h4>
          {{ metricTotal }}
        </h4>
      </div>
    </template>
    <template #toolbar>
      <BaseFormGroup
        no-margin
        class="d-flex align-items-center mb-0 w-300px me-4"
      >
        <label class="w-100px text-end pe-2"> Group By </label>
        <BaseFormSelect
          v-model="groupBy"
          :options="groupByOptions"
        />
      </BaseFormGroup>
      <BaseFormGroup
        no-margin
        class="d-flex align-items-center mb-0 w-300px ms-auto"
      >
        <label class="w-100px text-end pe-2"> Metrics </label>
        <BaseFormSelect
          v-model="proxyMetricType"
          :options="metricTypeOptions"
        />
      </BaseFormGroup>
    </template>
    <Line
      chart-id="line-chart"
      dataset-id-key="label"
      :chart-options="chartOptions"
      :chart-data="chartData"
      :plugins="plugins"
      :width="width"
      :height="height"
    />
  </Component>
</template>

<script setup>
import { Line } from 'vue-chartjs';
import { computed, watch, ref } from 'vue';
import dayjs from 'dayjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
} from 'chart.js';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale
);

const props = defineProps({
  width: {
    type: Number,
    default: 400,
  },
  height: {
    type: Number,
    default: 400,
  },
  /**
   * A collection of data for each date in the specified date range
   */
  datasets: {
    type: Array,
    default: () => [],
  },
  /**
   * An array of dates in `[startDate, endDate]` format
   */
  dateRange: {
    type: Array,
    default: () => [],
  },
  /**
   * The name of the metric which the chart is displaying, will be displayed on card title
   */
  metricType: {
    type: String,
    required: true,
  },
  /**
   * The sum of the data of the selected metric, will be displayed on card title
   */
  metricTotal: {
    type: String,
    default: '',
  },
  /**
   * Array of metric types to be selected
   */
  metricTypeOptions: {
    type: Array,
    default: () => [],
  },
  /**
   * If true, will display chart only, without `BaseCard` wrapper and card header
   */
  chartOnly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  /**
   * Triggered when user update metricType
   */
  'update:metricType',
  /**
   * Triggered when dates to be displayed as chart labels are updated
   */
  'datesUpdated',
  /**
   * Triggered when user updates the Group By option
   */
  'groupByUpdated',
  /**
   * Triggered when the group by options are updated
   */
  'groupByOptionUpdated',
]);

const proxyMetricType = computed({
  get() {
    return props.metricType;
  },
  set(val) {
    emit('update:metricType', val);
  },
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
    },
    y: {
      grid: {
        borderDash: [5, 6],
      },
    },
  },
};

const chartWrapperAttributes = computed(() => {
  if (props.chartOnly) {
    return {
      is: 'div',
    };
  }
  return {
    is: 'BaseCard',
    'has-header': true,
    'has-toolbar': true,
  };
});

const groupBy = ref('Day');

watch(groupBy, (newValue) => {
  emit('groupByUpdated', newValue);
});

const differenceBetween2Dates = (unit = 'day') => {
  const [startDate, endDate] = props.dateRange;
  return dayjs(endDate).diff(dayjs(startDate), unit);
};

const groupByOptions = computed(() => {
  const options = ['Day'];
  const differentInDays = differenceBetween2Dates();
  if (differentInDays >= 7) {
    options.push('Week');
  }
  if (differentInDays >= 30) {
    options.push('Month');
  }
  return options;
});

watch(groupByOptions, (newValue) => {
  emit('groupByOptionUpdated', newValue);
});

const datesInChart = ref([]);

const differenceBetweenDates = computed(() => {
  return differenceBetween2Dates(groupBy.value.toLowerCase());
});

watch(
  differenceBetweenDates,
  (newValue) => {
    datesInChart.value = [];
    let startDate = dayjs(props.dateRange[0]);
    for (let i = 0; i <= newValue; i += 1) {
      if (i !== 0) {
        startDate = dayjs(startDate).add(1, groupBy.value.toLowerCase());
      }
      datesInChart.value.push({
        label: dayjs(startDate).format('D MMM'),
        value: startDate,
      });
    }
    emit('datesUpdated', datesInChart.value);
  },
  {
    immediate: true,
  }
);

const chartData = computed(() => {
  return {
    labels: datesInChart.value.map((e) => e.label),
    datasets: [
      {
        borderColor: '#009ef7',
        data: props.datasets,
      },
    ],
  };
});
</script>

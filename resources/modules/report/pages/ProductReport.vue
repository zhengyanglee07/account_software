<template>
  <div class="row justify-content-start">
    <div class="col-md-4">
      <BaseFormGroup label="Sales Channel: ">
        <BaseFormSelect
          v-model="salesChannel"
          :options="['All', 'Online Store', 'Mini Store', 'Funnel']"
        />
      </BaseFormGroup>
    </div>
    <div class="col-md-4">
      <BaseFormGroup label="Date Range: ">
        <BaseDatePicker
          v-model="dateRange"
          placeholder="Select date range"
          range
          value-type="date"
          :disabled-date="disableDatesAfterToday"
          @clear="clearDates"
        />
      </BaseFormGroup>
    </div>
  </div>

  <BaseDatatable
    no-header
    no-action
    no-hover
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    custom-description="No statistic found for the specified sales channel and date range"
  />
</template>

<script setup>
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import isBetween from 'dayjs/plugin/isBetween';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(isBetween);

const props = defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  products: {
    type: Array,
    default: () => [],
  },
  visitors: {
    type: Array,
    default: () => [],
  },
  timezone: {
    type: Object,
    required: true,
  },
  // currency: {
  //   type: Object,
  //   required: true,
  // },
  defaultCurrency: {
    type: String,
    required: true,
  },
});

const salesChannel = ref('All');

const generalHeaders = [
  {
    name: 'Orders',
    key: 'orderCount',
  },
  {
    name: 'Net Qty.',
    key: 'netSoldQuantity',
  },
  {
    name: 'Qty/Order',
    key: 'quantityPerOrder',
  },
  {
    name: `Total Sales (${props.defaultCurrency})`,
    key: 'totalSales',
  },
];

const tableHeaders = computed(() => {
  const nameHeader = {
    name: 'Name',
    key: 'name',
    width: '45%',
  };
  const additionalHeaders = [
    {
      name: 'Unique Views',
      key: 'uniquePageView',
    },
    {
      name: '% Orders',
      key: 'orderPerUniquePageView',
    },
  ];

  return ['Online Store', 'Mini Store'].includes(salesChannel.value)
    ? [nameHeader, ...additionalHeaders, ...generalHeaders]
    : [nameHeader, ...generalHeaders];
});

const orderInSpecifiedSalesChannel = computed(() => {
  if (salesChannel.value === 'All') return props.orders;
  return props.orders.filter((order) => {
    return order.acquisition_channel === salesChannel.value;
  });
});

const dateRange = ref([]);

const convertToAccountTimezone = (dateTime) => {
  return dayjs(dateTime)?.tz(props.timezone);
};

const isBetweenSelectedDateRange = (orderCreationDateTime) => {
  if (dateRange.value.length === 0) return true;
  const [startDate, endDate] = dateRange.value;
  return convertToAccountTimezone(orderCreationDateTime).isBetween(
    convertToAccountTimezone(startDate),
    convertToAccountTimezone(endDate)
  );
};

const orderInSpecifiedDate = computed(() => {
  return orderInSpecifiedSalesChannel.value.filter((order) => {
    return isBetweenSelectedDateRange(order.created_at);
  });
});

const soldProductMetadata = computed(() => {
  return orderInSpecifiedDate.value.reduce((acc, curr) => {
    curr.order_details.forEach((item) => {
      const productName = props.products.find(
        (e) => e.id === item.users_product_id
      )?.productTitle;
      if (!acc[productName]) {
        acc[productName] = {
          productId: null,
          orderIds: [],
          soldQuantity: 0,
          totalSales: 0,
        };
      }
      acc[productName].productId = item.users_product_id;
      acc[productName].orderIds.push(curr.id);
      acc[productName].soldQuantity += +item.quantity;
      acc[productName].totalSales += +item.total;
    });
    return acc;
  }, {});
});

const standardizeNumberFormat = (value) => {
  return value.toFixed(2);
};

const tableDatas = computed(() => {
  return Object.entries(soldProductMetadata.value).map(([name, data]) => {
    const { productId, orderIds, soldQuantity, totalSales } = data;
    const orderCount = [...new Set(orderIds ?? [])].length;
    const uniquePageView = props.visitors.filter((visitor) => {
      const {
        activity_logs: activityLogs,
        sales_channel: channel,
        created_at: createdAt,
      } = visitor;
      const isSameSalesChannel =
        salesChannel.value.toLowerCase() === channel.split('-').join(' ');
      if (!isBetweenSelectedDateRange(createdAt) || !isSameSalesChannel)
        return false;
      return activityLogs.some(({ type, value }) => {
        return type === 'product' && +value === +productId;
      });
    }).length;

    return {
      name,
      orderCount,
      netSoldQuantity: soldQuantity,
      quantityPerOrder: standardizeNumberFormat(
        soldQuantity / (orderCount || 1)
      ),
      totalSales: standardizeNumberFormat(totalSales),
      uniquePageView,
      orderPerUniquePageView: standardizeNumberFormat(
        (orderCount / (uniquePageView || 1)) * 100
      ),
    };
  });
});

const disableDatesAfterToday = (date) => {
  return date > new Date(new Date().setHours(0, 0, 0, 0));
};

const clearDates = () => {
  dateRange.value = [];
};
</script>

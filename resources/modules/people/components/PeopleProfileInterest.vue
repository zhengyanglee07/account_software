<template>
  <BaseDatatable
    no-header
    no-action
    custom-description="This contact doesn't has visit activities after the last conversion"
    :table-headers="tableHeaders"
    :table-datas="interestProducts"
  >
    <template #cell-image="{ row: { image } }">
      <img
        class="p-2 rounded w-80px"
        :src="image"
      >
    </template>
  </BaseDatatable>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  mixins: [specialCurrencyCalculationMixin],
  props: {
    trackingActivities: {
      type: Array,
      default: () => [],
    },
    products: {
      type: Array,
      default: () => [],
    },
    orderDetails: {
      type: [Array, Object],
      default: () => [],
    },
  },
  data() {
    return {
      sortingWithAscd: false,
      tableHeaders: [
        { name: 'Image', key: 'image', custom: true },
        { name: 'Title', key: 'productName' },
        { name: 'Visit', key: 'count' },
      ],
    };
  },
  computed: {
    interestProducts() {
      const pageViewPerProduct = this.trackingActivities
        .filter((activity) => !activity.is_completed)
        .reduce((acc, { activity_logs: logs }) => {
          logs.forEach((log) => {
            if (log.type === 'product') {
              const { image, name, url } = log;
              if (!acc[name]) {
                acc[name] = {
                  url,
                  image,
                  count: 0,
                };
              }
              acc[name].count += 1;
            }
          });
          return acc;
        }, {});
      return Object.entries(pageViewPerProduct).map(
        ([productName, { image, url, count }]) => {
          return {
            productName,
            image,
            count,
            url,
          };
        }
      );
    },
  },
};
</script>

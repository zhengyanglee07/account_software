<template>
  <EcommerceAccountLayout>
    <template #content>
      <BaseDatatable
        title="order"
        :table-headers="tableHeaders"
        :table-datas="datas"
        no-edit-action
        no-delete-action
        no-search
      >
        <template
          #cell-type="{
            row: {
              delivery_hour_type,
              delivery_type,
              delivery_date,
              delivery_timeslot,
            },
          }"
        >
          <div v-if="delivery_hour_type === 'custom'">
            {{
              delivery_type == 'pickup' ? 'Store Pickup' : 'Scheduled Delivery'
            }}
            <br>
            {{ delivery_date }} <br>
            {{ delivery_timeslot }}
          </div>
          <p v-else>
            -
          </p>
        </template>

        <template #cell-fulfillment_status="{ row: { fulfillment_status } }">
          <BaseBadge
            :text="fulfillment_status"
            :type="fulfillment_status === 'Fulfilled' ? 'success' : 'warning'"
          />
        </template>

        <template #action-options="{ row: { editLink } }">
          <BaseDropdownOption
            text="View"
            :link="editLink"
          />
        </template>
      </BaseDatatable>
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import PublishLayout from '@builder/layout/PublishLayout.vue';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  name: 'OrderDashboard',
  mixins: [specialCurrencyCalculationMixin],
  layout: PublishLayout,
  props: {
    orders: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */
        { name: 'Order', key: 'orderSeq' },
        { name: 'Date', key: 'created_at', isDateTime: true },
        { name: 'Type', key: 'type', custom: true },
        { name: 'Total', key: 'total' },
        { name: 'Status', key: 'fulfillment_status', custom: true },
      ],
    };
  },

  computed: {
    datas() {
      return this.orders.map((item) => {
        return {
          ...item,
          total: `${item.total.split(' ')[0]} ${this.specialCurrencyCalculation(
            item.total.split(' ')[1],
            item.total.split(' ')[0] === 'RM' ? 'MYR' : item.total.split(' ')[0]
          )}`,
          orderSeq: `#${item.order_number}`,
          editLink: `/order/${item.payment_references}`,
        };
      });
    },
  },
};
</script>
<style scoped>
@media (max-width: 450px) {
  :deep(th.sorting:nth-child(2)) {
    min-width: 150px !important;
  }
}
</style>

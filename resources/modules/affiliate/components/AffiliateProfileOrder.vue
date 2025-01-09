<template>
  <BaseDatatable
    no-header
    no-edit-action
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="orders"
  >
    <template #cell-customer-name="{ row: { processed_contact_id } }">
      {{ customerName(processed_contact_id) }}
    </template>
    <template #action-options="{ row: { reference_key } }">
      <BaseDropdownOption
        text="View"
        :link="`/orders/details/${reference_key}`"
      />
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  props: {
    orders: {
      type: Array,
      default: () => [],
    },
    currency: {
      type: String,
      default: () => 'RM',
    },
    allCustomers: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Orders', key: 'order_number' },
        { name: 'Dates', key: 'dates', isDateTime: true },
        { name: 'Customer Name', key: 'customer-name', custom: true },
        { name: 'Payment', key: 'payment_status' },
        { name: `Total Sales (${this.currency})`, key: 'total' },
      ],
    };
  },
  methods: {
    customerName(id) {
      return this.allCustomers.find((cust) => cust.id === id).displayName;
    },
  },
};
</script>

<template>
  <BaseDatatable
    no-header
    no-edit-action
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="Object.values(people)"
  >
    <template #cell-name="{ row: item }">
      {{ peopleName(item) }}
    </template>
    <template #cell-order="{ row: { affOrders } }">
      {{ affOrders?.length ?? 0 }}
    </template>
    <template #cell-phone-number="{ row: { phone_number } }">
      {{ phone_number ?? '-' }}
    </template>
    <template #cell-total="{ row: { totalSales } }">
      {{ specialCurrencyCalculation(totalSales) }}
    </template>
    <template #action-options="{ row: { contactRandomId } }">
      <BaseDropdownOption
        text="View"
        :link="`/people/profile/${contactRandomId}`"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  mixins: [specialCurrencyCalculationMixin],
  props: {
    people: {
      type: Array,
      default: () => [],
    },
    currency: {
      type: String,
      default: () => 'RM',
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Name', key: 'name', custom: true },
        { name: 'Email', key: 'email' },
        { name: 'Mobile Number', key: 'phone-number', custom: true },
        { name: 'Orders', key: 'order', custom: true },
        { name: `Total Sales (${this.currency})`, key: 'total', custom: true },
      ],
    };
  },
  methods: {
    peopleName(people) {
      return `${people.fname ?? ''} ${people.lname ?? ''}`;
    },
  },
};
</script>

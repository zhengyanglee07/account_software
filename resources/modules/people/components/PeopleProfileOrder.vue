<template>
  <BaseDatatable
    no-header
    no-action
    custom-description="This contact doesn't purchase anything yet"
    :table-headers="tableHeaders"
    :table-datas="props.orders"
  >
    <template #cell-id="{ row: { order_number, reference_key } }">
      <BaseButton
        type="link"
        is-open-in-new-tab
        :href="`/orders/details/${reference_key}`"
      >
        {{ `#${order_number}` }}
      </BaseButton>
    </template>
    <template #cell-additional_status="{ row: { additional_status } }">
      <BaseBadge
        v-if="additional_status !== 'Closed'"
        :text="additional_status"
        type="success"
      />
      <BaseBadge v-else :text="additional_status" />
    </template>
    <template #cell-total="{ row: { currency, total } }">
      {{ currency === 'MYR' ? 'RM' : currency }} {{ total }}
    </template>
    <template #cell-action_button="{ row: { reference_key } }">
      <BaseButton
        type="light"
        size="sm"
        class="btn-active-light-primary"
        data-bs-toggle="dropdown"
        aria-expanded="false"
      >
        Actions
        <span class="svg-icon svg-icon-5 m-0 ms-1">
          <i class="fa-solid fa-angle-down" />
        </span>
      </BaseButton>
      <BaseDropdown id="datatable-actions">
        <BaseDropdownOption
          is-open-in-new-tab
          text="View"
          :link="`/orders/details/${reference_key}`"
        />
      </BaseDropdown>
    </template>
  </BaseDatatable>
</template>

<script setup>
const props = defineProps({
  orders: { type: Array, default: () => [] },
});

const tableHeaders = [
  { name: 'Date', key: 'created_at', isDateTime: true },
  { name: 'Order ID', key: 'id', custom: true },
  { name: 'Status', key: 'additional_status', custom: true },
  { name: 'Total', key: 'total', custom: true },
  { name: 'Action', key: 'action_button', custom: true },
];
</script>

<style scoped lang="scss"></style>

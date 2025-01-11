<template>
  <BaseDatatable title="Invoices" no-delete-action :table-headers="tableHeaders" :table-datas="[]"
    :pagination-info="[]">
    <template #action-button>
      <BaseButton has-add-icon href="/sales/invoices/new"> Add Invoices </BaseButton>
    </template>
  </BaseDatatable>
  <
</template>

<script setup>
const tableHeaders = [
  { name: 'No', key: 'order' },
  { name: 'Date', key: '' },
  { name: 'Customer', key: '' },
  { name: 'Amount', key: '' },
  { name: 'Balance', key: '' },
  { name: 'Status', key: '' },
];
</script>

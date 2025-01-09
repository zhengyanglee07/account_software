<template>
  <BaseDatatable
    title="currencie"
    :table-headers="tableHeaders"
    :table-datas="state.currencyArray"
    no-edit-action
    @delete="removeRecord"
  >
    <template
      #cell-exchangeRate="{ row: { isDefault, exchangeRate, currency } }"
    >
      {{
        isDefault === '1'
          ? 'Default Currency'
          : `1 ${state.currencyArray[0].currency} = ${exchangeRate} ${currency}`
      }}
    </template>

    <template #action-options="{ row }">
      <BaseDropdownOption
        text="Edit"
        data-bs-toggle="modal"
        data-bs-target="#currencyModel"
        is-open-in-new-tab
        @click="changeType(row)"
      />
    </template>
  </BaseDatatable>
</template>

<script setup>
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';
import { onMounted, reactive, defineEmits, inject } from 'vue';
import currencyAPI from '@setting/api/currencyAPI.js';
import { router } from '@inertiajs/vue3';

const $toast = inject('$toast');

const props = defineProps({
  currencySettings: { type: Array, default: () => [] },
});

const emits = defineEmits(['changeType']);

const tableHeaders = [
  { name: 'Currency', key: 'currency' },
  { name: 'Exchange Rate', key: 'exchangeRate', custom: true },
];

const state = reactive({
  currencyArray: [],
  specialCurrency: [
    'BIF',
    'CLP',
    'DJF',
    'GNF',
    'JPY',
    'KMF',
    'KRW',
    'MGA',
    'PYG',
    'RWF',
    'UGX',
    'VND',
    'VUV',
    'XAF',
    'XOF',
    'XPF',
  ],
});

const changeType = (param) => {
  emits('changeType', { type: 'Edit', id: param.id });
  eventBus.$emit('getSelectedCurrency', param);
};

const triggerEditModal = () => {
  const modal = new Modal(document.getElementById('currencyModel'));
  modal.show();
};

const removeRecord = (id) => {
  currencyAPI
    .delete(id)
    .then(() => {
      $toast.success('Success', 'Successful Removed Currency');
      router.visit('/currency/settings');
    })
    .catch((error) => {
      $toast.error('Error', 'Unsuccessful Removed Currency');
    });
};

onMounted(() => {
  state.currencyArray = props.currencySettings;
  eventBus.$on('updateCurrency', (data) => {
    state.currencyArray = data;
  });
});
</script>

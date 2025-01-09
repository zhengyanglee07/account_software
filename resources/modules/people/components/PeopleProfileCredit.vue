<template>
  <div
    v-if="computedRecords?.length"
    class="w-100 text-end pe-5"
  >
    <BaseButton
      data-bs-toggle="modal"
      data-bs-target="#manageCreditModal"
    >
      Manage Store Credit
    </BaseButton>
  </div>
  <BaseDatatable
    no-header
    no-action
    no-search
    :table-headers="tableHeaders"
    :table-datas="computedRecords"
    :empty-state="emptyState"
    title="Store Credits"
    custom-description="This customer doesn't have store credit yet. Kickstart today by offering him/her store credits"
  >
    <template #action-button>
      <BaseButton
        has-add-icon
        data-bs-toggle="modal"
        data-bs-target="#manageCreditModal"
      >
        Add Store Credit
      </BaseButton>
    </template>

    <template #cell-reason="{ row: { reason, notes } }">
      {{ reason }}
      <i
        v-if="notes"
        class="fa-solid fa-comment ms-2"
        data-bs-toggle="custom-tooltip"
        data-bs-placement="bottom"
        :title="notes"
      />
    </template>
  </BaseDatatable>

  <PeopleManageCreditModal
    modal-id="manageCreditModal"
    :currency="currency"
    :exchange-rate="exchangeRate"
    is-people-profile
  />
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useStore } from 'vuex';
import PeopleManageCreditModal from '@people/components/PeopleManageCreditModal.vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

const props = defineProps({
  processedContact: { type: Object, default: () => {} },
  creditRecords: { type: Array, default: () => [] },
  currency: { type: String, default: '' },
  exchangeRate: { type: String, default: '' },
  currencies: { type: Object, default: () => {} },
});

const store = useStore();

const tableHeaders = [
  /**
   * @param name : column header title
   * @param key : datas column name in table
   * @param order : order of column data, default => 0
   * @param width : column width in px, default => auto
   * @param textalign : text align, default => left
   */
  { name: 'Updated At', key: 'updatedTime', isDateTime: true },
  { name: 'Type', key: 'creditType' },
  { name: 'Credit', key: 'credit_amount' },
  { name: 'Reason', key: 'reason', custom: true },
  { name: 'Expiry date', key: 'expiryDate', isDateTime: true },
];

const accountTimezone = computed(
  () => usePage().props?.timezone ?? 'Asia/Kuala_Lumpur'
);

const computedRecords = computed(() =>
  props.creditRecords.map((item) => ({
    ...item,
    updatedTime: item.updated_at,
    creditType: item.credit_type,
    reason: item.reason,
    expiryDate:
      item.expire_date &&
      dayjs(item.expire_date)
        .tz(accountTimezone.value)
        .format('MMMM D, YYYY [at] h:mm a'),
    credit_amount: Math.abs(
      item.credit_amount / 100 / parseFloat(props.currencies[item.currency])
    ).toFixed(2),
    balance: Math.abs(
      item.balance / 100 / parseFloat(props.currencies[item.currency])
    ).toFixed(2),
    setTypeSign: Math.sign(
      item.balance / 100 / parseFloat(props.currencies[item.currency])
    ),
  }))
);

onMounted(() => {
  store.commit('people/updateCheckedContactIds', {
    contactIds: [props.processedContact.id],
  });
});
</script>

<style scoped lang="scss"></style>

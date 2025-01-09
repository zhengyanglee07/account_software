<template>
  <BaseDatatable
    title="cashback"
    :table-headers="tableHeaders"
    :table-datas="cashbackDatas"
    @delete="deleteCashback"
  >
    <template #action-button>
      <BaseButton
        id="add-cashback-button"
        has-add-icon
        @click="addCashback"
      >
        Add Cashback
      </BaseButton>
    </template>
  </BaseDatatable>
</template>

<script>
import cashbackAPI from '@cashback/api/cashbackAPI.js';

export default {
  props: {
    segments: { type: Array, default: () => [] },
    cashbacks: { type: Array, default: () => [] },
    cashbackDetails: [Object, Array],
  },
  data() {
    return {
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : justify-content, default => left
         */
        { name: 'Title', key: 'title' },
        { name: 'Amount(%)', key: 'amount' },
        { name: 'Segments', key: 'segmentNames' },
      ],
      parsedCashbacks: [],
      emptyState: {
        title: 'cashback',
        description: 'cashbacks',
      },
    };
  },
  computed: {
    cashbackDatas() {
      return this.parsedCashbacks.map((cashback) => ({
        id: cashback.id,
        title: cashback.cashback_title,
        segmentNames: this.cashbackDetails[cashback.id],
        amount: parseFloat(cashback.cashback_amount / 100).toFixed(2),
        ref_key: cashback.ref_key,
        editLink: `/cashback/${cashback.ref_key}`,
      }));
    },
  },
  mounted() {
    this.parsedCashbacks = this.cashbacks;
  },
  methods: {
    addCashback() {
      this.$inertia.visit('/cashback/add');
    },

    deleteCashback(id) {
      cashbackAPI
        .delete(id)
        .then(({ data }) => {
          this.$inertia.visit('/marketing/cashback');
          this.$toast.success('Success', 'Cashback deleted.');
          this.parsedCashbacks = this.parsedCashbacks.filter(
            (cashback) => cashback.id !== id
          );
        })
        .catch((e) => this.$toast.error('Error', 'Something went wrong!'));
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BaseDatatable
    title="prize"
    :table-headers="tableHeaders"
    :table-datas="prizeArray"
    no-action
    no-header
  >
    <template #cell-actions>
      <BaseButton
        type="secondary"
        size="sm"
        @click="deletePrize"
      >
        <span>Delete</span>
      </BaseButton>
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  props: {
    prize: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Title', key: 'prizeTitle' },
        { name: 'Number of Winners', key: 'noOfWinner' },
        { name: 'Actions', key: 'actions', custom: true },
      ],
    };
  },
  computed: {
    prizeArray() {
      return [this.prize];
    },
  },
  methods: {
    deletePrize() {
      // eslint-disable-next-line no-restricted-globals
      const res = confirm(
        'Are you sure you want to delete this prize? Winner(s) who won this prize would be deleted as well.'
      );
      if (res) {
        this.$emit('delete-prize');
      }
    },
  },
};
</script>

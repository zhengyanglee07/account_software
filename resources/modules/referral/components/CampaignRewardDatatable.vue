<template>
  <BaseDatatable
    title="reward"
    :table-headers="tableHeaders"
    :table-datas="rewardArray"
    no-edit-action
    no-delete-action
    no-header
  >
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        is-open-in-new-tab
        @click="triggerReferalRewardModal(item)"
      />
      <BaseDropdownOption
        text="Delete"
        is-open-in-new-tab
        @click="deleteReward(item)"
      />
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  props: {
    rewards: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Title', key: 'rewardTitle' },
        { name: 'Type', key: 'typeTitle' },
        { name: 'Value', key: 'value' },
        { name: 'Points To Unlock', key: 'pointToUnlock' },
      ],
    };
  },
  computed: {
    rewardArray() {
      return this.rewards.map((el) => ({
        ...el,
        value:
          // eslint-disable-next-line no-nested-ternary
          el.rewardType === 'promo-code'
            ? el.promoCode
            : el.rewardType === 'custom-message'
            ? '-'
            : el.rewardValue,
      }));
    },
  },
  methods: {
    deleteReward(value) {
      this.$emit('delete-reward', value);
    },
    triggerReferalRewardModal(value) {
      this.$emit('trigger-reward-modal', value);
    },
  },
};
</script>

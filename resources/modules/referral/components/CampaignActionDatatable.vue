<template>
  <BaseDatatable
    title="action"
    :table-headers="tableHeaders"
    :table-datas="actionArray"
    no-edit-action
    no-delete-action
    no-header
  >
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        is-open-in-new-tab
        @click="triggerReferalActionModal(item)"
      />
      <BaseDropdownOption
        text="Delete"
        is-open-in-new-tab
        @click="deleteAction(item)"
      />
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  props: {
    actions: {
      type: Array,
      default: () => [],
    },
    selectedChannel: {
      type: String,
      default: 'funnel',
    },
    type: {
      type: String,
      default: 'inviter',
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Action', key: 'title' },
        { name: 'Point', key: 'point' },
      ],
    };
  },
  computed: {
    actionArray() {
      return this.actions.filter((e) =>
        this.selectedChannel !== 'funnel' ? e.actionType !== 'sign-up' : e
      );
    },
  },
  methods: {
    deleteAction(value) {
      const actionType =
        this.type === 'inviter' ? 'delete-action' : 'delete-invitee-action';
      this.$emit(actionType, value);
    },
    triggerReferalActionModal(value) {
      const actionType =
        this.type === 'inviter'
          ? 'trigger-action-modal'
          : 'trigger-invitee-action-modal';
      this.$emit(actionType, value);
    },
  },
};
</script>

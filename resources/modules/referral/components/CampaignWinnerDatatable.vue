<template>
  <div class="d-flex flex-stack flex-wrap mt-3">
    <span class="text-dark fw-bolder fs-3 px-8">
      {{ type === 'suggested' ? 'Winners Suggestion' : 'Prize Winners' }}
    </span>
    <BaseButton
      v-if="type === 'suggested'"
      class="me-5"
      :disabled="!selected || (type === 'suggested' && !winners.length)"
      @click="emit"
    >
      <span>Re-draw Winners</span>
      <span
        v-if="!selected"
      ><i
        class="fas fa-spinner fa-pulse"
        style="margin-left: 5px"
      /></span>
    </BaseButton>
  </div>
  <div class="px-6">
    <div
      v-if="!winners.length"
      class="d-flex align-items-center bg-light-info p-5 mt-3 mb-5"
      style="text-align: justify"
    >
      <span class="text-muted fw-bold">
        {{
          type === 'suggested'
            ? 'All the suggested winners are accepted'
            : 'No winner is accepted yet'
        }}
      </span>
    </div>
  </div>
  <BaseDatatable
    v-if="winners?.length"
    :id="`${type}-winner-datatable`"
    title="winner"
    :table-headers="tableHeaders"
    :table-datas="winners"
    no-search
    no-action
    no-header
  >
    <template #cell-actions="{ row: item }">
      <BaseButton
        size="sm"
        :type="type === 'suggested' ? 'light-primary' : 'secondary'"
        @click="edit(item)"
      >
        {{ type === 'suggested' ? 'Accept' : 'Deny' }}
      </BaseButton>
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: () => '',
    },
    winners: {
      type: Array,
      default: () => [],
    },
    type: {
      type: String,
      default: () => 'suggested',
    },
    selected: {
      type: Boolean,
      default: () => true,
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Email', key: 'email' },
        { name: 'Points', key: 'point' },
        { name: 'Actions', key: 'actions', custom: true },
      ],
    };
  },
  methods: {
    edit(item) {
      if (this.type === 'accepted') this.$emit('deny-winner', item);
      else this.$emit('accept-winner', item);
    },
    emit() {
      this.$emit('select-winner');
    },
  },
};
</script>

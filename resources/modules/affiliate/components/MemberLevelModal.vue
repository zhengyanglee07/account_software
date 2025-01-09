<template>
  <VueJqModal
    :modal-id="modalId"
    small
  >
    <template #title>
      Add level
    </template>

    <template #body>
      <div class="row">
        <div class="col-md-8">
          <input
            v-if="commissionType === 'percentage'"
            v-model="commission"
            class="form-control"
            type="number"
            min="1"
            max="100"
          >
          <input
            v-else
            v-model="commission"
            class="form-control"
            type="number"
          >
        </div>

        <div class="col-md-4">
          <select
            v-model="commissionType"
            class="form-select"
          >
            <option value="percentage">
              %
            </option>
            <option value="fixed">
              fixed
            </option>
          </select>
        </div>
      </div>
    </template>

    <template #footer>
      <button
        class="primary-small-square-button"
        @click="editLevel"
      >
        Save
      </button>
    </template>
  </VueJqModal>
</template>

<script>
export default {
  name: 'MemberLevelModal',
  props: {
    modalId: String,
    level: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      commission: 1,
      commissionType: 'percentage',
    };
  },
  watch: {
    level: {
      handler() {
        this.commission = this.level?.commission ?? 1;
        this.commissionType = this.level?.commissionType ?? 'percentage';
      },
      immediate: true,
    },
  },
  methods: {
    editLevel() {
      if (this.commission <= 0) {
        this.$toast.error('Error', 'Commission should be greater than 0');
        return;
      }

      if (this.commissionType === 'percentage' && this.commission > 100) {
        this.$toast.error('Error', 'Maximum commission in percentage is 100');
        return;
      }

      this.$emit('edit-level', {
        commission: this.commission,
        commissionType: this.commissionType,
      });

      // reset local data
      this.commission = 1;
      this.commissionType = 'percentage';
      $(`#${this.modalId}`).modal('hide');
    },
  },
};
</script>

<style scoped></style>

<template>
  <VueJqModal
    :modal-id="modalId"
    small
  >
    <template #title>
      Settle Balance
    </template>

    <template #body>
      <div>
        <p>Settle balance for {{ memberName }}</p>
        <input
          v-model="amountToSettle"
          class="form-control mt-3"
          type="number"
          :max="participant.amounts_owned"
        >
      </div>
    </template>

    <template #footer>
      <button
        type="button"
        class="cancel-button"
        data-bs-dismiss="modal"
        @click="cancel"
      >
        Cancel
      </button>
      <button
        :disabled="settling"
        class="primary-small-square-button"
        @click="settleBalance"
      >
        <div
          v-if="settling"
          class="spinner--white-small"
        >
          <div class="loading-animation loading-container">
            <div class="shape shape1" />
            <div class="shape shape2" />
            <div class="shape shape3" />
            <div class="shape shape4" />
          </div>
        </div>
        <span v-else>Save</span>
      </button>
    </template>
  </VueJqModal>
</template>

<script>
export default {
  name: 'SettleBalanceModal',
  props: {
    modalId: String,
    participant: Object,
    settling: Boolean,
  },
  data() {
    return {
      amountToSettle: 0,
    };
  },
  computed: {
    memberName() {
      return this.participant?.affiliate;
    },
  },
  watch: {
    participant(p) {
      this.amountToSettle = p.amounts_owned
        ? parseFloat(p.amounts_owned)
        : this.amountToSettle;
    },
  },
  methods: {
    cancel() {
      this.amountToSettle = 0;
    },

    settleBalance() {
      this.$emit('settle-balance', this.amountToSettle);
    },
  },
};
</script>

<style scoped></style>

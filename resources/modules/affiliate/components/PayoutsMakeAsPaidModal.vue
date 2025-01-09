<template>
  <BaseModal
    :modal-id="modalId"
    title="Mark as Paid"
  >
    <BaseFormGroup
      label="To mark as paid, please ensure you have settled the payment manually
      through your Paypal account. This payment won't be processed by
      Hypershapes and you won't be able to refund it using Hypershapes."
    />
    <template #footer>
      <BaseButton
        :disabled="processing"
        @click="markAsPaid"
      >
        Mark as paid
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
export default {
  name: 'PayoutsMakeAsPaidModal',
  props: {
    modalId: {
      type: String,
      required: true,
    },
    payout: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      processing: false,
    };
  },
  methods: {
    hideModal() {
      this.$emit('hide');
    },
    async markAsPaid() {
      this.processing = true;

      try {
        const res = await this.$axios.put(
          `/affiliate/members/commission/payout/${this.payout.id}`,
          {
            status: 'paid',
          }
        );

        this.$toast.success('Success', 'Successfully mark payout as paid');
        this.$emit('update-payout', res.data.payout);
        this.$emit('hide');
      } catch (err) {
        this.$toast.error('Error', 'Failed to mark payout as paid');
      } finally {
        this.processing = false;
      }
    },
  },
};
</script>

<style scoped></style>

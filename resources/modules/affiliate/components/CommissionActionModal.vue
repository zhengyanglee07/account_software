<template>
  <BaseModal
    :modal-id="modalId"
    small
    :title="isApproved ? 'Commission Details' : 'Commission Approval'"
  >
    <!-- <template #title>
      {{ isApproved ? 'Disapprove' : 'Approve' }} Commission
    </template> -->

    <BaseFormGroup :label="`Level: ${commission.level}`" />
    <BaseFormGroup :label="`Email: ${commission.affiliate_email}`" />
    <BaseFormGroup :label="`Status: ${commission.status}`" />

    <template
      v-if="!isApproved"
      #footer
    >
      <!-- disable disapprove on approved commission to avoid
           logic error in commission calc in dashboard -->
      <BaseButton
        v-if="!isDisapproved"
        type="secondary"
        @click="disapproveCommission"
      >
        Disapprove
      </BaseButton>

      <BaseButton @click="approveCommission">
        Approve
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
export default {
  name: 'CommissionActionModal',
  props: {
    modalId: {
      type: String,
      default: '',
    },
    commission: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    isApproved() {
      return this.commission.status === 'approved';
    },
    isDisapproved() {
      return this.commission.status === 'disapproved';
    },
  },
  methods: {
    approveCommission() {
      this.$emit('approve-commission');
    },
    disapproveCommission() {
      this.$emit('disapprove-commission');
    },
  },
};
</script>

<style scoped></style>

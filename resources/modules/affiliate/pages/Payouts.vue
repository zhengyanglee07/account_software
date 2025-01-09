<template>
  <BaseDatatable
    no-edit-action
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="tableData"
    title="payout"
  >
    <template #cell-status="{ row: { status } }">
      <BaseBadge
        class="text-capitalize"
        :text="status"
        :type="
          status === 'paid' || status === 'pending'
            ? status === 'paid'
              ? 'success'
              : 'warning'
            : 'secondary'
        "
      />
    </template>

    <template #action-options="{ row: item }">
      <template v-if="item.status === 'pending'">
        <BaseDropdownOption
          text="Mark as Paid"
          @click="toggleMarkAsPaidModal(item)"
        />
        <BaseDropdownOption
          text="Dissaprove"
          @click="disapprovePayout(item)"
        />
      </template>
      <template v-else>
        <BaseDropdownOption text="No Actions" />
      </template>
    </template>
  </BaseDatatable>

  <PayoutsMakeAsPaidModal
    :modal-id="markAsPaidModalId"
    :payout="selectedPayout"
    @hide="modal.hide()"
    @update-payout="handleUpdatePayout"
  />
</template>

<script>
import { Modal } from 'bootstrap';
import PayoutsMakeAsPaidModal from '@affiliate/components/PayoutsMakeAsPaidModal.vue';

export default {
  components: {
    PayoutsMakeAsPaidModal,
  },
  props: {
    payouts: {
      type: Array,
      default: () => [],
    },
    defaultCurrency: {
      type: String,
      default: () => '$',
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: 'Affiliate',
          key: 'affiliate',
        },
        {
          name: `Paypal Email Address`,
          key: 'paypal_email',
        },
        {
          name: `Amount to payout (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'amount_to_payout',
        },
        {
          name: `Status`,
          key: 'status',
          custom: true,
        },
      ],

      localPayouts: [],
      modal: null,
      markAsPaidModalId: 'mark-as-paid-modal',
      selectedPayout: null,
    };
  },
  computed: {
    tableData() {
      return this.localPayouts.map((po) => ({
        id: po.id,
        affiliate: `${po.participant.member.first_name} ${po.participant.member.last_name}`,
        paypal_email: po.participant.member.paypal_email ?? '-',
        amount_to_payout: po.convertedAmount.toFixed(2),
        status: po.status,
      }));
    },
  },
  mounted() {
    this.localPayouts = [...this.payouts];
  },
  methods: {
    toggleMarkAsPaidModal(p) {
      this.selectedPayout = p;
      this.modal = new Modal(document.getElementById(this.markAsPaidModalId));
      this.modal.show();
    },

    handleUpdatePayout(payout) {
      this.localPayouts = this.localPayouts.map((po) => {
        if (po.id !== payout.id) {
          return po;
        }

        return {
          ...po,
          ...payout,
        };
      });
    },

    async disapprovePayout(payout) {
      const data = {
        status: 'disapproved',
      };

      try {
        await this.$axios.put(
          `/affiliate/members/commission/payout/${payout.id}`,
          data
        );

        this.handleUpdatePayout({
          ...payout,
          ...data,
        });
        this.$toast.success('Success', 'Successfully disapproved payout');
      } catch (err) {
        console.error(err);
        this.$toast.error('Error', 'Failed to disapprove payout');
      }
    },
  },
};
</script>

<style scoped lang="scss">
</style>

<template>
  <BaseDatatable
    no-action
    no-search
    :table-headers="tableHeaders"
    :table-datas="tableData"
    custom-description="There are no any payout records yet. Request your first payout by clicking button below!"
    :title="'payout'"
  >
    <template #action-button>
      <BaseButton
        has-add-icon
        @click="showModal"
      >
        Request payout
      </BaseButton>
    </template>
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
  </BaseDatatable>

  <CreatePayoutRequestModal
    :modal-id="createPayoutRequestModalId"
    :minimum-payout="minimumPayout"
    :available-commissions="availableCommissions"
    :default-currency="defaultCurrency"
    @hide="hideCreatePayoutRequestModal"
  />
  <FailRequestPayoutModal
    :modal-id="failRequestPayoutModalId"
    :reason="failureReason"
    @hide="hideFailRequestPayoutModal"
  >
    <!-- <template
      v-if="!member?.paypal_email"
      #reason
    >
      <p>
        Please provide a valid PayPal email address in
        <a href="profile"> Profile Setttings</a> before creating a payout
        request
      </p>
    </template> -->
  </FailRequestPayoutModal>
</template>

<script>
import CreatePayoutRequestModal from '@affiliate/components/CreatePayoutRequestModal.vue';
import FailRequestPayoutModal from '@affiliate/components/FailRequestPayoutModal.vue';
import requestPayouts from '@affiliate/mixins/requestPayouts';
import NavLayout from '@hypershapesAffiliate/layouts/NavLayout.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: {
    CreatePayoutRequestModal,
    FailRequestPayoutModal,
  },
  mixins: [requestPayouts],
  layout: NavLayout,
  props: {
    affiliateUser: {
      type: Object,
      default: () => ({}),
    },
    payouts: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      createPayoutRequestModalId: 'create-payout-request-modal',
      createPayoutRequestModal: null,

      failRequestPayoutModalId: 'fail-payout-request-modal',
      failRequestPayoutModal: null,
      failureReason: '',
    };
  },
  computed: {
    tableHeaders() {
      return [
        {
          name: 'Date',
          key: 'date',
        },
        {
          name: `Amount (${this.defaultCurrency})`,
          key: 'amount',
        },
        {
          name: 'Status',
          key: 'status',
          custom: true,
        },
      ];
    },

    tableData() {
      return this.payouts.map((p) => ({
        id: p.id,
        date: p.datetime,
        status: p.status,
        amount: p.convertedAmount.toFixed(2),
      }));
    },
  },

  methods: {
    showModal() {
      if (this.availableCommissions < this.minimumPayout) {
        this.failureReason = `Your available approved commissions must more than ${
          this.defaultCurrency
        } ${parseFloat(this.minimumPayout, 10).toFixed(
          2
        )} in order to create a payout request. Currently ${
          this.defaultCurrency
        } ${parseFloat(this.availableCommissions, 10).toFixed(2)}.`;

        bootstrap?.then(({ Modal }) => {
          this.failRequestPayoutModal = new Modal(
            document.getElementById(this.failRequestPayoutModalId)
          );
          this.failRequestPayoutModal.show();
        });
        return;
      }

      // if (!this.member?.paypal_email) {
      //   this.failureReason =
      //     'Please provide a valid PayPal email address in order to create a payout request';

      //   bootstrap?.then(({ Modal }) => {
      //     this.failRequestPayoutModal = new Modal(
      //       document.getElementById(this.failRequestPayoutModalId)
      //     );
      //     this.failRequestPayoutModal.show();
      //   });
      //   return;
      // }
      bootstrap?.then(({ Modal }) => {
        this.createPayoutRequestModal = new Modal(
          document.getElementById(this.createPayoutRequestModalId)
        );
        this.createPayoutRequestModal.show();
      });
    },

    hideCreatePayoutRequestModal() {
      this.createPayoutRequestModal.hide();
    },
    hideFailRequestPayoutModal() {
      this.failRequestPayoutModal.hide();
    },
  },
};
</script>

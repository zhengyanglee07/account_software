<template>
  <BaseDatatable
    no-delete-action
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableData"
    title="affiliate"
    @selectAll="handleSelectAllParticipants"
  >
    <template #action-button>
      <BaseButton
        id="dropdownMenuButton"
        has-edit-icon
        has-dropdown-icon
        type="secondary"
        data-disabled="true"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
      >
        Bulk Edit
      </BaseButton>

      <BaseDropdown id="dropdownMenuButton">
        <BaseDropdownOption
          text="Add Group"
          data-bs-toggle="modal"
          :data-bs-target="`#${bulkEditMembersAffiliateGroupsModalId}`"
          @click="$emit('option:selected', false)"
        />
        <BaseDropdownOption
          text="Remove Group"
          data-bs-toggle="modal"
          :data-bs-target="`#${bulkEditMembersAffiliateGroupsModalId}`"
          @click="$emit('option:selected', true)"
        />
      </BaseDropdown>
    </template>
    <template #cell-checkbox="{ row: { id } }">
      <BaseFormCheckBox
        v-model="checkedParticipants"
        :value="id"
        @change="handleCheckParticipant"
      />
    </template>
    <template #cell-formattedDiscountCodes="{ row: item }">
      <BaseBadge
        :text="item.formattedDiscountCodes"
        :type="item.formattedDiscountCodes === 'None' ? 'warning' : 'primary'"
        @change="handleCheckParticipant"
      />
    </template>

    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="View Profile"
        :link="`/affiliate/members/${item.ref}`"
      />
      <BaseDropdownOption
        text="Disapprove"
        @click="disapproveParticipant(item.id)"
      />
      <BaseDropdownOption
        text="Assign Promo Code"
        @click="showAssignPromoCodeModal(item)"
      />
    </template>
  </BaseDatatable>

  <AssignPromoCodeModal
    :modal-id="assignPromoCodeModalId"
    :available-promotions="[
      ...availablePromotionsForSelectedParticipant,
      availablePromotions.find(
        (el) => selectedParticipant?.formattedDiscountCodes === el.discount_code
      ),
    ]"
    :participant="selectedParticipant"
    :selected-promo="
      availablePromotions.find(
        (el) => selectedParticipant?.formattedDiscountCodes === el.discount_code
      )
    "
    @update-participants="emitParticipants"
  />
</template>

<script>
import AssignPromoCodeModal from '@affiliate/components/AssignPromoCodeModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'AffiliateApprovedDatatable',
  components: { AssignPromoCodeModal },
  props: {
    participants: {
      type: Array,
      required: true,
    },
    defaultCurrency: {
      type: String,
      default: '$',
    },
    availablePromotions: {
      type: Array,
      default: () => [],
    },
    checkedParticipantIds: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      selectedParticipant: null,
      assignPromoCodeModalId: 'assign-promo-code-modal',
      assignPromoCodeModal: null,
      bulkEditMembersAffiliateGroupsModalId: 'bulk-edit-mems-aff-groups-modal',
      checkedParticipants: [],
    };
  },
  computed: {
    displayCurrency() {
      return this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency;
    },

    tableHeaders() {
      return [
        { key: 'checkbox', sortable: false },
        { name: 'Name', key: 'name' },
        {
          name: `Email Address`,
          key: 'email',
        },
        {
          name: `Total Commissions (${this.displayCurrency})`,
          key: 'totalCommissions',
        },
        {
          name: `Pending Payouts  (${this.displayCurrency})`,
          key: 'pendingPayouts',
        },
        {
          name: 'No. of Orders',
          key: 'numOfOrders',
        },
        {
          name: `Total Sales (${this.displayCurrency})`,
          key: 'totalSales',
        },
        {
          name: 'Promo Code',
          key: 'formattedDiscountCodes',
          custom: true,
        },
      ];
    },

    tableData() {
      return this.participants.map((p) => ({
        id: p.id,
        name: `${p.member.first_name || ''} ${p.member.last_name || ''}`,
        email: p.member.email,
        totalCommissions: p.total_commissions.toFixed(2),
        pendingPayouts: p.pending_payouts.toFixed(2),
        numOfOrders: p.order_counts,
        totalSales: p.total_sales.toFixed(2),
        discountCodes: p.discount_codes,
        formattedDiscountCodes: p.discount_codes.join(', ') || 'None',
        ref: p.member.referral_identifier,
        editLink: `/affiliate/members/${p.member.referral_identifier}`,
      }));
    },

    availablePromotionsForSelectedParticipant() {
      const allUsedCodes = this.participants.flatMap(
        (participant) => participant.discount_codes
      );

      return this.availablePromotions.filter(
        (p) => !allUsedCodes.includes(p.discount_code)
      );
    },
  },
  watch: {
    checkedParticipantIds: {
      deep: true,
      handler(newVal) {
        this.checkedParticipants = newVal;
      },
    },
  },
  methods: {
    handleSelectAllParticipants(e) {
      const checked = e;

      if (checked) {
        this.emitCheckedParticipants(this.participants.map((p) => p.id));
        return;
      }

      this.emitCheckedParticipants([]);
    },

    handleCheckParticipant(e) {
      const { checked } = e.target;
      if (!checked)
        document.getElementById('select-all-checkbox').checked = false;
      const { checkedParticipants } = this;
      this.emitCheckedParticipants([...checkedParticipants]);
    },

    emitCheckedParticipants(ids) {
      this.$emit('update-checked', ids);
    },

    async disapproveParticipant(participantId) {
      const disapprovedStatus = {
        status: 'disapproved',
      };

      try {
        this.$axios.put('/affiliate/members/participant', {
          participantId,
          ...disapprovedStatus,
        });

        this.$toast.success('Success', 'Successfully disapprove affiliate');
        this.$emit('update-participants', participantId, disapprovedStatus);
      } catch (err) {
        this.$toast.error('Error', 'Failed to disapprove affiliate');
      }
    },

    emitParticipants(id, fields) {
      this.$emit('update-participants', id, fields);
    },
    showAssignPromoCodeModal(participant) {
      this.selectedParticipant = participant;
      new Modal(document.getElementById('assign-promo-code-modal')).show();
    },
  },
};
</script>

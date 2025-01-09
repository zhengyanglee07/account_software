<template>
  <BaseTab :tabs="tabs">
    <template #tab-approved>
      <AffiliateApprovedDatatable
        :checked-participant-ids="checkedParticipantIds"
        :participants="approvedMembers"
        :available-promotions="availablePromotions"
        :default-currency="defaultCurrency"
        @update-participants="updateParticipants"
        @update-checked="handleUpdateChecked"
        @option:selected="
          $nextTick(() => {
            isBulkEditRemove = $event;
          })
        "
      />
    </template>
    <template #tab-disapproved>
      <AffiliatePendingOrDisapprovedDatatable
        :participants="disapprovedMembers"
        type="disapproved"
        @update-participants="updateParticipants"
      />
    </template>
    <template #tab-pending>
      <AffiliatePendingOrDisapprovedDatatable
        :participants="pendingMembers"
        type="pending"
        @update-participants="updateParticipants"
      />
    </template>
  </BaseTab>

  <BulkEditMembersAffiliateGroupsModal
    :modal-id="bulkEditMembersAffiliateGroupsModalId"
    :checked-participant-ids="checkedParticipantIds"
    :groups="localGroups"
    :is-remove="isBulkEditRemove"
    @hide="hideBulkEditModal"
    @push-group="handlePushNewGroup"
    @update-checked="handleUpdateChecked"
  />
</template>

<script>
import AffiliateApprovedDatatable from '@affiliate/components/AffiliateApprovedDatatable.vue';
import AffiliatePendingOrDisapprovedDatatable from '@affiliate/components/AffiliatePendingOrDisapprovedDatatable.vue';
import BulkEditMembersAffiliateGroupsModal from '@affiliate/components/BulkEditMembersAffiliateGroupsModal.vue';
import { Modal } from 'bootstrap';

export default {
  components: {
    BulkEditMembersAffiliateGroupsModal,
    AffiliatePendingOrDisapprovedDatatable,
    AffiliateApprovedDatatable,
  },
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
    groups: {
      type: Array,
      required: true,
    },
    totalPendingAffiliateMember: {
      type: Number,
      default: () => 0,
    },
  },
  data() {
    return {
      localParticipants: [],
      localGroups: [],
      checkedParticipantIds: [],

      bulkEditMembersAffiliateGroupsModalId: 'bulk-edit-mems-aff-groups-modal',
      isBulkEditRemove: false,
      tabs: [
        { label: 'Approved', target: 'approved' },
        { label: 'Disapproved', target: 'disapproved' },
        {
          label: `Pending ${this.totalPendingAffiliateMember}`,
          target: 'pending',
        },
      ],
    };
  },
  computed: {
    approvedMembers() {
      return this.localParticipants.filter(
        (p) => p.status.toLowerCase() === 'approved'
      );
    },
    disapprovedMembers() {
      return this.localParticipants.filter(
        (p) => p.status.toLowerCase() === 'disapproved'
      );
    },
    pendingMembers() {
      return this.localParticipants.filter(
        (p) => p.status.toLowerCase() === 'pending'
      );
    },
  },
  mounted() {
    this.localParticipants = [...this.participants];
    this.localGroups = [...this.groups];
  },
  methods: {
    hideBulkEditModal() {
      this.$nextTick(() => {
        Modal.getInstance(
          document.getElementById(this.bulkEditMembersAffiliateGroupsModalId)
        ).hide();
        document.getElementById('select-all-checkbox').checked = false; // uncheck select all checkbox
      });
    },
    handleUpdateChecked(participantIds) {
      this.checkedParticipantIds = [...participantIds];
    },

    handlePushNewGroup(group) {
      this.localGroups = [...this.localGroups, group];
    },

    /**
     *
     * @param {object} participantId
     * @param {object} fields
     */
    updateParticipants(participantId, fields) {
      this.localParticipants = this.localParticipants.map((p) => {
        if (p.id !== participantId) {
          return p;
        }

        return {
          ...p,
          ...fields,
        };
      });
    },
  },
};
</script>

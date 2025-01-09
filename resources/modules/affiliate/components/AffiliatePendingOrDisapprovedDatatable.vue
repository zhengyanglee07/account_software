<template>
  <BaseDatatable
    no-delete-action
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableData"
    :title="tableData?.length ? 'affiliate' : type + ' affiliate'"
    :custom-description="`There are no ${type} affiliates.`"
  >
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="View Profile"
        :link="`/affiliate/members/${item.ref}`"
      />
      <BaseDropdownOption
        text="Approve"
        @click="approveParticipant(item.id)"
      />
    </template>
  </BaseDatatable>
</template>

<script>
export default {
  name: 'AffiliatePendingOrDisapprovedDatatable',
  props: {
    participants: {
      type: Array,
      required: true,
    },
    type: {
      type: String,
      default: () => '',
    },
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Name', key: 'name' },
        {
          name: `Email Address`,
          key: 'email',
        },

        // TODO: any extra input fields
        // TODO: remember to add program field if multiple programs is enabled
      ],
      bulkEditMembersAffiliateGroupsModalId: 'bulk-edit-mems-aff-groups-modal',
    };
  },
  computed: {
    tableData() {
      return this.participants.map((p) => ({
        id: p.id,
        name: `${p.member.first_name || ''} ${p.member.last_name || ''}`,
        email: p.member.email,
        ref: p.member.referral_identifier,
        editLink: `/affiliate/members/${p.member.referral_identifier}`,
      }));
    },
  },
  methods: {
    async approveParticipant(participantId) {
      const approvedStatus = {
        status: 'approved',
      };

      try {
        this.$axios
          .put('/affiliate/members/participant', {
            participantId,
            ...approvedStatus,
          })
          .then(async () => {
            await this.$inertia.visit('/affiliate/members', { replace: true });
            this.$toast.success('Success', 'Successfully approve affiliate');
            // this.$emit('update-participants', participantId, approvedStatus);
          });
      } catch (err) {
        this.$toast.error('Error', 'Failed to approve affiliate');
      }
    },
  },
};
</script>

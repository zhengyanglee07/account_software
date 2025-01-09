<template>
  <BaseDatatable
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableData"
    title="group"
    @delete="deleteMemberGroup"
  >
    <template #action-button>
      <BaseButton
        has-add-icon
        data-bs-toggle="modal"
        :data-bs-target="`#${createMemberGroupModalId}`"
      >
        Add Affiliate Group
      </BaseButton>
    </template>

    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        @click="showUpdateMemberGroupModal(item)"
      />
    </template>
  </BaseDatatable>

  <CreateAffiliateMemberGroupModal
    :modal-id="createMemberGroupModalId"
    @create="createNewGroup"
    @hide="hideCreateMemberGroupModal"
  />
  <UpdateAffiliateMemberGroupModal
    :modal-id="updateMemberGroupModalId"
    :group="selectedGroup"
    @update="updateMemberGroup"
    @hide="hideUpdateMemberGroupModal"
  />
</template>

<script>
import shortid from 'shortid';
import { Modal } from 'bootstrap';
import CreateAffiliateMemberGroupModal from '@affiliate/components/CreateAffiliateMemberGroupModal.vue';
import UpdateAffiliateMemberGroupModal from '@affiliate/components/UpdateAffiliateMemberGroupModal.vue';

export default {
  components: {
    UpdateAffiliateMemberGroupModal,
    CreateAffiliateMemberGroupModal,
  },
  props: {
    groups: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: `Name`,
          key: 'title',
        },
        {
          name: `Affiliates`,
          key: 'affiliates',
        },
      ],
      localGroups: [],

      createMemberGroupModal: null,
      createMemberGroupModalId: 'create-affiliate-member-group-modal',
      updateMemberGroupModal: null,
      updateMemberGroupModalId: 'update-affiliate-member-group-modal',
      deleteMemberGroupModal: null,
      deleteMemberGroupModalId: 'delete-member-group-modal',
      selectedGroup: null,
    };
  },
  computed: {
    tableData() {
      return this.localGroups.map((g) => ({
        id: g.id,
        title: g.title,
        affiliates: g.participants.length,
      }));
    },
  },
  mounted() {
    this.localGroups = [...this.groups];
  },
  methods: {
    showUpdateMemberGroupModal(group) {
      this.selectedGroup = group;
      this.updateMemberGroupModal = new Modal(
        document.getElementById(this.updateMemberGroupModalId)
      );
      this.updateMemberGroupModal.show();
    },

    hideUpdateMemberGroupModal() {
      this.updateMemberGroupModal.hide();
    },

    hideCreateMemberGroupModal() {
      Modal.getInstance(
        document.getElementById('create-affiliate-member-group-modal')
      ).hide();
    },

    createNewGroup(group) {
      this.localGroups = [
        ...this.localGroups,
        {
          ...group,
          id: shortid.generate(),
          participants: [], // just a dummy value
        },
      ];
    },

    updateMemberGroup(updatedGroup) {
      this.localGroups = this.localGroups.map((g) => {
        if (g.id !== updatedGroup.id) {
          return g;
        }

        return {
          ...g,
          title: updatedGroup.title,
        };
      });
    },

    async deleteMemberGroup(id) {
      // const id = this.selectedGroup?.id;

      if (!id) {
        this.$toast.error('Error', 'Nothing to delete');
        return;
      }

      try {
        await this.$axios.delete(`/affiliate/members/groups/${id}`);

        this.$toast.success('Success', 'Successfully deleted group');

        this.localGroups = this.localGroups.filter((g) => g.id !== id);
      } catch (err) {
        this.$toast.error('Error', 'Failed to delete group');
      }
    },
  },
};
</script>

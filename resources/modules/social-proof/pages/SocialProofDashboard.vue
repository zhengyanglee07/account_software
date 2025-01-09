<template>
  <BaseDatatable
    title="social proof"
    :table-headers="tableHeaders"
    :table-datas="tableNotificationsData"
    @delete="deleteSocialProof"
  >
    <template #cell-switch="{ row: { id, is_enabled } }">
      <BaseFormGroup>
        <BaseFormSwitch
          :model-value="!!is_enabled"
          @input="enableSocialProof(id)"
        />
      </BaseFormGroup>
    </template>
    <template #action-button>
      <BaseButton
        id="add-social-proof-button"
        data-bs-toggle="modal"
        data-bs-target="#add-social-proof-notification-modal"
      >
        Add Social Proof
      </BaseButton>
    </template>
  </BaseDatatable>
  <AddSocialProofNotificationModal />
</template>

<script>
import AddSocialProofNotificationModal from '@socialProof/components/AddSocialProofNotificationModal.vue';

export default {
  components: {
    AddSocialProofNotificationModal,
  },

  props: {
    notifications: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      notificationsData: [],
      tableHeaders: [
        {
          name: 'Name',
          key: 'notificationName',
        },
        { name: 'Created At', key: 'createdTime' },
        { name: 'Enable/Disable', key: 'switch', custom: true },
      ],
    };
  },

  computed: {
    tableNotificationsData() {
      return this.notificationsData.map((item) => ({
        id: item.id,
        notificationName: item.name,
        createdTime: item.convertTime,
        is_enabled: item.is_enabled,
        editLink: `/notification/edit/${item.reference_key}`,
      }));
    },
  },

  mounted() {
    this.notificationsData = this.notifications;
  },

  methods: {
    triggerErrorToast() {
      this.$toast.error(
        'Error',
        'Something went wrong. Please try again later.'
      );
    },

    deleteSocialProof(id) {
      axios
        .delete(`/notification/delete/${id}`)
        .then((response) => {
          // this.notificationsData = response.data.allNotifications;
          this.$inertia.visit('/marketing/social-proof', { replace: true });
          this.$toast.success('Success', response.data.message);
        })
        .catch((error) => {
          this.triggerErrorToast();
          throw new Error(error);
        });
    },

    enableSocialProof(id) {
      axios
        .post(`/notification/enable/${id}`)
        .then((response) => {
          this.$toast.success('Success', response.data.message);
        })
        .catch((error) => {
          this.triggerErrorToast();
          throw new Error(error);
        });
    },
  },
};
</script>

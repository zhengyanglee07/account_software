<template>
  <div style="margin-bottom: 20px">
    <Datatable
      :headers="tableHeaders"
      :datas="subscriptionData || []"
      without-header
      :has-search-bar="false"
      :column-sortable="false"
    >
      <template #buttonHeader>
        <th class="datatable-actions">
          Actions
        </th>
      </template>
      <template #buttonAction="scopedData">
        <td class="datatable-actions-button">
          <div style="font-size: 13px">
            <a
              class="datatableEditButton"
              title="Edit"
              data-bs-toggle="modal"
              data-bs-target="#add-subscription-modal"
              @click="editSubscription(scopedData.item)"
            >
              <i
                class="fas fa-pencil-alt"
                title="Edit"
              />
            </a>

            <a
              class="datatableEditButton"
              @click="triggerDeleteModal(scopedData.item.id)"
            >
              <i
                class="fas fa-trash-alt"
                title="Delete"
              />
            </a>
          </div>
        </td>
      </template>
    </Datatable>
    <DeleteConfirmationModal
      :modal-id="'subscription-delete-modal'"
      title="subscription"
      @cancel="closeDeleteModal"
      @save="deleteSubscription()"
    />
  </div>
</template>

<script>
/* eslint-disable no-unused-expressions */
import { mapState, mapMutations } from 'vuex';

export default {
  name: 'SubscriptionTable',
  props: {
    datas: Array,
  },

  computed: {
    ...mapState('product', ['savedSubscriptionArray']),
    subscriptionData() {
      return this.datas.map((data) => {
        return {
          id: data.id,
          displayName: data.display_name,
          frequency: `${data.interval_count} ${data.interval}`,
          type: data.type,
        };
      });
    },
  },
  data() {
    return {
      tableHeaders: [
        { text: 'Display Name', value: 'displayName', order: 0 },
        { text: 'Frequency  ', value: 'frequency', order: 0 },
        { text: 'Discount Type', value: 'type', order: 0 },
      ],
      selectedId: '',
      modalId: 'subscription-delete-modal',
    };
  },
  methods: {
    ...mapMutations('product', [
      'initialSubscriptionArray',
      'deleteSelectedSubscriptionOption',
    ]),
    editSubscription(subscription) {
      this.initialSubscriptionArray(subscription.id);
    },
    triggerDeleteModal(id) {
      this.selectedId = id;
      Number.isNaN(this.selectedId)
        ? this.deleteSubscription()
        : $('#subscription-delete-modal').modal('show');
    },
    deleteSubscription() {
      if (!Number.isNaN(this.selectedId)) {
        axios.get(`delete/subscription/${this.selectedId}`).then(({ data }) => {
          this.$toast.sucess('Sucessful', 'Successfully deleted');
        });
      }
      this.deleteSelectedSubscriptionOption(this.selectedId);
    },
    closeDeleteModal() {
      $('#subscription-delete-modal').modal('hide');
    },
  },
};
</script>

<style scoped>
:deep tbody tr:last-child {
  border-bottom-width: 0;
}
</style>

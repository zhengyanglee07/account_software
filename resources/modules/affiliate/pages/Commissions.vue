<template>
  <BaseDatatable
    no-edit-action
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="tableData"
    title="commission"
  >
    <template #cell-status="{ row: { status } }">
      <BaseBadge
        class="text-capitalize"
        :text="status"
        :type="
          status === 'approved' || status === 'pending'
            ? status === 'approved'
              ? 'success'
              : 'warning'
            : 'secondary'
        "
      />
    </template>

    <template #cell-order-number="{ row: { order } }">
      <Link
        style="text-decoration: none"
        :href="`/orders/details/${order.reference_key}`"
      >
        #{{ order.order_number }}
      </Link>
    </template>

    <!-- <template #Image>
      <th
        style="width: 150px"
        class="datatable-actions"
      >
        Order Number
      </th>
    </template>

    <template #ImageData="scopedData">
      <td>
        <Link
          class="h_link"
          style="text-decoration: none"
          :href="`/orders/details/${scopedData.item.order.reference_key}`"
        >
          #{{ scopedData.item.order.order_number }}
        </Link>
      </td>
    </template> -->

    <template #action-options="{ row: item }">
      <BaseDropdownOption
        :text="item.status !== 'approved' ? 'Approval' : 'Details'"
        data-bs-toggle="modal"
        :data-bs-target="`#${commissionModalId}`"
        @click="selectedCommission = item"
      />
    </template>

    <!-- <template #buttonAction="scopedData">
      <td class="datatable-actions-button">
        <div>
          <button
            type="button"
            class="dropdown-toggle-split datatableEditButton"
            style="border: none; color: black; background-color: transparent"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <i
              class="fas fa-ellipsis-h"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="More Changes"
            />
            <span class="sr-only">Toggle Dropdown</span>
          </button>

          <div class="dropdown-menu">
            <a
              class="dropdown-item delete"
              style="cursor: pointer"
              data-bs-toggle="modal"
              :data-bs-target="`#${commissionModalId}`"
              @click="selectedCommission = scopedData.item"
            >
              {{
                scopedData.item.status !== 'approved' ? 'Approve' : 'Details'
              }}
            </a>
          </div>
        </div>
      </td>
    </template> -->
  </BaseDatatable>

  <CommissionActionModal
    :modal-id="commissionModalId"
    :commission="selectedCommission"
    @approve-commission="handleApproveCommission"
    @disapprove-commission="handleDisapproveCommission"
  />
</template>

<script>
import CommissionActionModal from '@affiliate/components/CommissionActionModal.vue';
import { Modal } from 'bootstrap';

export default {
  components: { CommissionActionModal },
  props: {
    commissions: {
      type: Array,
      required: true,
    },
    defaultCurrency: {
      type: String,
      default: '$',
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: `Order Number`,
          key: 'order-number',
          custom: true,
        },
        {
          name: `Datetime`,
          key: 'datetime',
        },
        {
          name: `Order Total (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'order_total',
        },
        {
          name: `Commissions (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'converted_commission',
        },
        {
          name: `Campaign`,
          key: 'campaign',
        },
        {
          name: `Affiliates`,
          key: 'affiliates',
        },
        {
          name: `Status`,
          key: 'status',
          custom: true,
        },
      ],

      localCommissions: [],
      selectedCommission: {},
      commissionModalId: 'commission-action-modal',
    };
  },
  computed: {
    tableData() {
      return this.localCommissions.map((c) => ({
        id: c.id,
        datetime: c.datetime,
        order_total: c.orderTotal.toFixed(2),
        converted_commission: c.convertedCommission.toFixed(2),
        campaign: c?.campaign?.title || 'Deleted Campaign',
        affiliates: `${c.participant.member.first_name} ${c.participant.member.last_name}`,
        level: c.level,
        affiliate_email: c.affiliate_email,
        status: c.status,
        order: c.order,
      }));
    },
  },
  mounted() {
    this.localCommissions = [...this.commissions];
  },
  methods: {
    async handleDisapproveCommission() {
      await this.setCommissionStatus('disapproved');
    },

    async handleApproveCommission() {
      await this.setCommissionStatus('approved');
    },

    async setCommissionStatus(status) {
      const commissionId = this.selectedCommission?.id;

      try {
        await this.$axios.put('/affiliate/members/commission', {
          commissionId,
          status,
        });

        this.$toast.success('Success', `Successfully ${status} commission`);
        Modal.getInstance(
          document.getElementById('commission-action-modal')
        ).hide();
        this.localCommissions = this.localCommissions.map((c) => {
          if (c.id !== commissionId) {
            return c;
          }

          return {
            ...c,
            status,
          };
        });
        this.selectedCommission = {};
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to ${status} commission: ${err.response.data.message}`
        );
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BaseDatatable
    no-action
    :table-headers="tableHeaders"
    :table-datas="
      $page.props.member?.participant?.status === 'approved' ? tableData : []
    "
    :title="
      $page.props.member?.participant?.status !== 'approved'
        ? 'Be Patient'
        : 'conversion'
    "
    :custom-description="
      $page.props.member?.participant?.status !== 'approved'
        ? 'Store owner is reviewing your application. You will see the details after approved.'
        : 'No conversions available now'
    "
  >
    <template
      v-if="$page.props.member?.participant?.status !== 'approved'"
      #action-button
    >
      <p class="text-gray-400 fs-5 fw-bold mb-13">
        * We will notify you by email when you are approved.
      </p>
    </template>
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
  </BaseDatatable>
</template>

<script>
import AffiliateLayout from '@shared/layout/AffiliateLayout.vue';

export default {
  layout: AffiliateLayout,
  props: {
    commissions: {
      type: Array,
      default: () => [],
    },
    defaultCurrency: {
      type: String,
      default: '$',
    },
  },
  computed: {
    displayCurrency() {
      return this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency;
    },

    tableHeaders() {
      return [
        { name: 'DateTime', key: 'datetime' },
        { name: 'Campaign Name', key: 'campaignName' },
        // {
        //   text: `Order total (${this.displayCurrency})`,
        //   value: 'orderTotal',
        //   order: 0,
        // },
        {
          name: `Commissions (${this.displayCurrency})`,
          key: 'commission',
        },
        { name: 'Status', key: 'status', custom: true },
      ];
    },

    tableData() {
      return this.commissions.map((c) => ({
        datetime: c.datetime,
        campaignName: c?.campaign?.title ?? 'Inactive Campaign',
        orderTotal: c.orderTotal.toFixed(2),
        commission: c.convertedCommission.toFixed(2),
        status: c.status,
      }));
    },
  },
};
</script>

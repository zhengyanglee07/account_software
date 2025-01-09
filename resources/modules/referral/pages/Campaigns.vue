<template>
  <BaseDatatable
    title="referral campaign"
    :table-headers="tableHeaders"
    :table-datas="tableReferralCampaignsData"
    @delete="deleteReferralCampaign"
  >
    <template #action-button>
      <BaseButton
        id="add-referral-campaign-button"
        has-add-icon
        href="campaigns/create"
      >
        Add Campaign
      </BaseButton>
    </template>
    <template #cell-salesChannel="{ row: item }">
      {{
        item.salesChannel === 'Funnel'
          ? 'Funnel ' + item.funnelName
          : item.salesChannel
      }}
      <!-- <a
        v-if="item.domain"
        :href="item.domain"
        title="View live campaign"
        data-bs-toggle="custom-tooltip"
        data-bs-placement="right"
        target="_blank"
      >
        <i class="icon fa-solid fa-square-up-right mx-1" />
      </a> -->
    </template>
    <template #cell-status="{ row: { status } }">
      <BaseBadge
        class="text-capitalize"
        :text="
          typeof status !== 'string' ? (status ? 'Active' : 'Paused') : status
        "
        :type="
          typeof status !== 'string'
            ? status
              ? 'success'
              : 'warning'
            : 'secondary'
        "
      />
    </template>
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        v-if="typeof item.status !== 'string'"
        :text="item?.status ? 'Pause' : 'Resume'"
        @click="referralCampaignStatus(item?.id)"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import { Tooltip } from 'bootstrap';

export default {
  props: {
    referralCampaigns: {
      type: Array,
      default: () => [],
    },
    onlineStoreDomain: {
      type: String,
      default: () => null,
    },
    miniStoreDomain: {
      type: String,
      default: () => null,
    },
    // funnelDomain: {
    //   type: Array,
    //   default: () => null,
    // },
  },

  data() {
    return {
      referralCampaignsData: [],
      tableHeaders: [
        {
          name: 'Title',
          key: 'title',
        },
        { name: 'Sales Channel', key: 'salesChannel', custom: true },
        { name: 'Participants', key: 'participantCount' },
        { name: 'Status', key: 'status', custom: true },
      ],
    };
  },

  computed: {
    tableReferralCampaignsData() {
      return this.referralCampaignsData.map((item) => ({
        id: item.id,
        title: item.title,
        status: item.status,
        ref: item.reference_key,
        salesChannel: item.salesChannel,
        actions: item.actions,
        inviteeActions: item.inviteeActions,
        rewards: item.rewards,
        funnelName: item?.funnelName || null,
        editLink: `/referral/campaigns/${item.reference_key}`,
        // domain: this.getReferralPage(item?.salesChannel, item?.funnel_id),
        participantCount: item.participantCount,
      }));
    },
  },

  mounted() {
    this.referralCampaignsData = this.referralCampaigns;
    setTimeout(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="custom-tooltip"]')
      );

      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
      });
    }, 1000);
  },

  methods: {
    // getReferralPage(salesChannel, funnelId) {
    //   const selectedFunnel = this.funnelDomain.find(
    //     (el) => el.type_id === funnelId
    //   );
    //   const funnelPage = selectedFunnel ? selectedFunnel?.domain : null;
    //   switch (salesChannel) {
    //     case 'Funnel':
    //       return funnelPage;
    //     case 'Mini Store':
    //       return this.miniStoreDomain;
    //     default:
    //       return this.onlineStoreDomain;
    //   }
    // },
    triggerErrorToast() {
      this.$toast.error(
        'Error',
        'Something went wrong. Please try again later.'
      );
    },

    referralCampaignStatus(id) {
      axios
        .put(`/referral/status/${id}`)
        .then((response) => {
          this.$toast.success('Success', 'Referral campaign updated');
          this.$inertia.visit('/referral/campaigns', { replace: true });
        })
        .catch((error) => {
          this.triggerErrorToast();
          throw new Error(error);
        });
    },

    deleteReferralCampaign(id) {
      axios
        .delete(`/referral/delete/${id}`)
        .then((response) => {
          this.$toast.success('Success', 'Referral campaign deleted');
          this.$inertia.visit('/referral/campaigns', { replace: true });
        })
        .catch((error) => {
          this.triggerErrorToast();
          throw new Error(error);
        });
    },
  },
};
</script>
<style>
.icon:hover {
  color: #000;
}
</style>

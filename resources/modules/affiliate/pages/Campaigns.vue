<template>
  <p
    v-if="!dashboardDomain"
    class="alert alert-danger"
  >
    No affiliate member dashboard domain detected. Please set an affiliate
    member dashboard domain in
    <Link href="/domain/settings">
      Domain Settings
    </Link>
  </p>

  <p
    v-if="dashboardDomain && localCampaigns.length !== 0"
    class="alert alert-primary"
  >
    Your contacts can sign up as affiliate member here:
    <a
      id="clipboard-button-campaign"
      href="javascript:void(0)"
      class="campaign-copy-btn"
      :data-clipboard-text="affiliateSignUpLink"
      data-bs-toggle="custom-tooltip"
      data-bs-placement="bottom"
      title="Copied to Clipboard!"
      style="text-decoration: none"
    >
      {{ affiliateSignUpLink }}
      <i class="far fa-copy" />
    </a>
  </p>

  <BaseDatatable
    :table-headers="tableHeaders"
    :table-datas="tableData"
    title="campaign"
    @delete="deleteCampaign"
  >
    <template #action-button>
      <BaseButton
        id="add-affiliate-campaign-button"
        has-add-icon
        @click="redirectToCreateCampaignPage"
      >
        Add Campaign
      </BaseButton>
    </template>
    <template #cell-commission="{ row: { commission } }">
      <div
        v-for="(el, index) in commission"
        :key="index"
        :class="{
          'mb-6': commission.length > 1 && index !== commission.length - 1,
        }"
      >
        Condition group {{ index + 1 }} :
        <div
          v-for="(e, i) in el.split('level')"
          :key="i"
          class="mb-1"
        >
          <span v-if="i">level {{ e }}</span>
        </div>
      </div>
    </template>
  </BaseDatatable>
</template>

<script>
import Clipboard from 'clipboard';
import { Tooltip } from 'bootstrap';
import campaignsDT from '@affiliate/mixins/campaignsDT.js';

export default {
  mixins: [campaignsDT],
  props: {
    dashboardDomain: {
      type: String,
      default: () => '',
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: `Title`,
          key: 'title',
        },
        {
          name: `Products`,
          key: 'products',
        },
        {
          name: `Commission rate`,
          key: 'commission',
          width: '180px',
          custom: true,
        },
      ],
      localCampaigns: [],
      selectedCampaignId: null,
    };
  },
  computed: {
    tableData() {
      return this.localCampaigns.map((c) => ({
        id: c.id,
        reference_key: c.reference_key,
        title: c.title,
        products:
          c.productOrCategoryNames.length === 0
            ? 'All products'
            : c.productOrCategoryNames.join(', '),
        commission: this.generateCommissionRateDesc(c.condition_groups)
          .split('Condition group')
          .filter((e) => e !== 'Condition group' && e !== ''),
        editLink: this.getEditCampaignActionLink(c.reference_key),
      }));
    },

    affiliateSignUpLink() {
      return this.dashboardDomain
        ? encodeURI(`https://${this.dashboardDomain}/affiliates/signup`)
        : '';
    },
  },
  mounted() {
    this.localCampaigns = [...this.campaigns];

    setTimeout(() => {
      const cb = new Clipboard('.campaign-copy-btn');

      cb.on('success', (e) => {
        e.clearSelection();
      });
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('.campaign-copy-btn')
      );
      const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
        const tooltip = new Tooltip(tooltipTriggerEl, {
          trigger: 'click',
        });
        tooltipTriggerEl.onmouseleave = () => {
          tooltip.hide();
        };
        return tooltip;
      });
    }, 1000);
  },
  methods: {
    getEditCampaignActionLink(refKey) {
      return `/affiliate/members/campaigns/${refKey}?edit=1`;
    },

    redirectToCreateCampaignPage() {
      this.$inertia.visit('/affiliate/members/campaigns/create');
    },

    async deleteCampaign(id) {
      try {
        await this.$axios.delete(
          `/affiliate/members/campaigns/${id}`,
          this.$toast.success('Success', 'Campaign Deleted.')
        );

        this.localCampaigns = this.localCampaigns.filter((c) => c.id !== id);
      } catch (err) {
        this.$toast.error('Error', 'Failed to delete campaign');
      }
    },
  },
};
</script>

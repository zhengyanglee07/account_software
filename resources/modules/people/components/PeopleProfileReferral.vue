<template>
  <section v-if="referralCampaigns.length">
    <BaseFormGroup>
      <div
        class="d-flex border-transparent fs-5 fw-bolder"
        style="justify-content: space-between"
      >
        <span
          v-if="referByPeople?.refer_by"
          class="m-5"
        >
          Referred By
          <a :href="`/people/profile/${referByPeople.refKey}`">
            {{ referByPeople.name || referByPeople.email }}
          </a>
        </span>
        <div
          class="d-flex"
          style="justify-content: end"
        >
          <span class="m-5"> Campaign: </span>
          <BaseFormSelect
            v-model="selectedCampaignId"
            label-key="title"
            value-key="value"
            :options="campaignActions"
            style="width: 300px"
          />
        </div>
      </div>
    </BaseFormGroup>

    <BaseDatatable
      no-header
      no-action
      no-search
      :table-headers="tableHeaders"
      :table-datas="referArray"
      :custom-description="customDescription"
    />
  </section>
  <section v-else>
    <BaseDatatable
      no-header
      no-action
      no-search
      :table-headers="[]"
      :table-datas="[]"
      title="referral campaign"
    />
  </section>
</template>

<script>
export default {
  props: {
    referralCampaigns: {
      type: Array,
      default: () => [],
    },
    refer: {
      type: Array,
      default: () => [],
    },
    referBy: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        { name: 'Name', key: 'name' },
        { name: 'Email', key: 'email' },
        { name: 'Points', key: 'point' },
        { name: 'Sign Up Date & Time', key: 'joinedDate' },
      ],
      selectedCampaignId: null,
    };
  },

  computed: {
    customDescription() {
      if (
        !this.referBy
          ?.map((el) => el.referral_campaign_id)
          ?.includes(this.selectedCampaignId)
      )
        return "This person hasn't enrolled in this referral campaign yet.";

      return "This person hasn't referred anyone yet in this referral campaign.";
    },
    referArray() {
      return this.refer
        ?.map((el) => ({
          ...el,
          name: el.name || '-',
        }))
        .filter((e) => e.referral_campaign_id === this.selectedCampaignId);
    },
    campaignActions() {
      return this.referralCampaigns.map((campaign) => ({
        title: campaign.title,
        value: campaign.id,
      }));
    },
    referByPeople() {
      return this.referBy?.find(
        (el) => el.referral_campaign_id === this.selectedCampaignId
      );
    },
  },

  watch: {
    referralCampaigns: {
      deep: true,
      handler(newValue) {
        if (newValue.length) {
          this.selectedCampaignId = this.selectedCampaignId || newValue[0].id;
        }
      },
    },
  },

  mounted() {
    if (this.referralCampaigns.length) {
      const urlSearchParams = new URLSearchParams(window.location.search);
      const params = Object.fromEntries(urlSearchParams.entries());
      if (params.campaign) {
        this.selectedCampaignId = +params.campaign;
      }
      this.selectedCampaignId =
        this.selectedCampaignId || this.referralCampaigns[0].id;
    }
  },
};
</script>

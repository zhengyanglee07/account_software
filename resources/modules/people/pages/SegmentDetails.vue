<template>
  <BasePageLayout
    is-setting
    page-name="Back to Segments"
    back-to="/people/segments"
  >
    <h2 class="text-gray-900 fs-2 fw-bolder mb-4 mx-8">
      {{ segment.segmentName }}
    </h2>

    <PeopleConditionsPanel />
    <div class="my-5">
      <PeopleFilterConditionsButtons />

      <BaseButton @click="updateSegment">
        Update
      </BaseButton>
    </div>

    <PeoplePageDatatable
      :currency="currency"
      from="segment"
      :ref-key="segment.reference_key"
      :exchange-rate="exchangeRate"
    />

    <PeopleAddTagsModal modal-id="addTagModal" />
    <PeopleRemoveTagsModal modal-id="removeTagModal" />
  </BasePageLayout>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import PeopleConditionsPanel from '@people/components/PeopleConditionsPanel.vue';
import PeopleFilterConditionsButtons from '@people/components/PeopleFilterConditionsButtons.vue';
import PeoplePageDatatable from '@people/components/PeoplePageDatatable.vue';
import PeopleAddTagsModal from '@people/components/PeopleAddTagsModal.vue';
import PeopleRemoveTagsModal from '@people/components/PeopleRemoveTagsModal.vue';
import { validateConditionFilters } from '@people/lib/conditionFilters.js';

export default {
  components: {
    PeopleConditionsPanel,
    PeopleFilterConditionsButtons,
    PeoplePageDatatable,
    PeopleAddTagsModal,
    PeopleRemoveTagsModal,
  },
  props: {
    conditionJson: Object,
    dbConditionFilters: Array,
    contacts: Array,
    currency: String,
    tags: Array,
    usersProducts: Array,
    customFieldNames: Array,
    segment: Object,
    exchangeRate: [Number, String],
    allFunnels: {
      type: Array,
      default: () => [],
    },
    allEcommercePages: {
      type: Array,
      default: () => [],
    },
    allForms: {
      type: Array,
      default: () => [],
    },
  },

  computed: mapState('people', ['conditionFilters']),

  beforeMount() {
    // update data from db to Vuex
    this.updateCondition({ condition: this.conditionJson });
    this.updateContacts({ contacts: this.contacts });
    this.updateTags({ tags: this.tags });
    this.updateUsersProducts({ usersProducts: this.usersProducts });
    this.updateCustomFieldNames({ customFieldNames: this.customFieldNames });
    this.updateConditionFilters({ conditionFilters: this.dbConditionFilters });
    this.updateFunnelLists(this.allFunnels);
    this.updateEcommercePages(this.allEcommercePages);
    this.updateForms(this.allForms);

    // unlike All People page, view segment will show "There's no data found here" by default
    this.updateShowPeopleDesc(false);
  },

  methods: {
    ...mapMutations('people', [
      'updateShowPeopleDesc',
      'updateCondition',
      'updateContacts',
      'updateTags',
      'updateUsersProducts',
      'updateCustomFieldNames',
      'updateConditionFilters',
      'updateConditionFiltersShowErrors',
      'updateFunnelLists',
      'updateEcommercePages',
      'updateForms',
    ]),
    updateSegment() {
      const { conditionFilters } = this;

      if (!validateConditionFilters(conditionFilters)) {
        this.updateConditionFiltersShowErrors({ show: true });

        this.$toast.error(
          'Failed',
          'Failed to update segment. Please check your conditions.'
        );
        return;
      }

      this.$axios
        .put(`/segments/${this.segment.id}`, {
          conditionFilters,
        })
        .then(() => {
          this.$toast.success('Success', 'Successfully updated segment');
        })
        .catch(() => {
          this.$toast.error(
            'Error',
            'Failed to update segment. Unexpected error occurs.'
          );
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.back-to-segments-link {
  background: transparent;
  color: #202930;
  font-size: 16px;
}

.view-segment-div {
  width: 100%;
  max-width: 80vw;
  padding: 0;
  margin: 0 auto 1.5rem;
}

@media (max-width: $sm-display) {
  .view-segment-div {
    max-width: 100vw !important;
  }
}

.mt-3.d-flex {
  margin-bottom: 1rem !important;
}

.people__action-button__wrapper {
  padding: 12px 0 20px;
  display: flex;
}

@media (max-width: $sm-display) {
  .people__action-button__wrapper {
    flex-direction: column;
  }

  .primary-white-button {
    margin: 0 !important;
    padding: 6px 14px !important;
    width: 100%;
  }
}
</style>

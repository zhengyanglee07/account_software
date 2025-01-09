<template>
  <div
    v-for="(orCondition, orIdx) in conditionFilters"
    :key="orIdx"
  >
    <BaseCard>
      <BaseFormGroup
        v-if="orIdx == 0"
        label="Include people matching these filters"
      />
      <div
        v-for="(andCondition, andIdx) in orCondition"
        :key="andCondition.id"
      >
        <div class="d-flex">
          <ConditionFilterFirstSelect
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <AverageOrderValueConditionFilter
            v-if="andCondition.name === 'Average Order Value'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <MarketingEmailStatusConditionFilter
            v-if="andCondition.name === 'Marketing Email Status'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <TagsConditionFilter
            v-if="andCondition.name === 'Tags'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <OrdersConditionFilter
            v-if="andCondition.name === 'Orders'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <TotalSalesConditionFilter
            v-if="andCondition.name === 'Total Sales'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <PeopleProfileConditionFilter
            v-if="andCondition.name === 'People Profile'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <CustomFieldConditionFilter
            v-if="andCondition.name === 'Custom Field'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <PurchasesConditionFilter
            v-if="andCondition.name === 'Purchases'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <VisitActivityConditionFilter
            v-if="andCondition.name === 'Site Activity'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />
          <FormSubmissionFilter
            v-if="andCondition.name === 'Form Submission'"
            :condition-id="andCondition.id"
            :or-index="orIdx"
          />

          <div style="margin-left: auto; align-items: baseline">
            <BaseFormGroup>
              <BaseButton
                v-if="andIdx !== 0"
                type="close"
                aria-label="close"
                @click="
                  deleteANDConditionFilter({ id: andCondition.id, orIdx })
                "
              />
              <BaseButton
                v-else-if="orIdx !== 0"
                type="close"
                aria-label="close"
                @click="deleteORConditionFilter({ orIdx })"
              />
            </BaseFormGroup>
          </div>
        </div>

        <BaseButton
          v-if="andIdx === orCondition.length - 1"
          size="sm"
          has-add-icon
          @click="addNewCondition(orIdx)"
        >
          And
        </BaseButton>

        <BaseFormGroup
          v-else
          style="margin-left: 5px"
          label="AND"
        />
      </div>
    </BaseCard>

    <BaseFormGroup
      v-show="orIdx != conditionFilters.length - 1"
      id="ortext"
      style="margin: 20px 0 0 30px"
      label="OR"
    />
  </div>

  <div style="padding-top: 12px">
    <BaseButton
      type="light-primary"
      style="border: dotted; width: 100%"
      has-add-icon
      @click="addAnotherSetOfConditions"
    >
      Add Another set of Filter
    </BaseButton>
  </div>
</template>

<script>
import kebabCase from 'lodash/kebabCase';
import { mapState, mapMutations } from 'vuex';
import ConditionFilterFirstSelect from '@people/components/ConditionFilterFirstSelect.vue';
import AverageOrderValueConditionFilter from '@people/components/AverageOrderValueConditionFilter.vue';
import MarketingEmailStatusConditionFilter from '@people/components/MarketingEmailStatusConditionFilter.vue';
import TagsConditionFilter from '@people/components/TagsConditionFilter.vue';
import OrdersConditionFilter from '@people/components/OrdersConditionFilter.vue';
import TotalSalesConditionFilter from '@people/components/TotalSalesConditionFilter.vue';
import PeopleProfileConditionFilter from '@people/components/PeopleProfileConditionFilter.vue';
import CustomFieldConditionFilter from '@people/components/CustomFieldConditionFilter.vue';
import PurchasesConditionFilter from '@people/components/PurchasesConditionFilter.vue';
import VisitActivityConditionFilter from '@people/components/VisitActivityConditionFilter.vue';
import FormSubmissionFilter from '@people/components/FormSubmissionFilter.vue';

export default {
  name: 'PeopleConditionsPanel',
  components: {
    ConditionFilterFirstSelect,
    AverageOrderValueConditionFilter,
    MarketingEmailStatusConditionFilter,
    TagsConditionFilter,
    OrdersConditionFilter,
    TotalSalesConditionFilter,
    PeopleProfileConditionFilter,
    CustomFieldConditionFilter,
    PurchasesConditionFilter,
    VisitActivityConditionFilter,
    FormSubmissionFilter,
  },

  computed: mapState('people', ['condition', 'conditionFilters']),

  methods: {
    ...mapMutations('people', [
      'addNewANDConditionFilter',
      'addNewORConditionFilter',
      'deleteANDConditionFilter',
      'deleteORConditionFilter',
    ]),
    kebabCase,

    addNewCondition(orIdx) {
      const ANDConditionsCount = this.conditionFilters[orIdx].length;

      // Edit July 2020:
      // currently your boss only allows maximum 5 conditions.
      // If somehow your boss want to remove this limit then
      // just remove the if statement below
      if (ANDConditionsCount >= 5) {
        this.$toast.warning(
          'Warning',
          'You have reached a maximum of 5 conditions'
        );
        return;
      }

      this.addNewANDConditionFilter({ orIdx });
    },
    addAnotherSetOfConditions() {
      const ORConditionsCount = this.conditionFilters.length;

      // Edit July 2020:
      // like addNewCondition method above, you boss only want max 5 set
      if (ORConditionsCount === 5) {
        this.$toast.warning(
          'Warning',
          'You have reached a maximum 5 set of conditions'
        );
        return;
      }

      this.addNewORConditionFilter();
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="subConditionsKey"
        @change="updateStatusSubCondition"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(mesSub, index) in getConditionMarketingEmailStatusSub"
          :key="index"
          :value="mesSub"
        >
          {{ mesSub }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('subConditionsKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="subConditionsValue"
        @change="updateStatusSubCondition"
      >
        <option
          value=""
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(status, index) in marketingEmailStatuses"
          :key="index"
          :value="status.toLowerCase()"
        >
          {{ status }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('subConditionsValue')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>
  </ConditionFilterContainer>
</template>

<script>
import { mapGetters, mapMutations, mapState } from 'vuex';
import { required } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'MarketingEmailStatusConditionFilter',
  components: {
    ConditionFilterContainer,
  },
  mixins: [conditionFilterMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      subConditionsKey: '',
      subConditionsValue: '',
    };
  },
  validations: {
    subConditionsKey: {
      required,
    },
    subConditionsValue: {
      required,
    },
  },
  computed: {
    ...mapState('people', ['marketingEmailStatuses']),
    ...mapGetters('people', [
      'getConditionMarketingEmailStatusSub',
      'getTagNames',
      'getConditionFilterSubConditions',
    ]),
    subConditions() {
      return this.getConditionFilterSubConditions(
        this.conditionId,
        this.orIndex
      );
    },
    emptySubConditions() {
      return this.subConditions.length === 0;
    },
  },
  async mounted() {
    // load from db if present (e.g. segment condition)
    this.subConditionsKey = this.subConditions[0].key;
    this.subConditionsValue = this.subConditions[0].value;
  },
  methods: {
    ...mapMutations('people', [
      'updateANDConditionFilter',
      'updateConditionFilterSubCondition',
    ]),

    updateStatusSubCondition() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Marketing Email Status',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: this.subConditionsKey,
              value: this.subConditionsValue,
            },
          ],
        },
      });
    },
  },
};
</script>

<style scoped lang="scss"></style>

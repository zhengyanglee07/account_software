<template>
  <BaseFormGroup
    col="md-3"
    style="margin-right: 20px"
  >
    <BaseFormSelect v-model="selectedFirstConditionName">
      <option
        value=""
        :selected="!selectedFirstConditionName"
        disabled
      >
        Choose...
      </option>
      <option
        v-for="firstCondition in condition['firstCondition']"
        :key="firstCondition"
        :value="firstCondition"
        :selected="selectedFirstConditionName === firstCondition"
      >
        {{ firstCondition }}
      </option>
    </BaseFormSelect>
    <template
      v-if="showError('selectedFirstConditionName')"
      #error-message
    >
      Please select an option
    </template>
  </BaseFormGroup>
</template>

<script>
import { required } from '@vuelidate/validators';
import { mapGetters, mapState, mapMutations } from 'vuex';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'ConditionFilterFirstSelect',
  mixins: [conditionFilterMixin],
  validations: {
    selectedFirstConditionName: {
      required,
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  computed: {
    ...mapState('people', ['condition', 'conditionFilters']),
    ...mapGetters('people', ['getConditionFilter']),

    selectedFirstConditionName: {
      get() {
        const conditionFilter = this.getConditionFilter(
          this.conditionId,
          this.orIndex
        );

        return conditionFilter && conditionFilter.name;
      },
      set(value) {
        this.updateANDConditionFilter({
          id: this.conditionId,
          orIdx: this.orIndex,
          newANDCondition: {
            id: this.conditionId,
            name: value,
            error: true,

            subConditions: this.setInitialSubConditions(value),
          },
        });
      },
    },
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),
    setInitialSubConditions(filter) {
      return [
        // put one subConditions item here just to show condition filter, no practical usage
        {
          key: '',
          value: filter === 'Marketing Email Status' ? '' : [],
        },
      ];
    },
  },
};
</script>

<style scoped lang="scss"></style>

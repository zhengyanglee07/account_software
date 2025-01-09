<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="selectedPeopleProfileLabel"
        @change="updatePeopleProfileSubConditions"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(l, k) in peopleProfileLabels"
          :key="k"
          :value="k"
        >
          {{ l }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('selectedPeopleProfileLabel')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="selectedPeopleProfileSelect"
        @change="handleSelectedPeopleProfileSelectChange"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(v, i) in peopleProfileSelect"
          :key="i"
          :value="v"
        >
          {{ v }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('selectedPeopleProfileSelect')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showPeopleProfileTextInput"
      col="md-3"
    >
      <BaseFormInput
        v-model="peopleProfileSelectValue"
        :type="showPeopleProfileTextInput ? 'text' : 'number'"
        @input="updatePeopleProfileSubConditions"
      />
      <template
        v-if="showError('peopleProfileSelectValue')"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>
  </ConditionFilterContainer>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex';
import { required, requiredIf } from '@vuelidate/validators';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'PeopleProfileConditionFilter',
  components: { ConditionFilterContainer },
  mixins: [conditionFilterMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      showPeopleProfileTextInput: false,

      selectedPeopleProfileLabel: '', //  2nd drop
      selectedPeopleProfileSelect: '', // 3rd drop
      peopleProfileSelectValue: '', //    4th drop (if 3rd drop = is/is not)
    };
  },
  validations: {
    selectedPeopleProfileLabel: {
      required,
    },
    selectedPeopleProfileSelect: {
      required,
    },
    peopleProfileSelectValue: {
      required: requiredIf(function () {
        return (
          this.selectedPeopleProfileSelect === 'is' ||
          this.selectedPeopleProfileSelect === 'is not'
        );
      }),
    },
  },
  computed: {
    ...mapState('people', ['condition']),
    ...mapGetters('people', ['getConditionFilterSubConditions']),

    peopleProfileLabels() {
      return this.condition.peopleProfileLabels;
    },
    peopleProfileSelect() {
      return this.condition.peopleProfileSelect;
    },
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
  mounted() {
    this.selectedPeopleProfileLabel = this.subConditions[0].value;
    this.selectedPeopleProfileSelect =
      this.subConditions[1] && this.subConditions[1].key;
    this.peopleProfileSelectValue =
      this.subConditions[1] && this.subConditions[1].value;

    // remember to toggle last input for appropriate third select option value
    this.togglePeopleProfileSelectValueInput(this.selectedPeopleProfileSelect);
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    togglePeopleProfileSelectValueInput(value) {
      if (value === 'is' || value === 'is not') {
        this.showPeopleProfileTextInput = true;
        return;
      }

      this.showPeopleProfileTextInput = false;

      // "is set" and "is not set" select doesn't require any value
      this.peopleProfileSelectValue = '';
    },

    updatePeopleProfileSubConditions() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'People Profile',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: 'label',
              value: this.selectedPeopleProfileLabel,
            },
            {
              key: this.selectedPeopleProfileSelect,
              value: this.peopleProfileSelectValue,
            },
          ],
        },
      });
    },
    handleSelectedPeopleProfileSelectChange(e) {
      // show text input or hide it based on selected value
      this.togglePeopleProfileSelectValueInput(e.target.value);

      this.updatePeopleProfileSubConditions();
    },
  },
};
</script>

<style scoped lang="scss"></style>

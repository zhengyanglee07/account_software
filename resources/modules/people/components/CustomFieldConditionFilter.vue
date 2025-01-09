<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="customFieldName"
        @change="updateCustomFieldSubConditions"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(customFieldName, index) in customFieldNames"
          :key="index"
          :value="customFieldName"
        >
          {{ customFieldName }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('customFieldName')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="customFieldSelect"
        @change="handleCustomSelectChange"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(customFieldSelect, index) in conditionCustomFieldSelect"
          :key="index"
          :value="customFieldSelect"
        >
          {{ customFieldSelect }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('customFieldSelect')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showCustomSelectTextInput || showCustomSelectNumberInput"
      col="md-3"
    >
      <BaseFormInput
        v-model="customFieldSelectValue"
        :type="showCustomSelectTextInput ? 'text' : 'number'"
        @input="updateCustomFieldSubConditions"
      />
      <template
        v-if="showError('customFieldSelectValue')"
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
  name: 'CustomFieldConditionFilter',
  components: { ConditionFilterContainer },
  mixins: [conditionFilterMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      showCustomSelectNumberInput: false,
      showCustomSelectTextInput: false,

      customFieldName: '',
      customFieldSelect: '',
      customFieldSelectValue: '',
    };
  },
  validations: {
    customFieldName: {
      required,
    },
    customFieldSelect: {
      required,
    },
    customFieldSelectValue: {
      required: requiredIf(function () {
        return (
          this.customFieldSelect === 'is' ||
          this.customFieldSelect === 'is not' ||
          this.customFieldSelect === 'is less than or equal to' ||
          this.customFieldSelect === 'is greater than or equal to'
        );
      }),
    },
  },
  computed: {
    ...mapState('people', ['customFieldNames', 'condition']),
    ...mapGetters('people', ['getConditionFilterSubConditions']),
    conditionCustomFieldSelect() {
      return this.condition.customSelect;
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
    this.customFieldName = this.subConditions[0].value;
    this.customFieldSelect = this.subConditions[1] && this.subConditions[1].key;
    this.customFieldSelectValue =
      this.subConditions[1] && this.subConditions[1].value;

    // remember to toggle last input for appropriate third select option value
    this.toggleCustomSelectValueInput(this.customFieldSelect);
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    toggleCustomSelectValueInput(value) {
      if (value === 'is' || value === 'is not') {
        this.showCustomSelectTextInput = true;
        this.showCustomSelectNumberInput = false;
      } else if (
        value === 'is less than or equal to' ||
        value === 'is greater than or equal to'
      ) {
        this.showCustomSelectTextInput = false;
        this.showCustomSelectNumberInput = true;
      } else {
        this.showCustomSelectTextInput = false;
        this.showCustomSelectNumberInput = false;

        // "is set" and "is not set" select doesn't require any value
        this.customFieldSelectValue = '';
      }
    },

    updateCustomFieldSubConditions() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Custom Field',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: 'custom_field_name',
              value: this.customFieldName,
            },
            {
              key: this.customFieldSelect,
              value: this.customFieldSelectValue,
            },
          ],
        },
      });
    },
    handleCustomSelectChange(e) {
      // show text/number input or hide it based on selected value
      this.toggleCustomSelectValueInput(e.target.value);

      this.updateCustomFieldSubConditions();
    },
  },
};
</script>

<style scoped lang="scss"></style>

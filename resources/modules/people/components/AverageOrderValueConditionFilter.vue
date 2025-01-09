<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="aovSubKey"
        @change="updateAOVConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(a, i) in aovSub"
          :key="i"
          :value="a"
        >
          {{ a }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('aovSubKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="aovSubKey !== 'between'"
      col="md-3"
    >
      <BaseFormInput
        v-model="aovSubValue"
        type="number"
        @input="updateAOVConditionFilter"
      />
      <template
        v-if="showError('aovSubValue')"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <div
      v-else
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseFormInput
          v-model="aovBetweenFrom"
          type="number"
          @input="updateAOVConditionFilter"
        />
        <template
          v-if="showError('aovBetweenFrom')"
          #error-message
        >
          Field is required
        </template>
      </BaseFormGroup>

      <BaseFormGroup col="md-0">
        <BaseButton
          type="secondary"
          disabled
        >
          and
        </BaseButton>
      </BaseFormGroup>

      <BaseFormGroup col="md-4">
        <BaseFormInput
          v-model="aovBetweenTo"
          type="number"
          @input="updateAOVConditionFilter"
        />
        <template #error-message>
          {{
            showError('aovBetweenTo') && v$.aovBetweenTo.required.$invalid
              ? 'Field is required'
              : showError('aovBetweenTo') &&
                aovBetweenFrom &&
                v$.aovBetweenTo.minValue.$invalid
                ? `Value must be greater than ${aovBetweenFrom}`
                : ''
          }}
        </template>
      </BaseFormGroup>
    </div>

    <BaseFormGroup col="md-3">
      <!-- Note: timeFrameKey v-model here exists only for triggering validation -->
      <BaseFormSelect
        v-model="timeFrameKey"
        @change="handleTimeFrameChange(updateAOVConditionFilter)"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(time, index) in timeFrame"
          :key="index"
          :value="time"
        >
          {{ time }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('timeFrameKey')"
        error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showInTheLastDurationInput"
      col="md-3"
    >
      <BaseFormInput
        v-model="durationValue"
        type="number"
        :min="1"
        @input="updateAOVConditionFilter"
      />
      <template #error-message>
        {{
          conditionFiltersShowErrors && v$.durationValue.required.$invalid
            ? 'Field is required'
            : conditionFiltersShowErrors && v$.durationValue.minValue.$invalid
              ? 'This field should have a value of at least 1'
              : ''
        }}
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showInTheLastDurationInput"
      col="md-3"
    >
      <BaseFormSelect
        v-model="durationKey"
        @change="updateAOVConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(duration, index) in durationArr"
          :key="index"
          :value="duration"
        >
          {{ duration }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('durationKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <div
      v-if="showBetweenDurationInput"
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenFrom"
          :show-error="showError('durationBetweenFrom')"
          placeholder="From"
          @change="updateAOVConditionFilter"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationFromRequiredErr" />
        </template>
      </BaseFormGroup>

      <BaseFormGroup col="md-0">
        <BaseButton
          type="secondary"
          disabled
        >
          and
        </BaseButton>
      </BaseFormGroup>

      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenTo"
          :show-error="showError('durationBetweenTo')"
          placeholder="To"
          :disable-before="durationBetweenFrom"
          @change="updateAOVConditionFilter"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationToRequiredErr" />
        </template>
      </BaseFormGroup>
    </div>
  </ConditionFilterContainer>
</template>

<script>
/* eslint-disable indent */

import { mapGetters, mapState, mapMutations } from 'vuex';
import { helpers, required, requiredIf } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import timeframeMixin from '@people/mixins/timeframeMixin.js';
import useVuelidate from '@vuelidate/core';

// Why make custom func? Because the minValue validator is useless
function customMinVal(val) {
  return !helpers.req(val) || parseFloat(val) > parseFloat(this.aovBetweenFrom);
}

export default {
  name: 'AverageOrderValueConditionFilter',
  components: { ConditionFilterContainer },
  mixins: [conditionFilterMixin, timeframeMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      aovSubKey: '',
      aovSubValue: '',

      aovBetweenFrom: '',
      aovBetweenTo: '',
    };
  },
  validations() {
    return {
      aovSubKey: {
        required,
      },
      aovSubValue: {
        required: requiredIf(function () {
          return !this.subValueIsBetween;
        }),
      },
      aovBetweenFrom: {
        required: requiredIf(function () {
          return this.subValueIsBetween;
        }),
      },
      aovBetweenTo: {
        required: requiredIf(function () {
          return this.subValueIsBetween;
        }),
        minValue: customMinVal,
      },
      ...this.timeFrameValidation,
    };
  },
  computed: {
    ...mapState('people', ['condition']),
    ...mapGetters('people', ['getConditionFilterSubConditions']),
    subConditions() {
      return this.getConditionFilterSubConditions(
        this.conditionId,
        this.orIndex
      );
    },
    emptySubConditions() {
      return this.subConditions.length === 0;
    },
    aovSub() {
      return this.condition.aovSub;
    },

    subValueIsBetween() {
      return this.aovSubKey === 'between';
    },
  },

  // Note: ops below are used to show saved state in "View segment" feature,
  //       and it isn't used in "People" filter. Despite that, you still
  //       need to check for object property existence since this component
  //       is also used in "People" filter
  mounted() {
    this.aovSubKey = this.subConditions[0].key;
    this.aovSubValue = this.subConditions[0].value;

    if (this.subConditions[1]) {
      this.timeFrameKey = this.subConditions[1].key;
      this.toggleTimeFrameInput();
    }

    if (this.subConditions[2]) {
      this.durationKey = this.subConditions[2].key;
      this.durationValue = this.subConditions[2].value;
    }

    if (this.durationKey === 'custom') {
      this.durationBetweenFrom = this.durationValue.from;
      this.durationBetweenTo = this.durationValue.to;
    }
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    updateAOVConditionFilter() {
      // just don't let - sign and '.' show up, on input display, though there's
      // already have validation. Your boss want
      this.durationValue = Math.abs(Math.floor(this.durationValue)) || '';

      if (!this.subValueIsBetween) {
        this.aovBetweenFrom = '';
        this.aovBetweenTo = '';
      }

      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Average Order Value',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: this.aovSubKey,
              value: !this.subValueIsBetween
                ? this.aovSubValue
                : {
                    from: this.aovBetweenFrom,
                    to: this.aovBetweenTo,
                  },
            },
            this.timeframeSubCondition,
            this.durationSubCondition,
          ],
        },
      });
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="formId"
        @change="updateTagSubCondition"
      >
        <option
          value=""
          disabled
        >
          -- Which form --
        </option>
        <option
          v-for="(form, index) in forms"
          :key="index"
          :value="form.id"
        >
          {{ form.title }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('formId')"
        #error-message
      >
        Please select a form
      </template>
    </BaseFormGroup>

    <!-- timeframe dropdown (in the last/over all time) -->
    <BaseFormGroup
      v-if="formId"
      col="md-3"
    >
      <BaseFormSelect
        v-model="timeFrameKey"
        @change="handleTimeFrameChange(updateTagSubCondition)"
      >
        <option
          value=""
          selected
          disabled
        >
          -- Timeframe pattern --
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
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <!-- duration input -->
    <template v-if="timeFrameKey === 'in the last'">
      <BaseFormGroup col="md-3">
        <BaseFormInput
          v-model="durationValue"
          type="number"
          :min="1"
          @input="updateTagSubCondition"
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

      <BaseFormGroup col="md-3">
        <!-- duration dropdown (days/months/years) -->
        <BaseFormSelect
          v-model="durationKey"
          @change="updateTagSubCondition"
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
    </template>

    <div
      v-if="timeFrameKey === 'between'"
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenFrom"
          placeholder="From"
          no-short-cuts
          :show-error="showError('durationBetweenFrom')"
          @change="updateTagSubCondition"
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
          placeholder="To"
          no-short-cuts
          :disable-before="durationBetweenFrom"
          :show-error="showError('durationBetweenTo')"
          @change="updateTagSubCondition"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationToRequiredErr" />
        </template>
      </BaseFormGroup>
    </div>
  </ConditionFilterContainer>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex';
import { required } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import timeframeMixin from '@people/mixins/timeframeMixin.js';
import useVuelidate from '@vuelidate/core';

export default {
  components: {
    ConditionFilterContainer,
  },

  mixins: [conditionFilterMixin, timeframeMixin],

  setup() {
    return {
      v$: useVuelidate(),
    };
  },

  data() {
    return {
      formId: null,
    };
  },

  validations: {
    formId: {
      required,
    },
  },

  computed: {
    ...mapState('people', ['condition', 'forms']),

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
  },

  mounted() {
    this.formId = this.subConditions[0].value;
    console.log(this.subConditions, this.formId);

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
    ...mapMutations('people', [
      'updateANDConditionFilter',
      'updateConditionFilterSubCondition',
    ]),

    updateTagSubCondition() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Form Submission',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: 'form',
              value: this.formId,
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

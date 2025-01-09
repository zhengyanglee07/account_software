<template>
  <div>
    <BaseFormGroup label="When to trigger?">
      <div class="input-wrapper row">
        <BaseFormGroup
          label=""
          col="md-6"
        >
          <BaseFormInput
            v-model.number="executionTime.period"
            type="number"
            min="1"
            :disabled="executionTimeDirectionIsOn"
            @input="handleDateBasedPropertiesChange"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label=""
          col="md-3"
        >
          <BaseFormSelect
            v-model="executionTime.unit"
            :options="executionUnitOptions"
            label-key="name"
            value-key="value"
            @update:modelValue="handleDateBasedPropertiesChange"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label=""
          col="md-3"
        >
          <BaseFormSelect
            v-model="executionTime.direction"
            :options="['on', 'before', 'after']"
            @update:modelValue="handleDateBasedPropertiesChange"
          />
        </BaseFormGroup>
      </div>
    </BaseFormGroup>
    <BaseFormGroup label="What target?">
      <BaseFormSelect
        v-model="target"
        :options="targetOptions"
        label-key="name"
        value-key="value"
        @update:modelValue="handleDateBasedPropertiesChange"
      />
      <BaseFormGroup v-if="target === 'specific_date'">
        <BaseDatePicker
          v-model="targetSpecificDate"
          placeholder="Select date"
          :disabled-date="disabledDates"
          :editable="false"
        />
      </BaseFormGroup>
    </BaseFormGroup>
    <BaseFormGroup label="Repeat yearly?">
      <BaseFormSelect
        v-model="repeat_yearly"
        :options="[
          { name: 'Yes', value: 1 },
          { name: 'No', value: 0 },
        ]"
        label-key="name"
        value-key="value"
        @update:modelValue="handleDateBasedPropertiesChange"
      />
    </BaseFormGroup>
  </div>
</template>

<script>
const DEFAULT_EXECUTION = {
  period: 1,
  unit: 'day',
  direction: 'on',
};

export default {
  name: 'TriggerModalDateBasedContent',

  props: ['modelValue'],
  data() {
    return {
      executionTime: { ...DEFAULT_EXECUTION },
      executionUnitOptions: [
        {
          value: 'day',
          name: 'day(s)',
        },
        {
          value: 'week',
          name: 'week(s)',
        },
        {
          value: 'month',
          name: 'month(s)',
        },
      ],

      // <option value="people_profile_birthday">
      //               People Profile - Birthday
      //             </option>
      //             <option value="people_profile_acquisition_date">
      //               People Profile - Acquisition Date
      //             </option>
      //             <option value="people_profile_date_type_custom_field">
      //               People Profile - Date Type Custom Field
      //             </option>
      //             <option value="specific_date">
      //               Specific Date
      //             </option>
      target: 'people_profile_birthday',
      targetSpecificDate: null,
      targetOptions: [
        {
          value: 'people_profile_birthday',
          name: 'People Profile - Birthday',
        },
        {
          value: 'people_profile_acquisition_date',
          name: 'People Profile - Acquisition Date',
        },
        {
          value: 'people_profile_date_type_custom_field',
          name: 'People Profile - Date Type Custom Field',
        },
        {
          value: 'specific_date',
          name: 'Specific Date',
        },
      ],

      repeat_yearly: 0,
    };
  },
  computed: {
    executionTimeDirectionIsOn() {
      return this.executionTime.direction === 'on';
    },
  },
  watch: {
    targetSpecificDate(newVal) {
      if (newVal) this.handleDateBasedPropertiesChange(newVal);
    },
  },
  mounted() {
    if (!this.modelValue) {
      this.emitDateBasedProperties();
      return;
    }

    this.loadSavedProperties();
  },
  methods: {
    disabledDates(date) {
      return date.getTime() <= new Date().getTime();
    },
    loadSavedProperties() {
      const properties = this.modelValue;

      this.executionTime = {
        period: properties.execution_time_period,
        unit: properties.execution_time_unit,
        direction: properties.execution_time_direction,
      };

      this.target = properties.target;
      this.targetSpecificDate = properties.target_specific_date;
      this.repeat_yearly = properties.repeat_yearly;
    },
    handleDateBasedPropertiesChange(e) {
      // just to reset executionTime input/select values when direction is 'on'
      if (e.target?.value === 'on' || e === 'on') {
        this.executionTime = { ...DEFAULT_EXECUTION };
      }

      this.emitDateBasedProperties();
    },
    emitDateBasedProperties() {
      this.$emit('update:modelValue', {
        execution_time_period: this.executionTime.period,
        execution_time_unit: this.executionTime.unit,
        execution_time_direction: this.executionTime.direction,
        target: this.target,
        target_specific_date: this.targetSpecificDate,
        repeat_yearly: this.repeat_yearly,
      });
    },
  },
};
</script>

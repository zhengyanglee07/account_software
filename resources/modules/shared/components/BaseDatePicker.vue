<template>
  <DatePicker
    v-model:value="proxyValue"
    v-bind="{
      ...$props,
      separator: ' ~ ',
      confirm: !noShortCuts,
    }"
    :shortcuts="noShortCuts ? '' : shortcuts"
    @change="$emit('change', $event)"
    @clear="$emit('clear', $event)"
  >
    <template
      v-if="
        isValueInBadge && proxyValue instanceof Array && proxyValue.length > 0
      "
      #input
    >
      <div class="input-wrapper">
        <BaseBadge
          v-for="(date, index) in proxyValue"
          :key="index"
          :text="date"
          has-delete-button
          @click.stop="$emit('delete', index)"
        />
      </div>
    </template>
  </DatePicker>
</template>

<script setup>
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  /**
   * Determine the type of the picker
   */
  type: {
    type: String,
    default: 'date',
    validator: (value) => {
      return ['date', 'datetime', 'year', 'month', 'time'].includes(value);
    },
  },
  /**
   * Set the format of the date and time. Refer
   * https://github.com/mengxiong10/vue-datepicker-next#token
   */
  format: {
    type: String,
    default: 'YYYY-MM-DD',
  },
  /**
   * If true, need click the button to change value
   */
  confirm: {
    type: Boolean,
    default: true,
  },
  /**
   * If true, need click the button to change value
   */
  confirmText: {
    type: String,
    default: 'Apply',
  },
  /**
   * If false, the date cannot be cleared
   */
  clearable: {
    type: Boolean,
    default: true,
  },
  /**
   * If false, the date in the input cannot be edited
   */
  editable: {
    type: Boolean,
    default: true,
  },
  /**
   * If true, user will be able to select a start date and a end date
   */
  range: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine the format of the value returned by the date picker
   */
  valueType: {
    type: String,
    default: 'format',
  },
  /**
   * The default value of the date picker
   */
  defaultValue: {
    type: [String, Date],
    default: () => new Date(),
  },
  /**
   * The dates that matched the condition in the provided function will be disabled
   */
  disabledDate: {
    type: Function,
  },
  /**
   * The time that matched the condition in the provided function will be disabled
   */
  disabledTime: {
    type: Function,
  },
  /**
   * Placeholder for the date picker
   */
  placeholder: {
    type: String,
    default: '',
  },
  /**
   * Customize the styles of the picker
   */
  popupStyle: {
    type: Object,
    default: () => ({}),
  },
  /**
   * Determine whether allow the user to select seconds
   */
  showSecond: {
    type: Boolean,
    default: true,
  },
  /**
   * If false, the time picker will use 24-hours format
   */
  use12h: {
    type: Boolean,
    default: true,
  },
  /**
   * The options in the minutes picker will follow the interval here
   */
  minuteStep: {
    type: Number,
    default: null,
  },

  isValueInBadge: {
    type: Boolean,
    default: false,
  },
  noShortCuts: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'update:modelValue',
  /**
   * Fires when the value changes
   */
  'change',
  /**
   * Fires when the clear button is clicked
   */
  'clear',
  /**
   * Fires when the delete button is clicked
   */
  'delete',
]);

const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const shortcutOptions = [
  {
    day: 7,
    label: '7 days',
  },
  {
    day: 30,
    label: '30 days',
  },
  {
    day: 90,
    label: '3 months',
  },
  {
    day: 180,
    label: '6 months',
  },
  {
    day: 365,
    label: 'year',
  },
];

const shortcuts = shortcutOptions.map(({ day, label }) => {
  return {
    text: `Last ${label}`,
    onClick: () => {
      const date = new Date();
      date.setTime(date.getTime() - 3600 * 1000 * 24 * day);
      return [date, new Date()];
    },
  };
});
</script>

<style lang="scss">
.mx-datepicker {
  display: block;
  width: 100% !important;
}

.mx-input {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 1.1rem;
  font-weight: 500;
  line-height: 1.5;
  color: #5e6278;
  background-color: #ffffff;
  background-clip: padding-box;
  border: 1px solid #e4e6ef !important;
  appearance: none;
  border-radius: 0.475rem !important;
  box-shadow: none !important;
  height: 43px !important;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.input-wrapper {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 0.7rem;
  background: #fff;
  box-shadow: inset 1px 2px #00000005;
}

.mx-datepicker-sidebar {
  width: 120px;
  padding: 8px;
}

.mx-datepicker-sidebar + .mx-datepicker-content {
  margin-left: 120px;
}

.mx-btn-shortcut {
  padding: 2px 6px;
}
</style>

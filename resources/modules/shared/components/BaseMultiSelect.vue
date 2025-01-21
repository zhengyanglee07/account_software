<template>
  <VSelect
    v-model="proxyValue"
    class="base-multi-select"
    :options="options"
    :label="label"
    :multiple="multiple"
    :taggable="taggable"
    :push-tags="pushTags"
    :reduce="reduce"
    :filter="filter"
    @option:selected="(val) => $emit('option:selected', val)"
  >
    <template #selected-option="option">
      <slot
        name="selected-option"
        :option="option"
      />
    </template>

    <template #option="option">
      <!-- @slot To customize the presentation of dropdown options -->
      <slot
        name="option"
        :option="option"
      />
    </template>
    <template #no-options="{ search, searching, loading }">
      <!-- @slot To customize the message when no options are available -->
      <slot
        name="no-options"
        :search="search"
        :searching="searching"
        :loading="loading"
      />
    </template>
  </VSelect>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Array of options
   */
  options: {
    type: Array,
    default: () => [],
  },
  /**
   * If options are an array of objects, this
   * prop will be used to determine the key for the option label
   */
  label: {
    type: String,
    default: null,
  },
  /**
   * Set this to true will allow more than 1 values selected at the same time
   */
  multiple: {
    type: Boolean,
    default: false,
  },
  /**
   * Set this to true will allow new input that is not present in options
   */
  taggable: {
    type: Boolean,
    default: false,
  },
  /**
   * Set this to true will select the user-typed's new input and add new input into the option list
   */
  pushTags: {
    type: Boolean,
    default: false,
  },
  /**
   * Pass a function here if you want to get value of a specific key in object instead of returning whole object as value.
   * Refer https://vue-select.org/guide/values.html#returning-a-single-key-with-reduce
   */
  reduce: {
    type: Function,
  },
  filter: {
    type: Function,
  },
});

const emit = defineEmits([
  /**
   * Triggered when the selected value changes
   */
  'input',
  /**
   * Triggered when an option has been selected
   */
  'option:selected',
]);

const proxyValue = computed({
  get() {
    return props.value;
  },
  set(value) {
    emit('input', value);
  },
});
</script>

<style>
.base-multi-select .vs__dropdown-toggle {
  width: 100%;
  padding: 0.75rem 1rem;
  -moz-padding-start: calc(1rem - 3px);
  font-size: 1.1rem;
  font-weight: 500;
  line-height: 1.5;
  color: #5e6278;
  background-color: #ffffff;
  border: 1px solid #e4e6ef;
  border-radius: 0.475rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  appearance: none;
}

.base-multi-select .vs__search {
  margin: 0;
  display: none;
}

.base-multi-select .vs__actions {
  padding: 4px 2px 0 3px;
}

.base-multi-select .vs__open-indicator {
  transform: scale(0.8);
}

.base-multi-select .vs__selected {
  margin: 2px 4px;
}
</style>

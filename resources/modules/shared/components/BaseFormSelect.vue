<template>
  <select
    v-model="proxyValue"
    class="form-select"
    :name="name"
    :disabled="disabled"
    @change="emit('change', $event)"
  >
    <!-- @slot Slot for custom options -->
    <slot />
    <template v-if="options.length > 0">
      <option
        v-for="option in options"
        :key="option.index"
        :value="valueKey ? option[valueKey] : option"
      >
        {{ labelKey ? option[labelKey] : option }}
      </option>
    </template>
  </select>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * The options to display in the select. If
   * you want to use a specified key in object for label or value, use
   * default slot for options instead
   */
  options: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  labelKey: {
    type: String,
    default: null,
  },
  valueKey: {
    type: String,
    default: null,
  },
  name: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});
</script>

<style></style>

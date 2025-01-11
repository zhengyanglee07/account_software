<template>
  <div>
    <Multiselect
      v-model="value"
      :options="options"
      :placeholder="placeholder"
      :label="label"
      :track-by="trackBy"
      :custom-label="customLabel"
      :disabled="disabled || options.length === 0"
      :open-direction="openDirection"
    ></Multiselect>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import Multiselect from 'vue-multiselect';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  modelValue: {
    type: [String, Number],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: 'Select one',
  },
  labelKey: {
    type: String,
    default: 'name',
  },
  customLabel: {
    type: Function,
    default: () => {},
  },
  trackBy: {
    type: String,
    default: 'name',
  },
  sortBy: {
    type: String,
    default: 'name',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  openDirection: {
    type: String,
    default: 'auto',
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const value = computed({
  get: () => props.modelValue,
  set: (newValue) => {
    emit('update:modelValue', newValue);
    emit('change', newValue);
  },
});
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

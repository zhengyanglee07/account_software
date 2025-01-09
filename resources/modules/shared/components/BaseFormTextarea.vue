<template>
  <textarea
    :id="id"
    v-model="proxyValue"
    cols="60"
    :rows="rows"
    :disabled="disabled"
    :placeholder="placeholder"
    :maxlength="maxlength"
    :required="required"
    class="form-control"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    default: null,
  },
  modelValue: {
    type: String,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine the number of visible text lines for the textarea.
   */
  rows: {
    type: [Number, String],
    default: 5,
  },
  placeholder: {
    type: String,
    default: '',
  },
  /**
   * Determine the maximum number of characters that the user can enter. Default: no limit.
   */
  maxlength: {
    type: Number,
    default: null,
  },
  required: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});
</script>

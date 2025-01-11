<template>
  <div
    class="form-check form-check-custom form-check-solid form-check-sm mb-3"
    :class="{
      'form-check-inline me-3': inline,
    }"
  >
    <input
      :id="id"
      :value="value"
      :name="name"
      :disabled="disabled"
      type="checkbox"
      class="form-check-input"
      :checked="isChecked"
      @change="updateInput"
    >
    <label
      class="form-check-label"
      :for="id"
    >
      <slot />
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
  value: {
    type: [String, Boolean, Number],
    default: null,
  },
  name: {
    type: String,
    default: null,
  },
  modelValue: {
    type: [String, Boolean, Array],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  inline: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isChecked = computed(() => {
  if (props.modelValue instanceof Array) {
    return props.modelValue.includes(props.value);
  }
  return props.modelValue === props.value;
});

const updateInput = (event) => {
  const { checked } = event.target;
  if (props.modelValue instanceof Array) {
    const newValue = [...props.modelValue];
    if (checked) {
      newValue.push(props.value);
    } else {
      newValue.splice(newValue.indexOf(props.value), 1);
    }
    emit('update:modelValue', newValue);
  } else {
    emit('update:modelValue', !props.modelValue);
  }
  emit('change', event);
};
</script>

<style scoped>
.form-check-inline {
  display: inline-block !important;
}
</style>

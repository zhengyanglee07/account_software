<template>
  <div
    class="form-check form-check-custom form-check-solid form-check-sm mb-3"
    :class="{
      'form-check-inline me-3': inline,
    }"
  >
    <input
      :id="id"
      class="form-check-input"
      type="radio"
      :value="value"
      :name="name"
      :checked="modelValue == value"
      :disabled="disabled"
      @change="$emit('update:modelValue', $event.target.value)"
      @input="$emit('input', $event.target.value)"
    />
    <label class="form-check-label" :for="id">
      <!-- @slot Label for radio -->
      <slot />
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    default: null,
  },
  modelValue: {
    type: String,
    default: null,
  },
  name: {
    type: String,
    default: null,
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

const emit = defineEmits(['update:modelValue', 'input']);
</script>

<style scoped>
.form-check-inline {
  display: inline-block !important;
}
</style>

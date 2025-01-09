<template>
  <div class="form-check form-switch form-check-custom form-check-solid">
    <input
      :id="id"
      v-model="proxyValue"
      class="form-check-input"
      type="checkbox"
      :disabled="disabled"
    >
    <label class="form-check-label">
      <slot />
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  modelValue: {
    type: Boolean,
    default: false,
  },
  disabled: {
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

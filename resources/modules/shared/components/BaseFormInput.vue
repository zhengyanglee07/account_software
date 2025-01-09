<template>
  <div class="input-group">
    <span
      v-if="$slots.prepend"
      class="input-group-text"
      :style="{ background: color }"
    >
      <slot name="prepend" />
    </span>

    <div v-if="$slots.floatEnd" class="float-end">
      <slot name="floatEnd" />
    </div>

    <input
      v-model="value"
      class="form-control"
      :class="$slots.floatEnd ? 'form-float-end' : ''"
      v-bind="inputAttributes"
      @blur="emit('blur', $event)"
      @input="emit('input', $event)"
      @click="emit('click', $event)"
      @keyup="emit('keyup', $event)"
    />

    <span
      v-if="$slots.append"
      class="input-group-text"
      :style="{ background: color }"
    >
      <slot name="append" />
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'text',
  },
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: '',
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: [String, Number],
    default: '',
  },
  min: {
    type: [Number, String],
    default: null,
  },
  max: {
    type: [Number, String],
    default: null,
  },
  step: {
    type: [Number, String],
    default: null,
  },
  color: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'input', 'click']);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const inputAttributes = computed(() => {
  let attrs = {
    type: props.type,
    id: props.id,
    name: props.name,
    disabled: props.disabled,
    placeholder: props.placeholder,
    required: props.required,
    readonly: props.readonly,
  };

  if (props.type === 'number') {
    attrs = {
      ...attrs,
      min: props.min,
      max: props.max,
      step: props.step ?? 1,
    };
  } else if (props.type === 'text') {
    attrs.maxlength = props.max;
  }

  return attrs;
});
</script>

<style scoped>
.form-float-end {
  padding-right: 50px;
}
.float-end {
  position: absolute;
  right: 10px;
  top: 10px;
  z-index: 10;
}
</style>

<template>
  <div class="d-flex">
    <button v-for="(item, index) in list" :key="index" :disabled="disabled" :class="[
      `${['theme-primary', 'theme-secondary'].includes(type) ? '' : 'btn'
      } btn-${type} btn-${size} ${color ? `btn-color-${color}` : ''} ${item.value === modelValue ? 'opacity-100' : ''
      }`,
      `${list.length > 1 && index === 0 ? 'btn-first' : ''}`,
      `${list.length > 1 && list.length - 1 === index ? 'btn-last' : ''}`,
      {
        'p-0': type === 'link',
      },
      'opacity-50'
    ]" @click="handleOnClick(item)">
      {{ item.label }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  list: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: [String, Number],
    default: '',
    default: '',
  },
  type: {
    type: String,
    default: 'primary',
    validator: (value) => {
      return [
        'primary',
        'secondary',
        'info',
        'success',
        'warning',
        'danger',
        'close',
        'light',
        'light-primary',
        'link',
        'dark',
      ].includes(value);
    },
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'sm',
    validator: (value) => {
      return ['sm', 'md', 'lg'].includes(value);
    },
  },
  color: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const handleOnClick = (item) => {
  value.value = item.value;
};
</script>

<style scoped>
button {
  border-radius: 0;
  width: fit-content;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-first {
  border-top-left-radius: 0.475rem;
  border-bottom-left-radius: 0.475rem;
}

.btn-last {
  border-top-right-radius: 0.475rem;
  border-bottom-right-radius: 0.475rem;
}

.btn:deep(i) {
  line-height: 1.5;
  padding-right: 0;
}
</style>

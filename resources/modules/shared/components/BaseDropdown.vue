<template>
  <div
    class="dropdown-menu menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 py-4"
    :aria-labelledby="id"
    :class="dropdownWidthClass"
  >
    <!-- 
      @slot Slot for the content of dropdown. Usually 
      will be a list of BaseDropdownOption components here 
    -->
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Unique id for this dropdown
   */
  id: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: 'sm',
    validator: (value) => {
      return ['sm', 'md', 'lg'].includes(value);
    },
  },
});

const dropdownWidthClass = computed(() => {
  let width = null;
  switch (props.size) {
    case 'md':
      width = 250;
      break;
    case 'lg':
      width = 325;
      break;
    default:
      width = 125;
  }
  return `w-${width}px`;
});
</script>

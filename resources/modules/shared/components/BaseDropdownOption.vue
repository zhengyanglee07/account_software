<template>
  <div class="menu-item px-3">
    <Component
      :is="componentTag"
      :href="link"
      :target="isOpenInNewTab ? '_blank' : null"
      class="menu-link px-3 text-start"
      :disabled="disabled"
      @click="$emit('click')"
    >
      <div class="me-2">
        <slot />
      </div>
      {{ text }}
    </Component>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  link: {
    type: String,
    default: null,
  },
  text: {
    type: String,
    required: true,
  },
  isOpenInNewTab: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const componentTag = computed(() => {
  if (!props.link) return 'button';
  return props.isOpenInNewTab ? 'a' : 'Link';
});

defineEmits(['click']);
</script>

<style scoped>
button {
  background: white;
  border: 0;
  width: 100%;
}

.menu-link {
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0;
  flex: 0 0 100%;
  padding: 0.65rem 1rem;
  transition: none;
  outline: none !important;
  color: #7e8299;
  border-radius: 6px;
}

.menu-link:hover {
  transition: color 0.2s ease, background-color 0.2s ease;
  background-color: #f1faff;
  color: #009ef7;
}
</style>

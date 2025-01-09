<template>
  <span
    :id="id"
    data-bs-toggle="popover"
    data-bs-custom-class="custom-popover"
    :data-bs-content="popoverContentStr"
    class="h-100"
  >
    <slot />
  </span>

  <div v-show="false">
    <div ref="popoverTitle">
      <slot name="title" />
    </div>
    <div ref="popoverContent">
      <slot name="content" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import eventBus from '@shared/services/eventBus.js';

const props = defineProps({
  id: { type: String, default: 'popover' },
  title: { type: String, default: '&nbsp;' },
  placement: {
    type: String,
    default: 'auto',
    validator: (value) => {
      return ['auto', 'top', 'bottom', 'left', 'right'].includes(value);
    },
  },
  trigger: {
    type: String,
    default: 'hover focus',
    validator: (value) => {
      return ['manual', 'click', 'hover', 'focus', 'manual'].includes(value);
    },
  },
});

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const popoverTitle = ref(null);
const popoverContent = ref(null);
const popoverContentStr = computed(() => popoverContent.value?.innerHTML ?? '');

const popover = ref(null);
onMounted(() => {
  const popoverTriggerEl = document.querySelector(`#${props.id}`);

  const options = {
    html: true,
    placement: props.placement,
    trigger: props.trigger,
  };

  if (!props.id.includes('Builder')) options.container = popoverTriggerEl;
  if (popoverTitle.value) options.title = popoverTitle.value?.innerHTML ?? '';

  bootstrap?.then(({ Popover }) => {
    popover.value = new Popover(popoverTriggerEl, options);

    eventBus.$emit(`${props.id}`, popover.value);

    popoverTriggerEl.addEventListener('shown.bs.popover', () => {
      document.getElementsByClassName('clickable').forEach((element) => {
        element.addEventListener('click', () => {
          eventBus.$emit(`insertPlaceholder-${props.id}`, element.textContent);
          popover.value.hide();
        });
      });
    });
  });
});

const callPopoverMethod = (action) => {
  popover.value[action]();
};
</script>

<style>
.popover {
  position: absolute;
}
</style>

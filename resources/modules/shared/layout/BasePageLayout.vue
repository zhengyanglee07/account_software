<template>
  <template v-if="!isSetting">
    <div class="row">
      <div class="col-lg-12">
        <slot />
      </div>
      <div :class="leftTemplateClass">
        <!-- @slot Slot for the content at left container (ratio: 8/12) -->
        <slot name="left" />
      </div>
      <div :class="rightTemplateClass">
        <!-- @slot Slot for the content at right container (ratio: 4/12) -->
        <slot name="right" />
      </div>
    </div>
  </template>
  <template v-else>
    <div
      v-if="$slots['action-button']"
      class="text-end mb-4"
    >
      <!-- @slot Slot for the action button at the top right of setting page -->
      <slot name="action-button" />
    </div>
    <slot />
  </template>

  <div
    class="d-flex justify-content-end align-items-center mt-6 mb-10"
    :class="footerClass($slots.right)"
  >
    <!-- @slot Slot for the buttons at the end of page -->
    <slot name="footer" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import eventBus from '@services/eventBus.js';

const props = defineProps({
  /**
   * Set this to true if you use this layout in setting pages
   */
  isSetting: {
    type: Boolean,
    default: false,
  },
  /**
   * Page title to be showed at the header
   */
  pageName: {
    type: String,
    required: true,
  },
  /**
   * URL to redirect the user back when he clicks on back button in header
   */
  backTo: {
    type: String,
    required: true,
  },
  /**
   * Class for left template
   */
  leftTemplateClass: {
    type: String,
    required: false,
    default: 'col-lg-8',
  },
  /**
   * Class for right template
   */
  rightTemplateClass: {
    type: String,
    required: false,
    default: 'col-lg-4',
  },
});

const footerClass = (hasRightSlot) => {
  if (props.isSetting) {
    return '';
  }
  return hasRightSlot ? '' : 'col-lg-8';
};

eventBus.$emit('setup-page', {
  pageName: props.pageName,
  backTo: props.backTo,
});
</script>

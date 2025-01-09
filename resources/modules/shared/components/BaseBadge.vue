<template>
  <span
    class="badge me-2 mt-2 mb-0"
    :class="`badge-${hasDeleteButton ? 'secondary' : type}`"
  >
    <span>
      {{ text }}
    </span>
    <BaseButton
      v-if="hasDeleteButton"
      type="link"
      class="p-2 py-0 ms-2"
      @click="$emit('delete')"
    >
      <i class="fa-solid fa-xmark" />
    </BaseButton>
  </span>
</template>

<script setup>
defineProps({
  /**
   * The text to be displayed inside the badge
   */
  text: {
    type: String,
    required: true,
  },
  /**
   * Determine the background color of the badge
   */
  type: {
    type: String,
    default: 'secondary',
    validator: (value) => {
      return ['primary', 'secondary', 'success', 'info', 'warning'].includes(
        value
      );
    },
  },
  /**
   * Determine if the badge has a delete button
   */
  hasDeleteButton: {
    type: Boolean,
    default: false,
  },
});

defineEmits([
  /**
   * Fires when the delete button is clicked
   */
  'delete',
]);
</script>

<style scoped>
.badge {
  width: fit-content !important;
}

.badge * {
  line-height: 1;
}
</style>

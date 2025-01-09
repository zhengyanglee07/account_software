<template>
  <BaseModal
    title="Mark as paid"
    modal-id="markAsPaidModal"
  >
    <template #footer>
      <BaseButton
        :disabled="isLoading"
        @click="emits('submit')"
      >
        <i
          v-if="isLoading"
          class="fas fa-spinner fa-pulse"
        />
        <span v-else>Mark As Paid</span>
      </BaseButton>
    </template>

    <p>
      If you received payment for this order manually, mark this order as paid.
      This payment won't be captured by Hypershapes and you won't be able to
      refund it using Hypershapes.
    </p>
  </BaseModal>
</template>

<script setup>
defineProps({
  isLoading: { type: Boolean, default: false },
});
const emits = defineEmits(['submit']);
</script>

<style></style>

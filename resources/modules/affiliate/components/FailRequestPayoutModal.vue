<template>
  <BaseModal
    :modal-id="modalId"
    title="Sorry"
  >
    <slot name="reason">
      {{ reason }}
    </slot>
  </BaseModal>
</template>
<script>
export default {
  name: 'FailRequestPayoutModal',
  props: {
    modalId: {
      type: String,
      required: true,
    },
    reason: {
      type: String,
      required: true,
    },
  },
};
</script>

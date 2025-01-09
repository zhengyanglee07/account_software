<template>
  <!-- err msg placeholder for payment failed (stripe, fpx, etc) -->
  <div
    v-if="hasPaymentErr"
    class="alert alert-danger"
    role="alert"
    fade
  >
    <span id="payment-failed-msg">{{ paymentError }}</span>
    <button
      type="button"
      class="btn-close"
      aria-label="Close"
      style="float: right"
      @click="hasPaymentErr = false"
    />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useGetPaymentData } from '@onlineStore/hooks/usePayment.js';

const { paymentError } = useGetPaymentData();

const hasPaymentErr = ref(false);

onMounted(() => {
  hasPaymentErr.value = !!paymentError.value;
});
</script>

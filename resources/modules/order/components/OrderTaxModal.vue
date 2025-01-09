<template>
  <form @submit.prevent="submit">
    <BaseModal
      title="Add Tax"
      modal-id="orderTaxModal"
      no-dismiss
    >
      <template #footer>
        <BaseButton
          type="secondary"
          data-bs-dismiss="modal"
          @click="reset"
        >
          Delete
        </BaseButton>
        <BaseButton is-submit>
          Save
        </BaseButton>
      </template>

      <BaseFormGroup
        label="Tax Name"
        required
      >
        <BaseFormInput
          v-model="taxName"
          type="text"
          required
          placeholder="e.g. GST, VAT, Sales tax"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Tax Rate"
        required
      >
        <BaseFormInput
          v-model="taxRate"
          required
          min="0"
          max="100"
          type="number"
        >
          <template #append>
            %
          </template>
        </BaseFormInput>
      </BaseFormGroup>
    </BaseModal>
  </form>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  currency: { type: String, default: 'RM' },
});

const emits = defineEmits(['submit', 'delete']);

const taxName = ref(null);
const taxRate = ref(6);

const getTotalTax = (price) => price * (taxRate.value / 100);
const submit = () => {
  emits('submit', {
    name: taxName.value,
    rate: taxRate.value,
    getTotalTax,
  });
};

const reset = () => {
  emits('delete');
  taxName.value = null;
  taxRate.value = 6;
};
</script>

<style></style>

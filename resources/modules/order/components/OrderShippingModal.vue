<template>
  <form @submit.prevent="submit">
    <BaseModal
      title="Add Shipping"
      modal-id="orderShippingModal"
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
        label="Shipping Name"
        required
      >
        <BaseFormInput
          v-model="shippingName"
          type="text"
          required
          placeholder="e.g. Pos Laju (3-5 working days)"
        />
      </BaseFormGroup>

      <BaseFormGroup label="Shipping Rate">
        <BaseFormInput
          v-model="shippingRate"
          min="0"
          type="number"
        >
          <template #prepend>
            {{ currency }}
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

const shippingName = ref(null);
const shippingRate = ref(10);

const submit = () => {
  emits('submit', {
    name: shippingName.value,
    rate: shippingRate.value,
  });
};

const reset = () => {
  emits('delete');
  shippingName.value = null;
  shippingRate.value = 10;
};
</script>

<style></style>

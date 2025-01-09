<template>
  <form @submit.prevent="applyDiscount">
    <BaseModal
      title="Add Discount"
      modal-id="orderDiscountModal"
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

      <div class="mb-5">
        <BaseFormRadio
          v-for="(type, index) in discountType"
          :key="index"
          v-model="selectedDiscountType"
          :value="type.value"
        >
          {{ type.label }}
        </BaseFormRadio>
      </div>

      <BaseFormGroup
        label="Discount value"
        required
        col="6"
      >
        <BaseFormInput
          v-model="discountValue"
          min="0"
          :max="maxDiscountValue"
          type="number"
          required
        >
          <template
            v-if="selectedDiscountType === 'percentage'"
            #append
          >
            %
          </template>
          <template
            v-else
            #prepend
          >
            {{ currency }}
          </template>
        </BaseFormInput>
      </BaseFormGroup>

      <BaseFormGroup
        v-if="selectedDiscountType === 'percentage'"
        label="Capped at"
        col="6"
      >
        <BaseFormInput
          v-model="discountCappedAt"
          type="number"
          placeholder="&#8734;"
        >
          <template #prepend>
            {{ currency }}
          </template>
        </BaseFormInput>
      </BaseFormGroup>

      <BaseFormGroup
        label="Reason"
        description="Customer will see this during checkout or on invoice"
      >
        <BaseFormInput
          v-model="reason"
          type="text"
        />
      </BaseFormGroup>
    </BaseModal>
  </form>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  currency: { type: String, default: 'RM' },
  subTotal: { type: Number, default: 0 },
});

const emits = defineEmits(['submit', 'delete']);

const discountType = [
  { label: 'Percentage', value: 'percentage' },
  { label: 'Fixed value', value: 'fixed' },
];

const selectedDiscountType = ref('percentage');
const discountValue = ref(10);
const discountCappedAt = ref(null);
const reason = ref(null);

const getDiscountPrice = (price) => {
  let discountPrice = 0;
  if (selectedDiscountType.value === 'percentage') {
    discountPrice = price * (discountValue.value / 100);
    const isExceed =
      typeof discountCappedAt.value === 'number' &&
      discountPrice > discountCappedAt.value;
    discountPrice = isExceed ? discountCappedAt.value : discountPrice;
  } else discountPrice = discountValue.value;
  return discountPrice.toFixed(2);
};

const applyDiscount = () => {
  emits('submit', {
    type: selectedDiscountType.value,
    value: discountValue.value,
    cappedAt: discountCappedAt.value,
    reason: reason.value,
    getDiscountPrice,
  });
};

const maxDiscountValue = computed(() =>
  selectedDiscountType.value === 'percentage' ? 100 : props.subTotal
);

const reset = () => {
  emits('delete');
  selectedDiscountType.value = 'percentage';
  discountValue.value = 10;
  discountCappedAt.value = null;
  reason.value = null;
};
</script>

<style></style>

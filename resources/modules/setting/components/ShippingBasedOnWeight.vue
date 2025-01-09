<template>
  <BaseSettingLayout :is-onboarding="props.onboarding">
    <template
      v-if="!props.onboarding"
      #description
    >
      <b>How does state shipping rate calculator work?</b>
      <div class="mt-3">
        If within first shipping weight range:
        <p class="text-muted">
          First shipping charge
        </p>
      </div>
      <div class="mt-3">
        If above first shipping weight range:
        <p class="text-muted">
          First shipping charge + ( Total weight - First shipping weight range )
          / Additional charge unit * Additional charge
        </p>
      </div>

      <BaseCard>
        <BaseFormGroup label="Trial calculation:">
          <BaseFormInput
            id="bow-trial-calculation"
            v-model="basedOnWeight.calculatorWeight"
            type="number"
            step="0.0001"
            min="0.00"
          >
            <template #append>
              kg
            </template>
          </BaseFormInput>
        </BaseFormGroup>

        <BaseFormGroup label="Total Shipping Charge:">
          {{ props.defaultCurrency }}
          {{ shippingCalculator.toFixed(2) }}
        </BaseFormGroup>
      </BaseCard>
    </template>

    <template #content>
      <BaseFormGroup label="Based on weight">
        <template #label-row-end>
          <BaseButton
            type="link"
            @click="deleteComponent"
          >
            <i
              class="fas fa-trash"
              data-bs-toggle="tooltip"
              data-placement="top"
              title="Delete"
            />
          </BaseButton>
        </template>
      </BaseFormGroup>
      <BaseFormGroup label="Name">
        <BaseFormInput
          :id="`shipping-bow-name-${props.index}`"
          ref="shippingNameInputField"
          v-model="basedOnWeight.shippingName"
          type="text"
          placeholder="Shipping Name"
          @input="updateBowData"
        />
      </BaseFormGroup>

      <BaseFormGroup label="Range of rates">
        <table class="w-100">
          <tr>
            <td>First</td>
            <td>
              <BaseFormGroup>
                <BaseFormInput
                  :id="`shipping-bow-${index}-first-weight`"
                  ref="firstWeight"
                  v-model="basedOnWeight.firstWeight"
                  step="0.0001"
                  min="0.00"
                  type="text"
                  class="pe-3"
                  @input="updateBowData"
                >
                  <template #append>
                    kg
                  </template>
                </BaseFormInput>
              </BaseFormGroup>
            </td>
            <td>
              <BaseFormGroup>
                <BaseFormInput
                  :id="`shipping-bow-${index}-first-money`"
                  ref="firstMoney"
                  v-model="basedOnWeight.firstMoney"
                  placeholder="0.00"
                  step="0.0001"
                  min="0.00"
                  type="text"
                  @input="updateBowData"
                >
                  <template #prepend>
                    {{ props.defaultCurrency }}
                  </template>
                </BaseFormInput>
              </BaseFormGroup>
            </td>
          </tr>
          <tr>
            <td>Every Additional</td>
            <td>
              <BaseFormGroup>
                <BaseFormInput
                  :id="`shipping-bow-${index}-additional-weight`"
                  ref="additionalWeight"
                  v-model="basedOnWeight.additionalWeight"
                  step="0.0001"
                  min="0.00"
                  type="text"
                  class="pe-3"
                  @input="updateBowData"
                >
                  <template #append>
                    kg
                  </template>
                </BaseFormInput>
              </BaseFormGroup>
            </td>
            <td>
              <BaseFormGroup>
                <BaseFormInput
                  :id="`shipping-bow-${index}-additional-money`"
                  ref="additionalMoney"
                  v-model="basedOnWeight.additionalMoney"
                  placeholder="0.00"
                  step="0.0001"
                  min="0.00"
                  type="text"
                  @input="updateBowData"
                >
                  <template #prepend>
                    {{ props.defaultCurrency }}
                  </template>
                </BaseFormInput>
              </BaseFormGroup>
            </td>
          </tr>
        </table>
      </BaseFormGroup>

      <!-- <div v-if="basedOnWeight.tempDisabled" class="checkbox-container">
          <label class="d-flex" style="align-items: center">
            <input
              ref="isFreeshipping"
              v-model="basedOnWeight.isFreeshipping"
              type="checkbox"
              class="bow-checkbox"
              @click="basedOnWeight.freeShipFrom = ''"
            />
            <span class="p-two"
              >Enable "Free shipping" when order total amount equal to or
              above</span
            >
          </label>
          <div v-if="basedOnWeight.isFreeshipping" class="expand">
            <span style="display: inline-block; margin-bottom: 10px">
              <div class="money-input">
                <div class="money-input-container">
                  <div class="money-input-body" style="font-size: 1.4rem">
                    <label class="money-label p-two">{{
                      props.defaultCurrency
                    }}</label>
                    <input
                      ref="freeShipFrom"
                      v-model="basedOnWeight.freeShipFrom"
                      class="input-money price"
                      placeholder="0.00"
                      type="number"
                      step="0.0001"
                      min="0.00"
                      @input="updateBowData"
                    />
                  </div>
                </div>
              </div>
            </span>
          </div>
          <button @click="passbowData">click me</button> //disabled
        </div> -->
    </template>
  </BaseSettingLayout>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import { defineEmits, reactive, computed, onMounted, ref } from 'vue';

export default {
  mixins: [specialCurrencyCalculationMixin],
};
</script>

<script setup>
const props = defineProps({
  index: { type: Number, default: null },
  type: { type: String, default: '' },
  value: { type: Array, default: () => [] },
  defaultCurrency: { type: String, default: 'RM' },
  onboarding: { type: Boolean, default: false },
});

const emits = defineEmits(['input', 'delete-component']);

const shippingNameInputField = ref(null);

const basedOnWeight = ref({
  isFreeshipping: 0,
  shippingName: '',
  firstWeight: 0,
  firstMoney: 0,
  additionalWeight: 0,
  additionalMoney: 0,
  freeShipFrom: 0,
  calculatorWeight: '',
  id: '',
  tempDisabled: false,
});

const shippingCalculator = computed(() => {
  let totalShipping = 0;
  const {
    calculatorWeight,
    firstWeight,
    additionalWeight,
    additionalMoney,
    firstMoney,
  } = basedOnWeight.value;
  if (calculatorWeight !== '') {
    if (calculatorWeight > parseFloat(firstWeight)) {
      const additionalRate =
        ((calculatorWeight - parseFloat(firstWeight)) /
          parseFloat(additionalWeight)) *
        parseFloat(additionalMoney);
      totalShipping =
        parseFloat(firstMoney) +
        (Number.isNaN(additionalRate) ? 0 : additionalRate);
    } else {
      totalShipping = parseFloat(firstMoney);
    }
  }
  return totalShipping;
});

const updateBowData = () => {
  emits('input', {
    type: 'bow',
    value: basedOnWeight.value,
    index: props.index,
  });
};

const deleteComponent = () => {
  emits('delete-component', {
    type: 'bow',
    value: basedOnWeight.value,
    index: props.index,
  });
};

onMounted(() => {
  basedOnWeight.value = { ...basedOnWeight.value, ...props.value[props.index] };
  // shippingNameInputField.value?.focus();
  updateBowData();
});
</script>

<template>
  <BaseSettingLayout :is-onboarding="props.onboarding">
    <template #content>
      <BaseFormGroup label="Flat Rate Per Order">
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
          :id="`shipping-flat-name-${props.index}`"
          ref="shippingNameInputFiled"
          v-model="flatRate.shippingName"
          type="text"
          @input="updateFlatData"
        />
      </BaseFormGroup>
      <BaseFormGroup label="Per order rate">
        <BaseFormInput
          :id="`shipping-flat-name-${props.index}`"
          ref="perOrderRate"
          v-model="flatRate.perOrderRate"
          type="number"
          step="0.0001"
          min="0.00"
          @input="updateFlatData"
        >
          <template #prepend>
            {{ props.defaultCurrency }}
          </template>
          <BaseFormInput />
        </BaseFormInput>
      </BaseFormGroup>
      <!-- <div class="w-100">
          <div
            class="row"
            style="padding-top: 20px"
          >
            <div
              v-if="flatRate.tempDisabled"
              class="checkbox-container"
            >
              <label
                class="d-flex"
                style="align-items: center"
              >
                <input
                  v-model="flatRate.isFreeshipping"
                  type="checkbox"
                  class="bow-checkbox"
                  @click="flatRate.freeShipFrom = ''"
                >
                <span
                  class="p-two"
                >Enable "Free shipping" when order total amount equal to or
                  above</span>
              </label>
              <div
                v-if="flatRate.isFreeshipping"
                class="expand"
              >
                <span style="display: inline-block; margin-bottom: 10px">
                  <div class="money-input">
                    <div class="money-input-container">
                      <div class="money-input-body">
                        <label class="money-label p-two">{{
                          props.defaultCurrency
                        }}</label>
                        <input
                          ref="freeShipFrom"
                          v-model="flatRate.freeShipFrom"
                          class="input-money form-control"
                          placeholder="0.00"
                          type="number"
                          step="0.0001"
                          min="0.00"
                          @input="updateFlatData"
                        >
                      </div>
                    </div>
                  </div>
                </span>
              </div>
            </div>
          </div>

          <div
            v-if="flatRate.disabled"
            class="setting-page__content-oneline"
          >
            <h4 class="p-two">
              Calculator of {{ flatRate.shippingName }}
            </h4>
            <div class="input-wrapper">
              <label class="p-two"> Calculation </label>
              <p class="p-two">
                Rate per order
              </p>
            </div>
          </div>
        </div> -->
    </template>
  </BaseSettingLayout>
</template>

<script setup>
import { defineEmits, reactive, computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
  index: { type: Number, default: null },
  value: { type: Array, default: () => [] },
  defaultCurrency: { type: String, default: 'RM' },
  onboarding: { type: Boolean, default: false },
});

const emits = defineEmits(['input', 'delete-component']);

const flatRate = ref({
  disabled: false,
  shippingName: '',
  perOrderRate: 0,
  freeShipFrom: 0,
  isFreeshipping: false,
  id: '',
  tempDisabled: false,
});

const shippingNameInputFiled = ref(null);

const updateFlatData = () => {
  emits('input', {
    type: 'flat',
    value: flatRate.value,
    index: props.index,
  });
};

const deleteComponent = () => {
  emits('delete-component', {
    type: 'flat',
    index: props.index,
    value: flatRate.value,
  });
};

watch(flatRate, () => {
  updateFlatData();
});

onMounted(() => {
  flatRate.value = { ...flatRate.value, ...props.value[props.index] };
  // shippingNameInputFiled.value?.focus();
  updateFlatData();
});
</script>

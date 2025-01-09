<template>
  <BaseCard has-header title="Value">
    <BaseFormGroup>
      <BaseFormRadio
        v-for="discountValueType in ['percentage', 'fixed']"
        :id="`promotion-${discountValueType}`"
        :key="discountValueType"
        :value="discountValueType"
        :model-value="setting.discountValueType"
        @input="changeDiscountValueType"
      >
        {{ discountValueType === 'percentage' ? 'Percentage' : 'Fixed value' }}
      </BaseFormRadio>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="setting.discountValueType == 'percentage'"
      col="6"
      label="Discount value"
      :error-message="
        hasError('discountValue') ? errors['discountValue'][0] : ''
      "
      @keydown="clearError($event.target.name)"
    >
      <BaseFormInput
        id="promotion-discount-value-percentage"
        name="discountValue"
        :model-value="setting.discountValue"
        type="number"
        @input="updateSetting('discountValue', parseInt($event.target.value))"
        @keydown="validateInteger($event)"
      >
        <template #append> % </template>
      </BaseFormInput>
    </BaseFormGroup>
    <BaseFormGroup
      v-if="setting.discountValueType == 'percentage'"
      col="6"
      label="Capped At"
      :error-message="hasError('discountCap') ? errors['discountCap'][0] : ''"
      @keydown="clearError($event.target.name)"
    >
      <BaseFormInput
        id="promotion-discount-cap"
        name="discountCap"
        type="number"
        :model-value="setting.discountCap"
        placeholder="∞"
        @input="updateSetting('discountCap', parseInt($event.target.value))"
        @keydown="validateNumber($event)"
      >
        <template #prepend>
          {{ currency === 'MYR' ? 'RM' : currency }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>

    <BaseFormGroup
      v-show="setting.discountValueType == 'fixed'"
      col="6"
      label="Discount value"
      :error-message="
        hasError('discountValue') ? errors['discountValue'][0] : ''
      "
      @keydown="clearError($event.target.name)"
    >
      <BaseFormInput
        id="promotion-discount-value-fixed"
        name="discountValue"
        type="number"
        :model-value="setting.discountValue"
        placeholder="∞"
        @input="updateSetting('discountValue', parseInt($event.target.value))"
        @keydown="validateNumber($event)"
      >
        <template #prepend>
          {{ currency === 'MYR' ? 'RM' : currency }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';

export default {
  name: 'DiscountValueCard',
  mixins: [PromotionCentraliseMixins],
  methods: {
    changeDiscountValueType(value) {
      this.updateSetting('discountValueType', value);
      this.updateSetting('discountValue', 0);
      this.updateSetting('discountCap', '');
    },
  },
};
</script>

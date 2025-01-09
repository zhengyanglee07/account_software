<template>
  <BaseCard
    has-header
    title="General"
    @keydown="clearError($event.target.name)"
  >
    <BaseFormGroup
      v-if="setting.promotionType == 'manual'"
      label="Promo Code"
      :error-message="hasError('promoCode') ? errors['promoCode'][0] : ''"
      required
    >
      <BaseFormInput
        id="promotion-code"
        type="text"
        placeholder="e.g. WELCOME"
        :model-value="setting.promoCode"
        @input="updateSetting('promoCode', $event.target.value)"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Promotion Title"
      :error-message="hasError('promoTitle') ? errors['promoTitle'][0] : ''"
      description="Customers will not see this"
      required
    >
      <BaseFormInput
        id="promotion-title"
        type="text"
        :model-value="setting.promoTitle"
        @input="updateSetting('promoTitle', $event.target.value)"
      />
    </BaseFormGroup>

    <BaseFormGroup
      label="Display Title"
      :error-message="hasError('displayTitle') ? errors['displayTitle'][0] : ''"
      description="Customers will see this on product and cart summary page."
      required
    >
      <BaseFormInput
        id="promotion-display-title"
        type="text"
        :model-value="setting.displayTitle"
        @input="updateSetting('displayTitle', $event.target.value)"
      />
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';

export default {
  name: 'PromotionGeneral',
  mixins: [PromotionCentraliseMixins],
  props: {
    type: {
      type: String,
      default: 'manual',
    },
  },
};
</script>

<template>
  <BaseCard has-header title="Extra Conditions">
    <div v-if="loading">
      <!-- TODO(ZHENG YANG): Loading -->
      <span>
        <div class="spinner--small">
          <div class="loading-animation loading-container">
            <div class="shape shape1" />
            <div class="shape shape2" />
            <div class="shape shape3" />
            <div class="shape shape4" />
          </div>
        </div>
      </span>
    </div>
    <div v-else>
      <!-- target customers  -->
      <BaseFormGroup label="Target segments">
        <BaseFormRadio
          v-for="targetCustomerType in ['all', 'specific-segment']"
          :id="`promotion-target-customer-type-${targetCustomerType}`"
          :key="targetCustomerType"
          :value="targetCustomerType"
          :model-value="setting.targetCustomerType"
          @input="(val) => updateSetting('targetCustomerType', val)"
        >
          {{
            `${
              targetCustomerType === 'all'
                ? 'All segments'
                : 'Specific segment(s)'
            }`
          }}
        </BaseFormRadio>
        <BaseFormGroup
          v-if="setting.targetCustomerType == 'specific-segment'"
          description="Only contacts in the selected segments are eligible to apply this promotion"
          :error-message="
            hasError('targetValue') ? errors['targetValue'][0] : ''
          "
        >
          <BaseMultiSelect
            id="promotion-target-segment"
            :model-value="setting.targetValue"
            multiple
            placeholder="Select a segment"
            label="segmentName"
            :options="allSegments"
            @input="setSelectedSegment"
          />
        </BaseFormGroup>
      </BaseFormGroup>
      <!-- end target customers  -->

      <!-- store usage limit  -->
      <BaseFormGroup label="Store usage limit">
        <BaseFormRadio
          v-for="storeUsageLimitType in ['unlimited', 'limited']"
          :id="`promotion-store-usage-limit-type-${storeUsageLimitType}`"
          :key="storeUsageLimitType"
          :value="storeUsageLimitType"
          :model-value="setting.storeUsageLimitType"
          @input="(val) => updateSetting('storeUsageLimitType', val)"
        >
          {{
            `${
              storeUsageLimitType === 'unlimited'
                ? 'Unlimited'
                : 'Limited times'
            }`
          }}
        </BaseFormRadio>

        <BaseFormGroup
          v-if="setting.storeUsageLimitType == 'limited'"
          :error-message="
            hasError('storeUsageValue') ? errors['storeUsageValue'][0] : ''
          "
          @keydown="clearError($event.target.name)"
        >
          <BaseFormInput
            id="promotion-storge-usage-value"
            type="number"
            name="storeUsageValue"
            :model-value="setting.storeUsageValue"
            step="1"
            min="0"
            placeholder="e.g. 10"
            @input="updateSetting('storeUsageValue', $event.target.value)"
            @keydown="validateNumber($event)"
          />
        </BaseFormGroup>
      </BaseFormGroup>
      <!--end store usage limit  -->

      <!-- customer usage limit  -->
      <BaseFormGroup label="Each customer usage limit ">
        <BaseFormRadio
          v-for="customerUsageLimitType in ['unlimited', 'limited']"
          :id="`promotion-customer-usage-limit-type-${customerUsageLimitType}`"
          :key="customerUsageLimitType"
          :value="customerUsageLimitType"
          :model-value="setting.customerUsageLimitType"
          @input="(val) => updateSetting('customerUsageLimitType', val)"
        >
          {{
            `${
              customerUsageLimitType === 'unlimited'
                ? 'Unlimited'
                : 'Limited times'
            }`
          }}
        </BaseFormRadio>
        <BaseFormGroup
          v-if="setting.customerUsageLimitType == 'limited'"
          :error-message="
            hasError('customerUsageValue')
              ? errors['customerUsageValue'][0]
              : ''
          "
          @keydown="clearError($event.target.name)"
        >
          <BaseFormInput
            id="promotion-customer-usage-value"
            type="number"
            name="customerUsageValue"
            :model-value="setting.customerUsageValue"
            placeholder="e.g. 10"
            @input="updateSetting('customerUsageValue', $event.target.value)"
            @keydown="validateNumber($event)"
          />
        </BaseFormGroup>
      </BaseFormGroup>
      <!-- end customer usage limit  -->
    </div>
  </BaseCard>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import { mapGetters, mapMutations, mapState } from 'vuex';
import promotionAPI from '@promotion/api/promotionAPI.js';

export default {
  name: 'PromotionExtraCondition',
  mixins: [PromotionCentraliseMixins],
  props: ['type', 'loading'],
  data() {
    return {
      voucherCodeApplicable: 'nonApplicable',
      targetCustomers: '',
    };
  },

  computed: {},
  watch: {},
  methods: {
    ...mapMutations('promotions', ['updateAllSegments']),
    loadAllSegment() {
      promotionAPI
        .getSegments()
        .then((response) => {
          this.updateAllSegments(response.data);
        })
        .catch((error) => {});
    },
    setSelectedSegment(value) {
      console.log(value, 'value');
      this.updateSetting('targetValue', value);
    },
  },
  mounted() {
    this.loadAllSegment();
  },
};
</script>

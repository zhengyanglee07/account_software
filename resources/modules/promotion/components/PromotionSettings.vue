<template>
  <BaseCard has-header title="Type">
    <BaseFormGroup>
      <BaseFormRadio
        v-for="promoType in discountType"
        :id="`promotion-${promoType.label}`"
        :key="promoType.label"
        :value="promoType.value"
        :model-value="setting.discountType"
        @input="changeDiscountType(promoType)"
      >
        {{ promoType.label }}
      </BaseFormRadio>
    </BaseFormGroup>
  </BaseCard>
  <DiscountValueCard v-if="setting.discountType != 'freeShipping'" />
  <PBMinimumQuantity v-if="setting.discountType == 'productBased'" />
  <CountrySelectCard v-if="setting.discountType == 'freeShipping'" />
  <BaseCard
    v-if="setting.discountType != 'productBased'"
    has-header
    title="Customer Buys"
  >
    <BaseFormGroup label="Minimum Spend">
      <BaseFormRadio
        v-for="minimumType in ['none', 'minimumAmount']"
        :id="`promotion-minimum-type-${minimumType}`"
        :key="minimumType"
        :value="minimumType"
        :model-value="setting.minimumType"
        @input="changeMinimumType"
      >
        {{
          minimumType === 'none' ? 'None' : `Minimum order total (${currency})`
        }}
      </BaseFormRadio>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="setting.minimumType == 'minimumAmount'"
      :error-message="hasError('minimumValue') ? errors['minimumValue'][0] : ''"
      @keydown="clearError($event.target.name)"
    >
      <BaseFormInput
        id="promotion-mimimum-value"
        type="number"
        :model-value="setting.minimumValue"
        @input="updateSetting('minimumValue', $event.target.value)"
        @keydown="validateNumber($event)"
      >
        <template #prepend>
          {{ currency === 'MYR' ? 'RM' : currency }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>
  </BaseCard>
  <BaseCard has-header title="Active Dates">
    <BaseDatePicker
      v-model="startDate"
      :clearable="false"
      type="datetime"
      format="YYYY-MM-DD h:mm a"
      value-type="format"
      :default-value="currentDate"
      :disabled-date="notBeforeToday"
      :editable="false"
      :show-second="false"
    />
    <BaseFormGroup>
      <BaseFormCheckBox
        id="promotion-is-expiry-date"
        :value="true"
        :model-value="!!setting.isExpiryDate"
        @input="updateSetting('isExpiryDate', !setting.isExpiryDate)"
      >
        Set end date
      </BaseFormCheckBox>
    </BaseFormGroup>
    <BaseFormGroup v-if="setting.isExpiryDate" label="End date">
      <BaseDatePicker
        v-model="endDate"
        :clearable="false"
        type="datetime"
        format="YYYY-MM-DD h:mm a"
        value-type="format"
        :default-value="currentDate"
        :disabled-time="notBeforeStartTime"
        :disabled-date="notBeforeStartDate"
        :editable="false"
        :show-second="false"
      />
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
import PBMinimumQuantity from '@promotion/components/PromotionPBMinimumQuantity.vue';
import DiscountValueCard from '@promotion/components/PromotionDiscountValueCard.vue';
import CountrySelectCard from '@promotion/components/PromotionCountrySelectCard.vue';
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

export default {
  name: 'PromotionSetting',
  components: {
    PBMinimumQuantity,
    DiscountValueCard,
    CountrySelectCard,
  },
  mixins: [PromotionCentraliseMixins],
  props: ['type', 'timezone', 'promotionDetail'],
  data() {
    return {
      searchInput: '',
    };
  },
  computed: {
    currentDate() {
      return new Date();
    },
    startDate: {
      get() {
        return this.setting.startDate;
      },
      set(value) {
        this.updateSetting('startDate', value);
      },
    },
    endDate: {
      get() {
        return this.setting.endDate;
      },
      set(value) {
        this.updateSetting('endDate', value);
      },
    },
    discountType() {
      const { featureForPaidPlan } = this.$page.props.permissionData;
      const type = [
        { label: 'Order Based', category: 'Order', value: 'orderBased' },
        { label: 'Product Based', category: 'Product', value: 'productBased' },
      ];
      if (
        this.promotionDetail?.discountType === 'freeShipping' ||
        !featureForPaidPlan.includes('free-shipping-discount')
      ) {
        type.push({
          label: 'Free Shipping',
          category: 'Shipping',
          value: 'freeShipping',
        });
      }
      return type;
    },
  },
  watch: {
    startDate() {
      this.updateEndDate();
    },
    'setting.isExpiryDate': function () {
      this.updateEndDate(true);
    },
  },
  mounted() {},
  methods: {
    updateEndDate(isDefault = false) {
      if (!isDefault && this.setting.isExpiryDate) return;
      this.updateSetting(
        'endDate',
        dayjs(new Date(new Date(this.setting.startDate)))
          .add(1, 'month')
          .format('YYYY-MM-DD hh:mm a')
      );
    },
    notBeforeToday(date) {
      const datepicker = dayjs(date).format('YYYY-MM-DD');
      const today = new Date();
      const convertToday = dayjs(today).tz(this.timezone).format('YYYY-MM-DD');
      return (
        +new Date(this.setting.startDate) !== +date && datepicker < convertToday
      );
    },
    notBeforeStartDate(date) {
      return (
        date < new Date(new Date(this.setting.startDate).setHours(0, 0, 0, 0))
      );
    },
    notBeforeStartTime(date) {
      const startDate = new Date(this.setting.startDate);
      const afterAdded = new Date(
        startDate.setMinutes(startDate.getMinutes() + 1)
      );
      return date <= new Date(afterAdded);
    },
    setFocus() {
      this.triggerModal('browseCountryModal');
    },
    loadPromotionSetting() {
      Object.keys(this.setting).forEach((key) => {
        this.setting[key] = this.promotionSetting[key];
      });
    },
    changeDiscountType(type) {
      this.updateSetting('discountType', type.value);
      this.updateSetting('discountValue', 0);
      this.updateSetting('discountCap', '');
      this.updateSetting('discountCategory', type.category);
    },
    changeMinimumType(value) {
      this.updateSetting('minimumType', value);
      if (value === 'none') {
        this.updateSetting('minimumValue', 0);
      }
    },
  },
};
</script>

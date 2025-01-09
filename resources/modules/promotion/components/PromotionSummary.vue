<template>
  <BaseCard
    v-if="setting"
    has-header
    title="Summary"
  >
    <ul class="mx-5">
      <!-- <span style="font-weight:500; font-size:20px;">{{promotionGeneral.promoCode}}</span> -->
      <li v-if="setting.promotionType == 'manual' && setting.promoCode != ''">
        Promo code: {{ setting.promoCode }}
      </li>
      <li v-if="setting.discountCategory == 'Order'">
        Order sub-total :
        {{
          `${setting.discountValueType == 'fixed' ? currency : ''} ${parseInt(
            setting.discountValue
          )}${setting.discountValueType == 'percentage' ? '%' : ''} discount`
        }}
      </li>
      <template v-if="setting.discountCategory == 'Product'">
        <li>
          {{
            `${setting.discountValueType == 'fixed' ? currency : ''} ${parseInt(
              setting.discountValue
            )}${
              setting.discountValueType == 'percentage' ? '%' : ''
            } discount each item when purchase
                   ${setting.minimumQuantity} and above`
          }}
        </li>
        <li>
          Apply to:
          {{
            ` ${
              setting.productDiscountType == 'all-product'
                ? 'all products'
                : `${setting.selectedProduct.length} ${
                  setting.productDiscountType == 'specific-category'
                    ? 'categorie(s)'
                    : 'product(s)'
                }`
            }`
          }}
        </li>
      </template>
      <li v-if="setting.discountCategory == 'Shipping'">
        {{
          `${
            setting.countryType === 'all'
              ? 'For all countries'
              : `${setting.selectedCountries.length} countries`
          }`
        }}
      </li>
      <li>
        {{
          `${
            setting.targetValue.length > 0
              ? `${setting.targetValue.length} selected customer group only`
              : 'Apply to all customers'
          }`
        }}
      </li>
      <li>
        Total usage:
        {{
          `${
            setting.storeUsageLimitType === 'unlimited'
              ? '∞'
              : setting.storeUsageValue
          }`
        }}
      </li>
      <li>
        Usage per customer :
        {{
          `${
            setting.customerUsageLimitType === 'unlimited'
              ? '∞'
              : setting.customerUsageValue
          }`
        }}
      </li>
      <li>
        Active from
        <i> {{ formatDate(setting.startDate) }} </i>
        <i v-if="setting.isExpiryDate">
          to {{ formatDate(setting.endDate) }}
        </i>
      </li>
    </ul>
  </BaseCard>
</template>

<script>
import dayjs from 'dayjs';

export default {
  props: {
    setting: {
      type: Object,
      default: null,
    },
    currency: {
      type: String,
      default: '',
    },
  },
  methods: {
    formatDate(datetime) {
      return dayjs(datetime).format('MMMM D, YYYY [at] h:mm a');
    },
  },
};
</script>

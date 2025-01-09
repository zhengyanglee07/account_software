<template>
  <BaseDatatable
    v-if="datas?.length > 0"
    title="customer"
    :table-headers="tableHeaders"
    :table-datas="datas"
    no-header
    no-action
  >
    <template #cell-image="{ row: { image_url } }">
      <img
        :src="image_url || productDefaultImages"
        style="height: 80px; width: 80px"
      >
    </template>
    <template
      #cell-description="{
        row: {
          product_name,
          is_taxable,
          SKU,
          variant,
          customization,
          is_discount_applied,
          discount_details,
          quantity,
          unit_price,
        },
      }"
    >
      <p>
        {{ product_name }}<span
          v-if="is_taxable"
          class="text-danger"
        > * </span>
      </p>

      <p v-if="SKU">
        SKU : {{ SKU }}
      </p>
      <div v-if="JSON.parse(variant ?? '[]').length > 0">
        <p
          v-for="variantItem in JSON.parse(variant)"
          :key="variantItem.id"
        >
          {{ variantItem.label }} : {{ variantItem.value }}
        </p>
      </div>
      <div
        v-for="cus in JSON.parse(customization ?? '[]')"
        :key="cus.id"
      >
        {{ cus.label }} :
        <span
          v-for="(value, i) in cus.values"
          :key="value.id"
        >
          {{ value.value_label }}
          <span v-if="parseFloat(value?.single_charge) > 0">
            ( + {{ currency }} {{ value.single_charge }} )
          </span>
          <span>
            {{ i + 1 >= cus.values.length ? '' : ', ' }}
          </span>
          <span v-if="cus.is_total_charge && cus.total_charge_amount > 0">
            ( + {{ currency }} {{ cus.total_charge_amount }} )
          </span>
        </span>
      </div>

      <div v-if="is_discount_applied">
        Discount :
        {{ JSON.parse(discount_details ?? '{}').promotion?.display_name }}
      </div>

      <p>
        {{ quantity }} x {{ getCurrency }}
        {{ specialCurrencyCalculation(unit_price, getCurrency) }}
      </p>
    </template>
    <template #cell-quantity="{ row }">
      <BaseFormGroup
        class="product-quantity-form-group"
        :description="quantityDesc(row)"
      >
        <BaseFormInput
          :id="`order-product-fulfill-quantity-${row.index}`"
          :min="row.min"
          :max="row.max || null"
          type="number"
          :model-value="row.quantity"
          @blur="changeQty($event, row)"
        >
          <template #append>
            of {{ row.max > 0 ? row.max : '&#8734;' }}
          </template>
        </BaseFormInput>
      </BaseFormGroup>
    </template>
    <template #cell-total="{ row }">
      <div v-if="!props.isRefund">
        <div :class="{ 'discount-price': row.is_discount_applied }">
          {{ getCurrency }}
          {{ specialCurrencyCalculation(row.total, getCurrency) }}
        </div>

        <p v-if="row.is_discount_applied">
          {{ getCurrency }}
          {{
            specialCurrencyCalculation(
              row.total - row.discount / 100,
              getCurrency
            )
          }}
        </p>
      </div>
      <p v-else>
        {{ getCurrency }}
        {{
          specialCurrencyCalculation(calculateProductTotal(row), getCurrency)
        }}
      </p>
    </template>
    <template #cell-remove="{ row }">
      <BaseButton
        v-if="!row.hasFulfilledOrder"
        type="link"
        @click="emit('remove', row)"
      >
        <i class="fa-solid fa-x" />
      </BaseButton>
    </template>
  </BaseDatatable>
</template>

<script>
import { computed, defineEmits, watch } from 'vue';
import productDefaultImages from '@shared/assets/media/product-default-image.png';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  mixins: [specialCurrencyCalculationMixin],
};
</script>

<script setup>
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  currency: { type: String, default: 'RM' },
  tableHeaders: { type: Array, default: () => [] },
  isFulfillment: { type: Boolean, default: false },
  isRefund: { type: Boolean, default: false },
  isManualOrder: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'remove']);

const datas = computed({
  get() {
    return props.modelValue.map((m, index) => ({ ...m, index }));
  },
  set(val) {
    emit('update:modelValue', val);
  },
});

const quantityDesc = ({ originalQuantity }) => {
  if (props.isManualOrder && originalQuantity) {
    return `Original quantity: ${originalQuantity}`;
  }
  return '';
};

const getCurrency = computed(() =>
  props.currency === 'MYR' ? 'RM' : props.currency
);

const tableHeaders = computed(() => {
  const headers = [
    { name: 'Image', key: 'image', custom: true },
    { name: 'Description', key: 'description', custom: true },
  ];

  if (props.isFulfillment || props.isRefund || props.isManualOrder)
    headers.push({ name: 'Quantity', key: 'quantity', custom: true });
  if (!props.isFulfillment)
    headers.push({ name: 'Total', key: 'total', custom: true });
  if (props.isManualOrder) {
    headers.push({ name: '', key: 'remove', custom: true });
  }
  return headers;
});

const changeQty = (event, product) => {
  let qty = parseInt(event.target.value);
  if (Number.isNaN(qty)) return;
  if (product.max > 0 && qty > product.max) {
    qty = product.max;
  } else if (product.min > 0 && event.target.value < product.min) {
    qty = product.min;
  } else if (event.target.value <= 0) {
    qty = 0;
  }
  datas.value[product.index].quantity = parseInt(qty);
  emit('update:modelValue', datas.value);
};

const displayDiscountPrice = (discount, product) => {
  const promotionType = discount.promotion.promotion_type;
  const discountType = promotionType.product_discount_type;
  const percentageDiscount =
    (product.total / product.max) *
    ((100 - promotionType.product_discount_value) / 100);
  const discountValue =
    promotionType.product_discount_cap &&
    percentageDiscount > promotionType.product_discount_cap
      ? product.total - promotionType.product_discount_cap
      : percentageDiscount;
  const productTotal =
    discountType === 'percentage'
      ? discountValue
      : product.total - promotionType.product_discount_value;
  return productTotal * (discountType === 'percentage' ? product.max : 1);
};
const calculateProductTotal = (product) => {
  const discountDetail =
    product.discount_details &&
    Object.keys(JSON.parse(product.discount_details)).length > 0
      ? JSON.parse(product.discount_details)
      : null;
  const productPrice = discountDetail
    ? displayDiscountPrice(discountDetail, product)
    : product.unit_price;
  product.total = productPrice * product.quantity;
  return product.total;
};
</script>

<style>
.discount-price {
  font-size: 0.9rem;
  color: #a1a5b7;
  text-decoration: line-through;
}

@media screen and (max-width: 415px) {
  .product-quantity-form-group {
    width: 120px;
  }
}
</style>

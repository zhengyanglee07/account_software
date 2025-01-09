<template>
  <BaseCard :has-header="!!title" :has-footer="$slots.footer" :title="title">
    <table class="order-summary">
      <tr>
        <td>Subtotal</td>
        <td>{{ displayTotalItems }}</td>
        <td>{{ displayPrice(subTotal) }}</td>
      </tr>
      <tr v-for="(discount, index) in orderDiscount" :key="index">
        <td>{{ !index ? 'Discount' : '' }}</td>
        <td>{{ discount.display_name }}</td>
        <td>- {{ displayPrice(discount.discount_value / 100) }}</td>
      </tr>
      <tr v-if="shippingFee > 0">
        <td>Shipping</td>
        <td>{{ order.shipping_method_name }}</td>
        <td>{{ displayPrice(shippingFee) }}</td>
      </tr>
      <tr v-if="productTax > 0 || shippingTax > 0">
        <td>Tax</td>
        <td>{{ order.tax_name }}</td>
        <td>
          {{ displayPrice(productTax + shippingTax) }}
        </td>
      </tr>
      <tr v-if="productTax > 0 || shippingTax > 0">
        <td></td>
        <td colspan="2" class="text-start border border-secondary p-3">
          <div v-if="productTax > 0">
            Product Tax: {{ displayPrice(productTax) }}
            {{
              !!props.order.is_product_include_tax &&
              '(Included in product price)'
            }}
          </div>
          <div v-if="shippingTax > 0">
            <span>Shipping Tax: </span>
            <span>{{ displayPrice(shippingTax) }}</span>
          </div>
        </td>
      </tr>
      <tr v-if="storeCreditUsed > 0">
        <td>Store Credit Used</td>
        <td />
        <td>- {{ displayPrice(storeCreditUsed) }}</td>
      </tr>
      <tr>
        <td><b>Total</b></td>
        <td />
        <td>
          <b>{{ displayPrice(grandTotal) }}</b>
        </td>
      </tr>
      <tr>
        <td>Paid By Customer</td>
        <td>{{ order.payment_method }}</td>
        <td>{{ displayPrice(amountPaid) }}</td>
      </tr>
      <tr v-if="cashbackTotal > 0">
        <td>Cashback Amount</td>
        <td>{{ cashbackTitle }}</td>
        <td>{{ displayPrice(cashbackTotal) }}</td>
      </tr>
      <tr v-for="(refundLog, index) in refundLogs" :key="index">
        <td>Refunded</td>
        <td>{{ refundLog.refunded_reason ?? '-' }}</td>
        <td>{{ displayPrice(refundLog.refundAmount) }}</td>
      </tr>
      <tr v-if="order.refunded > 0">
        <td>Net Payment</td>
        <td />
        <td>{{ displayPrice(netPayment) }}</td>
      </tr>
      <tr v-if="amountToCollect !== 0">
        <td>Balance</td>
        <td>
          <span class="text-danger">
            Balance ({{
              amountToCollect > 0 ? 'customer owes you' : 'you owe customer'
            }})
          </span>
        </td>
        <td>
          {{ displayPrice(amountToCollect) }}
        </td>
      </tr>
    </table>

    <template #footer>
      <slot name="footer" />
    </template>
  </BaseCard>
</template>

<script setup>
import { computed } from 'vue';
import convertPrice from '@shared/hooks/useCurrency.js';
import clone from 'clone';

const props = defineProps({
  title: { type: String, default: null },
  order: { type: Object, default: () => {} },
  orderDetails: { type: Array, default: () => [] },
  refundLogs: { type: Array, default: () => [] },
});

// Start
const currencyPrefix = computed(() =>
  props.order.currency === 'MYR' ? 'RM' : props.order.currency
);

const formatPrice = (price) => parseFloat(price);

const displayPrice = (price) =>
  `${currencyPrefix.value} ${convertPrice(props.order.currency, price)}`;

const totalItems = computed(() =>
  props.orderDetails.reduce((total, item) => total + item.quantity, 0)
);

const displayTotalItems = computed(
  () => `${totalItems.value} item${totalItems.value > 1 ? 's' : ''}`
);

const originalSubtotal = computed(() =>
  props.order.order_details.reduce(
    (total, item) => total + formatPrice(item.total),
    0
  )
);

const subTotal = computed(() =>
  props.orderDetails.reduce(
    (total, product) => total + formatPrice(product.total),
    0
  )
);

const orderDiscount = computed(() =>
  clone(props.order.order_discount).map((m) => {
    m.originalDiscountValue = m.originalDiscountValue ?? m.discount_value;
    const orderTotal = subTotal.value * 100;
    const orderOriginalTotal = originalSubtotal.value * 100;
    if (m.promotion_category === 'Shipping') return m;

    if (m.discount_type === 'fixed') {
      m.discount_value =
        m.discount_value > orderTotal ? orderTotal : m.discount_value;
      return m;
    }
    const cappedAt = m.discount_capped_at;

    const discountPrice =
      m.originalDiscountValue / (orderOriginalTotal / orderTotal);
    const isExceed =
      typeof cappedAt === 'number' && discountPrice > cappedAt * 100;
    m.discount_value = isExceed ? cappedAt * 100 : discountPrice;
    return m;
  })
);

const discountTotal = computed(() =>
  props.order.order_discount.reduce((total, discount) => {
    return total + formatPrice(discount.discount_value / 100);
  }, 0)
);

const shippingFee = computed(() => formatPrice(props.order.shipping));

const calculateTax = (price) => {
  const taxRate = props.order.tax_rate / 100;
  const afterTax = price * taxRate;
  const tax = props.order.is_product_include_tax
    ? afterTax / (1 + taxRate)
    : afterTax;
  return formatPrice(tax);
};

const productTax = computed(() =>
  props.orderDetails.reduce(
    (total, item) =>
      total +
      (item.is_taxable
        ? calculateTax(formatPrice(item.total - item.discount / 100))
        : 0),
    0
  )
);

const hasShippingDiscount = computed(() =>
  props.order.order_discount.find((e) => e.promotion_category === 'Shipping')
);

const shippingTax = computed(() =>
  props.order.is_shipping_fee_taxable && !hasShippingDiscount.value
    ? calculateTax(shippingFee.value)
    : 0
);

const taxTotal = computed(
  () =>
    (props.order.is_product_include_tax ? 0 : productTax.value) +
    shippingTax.value
);

const storeCreditUsed = computed(() =>
  formatPrice(props.order.used_credit_amount / 100)
);

const grandTotal = computed(() =>
  formatPrice(
    subTotal.value -
      discountTotal.value +
      shippingFee.value +
      taxTotal.value -
      storeCreditUsed.value
  )
);

const cashbackTotal = computed(() =>
  formatPrice(props.order.cashback_amount / 100)
);

const cashbackTitle = computed(
  () => JSON.parse(props.order.cashback_detail)?.cashbackDetail?.cashback_title
);

const amountPaid = computed(() => formatPrice(props.order.paided_by_customer));

const refundTotal = computed(() => formatPrice(props.order.refunded));

const netPayment = computed(() => amountPaid.value - refundTotal.value);

const amountToCollect = computed(() => grandTotal.value - netPayment.value);
</script>

<style scoped>
.order-summary {
  width: 100%;
}
.order-summary td:first-child {
  width: 17%;
}
.order-summary td:last-child {
  text-align: right;
}
</style>

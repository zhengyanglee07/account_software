<template>
  <BasePageLayout
    page-name="Refund"
    :back-to="`/orders/details/${order.reference_key}`"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #left>
        <BaseCard
          v-if="unfulfilledArr.length > 0"
          has-header
          has-footer
          title="Unfulfilled"
        >
          <OrderProductsDatatable
            v-model="unfulfilledArr"
            is-refund
            :currency="order.currency"
          />
          <template #footer>
            <p>Refunded items will be removed from the order.</p>
          </template>
        </BaseCard>
        <BaseCard
          v-if="fulfilledArr.length > 0"
          has-header
          has-footer
          title="Fulfilled"
        >
          <OrderProductsDatatable
            v-model="fulfilledArr"
            is-refund
            :currency="order.currency"
          />
          <template #footer>
            <p>Refunded items will be removed from the order.</p>
          </template>
        </BaseCard>

        <BaseCard
          v-if="
            parseFloat(currentShipping) != 0 &&
              order.order_discount.filter(
                (discount) => discount.promotion_category == 'Shipping'
              ).length == 0
          "
          has-header
          title="Refund shipping"
        >
          <BaseFormGroup label="Shipping rate">
            {{ order.shipping_method }} ({{ order.currency }}
            {{ currentShipping.toFixed(2) }})
          </BaseFormGroup>

          <BaseFormGroup label="Refund amount">
            <BaseFormInput
              id="order-refund-amount"
              ref="firstMoney"
              type="number"
              placeholder="0.00"
              min="0"
              :model-value="refundShippingFee"
              class="p-two"
              @input="refundShipping($event)"
            >
              <template #prepend>
                {{ order.currency }}
              </template>
            </BaseFormInput>
          </BaseFormGroup>
        </BaseCard>

        <BaseCard
          has-header
          title="Reason for refund"
        >
          <BaseFormGroup
            description=" Only you and other staff can see this reason"
          >
            <BaseFormInput
              id="order-refund-reason"
              v-model="refundReason"
              type="text"
            />
          </BaseFormGroup>
        </BaseCard>
      </template>

      <template #right>
        <BaseCard
          has-header
          title="Summary"
        >
          <template v-if="checkItemSelected">
            <BaseFormGroup col="4">
              Items subtotal
            </BaseFormGroup>
            <BaseFormGroup col="4">
              {{ removedProduct.length }} {{ calculateItem }}
            </BaseFormGroup>
            <BaseFormGroup
              col="4"
              class="text-end"
            >
              {{ order.currency }}
              {{ calculateRefundTotal.toFixed(2) }}
            </BaseFormGroup>

            <BaseFormGroup
              v-if="order.taxes != 0"
              col="6"
            >
              Tax ( {{ order.tax_name }} {{ order.tax_rate }} % )
            </BaseFormGroup>
            <BaseFormGroup
              v-if="order.taxes != 0"
              col="6"
              class="text-end"
            >
              {{ order.currency }}
              {{ parseFloat(calculateTax || 0.0).toFixed(2) }}
            </BaseFormGroup>

            <BaseFormGroup
              v-if="parseFloat(refundShippingFee) != 0"
              col="6"
            >
              Shipping
            </BaseFormGroup>
            <BaseFormGroup
              v-if="parseFloat(refundShippingFee) != 0"
              col="6"
              class="text-end"
            >
              {{ order.currency }}
              {{ parseFloat(refundShippingFee || 0.0).toFixed(2) }}
            </BaseFormGroup>

            <BaseFormGroup col="6">
              Refund Total
            </BaseFormGroup>
            <BaseFormGroup
              col="6"
              class="text-end"
            >
              {{ order.currency }}
              {{ (calculateTotal - order.used_credit_amount / 100).toFixed(2) }}
            </BaseFormGroup>
          </template>
          <BaseFormGroup v-else>
            No items selected
          </BaseFormGroup>

          <template v-if="productTax != 0 || shippingTax != 0">
            <BaseFormGroup v-if="productTax != 0">
              Tax Summary:
              <BaseFormGroup
                v-if="productTax != 0"
                class="ms-3"
              >
                Product Tax: {{ order.currency }} {{ productTax.toFixed(2) }}
                <span v-if="order.is_product_include_tax">
                  (Included in product price)</span>
                <p v-if="shippingTax != 0">
                  Shipping Tax: {{ order.currency }}
                  {{ (shippingTax || 0.0).toFixed(2) }}
                </p>
              </BaseFormGroup>
            </BaseFormGroup>
          </template>
        </BaseCard>

        <BaseCard
          has-header
          has-footer
          title="Refund Amount"
        >
          <BaseFormGroup
            v-for="(log, index) in transactionLogJs"
            :key="index"
            label="Manual"
            :description="`${order.currency} ${log.total} available for refund`"
            :error-message="log.errRefund != '' ? log.errRefund : ''"
          >
            <BaseFormInput
              :id="`order-refund-first-money-${index}`"
              ref="firstMoney"
              placeholder="0.00"
              type="number"
              step="0.0001"
              min="0.00"
              :max="log.total"
              :model-value="log.refundTotal"
              @input="checkError($event.target.value, log, index)"
            >
              <template #prepend>
                {{ order.currency }}
              </template>
            </BaseFormInput>
          </BaseFormGroup>

          <template #footer>
            <BaseButton
              :disabled="isRefund"
              class="w-100"
              @click="makeRefund"
            >
              Refund {{ order.currency }}
              {{ calculateRefund.toFixed(2) }}
            </BaseButton>
          </template>
        </BaseCard>
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import orderAPI from '@order/api/orderAPI.js';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';

export default {
  name: 'RefundOrder',
  components: {
    BaseOrderPageLayout,
    OrderProductsDatatable,
  },
  mixins: [specialCurrencyCalculationMixin],
  props: ['order', 'orderDetail', 'currency', 'transactionLog'],
  data() {
    return {
      isItemSelected: false,
      allProduct: [],
      orderDetailJs: this.orderDetail,
      transactionLogJs: this.transactionLog,
      refundReason: '',
      isRefund: true,
      refundShippingFee: '0.00',
      errRefundShipping: '',
      unfulfilledArr: this.orderDetail.filter((item) => {
        return item.fulfillment_status === 'Unfulfilled';
      }),
      fulfilledArr: this.orderDetail.filter((item) => {
        return item.fulfillment_status === 'Fulfilled';
      }),
    };
  },

  computed: {
    calculateTax() {
      return this.productTax + this.shippingTax;
    },
    productTax() {
      const taxRate = parseFloat(this.order.tax_rate) / 100;
      return this.removedProduct.reduce((tax, item) => {
        if (item.is_taxable) {
          const currentPrice =
            parseFloat(item.unit_price) * parseFloat(item.quantity);
          const priceAfterTax =
            currentPrice * (parseFloat(this.order.tax_rate) / 100);
          const productIncludeTax = priceAfterTax / (1 + taxRate);
          if (this.order.is_product_include_tax) {
            return tax + productIncludeTax;
          }
          return tax + priceAfterTax;
        }
        return tax;
      }, 0);
    },
    shippingTax() {
      const taxRate = parseFloat(this.order.tax_rate) / 100;
      const shippingTax = this.order.is_shipping_fee_taxable
        ? this.refundShippingFee * taxRate
        : 0;
      return shippingTax || 0;
    },
    removedProduct() {
      const combineArr = this.allProduct.concat(
        this.fulfilledArr,
        this.unfulfilledArr
      );
      return combineArr.filter((item) => {
        return item.quantity > 0;
      });
    },

    calculateTotal() {
      const totalAmount = this.removedProduct.reduce((total, item) => {
        return parseFloat(item.total) + parseFloat(total);
      }, 0);
      const totalTax = this.order.is_product_include_tax
        ? 0
        : this.calculateTax;
      const refundShipping = !Number.isNaN(this.refundShippingFee)
        ? parseFloat(this.refundShippingFee)
        : 0;
      const storeCredit = this.order.used_credit_amount / 100;
      const grandTotal =
        totalAmount + parseFloat(refundShipping) + this.shippingTax + totalTax;
      return grandTotal.toFixed(2);
    },

    calculateRefundTotal() {
      const totalAmount = this.removedProduct.reduce((total, item) => {
        return parseFloat(item.total) + parseFloat(total);
      }, 0);
      return totalAmount > this.order.paided_by_customer
        ? this.order.paided_by_customer
        : totalAmount || 0.0;
    },

    calculateItem() {
      if (this.removedProduct.length <= 1) {
        return 'item';
      }
      return 'items';
    },

    calculateRefund() {
      const totalRefund = this.transactionLogJs.reduce(
        (total, item) =>
          total + (item.refundTotal === '' ? 0 : parseFloat(item.refundTotal)),
        0
      );
      return totalRefund || 0.0;
    },

    checkItemSelected() {
      const combineArr = this.allProduct.concat(
        this.fulfilledArr,
        this.unfulfilledArr
      );

      return combineArr.some((item) => item.quantity > 0);
    },

    currentShipping() {
      return (
        parseFloat(this.order.shipping) - parseFloat(this.order.refund_shipping)
      );
    },
  },

  watch: {
    calculateTotal(newValue, oldValue) {
      let condition = true;
      let index = 0;
      let defValue = newValue;

      this.transactionLogJs.forEach((item) => {
        item.refundTotal = (0).toFixed(2);
      });

      this.isRefund = newValue === 0;

      do {
        if (defValue >= parseFloat(this.transactionLogJs[index].total)) {
          this.transactionLogJs[index].refundTotal =
            this.transactionLogJs[index].total;
          defValue -= this.transactionLogJs[index].total;

          if (index + 1 === this.transactionLogJs.length) {
            condition = false;
          } else {
            condition = true;
          }
          index += 1;
        } else {
          this.transactionLogJs[index].refundTotal = defValue;
          condition = false;
        }
      } while (condition);
    },
  },

  created() {
    this.orderDetailJs.forEach((item) => {
      item.max = item.quantity;
      item.quantity = 0;
      item.isItemSelected = false;
    });

    this.transactionLogJs.forEach((item) => {
      item.errRefund = '';
      item.refundTotal = 0;
    });
  },

  methods: {
    displayDiscountPrice(discount, product) {
      const promotionType = discount.promotion.promotion_type;
      const discountType = promotionType.product_discount_type;
      const percentageDiscount =
        product.unit_price *
        ((100 - promotionType.product_discount_value) / 100);
      const discountValue =
        promotionType.product_discount_cap &&
        percentageDiscount > promotionType.product_discount_cap
          ? promotionType.product_discount_cap
          : percentageDiscount;
      const productTotal =
        discountType === 'percentage'
          ? discountValue
          : product.unit_price - promotionType.product_discount_value;
      return productTotal;
    },

    refundShipping(event) {
      this.errRefundShipping = '';
      if (event.target.value > this.currentShipping) {
        this.errRefundShipping = "Can't refund more than available";
      } else if (event.target.value <= 0) {
        event.target.value = 0;
        this.refundShippingFee = parseInt(event.target.value);
      } else {
        this.refundShippingFee = parseFloat(
          event.target.value.replace(/^0.00/, '')
        );
      }
    },
    checkError(value, log, index) {
      this.transactionLogJs[index].errRefund = '';
      log.refundTotal = parseFloat(value.replace(/^0.00/, ''));
      this.isRefund = !(
        parseFloat(this.transactionLogJs[index].refundTotal) > 0
      );

      if (log.refundTotal > parseFloat(log.total)) {
        this.transactionLogJs[index].errRefund =
          "Can't refund more than available";
        this.isRefund = true;
      }
    },

    limitInput(index, event, product) {
      if (event.target.value > product.max) {
        event.target.value = product.max;
      } else if (event.target.value <= 0) {
        event.target.value = 0;
      }
      product.quantity = parseInt(event.target.value);
    },

    calculateProductTotal(product) {
      const discountDetail =
        product.discount_details &&
        Object.keys(JSON.parse(product.discount_details)).length > 0
          ? JSON.parse(product.discount_details)
          : null;
      const productPrice = discountDetail
        ? this.displayDiscountPrice(discountDetail, product)
        : product.unit_price;
      product.total = productPrice * product.quantity;
      return this.specialCurrencyCalculation(
        product.total,
        this.order.currency
      );
    },
    makeRefund() {
      orderAPI
        .refund({
          removedProduct: this.removedProduct,
          transactionLogJs: this.transactionLogJs,
          refundReason: this.refundReason,
          orderJs: this.order,
          calculateRefund: this.calculateRefund,
          shippingTax: this.shippingTax,
          productTax: this.productTax,
          refundShippingFee: this.refundShippingFee,
        })
        .then((response) => {
          const orderRef = this.order.reference_key;
          this.$toast.success('Success', 'Update Order Successfully');
          this.$inertia.visit(`/orders/details/${orderRef}`);
        })
        .catch((error) => {
          this.$toast.error('Error', error);
        });
    },
  },
};
</script>

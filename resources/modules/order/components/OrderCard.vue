<template>
  <div style="overflow: auto">
    <DisplayProduct
      :items="cardArray"
      :currency="filteredCurrency"
    >
      <template #expander="scopedData">
        <div>
          <p
            v-if="scopedData.item.is_discount_applied"
            class="card-text p-two"
            style="color: #808285; text-align: left"
          >
            Discount :
            {{
              JSON.parse(scopedData.item.discount_details).promotion
                .display_name
            }}
          </p>
          <p
            class="card-text p-two"
            style="text-align: left"
          >
            {{ scopedData.item.quantity }} x {{ filteredCurrency }}
            {{
              specialCurrencyCalculation(
                scopedData.item.unit_price,
                filteredCurrency
              )
            }}
          </p>
          <slot name="review" />
        </div>
      </template>

      <template #right="scopedData">
        <div style="float: right; text-align: right">
          <span
            class="card-text product-price p-two"
            :style="{
              'text-decoration': scopedData.item.is_discount_applied
                ? 'line-through'
                : '',
            }"
          >{{ filteredCurrency }}
            {{
              specialCurrencyCalculation(
                scopedData.item.total,
                filteredCurrency
              )
            }} </span><br>
          <span
            v-if="scopedData.item.is_discount_applied"
            class="card-text product-price p-two"
          >{{ filteredCurrency }}
            {{
              specialCurrencyCalculation(
                displayDiscountPrice(
                  JSON.parse(scopedData.item.discount_details),
                  scopedData.item
                ),
                filteredCurrency
              )
            }}
          </span>
        </div>
        <!-- <divstyle="float:right">
                </div> -->

        <!-- {{scopedData.item.discount_details.valueAfterDiscount}} -->
      </template>
    </DisplayProduct>
    <slot name="card-button" />
  </div>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  name: 'CardComponent',
  mixins: [specialCurrencyCalculationMixin],
  props: ['cardArray', 'currency'],
  computed: {
    filteredCurrency() {
      return this.currency === 'MYR' ? 'RM' : this.currency;
    },
  },
  methods: {
    displayDiscountPrice(discount, product) {
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
    },
  },
};
</script>

<style lang="scss" scoped>

.row-product {
  border-top: 1px solid lightgray;
}

.row-product:first-child {
  border-top: 0;
}

// New design
p,
span {
  color: $base-font-color;
  font-family: $base-font-family;
  font-size: $base-font-size;
}

.product-price {
  font-weight: bold;
}

li {
  list-style: none;
  font-size: $base-font-size;
  color: $base-font-color;
  font-family: $base-font-family;
}
</style>

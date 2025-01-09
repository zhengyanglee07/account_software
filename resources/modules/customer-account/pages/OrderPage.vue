<template>
  <EcommerceAccountLayout>
    <template #content>
      <BaseOrderPageLayout>
        <template #header>
          <BaseCard title="AA">
            <h3>
              Order #{{ order.order_number }} was placed on
              {{ order.converted_time }}
            </h3>
          </BaseCard>
        </template>

        <template #left>
          <BaseCard
            v-if="unfulfilledArr.length > 0"
            has-header
            has-footer
            :title="`Unfulfilled (${unfulfilledArr.length})`"
          >
            <OrderProductsDatatable
              v-model="unfulfilledArr"
              :currency="order.currency"
            />
            <template
              v-if="reviews.writeReview"
              #footer
            >
              <BaseButton
                v-if="reviews"
                type="secondary"
                data-bs-toggle="modal"
                data-bs-target="#add-review-modal"
              >
                Write a review
              </BaseButton>
            </template>
          </BaseCard>

          <BaseCard
            v-for="(data, key, index) in arrangedOrderNumber"
            :key="index"
            has-header
            has-footer
            :title="`Fulfilled (${data.items.length}) #${order.order_number}-F${key}`"
          >
            <p>
              {{ data.trackingInfo.courierName }}
              <BaseButton
                v-if="
                  data.trackingInfo.trackingNumber ||
                    data.trackingInfo.trackingUrl
                "
                is-open-in-new-tab
                type="link"
                :href="data.trackingInfo.trackingUrl"
              >
                {{
                  data.trackingInfo.trackingNumber
                    ? data.trackingInfo.trackingNumber
                    : 'Track parcel'
                }}
              </BaseButton>
            </p>
            <OrderProductsDatatable
              v-model="data.items"
              :currency="order.currency"
            />
          </BaseCard>

          <BaseCard
            v-if="removedArr.length != 0"
            has-header
            has-footer
            :title="`Removed (${removedArr.length})`"
          >
            <OrderProductsDatatable
              v-model="removedArr"
              :currency="order.currency"
            />
          </BaseCard>

          <BaseCard
            has-header
            has-footer
          >
            <div>
              <table class="w-100">
                <tr>
                  <td>Subtotal</td>
                  <td>
                    {{ convertToPriceStr(order.subtotal) }}
                  </td>
                </tr>
                <tr
                  v-for="discount in order.order_discount.filter(
                    (discount) => discount.promotion_category !== 'Product'
                  )"
                  :key="discount.id"
                >
                  <td>Discount ( {{ discount.display_name }} )</td>
                  <td>
                    - {{ convertToPriceStr(discount.discount_value / 100) }}
                  </td>
                </tr>
                <tr v-if="order.shipping > 0">
                  <td>Shipping</td>
                  <td>{{ convertToPriceStr(order.shipping) }}</td>
                </tr>
                <tr v-if="order.taxes > 0">
                  <td>Tax</td>
                  <td>{{ convertToPriceStr(order.taxes) }}</td>
                </tr>
                <tr v-if="order.used_credit_amount > 0">
                  <td>Store Credit Used</td>
                  <td>
                    - {{ convertToPriceStr(order.used_credit_amount / 100) }}
                  </td>
                </tr>
                <tr class="fw-1 border-bottom">
                  <td>
                    <p class="h6">
                      Total
                    </p>
                  </td>
                  <td>
                    <p class="h6">
                      {{ convertToPriceStr(order.total) }}
                    </p>
                  </td>
                </tr>
                <tr>
                  <td>Payment Method</td>
                  <td>{{ order.payment_method }}</td>
                </tr>
                <tr v-if="order.shipping_method_name">
                  <td>Shipping Method</td>
                  <td>{{ order.shipping_method_name }}</td>
                </tr>
                <tr v-if="Object.keys(cashbackDetail).length">
                  <td>Cashback</td>
                  <td>
                    {{ convertToPriceStr(cashbackDetail.cashbackDetail.total) }}
                  </td>
                </tr>
              </table>
            </div>
          </BaseCard>
        </template>

        <template #right>
          <BaseCard
            has-header
            has-toolbar
            title="Notes"
          >
            <p class="text-muted">
              {{ order.notes ? order.notes : 'No notes from you' }}
            </p>
          </BaseCard>

          <BaseCard
            v-if="order.delivery_hour_type == 'custom'"
            has-header
            :title="
              order.delivery_type == 'delivery'
                ? 'Delivery Schedule'
                : ' Store Pickup'
            "
          >
            <p>
              {{
                `${
                  order.delivery_type == 'delivery' ? 'Delivery' : 'Pickup'
                } Date: ${order.delivery_date}`
              }}
            </p>
            <p>
              {{
                `${
                  order.delivery_type == 'delivery' ? 'Delivery' : 'Pickup'
                } Time: ${order.delivery_timeslot}`
              }}
            </p>

            <div v-if="order.delivery_type == 'pickup'">
              <p>
                Pickup Location :
                {{
                  Object.keys(locationJS).length > 0
                    ? `${locationJS.address1} ${locationJS.address2} 
            ${locationJS.zip} ${locationJS.city}
             ${locationJS.state} ${locationJS.countr}`
                    : ''
                }}
              </p>
            </div>
          </BaseCard>

          <BaseCard
            has-header
            title="Shipping Address"
          >
            <div v-if="isAddress(shippingAddress)">
              <p
                v-for="(shipping, index) in shippingAddress"
                :key="index"
              >
                {{ shipping }}
              </p>
            </div>
            <p v-else>
              No shipping address available
            </p>
          </BaseCard>

          <BaseCard
            has-header
            title="Billing Address"
          >
            <div v-if="isAddress(billingAddress)">
              <p
                v-for="(billing, index) in billingAddress"
                :key="index"
              >
                {{ billing }}
              </p>
            </div>
            <p v-else>
              No shipping address available
            </p>
          </BaseCard>
        </template>
      </BaseOrderPageLayout>
    </template>
  </EcommerceAccountLayout>

  <ProductReviewsFormModal
    :product="reviews.product"
    :customer="reviews.customer"
    :settings="reviews.settings"
    :customer-name="reviews.name !== null ? reviews.name : ''"
    :purchased="reviews.purchased"
  />
</template>

<script>
import OrderCard from '@order/components/OrderCard.vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';
import ProductReviewsFormModal from '@onlineStore/components/ProductReviewsFormModal.vue';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';

export default {
  name: 'OrderPage',
  components: {
    BaseOrderPageLayout,
    OrderProductsDatatable,
    ProductReviewsFormModal,
  },
  mixins: [specialCurrencyCalculationMixin],
  layout: PublishLayout,

  props: {
    order: { type: Object, default: () => {} },
    reviews: { type: Object, default: () => {} },
  },
  computed: {
    cashbackDetail() {
      return JSON.parse(this.order?.cashback_detail ?? '{}');
    },
    locationJS() {
      return this.order.delivery_type === 'pickup'
        ? JSON.parse(this.order.pickup_location)
        : {};
    },
    currency() {
      return this.order.currency === 'MYR' ? 'RM' : this.order.currency;
    },
    orderDetails() {
      return this.order.order_details;
    },
    fulfilledArr() {
      return this.orderDetails.filter((item) => {
        return item.fulfillment_status === 'Fulfilled';
      });
    },

    unfulfilledArr() {
      return this.orderDetails.filter((item) => {
        return item.fulfillment_status === 'Unfulfilled';
      });
    },

    removedArr() {
      return this.orderDetails.filter((item) => {
        return item.fulfillment_status === 'Removed';
      });
    },
    arrangedOrderNumber() {
      return this.fulfilledArr.reduce((acc, item) => {
        const order = parseInt(item.order_number.split('-F')[1]);
        if (!acc[order]) {
          acc[order] = {
            trackingInfo: {
              courierName: item.tracking_courier_service,
              trackingNumber: item.tracking_number,
              trackingUrl: item.tracking_url,
            },
            items: [],
          };
        }
        acc[order].items.push(item);
        return acc;
      }, {});
    },
    shippingAddress() {
      return [
        this.order.shipping_name,
        this.order.shipping_phoneNumber,
        this.order.shipping_company_name,
        this.order.shipping_address,
        this.order.shipping_city,
        this.order.shipping_state,
        this.order.shipping_zipcode,
        this.order.shipping_country,
      ];
    },
    billingAddress() {
      return [
        this.order.billing_name,
        this.order.billing_phoneNumber,
        this.order.billing_company_name,
        this.order.billing_address,
        this.order.billing_city,
        this.order.billing_state,
        this.order.billing_zipcode,
        this.order.billing_country,
      ];
    },
  },
  methods: {
    convertToPriceStr(price) {
      return `${this.order.currency} ${this.specialCurrencyCalculation(price)}`;
    },
    customValue(custom) {
      const values = [];
      console.log(custom.length === 0);
      if (Object.keys(custom).length !== 0) {
        custom.values.forEach((customValue) => {
          values.push(customValue.value_label);
        });
      }
      return values.join(',');
    },
    isAddress(address) {
      const addressLength = address.filter((item) => item !== null).length;
      return addressLength !== 0;
    },
  },
};
</script>

<style scoped>
td:last-child {
  text-align: right;
}
.card {
  margin-bottom: 0 !important;
}
</style>

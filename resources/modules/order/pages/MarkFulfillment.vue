<template>
  <BasePageLayout
    :page-name="`Fulfill #${order.order_number}`"
    :back-to="`/orders/details/${order.reference_key}`"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #header>
        <p
          v-if="order.easy_parcel_shipments.length !== 0"
          class="alert alert-info"
        >
          Note: This order will be fulfilled with <b>EasyParcel Malaysia</b>
        </p>

        <p
          v-if="order.lalamove_quotations.length !== 0"
          class="alert alert-info"
        >
          Note: This order will be fulfilled with <b>Lalamove Malaysia</b>
        </p>
      </template>
      <template #left>
        <BaseCard
          has-header
          title="Quantity to fulfill"
        >
          <OrderProductsDatatable
            v-model="unfulfillItem"
            is-fulfillment
            :currency="order.currency"
          />
        </BaseCard>

        <BaseCard
          has-header
          title="Tracking Information"
        >
          <BaseFormGroup label="Courier Service">
            <BaseFormSelect
              id="order-fulfillment-courier"
              v-model="selectedTracking"
              placeholder="Select a courier"
              :options="trackingOption"
              label-key="label"
            />
          </BaseFormGroup>

          <BaseFormGroup
            v-if="
              selectedTracking !== null && selectedTracking.label == 'Others'
            "
            label="Tracking URL"
          >
            <BaseFormInput
              id="order-fulfillment-tracking-url"
              v-model="trackingUrl"
              type="text"
              placeholder="Tracking URL"
            />
          </BaseFormGroup>

          <BaseFormGroup label="Tracking Number">
            <BaseFormInput
              id="order-fulfillment-tracking-number"
              v-model="trackingNumber"
              type="text"
              placeholder="Tracking number"
            />
          </BaseFormGroup>
        </BaseCard>
      </template>
      <template #right>
        <BaseCard
          v-if="order.delivery_hour_type == 'custom'"
          has-header
          :title="`${
            order.delivery_type.charAt(0).toUpperCase() +
            order.delivery_type.slice(1)
          } Schedule`"
        >
          <p>{{ order.delivery_type }} Date: {{ order.delivery_date }}</p>
          <p>{{ order.delivery_type }} Time: {{ order.delivery_timeslot }}</p>
        </BaseCard>

        <BaseCard
          has-header
          has-footer
          title="Summary"
        >
          <div v-if="order.shipping_address">
            <p>{{ order.shipping_name }}</p>
            <p>{{ order.shipping_address }}</p>
            <p>{{ order.shipping_zipcode }} {{ order.shipping_city }}</p>
            <p>{{ order.shipping_state }}</p>
            <p>{{ order.shipping_country }}</p>
            <p>{{ order.shipping_phoneNumber }}</p>
          </div>
          <p
            v-else
            class="text-muted"
          >
            No shipping address
          </p>

          <template #footer>
            <BaseButton
              type="secondary"
              class="me-3"
              :href="`/orders/details/${order.reference_key}`"
              @click="handleClickEvent"
            >
              Cancel
            </BaseButton>
            <BaseButton
              :disabled="fulfillingOrder"
              @click="fulfillItem"
            >
              {{ fulfillingOrder ? 'Loading...' : 'Fulfill' }}
            </BaseButton>
          </template>
        </BaseCard>
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>
</template>

<script>
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';
import orderAPI from '@order/api/orderAPI.js';

export default {
  name: 'MarkFulfillment',
  components: {
    BaseOrderPageLayout,
    OrderProductsDatatable,
  },
  props: {
    order: { type: Object, default: () => {} },
    orderDetail: { type: Array, default: () => [] },
    trackingMyJson: { type: Object, default: () => {} },
    afterShipJson: { type: Object, default: () => {} },
  },
  data() {
    return {
      unfulfillItem: this.orderDetail,
      fulfillingOrder: false,
      trackingOption: [],
      selectedTracking: null,
      trackingNumber: '',
      trackingUrl: '',
    };
  },

  computed: {},
  created() {},
  mounted() {
    this.loadTrackingInfo();
  },

  methods: {
    changeQty(id, event, product) {
      if (event.target.value > product.max) {
        event.target.value = product.max;
      } else if (event.target.value <= 0) {
        event.target.value = 0;
      }
      product.quantity = parseInt(event.target.value);
    },

    fulfillItem() {
      this.fulfillingOrder = true;
      const checkNull = this.unfulfillItem.some((item) => item.quantity > 0);
      console.log(checkNull);
      if (!checkNull) {
        this.$toast.error(
          'Order Fulfillment Fail.',
          'Please make sure at least 1 quantity is select'
        );
        this.fulfillingOrder = false;
        return;
      }
      if (this.trackingNumber !== '' && this.selectedTracking == null) {
        this.$toast.error(
          'Order Fulfillment Fail.',
          'Please select a courier service.'
        );
        this.fulfillingOrder = false;
        return;
      }
      orderAPI
        .updateFulfillment({
          unfulfillItem: this.unfulfillItem,
          orderID: this.order.id,
          trackingInfo: {
            selectedTracking: this.selectedTracking,
            trackingNumber: this.trackingNumber,
            trackingUrl: this.trackingUrl,
          },
        })
        .then((response) => {
          this.$inertia.visit(
            `/orders/details/${this.order.reference_key}?fulfillmentredirect=1`
          );
        })
        .catch((error) => {
          this.$toast.error(
            'Error',
            `Failed to fulfill order, ${error.response.data.message}`
          );
        })
        .finally(() => {
          this.fulfillingOrder = false;
        });
    },
    loadTrackingInfo() {
      const aftership = this.afterShipJson.couriers.filter(
        (courier) => courier.visible
      );
      const trackingmy = this.trackingMyJson.couriers.filter(
        (courier) => courier.visible
      );
      aftership.forEach((courier) => {
        courier.source = 'aftership';
      });
      trackingmy.forEach((courier) => {
        courier.source = 'tracking-my';
      });
      this.trackingOption = aftership.concat(trackingmy);
      this.trackingOption.sort((a, b) => {
        const labelA = a.label.toLowerCase();
        const labelB = b.label.toLowerCase();
        return labelA < labelB ? -1 : 0;
      });
      this.trackingOption.push({ label: 'Others' });
    },
  },
};
</script>

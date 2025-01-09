<template>
  <BasePageLayout
    :page-name="`Fulfill #${order.order_number} via EasyParcel`"
    :back-to="`/orders/details/${order.reference_key}`"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #header>
        <p
          v-if="order.shipping_country && !shippingAddressWithinMsia"
          class="alert alert-warning"
        >
          EasyParcel Malaysia can only be used within Malaysia. Your shipping
          address does not meet this requirement
        </p>
      </template>

      <template #left>
        <BaseCard
          has-header
          title="Quantity to fulfill"
        >
          <OrderProductsDatatable
            v-model="localUnfulfilledOrderDetails"
            is-fulfillment
            :currency="order.currency"
          />
        </BaseCard>

        <BaseCard
          v-if="!hasPendingEasyParcelFulfillment || reset"
          has-header
          has-footer
          title="Quote Shipping Rate"
        >
          <p>
            <span class="text-danger">*</span>
            Cut off time for same day courier (pickup) is about 12.00 P.M., if
            your current time is over 12.00 P.M., kindly select the next day to
            pickup.
          </p>
          <BaseFormGroup
            label="Choose a pickup date"
            :error-message="
              showValidationErrors && v$.pickupDate.required.$invalid
                ? 'Date is required'
                : ''
            "
            col="3"
          >
            <BaseDatePicker
              v-model="pickupDate"
              format="YYYY-MM-DD"
              value-type="format"
              :editable="false"
              :disabled-date="disabledDates"
            />
          </BaseFormGroup>

          <BaseFormGroup
            v-if="isQuoteRate"
            label="Choose a courier service to deliver your parcel"
          >
            <p
              v-if="showQuoteRateErrors"
              class="text-danger"
            >
              No courier service available.
            </p>

            <BaseDatatable
              v-if="couriers.length > 0"
              title="courier"
              :table-headers="courierTableHeader"
              :table-datas="couriers"
              no-header
              no-action
            >
              <template #cell-radio="{ row: { rate_id } }">
                <BaseFormGroup>
                  <BaseFormRadio
                    :id="`easyparcel-fulfillment-courier-${rate_id}`"
                    v-model="selectedCourierId"
                    :value="rate_id"
                  />
                </BaseFormGroup>
              </template>
              <template #cell-courier-logo="{ row: { courier_logo } }">
                <img
                  :src="courier_logo"
                  width="100"
                >
              </template>

              <template #cell-courier-name="{ row: { courier_name, price } }">
                <p>{{ courier_name }}</p>
                <b> {{ `${accountDefaultCurrency} ${price}` }} </b>
              </template>
            </BaseDatatable>
          </BaseFormGroup>

          <BaseFormGroup>
            <BaseButton
              v-if="hasPendingEasyParcelFulfillment"
              type="secondary"
              @click="cancelReset"
            >
              Cancel reset
            </BaseButton>
          </BaseFormGroup>

          <template #footer>
            <BaseButton
              :disabled="quotingRate"
              @click="quoteRate"
            >
              <div v-if="!quotingRate">
                Quote Rate
              </div>
              <i
                v-else
                class="fas fa-circle-notch fa-spin pr-0"
              />
            </BaseButton>
          </template>
        </BaseCard>

        <BaseCard
          v-else
          has-header
          has-toolbar
          title="Pending EasyParcel Fulfillment"
        >
          <BaseFormGroup col="2">
            <b>Courier name:</b>
          </BaseFormGroup>
          <BaseFormGroup col="10">
            {{ selectedCourier && selectedCourier.courier_name }}
          </BaseFormGroup>

          <BaseFormGroup col="2">
            <b>Price:</b>
          </BaseFormGroup>
          <BaseFormGroup col="10">
            {{ accountDefaultCurrency }}
            {{ selectedCourier && selectedCourier.price }}
          </BaseFormGroup>

          <template #toolbar>
            <div class="text-end">
              <BaseButton
                type="secondary"
                size="sm"
                @click="resetFulfillment"
              >
                Reset
              </BaseButton>
              <i
                class="ms-2 fa-solid fa-circle-question"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Only reset if you plan to partial fulfill the order or you want to use a different courier service to deliver items, instead of what the customer selected during checkout"
              />
            </div>
          </template>
        </BaseCard>
      </template>

      <template #right>
        <BaseCard
          has-header
          title="Summary"
        >
          <p
            v-if="!location"
            class="alert alert-warning"
          >
            Please update your default sending/pickup location in
            <BaseButton
              type="link"
              href="/location/settings"
            >
              Location Settings
            </BaseButton>
            before using EasyParcel Malaysia fulfillment
          </p>

          <b>Shipping From</b>
          <div v-if="location">
            <p>{{ location.name }}</p>
            <p>{{ location.address1 }}</p>
            <p>{{ location.address2 }}</p>
            <p>{{ location.zip }} {{ location.city }} {{ location.state }}</p>
            <p>{{ location.country }}</p>
            <p>{{ location.phone_number }}</p>
          </div>
          <p
            v-else
            class="text-muted"
          >
            No sending address
          </p>

          <p
            v-if="!order.shipping_address"
            class="alert alert-warning"
          >
            Shipping address is required for EasyParcel Malaysia fulfillment
          </p>

          <b>Shipping To</b>
          <div v-if="order.shipping_address">
            <p>{{ order.shipping_name }}</p>
            <p>{{ order.shipping_address }}</p>
            <p>
              {{ order.shipping_zipcode }} {{ order.shipping_city }}
              {{ order.shipping_state }}
            </p>
            <p>
              {{ order.shipping_country }}
            </p>
            <p>{{ order.shipping_phoneNumber }}</p>
          </div>
          <p
            v-else
            class="text-muted"
          >
            No shipping address
          </p>
        </BaseCard>
      </template>

      <template #footer>
        <BaseButton
          type="link"
          class="me-3"
          :href="`/orders/details/${order.reference_key}`"
        >
          Cancel
        </BaseButton>
        <BaseButton
          :disabled="!canFulfill || fulfilling"
          @click="fulfillEasyParcel"
        >
          <div v-if="!fulfilling">
            Fulfill Order
          </div>
          <i
            v-else
            class="fas fa-circle-notch fa-spin pr-0"
          />
        </BaseButton>
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import shortid from 'shortid';
import getOrderProductsTotalWeight from '@order/lib/order.js';
import orderAPI from '@order/api/orderAPI.js';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';

export default {
  name: 'EasyParcelOrderFulfillment',
  components: {
    BaseOrderPageLayout,
    OrderProductsDatatable,
  },
  props: {
    order: { type: Object, required: true, default: () => {} },
    unfulfilledOrderDetails: { type: Array, required: true, default: () => [] },
    location: { type: Object, default: () => {} },
    currency: { type: Object, default: () => {} },
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      localUnfulfilledOrderDetails: [],
      pickupDate: '',
      selectedCourierId: null,
      selectedCourier: null,

      couriers: [],

      quotingRate: false,
      fulfilling: false,
      showValidationErrors: false,
      showQuoteRateErrors: false,
      reset: false,
      isQuoteRate: false,

      courierTableHeader: [
        { name: '', key: 'radio', custom: true },
        { name: 'Courier Logo', key: 'courier-logo', custom: true },
        { name: 'Courier Name', key: 'courier-name', custom: true },
        { name: 'Scheduled pickup for parcel', key: 'scheduled_start_date' },
        { name: 'Pickup date', key: 'pickup_date' },
      ],
    };
  },
  validations: {
    pickupDate: {
      required,
    },
  },
  computed: {
    shippingAddressWithinMsia() {
      return this.order.shipping_country.toLowerCase() === 'malaysia';
    },

    hasPendingEasyParcelFulfillment() {
      return (
        this.order.easy_parcel_shipments.filter((es) => !es.order_number)
          .length !== 0
      );
    },

    unsettledEasyParcelShipment() {
      return this.order.easy_parcel_shipments.find((ep) => !ep.order_number);
    },

    canFulfill() {
      if (this.hasPendingEasyParcelFulfillment) {
        return (
          this.selectedCourier && this.location && this.order.shipping_address
        );
      }

      return (
        this.pickupDate &&
        this.selectedCourier &&
        this.location &&
        this.order.shipping_address
      );
    },

    accountDefaultCurrency() {
      return this.currency.currency === 'MYR' ? 'RM' : this.currency.currency;
    },
  },
  watch: {
    selectedCourierId(id) {
      this.selectedCourier = this.couriers.find((e) => e.rate_id === id);
    },
    pickupDate() {
      this.handlePickupDateInput();
    },
  },
  mounted() {
    this.localUnfulfilledOrderDetails = [...this.unfulfilledOrderDetails];

    if (this.unsettledEasyParcelShipment) {
      this.selectedCourier = {
        ...this.unsettledEasyParcelShipment,
        rate_id: shortid.generate(), // note: this rate_id is not the same with official rate_id, just a dummy placeholder
      };
      this.couriers = [this.selectedCourier];
    }
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

    disabledDates(date) {
      const oneDayInMs = 3600 * 1000 * 24;
      return date.getTime() < new Date(Date.now() - oneDayInMs).getTime();
    },
    clearValidationErrors() {
      this.showValidationErrors = false;
    },
    isProductQuantityZero() {
      return !this.localUnfulfilledOrderDetails.some((od) => od.quantity > 0);
    },

    handlePickupDateInput() {
      this.clearValidationErrors();
      this.couriers = [];
      this.selectedCourier = null;
      this.isQuoteRate = false;
    },

    cancelReset() {
      this.reset = false;

      if (this.unsettledEasyParcelShipment) {
        this.selectedCourier = {
          ...this.unsettledEasyParcelShipment,
          rate_id: shortid.generate(), // note: this rate_id is not the same with official rate_id, just a dummy placeholder
        };
        this.couriers = [this.selectedCourier];
      }
    },

    resetFulfillment() {
      this.reset = true;
      this.couriers = [];
      this.selectedCourier = null;
      this.isQuoteRate = false;
    },

    async quoteRate() {
      this.isQuoteRate = true;
      if (this.v$.pickupDate.$invalid) {
        this.showValidationErrors = true;
        return;
      }

      this.clearValidationErrors();

      if (this.isProductQuantityZero()) {
        this.$toast.error(
          'Order Fulfillment Fail.',
          'Please make sure at least 1 quantity is select'
        );
        return;
      }

      if (this.unfulfilledOrderDetails.some((od) => !od.users_product)) {
        this.$toast.warning(
          'Warning',
          'Could not identify some products of your order'
        );
        return;
      }

      this.quotingRate = true;

      try {
        const res = await orderAPI.easyparcelQuotation({
          accountId: this.order.account_id,
          fields: {
            send_code: this.order.shipping_zipcode,
            send_state: this.order.shipping_state,
            send_country: this.order.shipping_country,
            date_coll: this.pickupDate,
            weight: getOrderProductsTotalWeight(this.unfulfilledOrderDetails),
            abc: 'abc',
          },
        });

        if (res.data.methods.length === 0) {
          this.showQuoteRateErrors = true;
          return;
        }

        this.couriers = res.data.methods.map((m) => ({
          ...m,
          price:
            this.accountDefaultCurrency === 'RM'
              ? m.price
              : (parseFloat(m.price) * this.currency.suggestRate).toFixed(2),
        }));
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to quote rate: ${err.response.data.message}`
        );
      } finally {
        this.quotingRate = false;
      }
    },
    async fulfillEasyParcel() {
      this.fulfilling = true;

      try {
        const refKey = this.order.reference_key;

        await orderAPI.easyparcelFulfill(refKey, {
          unfulfilledOrderDetails: this.localUnfulfilledOrderDetails,
          courier: this.selectedCourier,
          unsettledCourier: this.unsettledEasyParcelShipment,
        });
        this.$inertia.visit(`/orders/details/${refKey}?fulfillmentredirect=1`);
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to fulfill order, ${err.response.data.message}`
        );
      } finally {
        this.fulfilling = false;
      }
    },
  },
};
</script>

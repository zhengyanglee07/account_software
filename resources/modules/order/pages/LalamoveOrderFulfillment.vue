<template>
  <BasePageLayout
    :page-name="`Fulfill #${order.order_number} via Lalamove`"
    :back-to="`/orders/details/${order.reference_key}`"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #header>
        <p
          v-if="order.shipping_country && !shippingAddressWithinMsia"
          class="alert alert-warning"
        >
          Lalamove Malaysia can only be used within Malaysia. Your shipping
          address does not meet this requirement.
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
          v-if="!hasPendingLalamoveQuotation || resetQuotation"
          has-header
          has-footer
          title="Quote Shipping Rate"
        >
          <p>
            <span class="text-danger">*</span>
            Note: pickup date & time you have chosen will always be assumed in
            UTC+8 (Malaysia) timezone
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
            label="Choose a pickup time"
            :error-message="
              showValidationErrors && v$.pickupTime.required.$invalid
                ? 'Time is required'
                : ''
            "
          >
            <BaseFormGroup col="3">
              <BaseFormSelect
                v-model="pickupTime"
                @change="clearValidationErrors"
              >
                <option
                  value=""
                  disabled
                >
                  Choose...
                </option>
                <option
                  v-for="time in pickupTimeOptions"
                  :key="time"
                  :value="time"
                >
                  {{ time }}
                </option>
              </BaseFormSelect>
            </BaseFormGroup>
          </BaseFormGroup>

          <BaseFormGroup
            v-if="isQuoteRate"
            label="Choose a service to deliver your parcel"
          >
            <BaseDatatable
              v-if="deliverTableDatas.length > 0"
              title="courier"
              :table-headers="deliverTableHeader"
              :table-datas="deliverTableDatas"
              no-header
              no-action
            >
              <template #cell-radio="{ row: { type } }">
                <BaseFormGroup>
                  <BaseFormRadio
                    :id="`lalamove-fulfillment-deliver-${type}`"
                    v-model="selectedServiceType"
                    :value="type"
                  />
                </BaseFormGroup>
              </template>
              <template #cell-courier-logo>
                <img
                  src="@order/assets/media/lalamove.png"
                  alt="lalamove-logo"
                  width="100"
                >
              </template>

              <template #cell-courier-name="{ row: { type, value } }">
                <p>Lalamove - {{ type }}</p>
                <b> {{ `${accountDefaultCurrency} ${value.totalFee}` }} </b>
              </template>
            </BaseDatatable>
          </BaseFormGroup>

          <BaseButton
            v-if="hasPendingLalamoveQuotation"
            type="secondary"
            @click="cancelReset"
          >
            Cancel reset
          </BaseButton>

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
          title="Pending Lalamove Malaysia fulfillment"
        >
          <BaseFormGroup col="2">
            <b>Service type: </b>
          </BaseFormGroup>
          <BaseFormGroup col="10">
            {{ selectedServiceType }}
          </BaseFormGroup>

          <BaseFormGroup col="2">
            <b>Price: </b>
          </BaseFormGroup>
          <BaseFormGroup col="10">
            {{ selectedQuotation && selectedQuotation.totalFeeCurrency }}
            {{ selectedQuotation && selectedQuotation.totalFee }}
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
            before using Lalamove Malaysia fulfillment
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
            Shipping address is required for Lalamove Malaysia fulfillment
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
          @click="fulfill"
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
import orderAPI from '@order/api/orderAPI.js';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

export default {
  name: 'LalamoveOrderFulfillment',
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
      pickupTime: '',
      selectedQuotation: null,
      selectedServiceType: '',

      quotations: {},

      quotingRate: false,
      fulfilling: false,
      showValidationErrors: false,
      resetQuotation: false,
      isQuoteRate: false,

      deliverTableHeader: [
        { name: '', key: 'radio', custom: true },
        { name: 'Courier Logo', key: 'courier-logo', custom: true },
        { name: 'Courier Name', key: 'courier-name', custom: true },
      ],
    };
  },
  validations: {
    pickupDate: {
      required,
    },
    pickupTime: {
      required,
    },
  },
  computed: {
    deliverTableDatas() {
      const datas = [];
      Object.entries(this.quotations).forEach(([key, value]) => {
        datas.push({ type: key, value });
      });
      return datas;
    },
    pickupTimeOptions() {
      const timeFormat = 'HH:mm';
      const times = ['11:00'];
      let i = 0;

      for (;;) {
        const nextTime = dayjs(times[i], timeFormat)
          .add(15, 'minutes')
          .format(timeFormat);
        times.push(nextTime);
        i += 1;

        if (nextTime === '23:00') break;
      }

      return times;
    },

    shippingAddressWithinMsia() {
      return this.order.shipping_country.toLowerCase() === 'malaysia';
    },

    hasPendingLalamoveQuotation() {
      return (
        this.order.lalamove_quotations.filter((l) => !l.lalamove_delivery_order)
          .length !== 0
      );
    },

    unsettledLalamoveFulfillment() {
      return this.order.lalamove_quotations.find(
        (l) => !l.lalamove_delivery_order
      );
    },

    canFulfill() {
      if (this.hasPendingLalamoveQuotation) {
        return (
          this.selectedQuotation &&
          this.selectedServiceType &&
          this.location &&
          this.order.shipping_address
        );
      }

      return (
        this.pickupDate &&
        this.selectedQuotation &&
        this.location &&
        this.order.shipping_address
      );
    },

    accountDefaultCurrency() {
      return this.currency.currency === 'MYR' ? 'RM' : this.currency.currency;
    },

    scheduledDatetime() {
      return dayjs(`${this.pickupDate} ${this.pickupTime}`, 'YYYY-MM-DD HH:mm')
        .tz('Asia/Kuala_Lumpur')
        .utc()
        .format();
    },
  },
  watch: {
    selectedServiceType(serviceType) {
      Object.entries(this.quotations).forEach(([type, value]) => {
        if (type === serviceType) this.selectedQuotation = value;
      });
      if (this.selectedQuotation)
        this.selectedQuotation.id = this.unsettledLalamoveFulfillment?.id;
    },
    pickupDate() {
      this.handlePickupDateInput();
    },
  },
  mounted() {
    this.localUnfulfilledOrderDetails = [...this.unfulfilledOrderDetails];

    if (this.unsettledLalamoveFulfillment) {
      const lalamoveQuotation = this.unsettledLalamoveFulfillment;
      this.selectedServiceType = lalamoveQuotation.service_type;
      this.selectedQuotation = {
        id: lalamoveQuotation.id,
        totalFee: lalamoveQuotation.total_fee_amount,
        totalFeeCurrency: lalamoveQuotation.total_fee_currency,
      };
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

    resetFulfillment() {
      this.resetQuotation = true;
      this.selectedServiceType = '';
      this.selectedQuotation = null;
      this.isQuoteRate = false;
    },

    cancelReset() {
      this.resetQuotation = false;

      if (this.unsettledLalamoveFulfillment) {
        const lalamoveQuotation = this.unsettledLalamoveFulfillment;
        this.selectedServiceType = lalamoveQuotation.service_type;
        this.selectedQuotation = {
          id: lalamoveQuotation.id,
          totalFee: lalamoveQuotation.total_fee_amount,
          totalFeeCurrency: lalamoveQuotation.total_fee_currency,
        };
        this.pickupDate = '';
        this.pickupTime = '';
        this.quotations = {};
      }
    },

    disabledDates(date) {
      const oneDayInMs = 3600 * 1000 * 24;
      return date.getTime() < new Date(Date.now() - oneDayInMs).getTime();
    },
    clearValidationErrors() {
      this.showValidationErrors = false;
    },

    handlePickupDateInput() {
      this.clearValidationErrors();
      this.quotations = [];
      this.isQuoteRate = false;
    },
    async quoteRate() {
      this.isQuoteRate = true;

      if (this.v$.pickupDate.$invalid || this.v$.pickupTime.$invalid) {
        this.showValidationErrors = true;
        return;
      }

      this.clearValidationErrors();

      this.quotingRate = true;

      try {
        const res = await orderAPI.lalamoveQuotation({
          accountId: this.order.account_id,
          fields: {
            street: this.order.shipping_address,
            city: this.order.shipping_city,
            state: this.order.shipping_state,
            zip: this.order.shipping_zipcode,
            country: this.order.shipping_country,
            name: this.order.shipping_name,
            phone: this.order.shipping_phoneNumber.replace(/\D/g, ''),
            scheduleAt: this.scheduledDatetime,
          },
        });

        // note: this is an object with service_type as key
        this.quotations = res.data.quotations;

        Object.keys(this.quotations).forEach((type) => {
          this.quotations[type] = {
            ...this.quotations[type],
            id: shortid.generate(), // this (dummy) id acts as a sanity check against unsettled quotation later
          };
        });
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to quote rate: ${err.response.data.message}`
        );
      } finally {
        this.quotingRate = false;
      }
    },

    async fulfill() {
      this.fulfilling = true;

      try {
        const refKey = this.order.reference_key;

        await orderAPI.lalamoveFulfill(refKey, {
          unfulfilledOrderDetails: this.localUnfulfilledOrderDetails,
          quotation: this.selectedQuotation,
          scheduledAt: this.scheduledDatetime,
          serviceType: this.selectedServiceType,
          unsettledQuotation: this.unsettledLalamoveFulfillment,
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

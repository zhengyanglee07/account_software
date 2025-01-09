<template>
  <BasePageLayout
    :page-name="`Fulfill #${order.order_number} via Delyva`"
    :back-to="`/orders/details/${order.reference_key}`"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #header>
        <p
          v-if="order.shipping_country && !shippingAddressWithinMsia"
          class="alert alert-warning"
        >
          Delyva can only be used within Malaysia. Your shipping address does
          not meet this requirement.
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
          v-if="!hasPendingDelyvaQuotation || resetQuotation"
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
            col="3"
            label="Choose a pickup date"
            :error-message="
              showValidationErrors && v$.pickupDate.required.$invalid
                ? 'Date is required'
                : ''
            "
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
              v-if="quotations.length > 0"
              title="courier"
              :table-headers="deliverTableHeader"
              :table-datas="quotations"
              no-header
              no-action
            >
              <template #cell-radio="{ row: { service } }">
                <BaseFormGroup>
                  <BaseFormRadio
                    :id="`delyva-fulfillment-deliver-${service.code}`"
                    v-model="selectedServiceType"
                    :value="service.code"
                  />
                </BaseFormGroup>
              </template>
              <template #cell-courier-logo="{ row: { service } }">
                <img
                  :src="
                    service?.serviceCompany?.logo
                      ? `https://cdn.delyva.app/${service.serviceCompany.logo}`
                      : delyvaLogo
                  "
                  alt="delyva-logo"
                  width="100"
                >
              </template>

              <template #cell-courier-name="{ row: { service, price } }">
                <p>
                  {{ service.serviceCompany.name }} -
                  {{ service.name }}
                </p>
                <br>
                <b>{{ accountDefaultCurrency }} {{ price.amount.toFixed(2) }}</b>
              </template>
            </BaseDatatable>
          </BaseFormGroup>

          <BaseButton
            v-if="hasPendingDelyvaQuotation"
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
          title="Pending Delyva fulfillment"
        >
          <BaseFormGroup col="3">
            <b>Delivery Courier:</b>
          </BaseFormGroup>
          <BaseFormGroup col="9">
            {{ selectedServiceName }}
          </BaseFormGroup>

          <BaseFormGroup col="3">
            <b>Service Type:</b>
          </BaseFormGroup>
          <BaseFormGroup col="9">
            {{ selectedQuotationDetail?.serviceType }}
          </BaseFormGroup>

          <BaseFormGroup col="3">
            <b>Price:</b>
          </BaseFormGroup>
          <BaseFormGroup col="9">
            {{
              selectedQuotation?.totalFeeCurrency === 'MYR'
                ? 'RM'
                : selectedQuotation?.totalFeeCurrency
            }}
            {{
              selectedQuotation &&
                parseInt(selectedQuotation.totalFee).toFixed(2)
            }}
          </BaseFormGroup>
          <BaseFormGroup col="3">
            <b>Service infos:</b>
          </BaseFormGroup>
          <BaseFormGroup col="9">
            <BaseButton
              type="link"
              size="sm"
              data-bs-toggle="modal"
              data-bs-target="#delyvaServiceDetailModal"
            >
              View
            </BaseButton>
          </BaseFormGroup>

          <BaseModal
            :title="`${selectedServiceName} service info`"
            modal-id="delyvaServiceDetailModal"
          >
            <div
              class="delyva-service-detail"
              v-html="selectedQuotationDetail?.custDescription"
            />
          </BaseModal>

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
            before using Delyva fulfillment
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
            Shipping address is required for Delyva fulfillment
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
import shortid from 'shortid';
import orderAPI from '@order/api/orderAPI.js';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';
import delyvaLogo from '@order/assets/media/delyva.png';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

export default {
  name: 'DelyvaOrderFulfillment',
  components: {
    BaseOrderPageLayout,
    OrderProductsDatatable,
  },
  props: {
    order: { type: Object, required: true, default: () => {} },
    unfulfilledOrderDetails: { type: Array, required: true, default: () => [] },
    location: { type: Object, default: () => {} },
    currency: { type: Object, default: () => {} },
    delyvaInfo: { type: Object, default: () => {} },
  },

  setup() {
    return {
      v$: useVuelidate(),
      delyvaLogo,
    };
  },
  data() {
    return {
      localUnfulfilledOrderDetails: [],
      pickupDate: '',
      pickupTime: '',
      selectedQuotation: null,
      selectedQuotationDetail: null,
      selectedServiceType: '',
      selectedServiceName: '',
      test: '',

      quotations: {},

      isQuoteRate: false,
      quotingRate: false,
      fulfilling: false,
      showValidationErrors: false,
      resetQuotation: false,
      deliverTableHeader: [
        { name: '', key: 'radio', custom: true },
        { name: 'Courier Logo', key: 'courier-logo', custom: true },
        { name: 'Courier Name', key: 'courier-name', custom: true },
      ],
    };
  },
  computed: {
    pickupTimeOptions() {
      const timeFormat = 'HH:mm';
      const times = ['06:00'];
      let i = 0;

      for (;;) {
        const nextTime = dayjs(times[i], timeFormat)
          .add(15, 'minutes')
          .format(timeFormat);
        times.push(nextTime);
        i += 1;

        if (nextTime === '23:45') break;
      }

      return times;
    },

    shippingAddressWithinMsia() {
      return (
        this.order.shipping_country.toLowerCase() === 'malaysia' &&
        this.location.country.toLowerCase() === 'malaysia'
      );
    },

    hasPendingDelyvaQuotation() {
      return (
        this.order.delyva_quotations.filter(
          (l) => !l.delyva_delivery_order_info
        ).length !== 0
      );
    },

    unsettledDelyvaFulfillment() {
      return this.order.delyva_quotations.find(
        (l) => !l.delyva_delivery_order_info
      );
    },

    canFulfill() {
      if (this.hasPendingDelyvaQuotation) {
        return (
          this.selectedQuotation &&
          this.selectedServiceType &&
          this.location &&
          this.order.shipping_address &&
          this.selectedServiceName
        );
      }

      return (
        this.pickupDate &&
        this.selectedQuotation &&
        this.location &&
        this.order.shipping_address
      );
    },

    selectedTotalWeight() {
      let sum = 0;
      for (let i = 0; i < this.unfulfilledOrderDetails.length; i++) {
        let eachItemWeight = 0;
        if (this.unfulfilledOrderDetails[i].quantity > 0) {
          eachItemWeight =
            this.unfulfilledOrderDetails[i].weight /
            this.unfulfilledOrderDetails[i].max;
          sum += eachItemWeight * this.unfulfilledOrderDetails[i].quantity;
        }
      }
      return sum;
    },
    selectedTotalQuantity() {
      let sum = 0;
      for (let i = 0; i < this.unfulfilledOrderDetails.length; i++) {
        sum += this.unfulfilledOrderDetails[i].quantity;
      }
      return sum;
    },

    accountDefaultCurrency() {
      return this.currency.currency === 'MYR' ? 'RM' : this.currency.currency;
    },

    scheduledDatetime() {
      if (this.pickupDate && this.pickupTime) {
        return dayjs(
          `${this.pickupDate} ${this.pickupTime}`,
          'YYYY-MM-DD HH:mm'
        )
          .tz('Asia/Kuala_Lumpur')
          .format();
      }
      return 'now';
    },
  },
  watch: {
    selectedServiceType(serviceType) {
      if (Object.keys(this.quotations).length === 0) return;
      this.selectedQuotation = this.quotations.find(
        (e) => e.service.code === serviceType
      );
    },
    pickupDate() {
      this.handlePickupDateInput();
    },
  },
  validations: {
    pickupDate: {
      required,
    },
    pickupTime: {
      required,
    },
  },
  mounted() {
    this.localUnfulfilledOrderDetails = [...this.unfulfilledOrderDetails];
    if (this.unsettledDelyvaFulfillment) {
      const delyvaQuotation = this.unsettledDelyvaFulfillment;
      this.selectedServiceType = delyvaQuotation.service_code;
      this.selectedServiceName = delyvaQuotation.service_name;
      this.selectedQuotation = {
        id: delyvaQuotation.id,
        totalFee: delyvaQuotation.total_fee_amount,
        totalFeeCurrency: delyvaQuotation.total_fee_currency,
        service: {
          name: delyvaQuotation.service_name,
          code: delyvaQuotation.service_code,
        },
        price: {
          amount: delyvaQuotation.total_fee_amount,
          currency: delyvaQuotation.total_fee_currency,
        },
      };
      this.selectedQuotationDetail = JSON.parse(
        delyvaQuotation.service_detail ?? '{}'
      );
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
      if (this.unsettledDelyvaFulfillment) {
        const delyvaQuotation = this.unsettledDelyvaFulfillment;
        this.selectedServiceType = delyvaQuotation.service_code;
        this.selectedQuotation = {
          id: delyvaQuotation.id,
          totalFee: delyvaQuotation.total_fee_amount,
          totalFeeCurrency: delyvaQuotation.total_fee_currency,
          service: {
            name: delyvaQuotation.service_name,
            code: delyvaQuotation.service_code,
          },
          price: {
            amount: delyvaQuotation.total_fee_amount,
            currency: delyvaQuotation.total_fee_currency,
          },
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
      this.selectedQuotation = null;
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
        const res = await orderAPI.delyvaQuotation({
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
          orderId: this.order.id,
          selectedTotalWeight: this.selectedTotalWeight,
          selectedTotalQuantity: this.selectedTotalQuantity,
        });
        this.quotations = res.data.data.services;
        // filter out next day delivery from food
        if (this.delyvaInfo.item_type === 'FOOD') {
          const forDeletion = ['NDD'];
          this.quotations = this.quotations.filter(
            (quotation) => !forDeletion.includes(quotation.service.code)
          );
        }
        // note: this is an object with service_type as key
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
    selectQuotation(q, k) {
      this.selectedQuotation = q;
      this.selectedServiceType = k + 1;
    },

    async fulfill() {
      this.fulfilling = true;
      try {
        const refKey = this.order.reference_key;
        await orderAPI.delyvaFulfill(refKey, {
          accountId: this.order.account_id,
          unfulfilledOrderDetails: this.localUnfulfilledOrderDetails,
          quotation: this.selectedQuotation,
          scheduledAt: this.scheduledDatetime,
          // serviceType: this.selectedServiceType,
          // serviceName: this.selectedServiceName,
          unsettledQuotation: this.unsettledDelyvaFulfillment,
          notes: this.order.notes,
          totalWeight: this.selectedTotalWeight,
          totalQuantity: this.selectedTotalQuantity,
        });

        this.$inertia.visit(`/orders/details/${refKey}?fulfillmentredirect=1`);
      } catch (err) {
        this.$toast.error(
          'Error',
          `Order Fulfillment Failed, ${err.response.data.message}`
        );
      } finally {
        this.fulfilling = false;
      }
    },
  },
};
</script>

<style>
.delyva-service-detail > b {
  margin-bottom: 1.5rem;
}
</style>

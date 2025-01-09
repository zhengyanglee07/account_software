<template>
  <BasePageLayout
    :page-name="`#${orders.order_number}`"
    back-to="/orders"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #header>
        <div class="order-header row ps-0 mb-5">
          <div class="col-xl-6">
            <BaseBadge
              :text="orders.payment_status"
              :type="paymentStatusBadges"
            />
            <BaseBadge
              :text="orders.fulfillment_status"
              :type="fulfillStatusBadges"
            />
            <BaseBadge
              v-if="syncOrder.additional_status == 'Archived'"
              text="Archived"
              type="secondary"
            />
            <span class="order-header-description">
              <span>Created at: </span>
              <span class="text-muted">{{ orders.convertTime }}</span>
              <span class="ms-3">From: </span>
              <span class="text-muted">{{
                syncOrder.acquisition_channel
              }}</span>
            </span>
          </div>

          <div class="col-xl-6 text-end">
            <BaseButton
              v-if="orders.previousOrder"
              :href="`/orders/details/${orders.previousOrder.reference_key}`"
              type="light-primary"
              class="text-end me-3"
            >
              <i class="fa-solid fa-chevron-left" />
            </BaseButton>

            <BaseButton
              v-if="orders.nextOrder"
              :href="`/orders/details/${orders.nextOrder.reference_key}`"
              type="light-primary"
              class="me-3"
            >
              <i class="fa-solid fa-chevron-right" />
            </BaseButton>

            <BaseButton
              v-if="netPayment > 0"
              type="white"
              :href="`/orders/details/${orders.reference_key}/makeRefund`"
              class="me-3"
            >
              <i class="fas fa-share-square menu-icons me-2" /> Refund
            </BaseButton>
            <BaseButton
              type="white"
              class="me-3"
              @click="printPackingSlip"
            >
              <i class="fas fa-print menu-icons me-2" />Packing Slip
            </BaseButton>

            <BaseButton
              id="order-action-dropdown-button"
              type="white"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              More Action
              <i class="fas fa-caret-down ms-2" />
            </BaseButton>

            <BaseDropdown id="order-action-dropdown-list">
              <BaseDropdownOption
                text="Edit"
                @click="editOrder"
              />
              <BaseDropdownOption
                v-if="syncOrder.additional_status == 'Archived'"
                text="Unarchive"
                @click="updateAdditionalStatus('unarchive')"
              />
              <BaseDropdownOption
                v-else
                text="Archive"
                @click="updateAdditionalStatus('archive')"
              />
            </BaseDropdown>
          </div>
        </div>

        <!-- easy parcel order status warnings -->
        <p
          v-if="isEasyParcelInsufficientCredit"
          class="alert alert-warning"
        >
          Your EasyParcel account has insufficient credit. Kindly settle the
          parcel payment manually on
          <BaseButton
            type="link"
            href="https://easyparcel.com/"
            is-open-in-new-tab
          >
            EasyParcel website
          </BaseButton>
        </p>
        <p
          v-if="isEasyParcelShipmentWaitingPayment"
          class="alert alert-warning"
        >
          Your parcel is waiting for payment. Kindly settle the payment manually
          on
          <BaseButton
            type="link"
            href="https://easyparcel.com/"
            is-open-in-new-tab
          >
            EasyParcel website
          </BaseButton>
        </p>
      </template>
      <template #left>
        <!-- ------------------------------------------------Unfulfilled---------------------------------------------------- -->
        <BaseCard
          v-if="unfulfilledArr.length > 0"
          has-header
          has-footer
          :title="`Unfulfilled (${unfulfilledArr.length})`"
        >
          <OrderProductsDatatable
            v-model="unfulfilledArr"
            :currency="orders.currency"
          />
          <template
            v-if="unfulfilledPhysicalProduct.length > 0"
            #footer
          >
            <BaseButton
              v-if="!hasEasyParcel && !hasLalamove && !hasDelyva"
              @click="markStatus"
            >
              Mark As Fulfilled
            </BaseButton>

            <BaseButton
              v-else
              id="order-fulfillment-dropdown-button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              Fulfill <i class="fas fa-caret-down ms-2" />
            </BaseButton>

            <BaseDropdown
              id="order-fulfillment-dropdown-list"
              size="lg"
            >
              <BaseDropdownOption
                text="Mark As Fulfilled"
                :link="markAsFulfilledLink"
              />
              <BaseDropdownOption
                v-if="hasEasyParcel"
                text="Mark as fulfilled with EasyParcel Malaysia"
                :link="`/orders/details/${orders.reference_key}/fulfillment/easyparcel`"
              >
                <img
                  src="@order/assets/media/easyparcel.png"
                  alt="easy-parcel-logo"
                  width="22"
                >
              </BaseDropdownOption>
              <BaseDropdownOption
                v-if="hasLalamove && !hasPendingEasyParcelFulfillment"
                text="Mark as fulfilled with Lalamove Malaysia"
                :link="`/orders/details/${orders.reference_key}/fulfillment/lalamove`"
              >
                <img
                  src="@order/assets/media/lalamove.png"
                  alt="easy-parcel-logo"
                  width="22"
                >
              </BaseDropdownOption>
              <BaseDropdownOption
                v-if="hasDelyva && !hasPendingEasyParcelFulfillment"
                text="Mark as fulfilled with Delyva Malaysia"
                :link="`/orders/details/${orders.reference_key}/fulfillment/delyva`"
              >
                <img
                  src="@order/assets/media/delyva.png"
                  alt="easy-parcel-logo"
                  width="22"
                >
              </BaseDropdownOption>
            </BaseDropdown>
          </template>
        </BaseCard>

        <!-- ------------------------------------------------Removed---------------------------------------------------- -->
        <BaseCard
          v-if="removedArr.length != 0"
          has-header
          has-footer
          :title="`Removed (${removedArr.length})`"
        >
          <OrderProductsDatatable
            v-model="removedArr"
            :currency="orders.currency"
          />
        </BaseCard>

        <!-- --------------------------------------------------Fulfilled-------------------------------------------------- -->
        <BaseCard
          v-for="(data, key, index) in arrangedOrderNumber"
          :key="index"
          has-header
          has-footer
          :title="`Fulfilled (${data.items.length}) #${orders.order_number}-F${key}`"
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
            :currency="orders.currency"
          />
          <template #footer>
            <BaseButton
              type="link"
              :disabled="hasClick"
              @click="cancelStatus(data.items, index)"
            >
              Cancel Fulfillment
            </BaseButton>
          </template>
        </BaseCard>

        <!-- ------------------------------------------------Paid Status---------------------------------------------------- -->
        <OrderSummary
          :title="orders.payment_status"
          :order="orders"
          :order-details="syncOrderDetail"
          :refund-logs="refundLogJs"
        >
          <template #footer>
            <BaseButton
              v-if="checkPayStatus"
              @click="triggerModal('markAsPaidModal')"
            >
              Mark As Paid
            </BaseButton>
          </template>
        </OrderSummary>
      </template>
      <template #right>
        <BaseCard
          v-for="(trackingDetail, index) in orders.lalamove_delivery_orders
            .length > 0
            ? lalamoveTrackingDetails
            : []"
          :key="index"
          has-header
          title="Lalamove Tracking"
        >
          <p class="ps-0">
            You can refresh this page periodically to get latest tracking
            information from Lalamove Malaysia
          </p>

          <b>Status:</b>
          <p>{{ trackingDetail.status || '-' }}</p>

          <b>Share Link: </b>
          <p>
            <BaseButton
              type="link"
              :href="trackingDetail.shareLink"
              is-open-in-new-tab
            >
              {{ trackingDetail.shareLink || '-' }}
            </BaseButton>
          </p>

          <b>Driver name: </b>
          <p>{{ trackingDetail.driver?.name }}</p>

          <b>Driver phone: </b>
          <p>{{ trackingDetail.driver?.phone }}</p>

          <b>Driver plate number: </b>
          <p>{{ trackingDetail.driver?.plateNumber }}</p>
        </BaseCard>
        <BaseCard
          v-for="parcel in orders.easy_parcel_shipment_parcels.length > 0
            ? orders.easy_parcel_shipment_parcels
            : []"
          :key="parcel.id"
          has-header
          title="EasyParcel Tracking"
        >
          <b class="ps-0">{{
            `Order ${
              getEasyParcelOrderNumber(parcel.easy_parcel_shipment_id) ?? '#'
            }`
          }}
          </b>
          <p class="ps-0">
            Below are some tracking information that can be found in your
            EasyParcel account on this order
          </p>

          <b>Tracking URL: </b>
          <p>{{ parcel.tracking_url || '-' }}</p>

          <b>Air WayBill (AWB) Number: </b>
          <p>{{ parcel.awb || '-' }}</p>

          <b>Air WayBill (AWB) ID Link: </b>
          <p>{{ parcel.awb_id_link || '-' }}</p>
        </BaseCard>

        <BaseCard
          v-for="parcel in updateDelyvaInfo.length > 0 ? updateDelyvaInfo : []"
          :key="parcel.id"
          has-header
          has-toolbar
          title="Delyva Delivery"
        >
          <template #toolbar>
            <BaseButton
              type="secondary"
              size="sm"
              data-bs-toggle="modal"
              data-bs-target="#delyvaServiceDetailModal"
            >
              Service Info
            </BaseButton>
          </template>

          <BaseModal
            :title="`${parcel.delyva_delivery_order_detail?.service_name} service info`"
            modal-id="delyvaServiceDetailModal"
          >
            <div
              class="delyva-service-detail"
              v-html="parcel?.serviceDetail?.custDescription"
            />
          </BaseModal>

          <b>Order {{ parcel.order_number ?? '#' }}</b>

          <b>Status: </b>
          <p :style="{ color: parcel.status }">
            {{ parcel.statusName }}
          </p>
          <p
            v-if="
              parcel?.status === 'red' && //display failed reason
                parcel.delyva_delivery_order_detail?.failed_reason
            "
            class="text-danger"
          >
            {{ parcel.delyva_delivery_order_detail?.failed_reason }}
          </p>

          <b>Deliver By: </b>
          <p>{{ parcel.delyva_delivery_order_detail?.service_name }}</p>
          <div v-if="parcel.serviceDetail?.name?.includes('(DROP)')">
            <b>Dropoff: </b>
            <p>Yes</p>
          </div>
          <div v-else>
            <b>Pickup At: </b>
            <p>{{ parcel.dateTimeComponent }}</p>
          </div>

          <b>Tracking URL: </b>
          <BaseButton
            v-if="parcel.delyva_delivery_order_detail?.consignmentNo"
            class="ms-3 fs-7"
            type="link"
            is-open-in-new-tab
            :href="getDelyvaUrl('tracking', parcel)"
          >
            {{ getDelyvaUrl('tracking', parcel) }}
          </BaseButton>
          <p v-else>
            To be updated
          </p>

          <b>Print Label URL: </b>
          <BaseButton
            v-if="
              parcel.delyva_order_id &&
                parcel.delyva_delivery_order_detail.company_id
            "
            class="ms-3 fs-7"
            type="link"
            is-open-in-new-tab
            :href="getDelyvaUrl('printLabel', parcel)"
          >
            {{ getDelyvaUrl('printLabel', parcel) ?? '-' }}
          </BaseButton>
          <p v-else>
            To be updated
          </p>
        </BaseCard>

        <BaseCard
          has-header
          has-toolbar
          title="Notes"
        >
          <template #toolbar>
            <BaseButton
              type="link"
              @click="triggerModal('customerNoteModal')"
            >
              Edit
            </BaseButton>
          </template>
          <p :class="`${syncOrder.notes ? 'text-dark' : 'text-muted'}`">
            {{ syncOrder.notes ? syncOrder.notes : 'No notes from customer' }}
          </p>
        </BaseCard>

        <BaseCard
          v-if="syncOrder.delivery_hour_type == 'custom'"
          has-header
          :title="
            syncOrder.delivery_type == 'delivery'
              ? 'Delivery Schedule'
              : ' Store Pickup'
          "
        >
          <p>
            {{
              `${
                syncOrder.delivery_type == 'delivery' ? 'Delivery' : 'Pickup'
              } Date: ${syncOrder.delivery_date}`
            }}
          </p>
          <p>
            {{
              `${
                syncOrder.delivery_type == 'delivery' ? 'Delivery' : 'Pickup'
              } Time: ${syncOrder.delivery_timeslot}`
            }}
          </p>

          <div v-if="syncOrder.delivery_type == 'pickup'">
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

        <!-- Shipping Detail -->
        <CustomerInformation
          ref="customerInfo"
          page="view"
          @save-address="saveAddress"
          @save-contact="updateContact"
        />

        <!-- Affiliate Detail -->
        <AffiliateInformation
          v-if="affiliateCommissionInfo?.length"
          :affiliate-info="affiliateCommissionInfo"
        />
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>

  <BaseModal
    title="Edit Note"
    modal-id="customerNoteModal"
  >
    <template #footer>
      <BaseButton @click="saveNote">
        Save
      </BaseButton>
    </template>

    <BaseFormGroup label="Notes">
      <textarea
        id="notes"
        v-model="customerNote"
        class="form-control"
        style="height: 100px"
      />
    </BaseFormGroup>
  </BaseModal>

  <!-- <div>
    <div class="row card-title-custom">
      <div class="col-md-12">
        <div style="float: left">
          <button
            type="button"
            class="btn btn-danger"
            @click="triggerModal('deletemodalTemplate)"
            hidden
          >
            Delete
          </button>
        </div>
        <div style="float: right">
          <button
            type="button"
            class="primary-small-square-button"
            hidden
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div> -->

  <!--Mark as Paid Modal -->
  <BaseModal
    title="Mark as paid"
    modal-id="markAsPaidModal"
  >
    <template #footer>
      <BaseButton @click="markAsPaid">
        Mark as paid
      </BaseButton>
    </template>

    <p>
      If you received payment for this order manually, mark this order as paid.
      This payment won't be captured by Hypershapes and you won't be able to
      refund it using Hypershapes.
    </p>
  </BaseModal>

  <!--Delete Order Modal -->
  <!-- <BaseModal
    title="Delete order"
    modal-id="deletemodalTemplate"
  >
    <template #footer>
      <BaseButton @click="deleteOrder">
        Delete Order
      </BaseButton>
    </template>

    <p>
      This order will be deleted and no longer tracked in reports. This action
      can't be reversed..
    </p>
  </BaseModal> -->

  <!--Cant Be Edit Modal -->
  <!-- <BaseModal
    title="Unable to edit order"
    modal-id="cantBeEditModal"
  >
    <p>
      Archived orders can't be edited..
    </p>
  </BaseModal> -->
</template>

<script>
import dayjs from 'dayjs';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';
import OrderSummary from '@order/components/OrderSummary.vue';
import CustomerInformation from '@order/components/CustomerInformation.vue';
import AffiliateInformation from '@order/components/AffiliateInformation.vue';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import { Modal } from 'bootstrap';
import { mapMutations } from 'vuex';
import orderAPI from '@order/api/orderAPI.js';

export default {
  name: 'ViewOrder',
  components: {
    CustomerInformation,
    BaseOrderPageLayout,
    OrderProductsDatatable,
    OrderSummary,
    AffiliateInformation,
  },
  mixins: [specialCurrencyCalculationMixin],

  props: {
    orders: { type: Object, default: () => {} },
    orderDetail: { type: Array, default: () => [] },
    currency: { type: String, default: 'RM' },
    refundLog: { type: Array, default: () => [] },
    location: { type: Object, default: () => {} },
    delyvaOrder: { type: Array, default: () => [] },
    environment: { type: String, default: 'production' },
    affiliateCommissionInfo: { type: Array, default: null },
  },

  data() {
    return {
      display: {
        customerNote: this.orders.notes === '' ? null : this.orders.notes,
      },
      customerNote: this.orders.notes,
      Notes: this.orders.notes,
      easyParcelOrderStatuses: [],

      // this details is a combination of order details and driver info from Lalamove API
      lalamoveTrackingDetails: [],
      errEmail: '',
      syncOrder: this.orders,
      syncOrderDetail: this.orderDetail,
      hasEasyParcel: false,
      hasLalamove: false,
      hasDelyva: false,
      sellerLocation: this.location,
      hasClick: false,
    };
  },
  computed: {
    displayOrderDiscount() {
      const shippingPromo = this.orders.order_discount
        .filter((discount) => discount.promotion_category === 'Shipping')
        .reduce(
          (prev, current) =>
            prev.discount_value > current.discount_value ? prev : current,
          {}
        );
      const orderPromo = this.orders.order_discount.filter(
        (discount) => discount.promotion_category === 'Order'
      );
      const promoList = orderPromo;
      if (Object.keys(shippingPromo ?? {})?.length)
        promoList.push(shippingPromo);
      return promoList;
    },
    orderCustomization() {
      return JSON.parse(this.syncOrderDetail.map((item) => item.customization));
    },
    orderVariant() {
      return JSON.parse(this.syncOrderDetail.map((item) => item.variant));
    },
    refundLogJs() {
      return this.refundLog;
    },
    locationJS() {
      return this.syncOrder.delivery_type === 'pickup'
        ? JSON.parse(this.syncOrder.pickup_location)
        : {};
    },

    isRefund() {
      return (
        this.orders.payment_status === 'Refunded' ||
        this.orders.payment_status === 'Partially Refunded'
      );
    },

    netPayment() {
      return this.syncOrder.paided_by_customer - this.syncOrder.refunded;
    },
    updateDelyvaInfo() {
      // delyva time format
      const delyvaOrder = this.orders.delyva_delivery_orders;
      let i = 0;
      for (; i < delyvaOrder.length; i += 1) {
        const serviceCode =
          delyvaOrder[i]?.delyva_delivery_order_detail?.serviceCode;
        const delyvaQuotation = this.orders.delyva_quotations.find(
          (e) => e.service_code === serviceCode
        );
        delyvaOrder[i].serviceDetail = JSON.parse(
          delyvaQuotation?.service_detail ?? '{}'
        );
        const scheduleAt =
          delyvaOrder[i].delyva_delivery_order_detail.schedule_at;
        if (scheduleAt) {
          const date = scheduleAt === 'now' ? dayjs() : dayjs(scheduleAt);
          const dateTimeComponent = date.format('MMMM D, YYYY [at] h:mm a');
          delyvaOrder[i].dateTimeComponent = dateTimeComponent;
        }
        if (
          [99, 475, 650, 663, 654, 655].indexOf(
            delyvaOrder[i].delyva_delivery_order_detail.statusCode
          ) > -1
        )
          delyvaOrder[i].status = 'red';
        else
          delyvaOrder[i].status =
            [1000].indexOf(
              delyvaOrder[i].delyva_delivery_order_detail.statusCode
            ) > -1
              ? 'green'
              : 'black';

        delyvaOrder[i].statusName =
          delyvaOrder[i].delyva_delivery_order_detail.status
            .charAt(0)
            .toUpperCase() +
          delyvaOrder[i].delyva_delivery_order_detail.status.slice(1);
      }
      return delyvaOrder;
    },
    billingAddress() {
      return {
        fullname: this.syncOrder.billing_name,
        address: this.syncOrder.billing_address,
        company: this.syncOrder.billing_company_name,
        city: this.syncOrder.billing_city,
        country: this.syncOrder.billing_country,
        state: this.syncOrder.billing_state,
        zip: this.syncOrder.billing_zipcode,
        phoneNo: this.syncOrder.billing_phoneNumber,
      };
    },
    shippingAddress() {
      return {
        fullname: this.syncOrder.shipping_name,
        address: this.syncOrder.shipping_address,
        company: this.syncOrder.shipping_company_name,
        city: this.syncOrder.shipping_city,
        country: this.syncOrder.shipping_country,
        state: this.syncOrder.shipping_state,
        zip: this.syncOrder.shipping_zipcode,
        phoneNo: this.syncOrder.shipping_phoneNumber,
      };
    },
    customerInfo() {
      return (({ name, email, fname, lname, phoneNo, processedRandomId }) => ({
        name,
        email,
        fname,
        lname,
        phoneNo,
        processedRandomId,
      }))(this.syncOrder);
    },

    fulfilledArr() {
      return this.syncOrderDetail.filter((item) => {
        return item.fulfillment_status === 'Fulfilled';
      });
    },

    unfulfilledArr() {
      return this.syncOrderDetail.filter((item) => {
        return item.fulfillment_status === 'Unfulfilled';
      });
    },
    unfulfilledPhysicalProduct() {
      return this.unfulfilledArr.filter((item) => !item.is_virtual);
    },
    removedArr() {
      return this.syncOrderDetail.filter((item) => {
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

    countItem() {
      const totalItem = this.syncOrderDetail.reduce((total, item) => {
        return total + item.quantity;
      }, 0);
      if (totalItem <= 1) return `${totalItem} item `;
      return `${totalItem} items `;
    },

    calculateBalance() {
      const refunded = parseFloat(this.syncOrder.refunded);
      const paidedByCustomer = parseFloat(this.syncOrder.paided_by_customer);
      const status = this.syncOrder.payment_status;
      const grandTotal = this.syncOrder.total;
      if (status === 'Refunded' || status === 'Partially Refunded') {
        return grandTotal > this.netPayment
          ? grandTotal - refunded - this.netPayment
          : 0;
      }
      return grandTotal - paidedByCustomer;
    },

    paymentStatusBadges() {
      const isUnpaid = this.orders.payment_status === 'Unpaid';
      return isUnpaid ? 'warning' : 'success';
    },

    fulfillStatusBadges() {
      const isFulfilled = this.orders.fulfillment_status === 'Fulfilled';
      return isFulfilled ? 'success' : 'warning';
    },

    checkPayStatus() {
      const total = parseFloat(this.syncOrder.total);
      const paidedByCustomer = parseFloat(this.syncOrder.paided_by_customer);
      const paymentStatus = this.syncOrder.payment_status;
      const balance = parseFloat(this.calculateBalance);

      if (paymentStatus === 'Paid') {
        return paidedByCustomer < total;
      }
      return balance > 0;
    },

    hasPendingEasyParcelFulfillment() {
      return (
        this.orders.easy_parcel_shipments.filter((es) => !es.order_number)
          .length !== 0
      );
    },

    hasPendingLalamoveFulfillment() {
      return (
        this.orders.lalamove_quotations.filter(
          (l) => !l.lalamove_delivery_order
        ).length !== 0
      );
    },

    hasPendingDelyvaFulfillment() {
      return (
        this.orders.delyva_quotations.filter(
          (d) => !d.delyva_delivery_order_info
        ).length !== 0
      );
    },

    markAsFulfilledLink() {
      const refKey = this.orders.reference_key;

      if (this.hasPendingEasyParcelFulfillment) {
        return `/orders/details/${refKey}/fulfillment/easyparcel`;
      }

      if (this.hasPendingLalamoveFulfillment) {
        return `/orders/details/${refKey}/fulfillment/lalamove`;
      }

      return `/orders/details/${refKey}/fulfillmentstatus`;
    },

    isEasyParcelInsufficientCredit() {
      return (
        this.easyParcelOrderStatuses.findIndex(
          (s) => s === 'Insufficient Credit'
        ) !== -1
      );
    },

    isEasyParcelShipmentWaitingPayment() {
      return (
        this.easyParcelOrderStatuses.findIndex(
          (s) => s === 'Waiting Payment'
        ) !== -1
      );
    },
  },
  methods: {
    ...mapMutations('orders', ['setState', 'setErrors']),
    triggerModal(modalId) {
      this.modal = new Modal(document.getElementById(modalId));
      this.modal.show();
    },
    convertedPrice(price) {
      return `${this.orders.currency} ${this.specialCurrencyCalculation(
        price
      )}`;
    },
    triggerPackingSlip() {
      if (this.syncOrder.fulfillment_status === 'Unfulfilled') {
        this.modal.show();
      } else {
        this.printPackingSlip();
      }
    },

    cancelStatus(items, value) {
      this.hasClick = true;
      orderAPI
        .cancelFulfillment({
          items,
          orders: this.syncOrder,
          order_number: items[0].order_number,
        })
        .then((response) => {
          this.$toast.success('Success', 'Fulfillment Cancelled');
          this.$inertia.visit(window.location.href);
        })
        .catch((error) => {
          this.$toast.error(
            'Error',
            `Failed to cancel fulfillment: ${error.response.data.message}`
          );
        });
    },

    updateAdditionalStatus(type) {
      const message =
        type === 'unarchive' ? 'Order Unarchived' : 'Order Archived';
      this.axiosPost(
        {
          orderId: this.syncOrder.id,
          type,
        },
        `/orders/details/${type}`,
        message
      );
    },

    axiosPost(data, url, toast) {
      return new Promise((resolve, reject) => {
        orderAPI
          .post(url, data)
          .then((response) => {
            this.$toast.success('Success', toast);
            this.syncOrder = response.data;
            resolve(response);
          })
          .catch((error) => {
            this.$toast.error('Error', 'Something went wrong');
            reject(error);
          });
      });
    },

    markAsPaid() {
      this.axiosPost(
        {
          orders: this.syncOrder,
          total: this.syncOrder.total,
        },
        '/orders/details/markpayment',
        'Payment Done Successfully'
      ).then((response) => {
        this.modal.hide();
        window.location.reload();
      });
    },

    deleteOrder() {
      orderAPI
        .deleteOrderDetail(this.syncOrder)
        .then((response) => {
          this.$inertia.visit('/orders');
        })
        .catch((error) => {});
    },

    updateContact(value) {
      console.log('is updated ....');
      this.axiosPost(
        {
          customer: value,
          orderId: this.syncOrder.id,
        },
        '/orders/details/contact/save',
        'Contact Information saved'
      );
    },

    saveNote() {
      this.axiosPost(
        {
          notes: this.customerNote,
          orderId: this.syncOrder.id,
        },
        '/orders/details/note/save',
        'Note Saved'
      ).then((response) => {
        this.modal.hide();
      });
    },

    saveAddress(value) {
      const message =
        value.type === 'shipping'
          ? 'Shipping address saved'
          : 'Billing address saved';
      this.axiosPost(
        {
          address: value.address,
          orders: this.syncOrder,
        },
        `/orders/details/${value.type}/save`,
        message
      )
        .then((response) => {
          this.setState({ key: 'shipping', value: this.shippingAddress });
          this.setState({ key: 'billing', value: this.billingAddress });
        })
        .catch((error) => {
          this.setState({ key: 'errors', value: error.response.data.errors });
        });
    },

    markStatus() {
      const orderId = this.syncOrder.reference_key;
      this.$inertia.visit(`/orders/details/${orderId}/fulfillmentstatus`);
    },

    editOrder() {
      if (this.syncOrder.additional_status !== 'Archived') {
        const orderRefKey = this.orders.reference_key;
        this.$inertia.visit(`/orders/details/${orderRefKey}/edit`);
      } else {
        this.modal.show();
      }
    },

    printPackingSlip() {
      // this.packingSlipModal.hide();
      const orderRefKey = this.orders.reference_key;
      window.open(`/orders/details/${orderRefKey}/packingSlip`, '_blank');
    },

    getEasyParcelOrderNumber(shipmentId) {
      return this.orders.easy_parcel_fulfillments.find(
        (f) => f.easy_parcel_shipment_id === shipmentId
      )?.order_detail.order_number;
    },
    getDelyvaUrl(type, parcel) {
      const {
        delyva_order_id: orderId,
        company_id: companyId,
        consignmentNo,
      } = parcel.delyva_delivery_order_detail;
      if (!orderId) return null;
      if (type === 'tracking')
        return `https://${
          this.environment === 'local' ? 'demo' : 'my'
        }.delyva.app/customer/track?trackingNo=${consignmentNo}`;
      return `https://api.delyva.app/v1.0/order/${orderId}/label?companyId=${companyId}`;
    },
  } /* end method */,
  watch: {
    shippingAddress: {
      deep: true,
      immediate: true,
      handler(newValue) {
        if (!newValue) return;
        this.setState({ key: 'shipping', value: newValue });
      },
    },
    billingAddress: {
      deep: true,
      immediate: true,
      handler(newValue) {
        if (!newValue) return;
        this.setState({ key: 'billing', value: newValue });
      },
    },
    customerInfo: {
      deep: true,
      immediate: true,
      handler(newValue) {
        if (!newValue) return;
        this.setState({ key: 'customer', value: newValue });
      },
    },
  },

  created() {
    orderAPI
      .checkShippingSettings()
      .then(({ data }) => {
        this.hasEasyParcel = data.hasEasyparcel;
        this.hasLalamove = data.hasLalamove;
        this.hasDelyva =
          data.hasDelyva &&
          this.shippingAddress.country === 'Malaysia' &&
          this.sellerLocation.country === 'Malaysia'; // only available on Malaysia
      })
      .catch(() => {
        this.$toast.error(
          'Error',
          'Something wrong when fetching shipping information.'
        );
      });

    const isRedirectedFromFulfillment = new URLSearchParams(
      window.location.search
    ).has('fulfillmentredirect');
    if (
      !isRedirectedFromFulfillment &&
      this.orders.easy_parcel_shipment_parcels.length !== 0
    ) {
      orderAPI.easyparcelOrderStatus(this.orders.id).then((res) => {
        this.easyParcelOrderStatuses = res.data.orderStatuses;
      });
    }

    const lalamoveDeliveryOrders = this.orders.lalamove_delivery_orders;
    if (lalamoveDeliveryOrders.length !== 0) {
      lalamoveDeliveryOrders.forEach((delivery) => {
        orderAPI.lalamoveOrder(delivery.id).then((res) => {
          const trackingDetail = res.data.details;

          // get driver details if driverId presents in trackingDetail
          const { driverId } = trackingDetail;
          if (driverId) {
            orderAPI
              .lalamoveDriverDetails(delivery.id, driverId)
              .then((response) => {
                trackingDetail.driver = {
                  ...response.data.driverDetails,
                };

                this.lalamoveTrackingDetails = [
                  ...this.lalamoveTrackingDetails,
                  trackingDetail,
                ];
              });
          } else {
            this.lalamoveTrackingDetails = [
              ...this.lalamoveTrackingDetails,
              trackingDetail,
            ];
          }
        });
      });
    }
  },
  mounted() {
    this.easyParcelOrderStatuses = this.orders.easy_parcel_shipments.map(
      (es) => es.order_status
    );
  },
};
</script>

<style scoped>
td:last-child {
  text-align: right;
}
td {
  padding-top: 0.5rem;
}

.order-header-description:nth-child(odd) {
  font-weight: 600;
  margin-left: 0.75rem;
}

@media screen and (max-width: 640px) {
  .btn {
    font-size: 0.925rem !important;
    margin-top: 0.5rem;
  }
  .order-header div {
    margin-top: 5px;
    text-align: start !important;
  }
  .order-header-description {
    display: block;
    margin-left: 0 !important;
  }
}
</style>

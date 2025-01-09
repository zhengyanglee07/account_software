<template>
  <BasePageLayout
    :page-name="`${isEdit ? 'Edit' : 'Add'} Order`"
    back-to="/orders"
    is-setting
  >
    <BaseOrderPageLayout>
      <template #left>
        <BaseCard>
          <BaseFormGroup class="px-7">
            <BaseFormInput
              v-model="searchInput"
              type="search"
              data-bs-target="#browseProductModal"
              placeholder="Search product to add"
              @keyup="productModal.show()"
              @change="insertSearchText"
            >
              <template #prepend>
                <i class="fas fa-search gray_icon" />
              </template>
              <template #append>
                <BaseButton type="link" @click="productModal.show()">
                  Browse
                </BaseButton>
              </template>
            </BaseFormInput>
          </BaseFormGroup>

          <!-- Product table (images / Title + quantity * price / Quantity) -->
          <OrderProductsDatatable
            v-model="orderDetails"
            :currency="currency"
            is-manual-order
            @remove="(val) => (removedProduct = val)"
          />

          <OrderSummary
            v-if="isEdit"
            :order="order"
            :order-details="orderDetails"
          />
          <BaseFormGroup v-else class="fs-6 px-7">
            <table class="order-summary">
              <tr>
                <td>Subtotal</td>
                <td>{{ totalItem }} items</td>
                <td>{{ displayPrice(subTotal) }}</td>
              </tr>
              <tr>
                <td>
                  <BaseButton
                    type="link"
                    class="text-start"
                    @click="orderDiscountModal.show()"
                  >
                    <template v-if="hasOrderDiscount">
                      Discount
                      <i
                        class="ms-2 fa-solid fa-pen-to-square"
                        style="color: inherit"
                      />
                    </template>
                    <template v-else> Add discount </template>
                  </BaseButton>
                </td>
                <td>{{ orderDiscount.reason }}</td>
                <td>
                  {{
                    hasOrderDiscount
                      ? displayPrice(
                          orderDiscount.getDiscountPrice(subTotal),
                          true
                        )
                      : '-'
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <BaseButton
                    type="link"
                    class="text-start"
                    @click="orderShippingModal.show()"
                  >
                    <template v-if="hasOrderShipping">
                      Shipping
                      <i
                        class="ms-2 fa-solid fa-pen-to-square"
                        style="color: inherit"
                      />
                    </template>
                    <template v-else> Add shipping </template>
                  </BaseButton>
                </td>
                <td>{{ orderShipping?.name }}</td>
                <td>
                  {{
                    hasOrderShipping ? displayPrice(orderShipping.rate) : '-'
                  }}
                </td>
              </tr>
              <tr>
                <td>
                  <BaseButton
                    type="link"
                    class="text-start"
                    @click="orderTaxModal.show()"
                  >
                    <template v-if="hasOrderTax">
                      Tax
                      <i
                        class="ms-2 fa-solid fa-pen-to-square"
                        style="color: inherit"
                      />
                    </template>
                    <template v-else> Add tax </template>
                  </BaseButton>
                </td>
                <td>{{ orderTax.name }}</td>
                <td>
                  {{
                    hasOrderTax
                      ? displayPrice(orderTax.getTotalTax(taxableProductTotal))
                      : '-'
                  }}
                </td>
              </tr>
              <tr>
                <td>Total</td>
                <td />
                <td>{{ displayPrice(grandTotal) }}</td>
              </tr>
            </table>
          </BaseFormGroup>

          <BaseFormGroup>
            <BaseButton
              v-if="!isEdit"
              data-bs-toggle="modal"
              data-bs-target="#markAsPaidModal"
              class="float-end"
              :disabled="selectedProducts.length === 0"
            >
              Mark As Paid
            </BaseButton>
          </BaseFormGroup>
        </BaseCard>
      </template>

      <template #right>
        <CustomerInformation v-if="hasSelectedCustomer" page="create" />
        <BaseCard v-else>
          <BaseFormGroup label="Add Customer">
            <BaseMultiSelect
              placeholder="Search customers"
              :filter="fuseSearch"
              label="fname"
              :options="processedContactJs"
              :value="0"
              @option:selected="addCustomer"
            >
              <template #no-options="{ search, searching }">
                <template v-if="searching">
                  No results found for
                  <em>{{ search }}</em>
                  .
                </template>
              </template>
              <template #option="{ option }">
                <div v-if="option.name !== 'Create Customer'" class="d-flex">
                  <div class="py-2">
                    <img
                      src="@order/assets/media/processed_contact.png"
                      style="width: 30px; height: 30px"
                    />
                  </div>
                  <div class="ps-5">
                    <div>{{ option.fname }}</div>
                    <div>{{ option.email }}</div>
                  </div>
                </div>
                <div v-else class="d-flex ms-3 me-3">
                  <div class="py-2">
                    <i class="pe-6 fas fa-plus w-auto" />
                    {{ option.name }}
                  </div>
                </div>
              </template>
            </BaseMultiSelect>
          </BaseFormGroup>
        </BaseCard>

        <BaseCard has-header has-toolbar title="Notes">
          <template #toolbar>
            <BaseButton type="link" @click="customerNoteModal.show()">
              Edit
            </BaseButton>
          </template>
          <p class="text-muted">
            {{ orderNote ? orderNote : 'No notes from customer' }}
          </p>
        </BaseCard>
      </template>

      <template #footer>
        <BaseButton
          href="/orders"
          type="link"
          :disabled="quotingRate"
          @click="quoteRate"
        >
          Cancel
        </BaseButton>
        <BaseButton
          class="ms-3"
          :disabled="selectedProducts.length === 0 || isOrderProcessing"
          @click="saveOrder"
        >
          <i v-if="isOrderProcessing" class="fas fa-spinner fa-pulse" />
          <span v-else>Save</span>
        </BaseButton>
      </template>
    </BaseOrderPageLayout>
  </BasePageLayout>

  <OrderProductModal
    :all-products="allProducts"
    :search-input="searchInput"
    :selected-products="order?.order_details ?? []"
    :removed-product="removedProduct"
    :currency="currency"
    @selected-product="updateSelectedProduct"
  />

  <OrderDiscountModal
    :currency="currency"
    :sub-total="subTotal"
    @delete="orderDiscount = {}"
    @submit="applyDiscount"
  />
  <OrderShippingModal
    :currency="currency"
    @delete="orderShipping = {}"
    @submit="saveShipping"
  />
  <OrderTaxModal
    :currency="currency"
    @delete="orderTax = {}"
    @submit="saveTax"
  />

  <CustomerAddModal @saveCustomer="saveCustomer" />

  <CustomerNoteModal :order="order" @submit="saveNote" />

  <MarkAsPaidModal :is-loading="isOrderProcessing" @submit="saveOrder(true)" />
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import { useStore } from 'vuex';
import BaseOrderPageLayout from '@shared/layout/BaseOrderPageLayout.vue';
import OrderProductsDatatable from '@order/components/OrderProductsDatatable.vue';
import OrderProductModal from '@order/components/OrderProductModal.vue';
import OrderSummary from '@order/components/OrderSummary.vue';
import CustomerAddModal from '@order/components/CustomerAddModal.vue';
import MarkAsPaidModal from '@order/components/MarkAsPaidModal.vue';
import CustomerNoteModal from '@order/components/CustomerNoteModal.vue';
import CustomerInformation from '@order/components/CustomerInformation.vue';
import OrderDiscountModal from '@order/components/OrderDiscountModal.vue';
import OrderShippingModal from '@order/components/OrderShippingModal.vue';
import OrderTaxModal from '@order/components/OrderTaxModal.vue';
import orderAPI from '@order/api/orderAPI.js';
import productDefaultImages from '@shared/assets/media/product-default-image.png';
import eventBus from '@shared/services/eventBus.js';
import clone from 'clone';
import convertPrice from '@shared/hooks/useCurrency.js';
import axios from 'axios';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const $toast = inject('$toast');

const props = defineProps({
  isEdit: { type: Boolean, default: Boolean },
  order: { type: Object, default: () => {} },
  allProducts: { type: Array, default: () => [] },
  processedContact: { type: Array, default: () => [] },
  selectedProcessedContact: { type: Object, default: () => {} },
  currency: { type: String, default: '' },
  orderReferenceKey: { type: String, default: '' },
});

const store = useStore();

const searchInput = ref(null);

const customer = computed(() => store.state.orders.customer);

const hasSelectedCustomer = computed(
  () => Object.keys(customer.value ?? {}).length > 0
);

const processedContactJs = computed(() => {
  const contact = props.processedContact;
  contact.unshift({ name: 'Create Customer' });
  return contact;
});

const storeOrderDetail = computed(() =>
  (props.order?.order_details ?? []).reduce((list, item) => {
    const identifier =
      !item.variant || item.variant === '[]'
        ? item.users_product_id
        : item.variant_combination_id;
    list[identifier] = list[identifier] || [];
    list[identifier].push(item);
    return list;
  }, Object.create(null))
);

const productDetailList = ref([]);
const getStoredProductDetail = (
  productId,
  variantRef,
  isVariant,
  qty = null
) => {
  const data = {};
  const identifier = isVariant ? variantRef : productId;
  if (productDetailList.value.includes(identifier))
    return { isDuplicated: true };
  const orderDetail = (storeOrderDetail.value[identifier] ?? []).reduce(
    (mergedDetail, item) => {
      item = clone(item);
      const isFulfilled = item.fulfillment_status === 'Fulfilled';
      if (Object.keys(mergedDetail)?.length === 0) {
        item.isFullyFulfilled = isFulfilled;
        item.fulfilledQty = isFulfilled ? item.quantity : 0;
        item.hasFulfilledOrder = isFulfilled;
        item.orderDetailIds = [item.id];
        mergedDetail = item;
      } else {
        mergedDetail.isFullyFulfilled &&= isFulfilled;
        mergedDetail.fulfilledQty += isFulfilled ? item.quantity : 0;
        mergedDetail.hasFulfilledOrder ||= isFulfilled;
        mergedDetail.orderDetailIds.push(item.id);
        mergedDetail.quantity += item.quantity;
      }
      return mergedDetail;
    },
    Object.create(null)
  );
  const productDetail = props.allProducts.find((e) =>
    isVariant
      ? e.variant_details.some((ee) => ee.reference_key === variantRef)
      : e.id === productId
  );

  data.max = productDetail?.is_selling ? 0 : productDetail?.quantity;
  if (isVariant) {
    const productVariant = productDetail.variant_details.find(
      (e) => e.reference_key === variantRef
    );
    data.max = productVariant.is_selling ? 0 : productVariant.quantity;
  }
  data.min = orderDetail.fulfilledQty || 0;
  data.originalQuantity = orderDetail?.quantity ?? 0;
  data.quantity = qty ?? data.quantity ?? orderDetail?.quantity ?? 1;
  data.orderDetailId = orderDetail.id ?? 0;
  data.orderDetailIds = orderDetail.orderDetailIds;
  data.isFullyFulfilled =
    orderDetail.is_virtual ||
    orderDetail.fulfilledQty === (qty || orderDetail.quantity);
  data.fulfillmentStatus =
    productDetail.type !== 'physical' || data.isFullyFulfilled
      ? 'Fulfilled'
      : 'Unfulfilled';
  data.hasFulfilledOrder = orderDetail?.hasFulfilledOrder;

  productDetailList.value.push(identifier);
  return data;
};

const selectedProducts = ref([]);
const orderDetails = computed({
  get() {
    return selectedProducts.value.filter((e) => !e.isDuplicated);
  },
  set(val) {
    selectedProducts.value = val.map((m) => ({
      ...m,
      total: m.unit_price * m.quantity,
      ...getStoredProductDetail(
        m.users_product_id,
        m.variant_combination_id,
        m.variant !== '[]',
        m.quantity
      ),
    }));
    productDetailList.value = [];
  },
});

const totalItem = computed(() =>
  orderDetails.value.reduce((total, o) => total + (o?.quantity ?? 0), 0)
);

// Remain updated product data
const productDataList = computed(() =>
  orderDetails.value.reduce((list, item) => {
    list[
      !item.variant || item.variant === '[]'
        ? item.users_product_id
        : item.variant_combination_id
    ] = {
      quantity: item.quantity,
      originalQuantity: item.originalQuantity,
      orderDetailId: item.orderDetailId,
      fulfillmentStatus: item.fulfillmentStatus,
    };
    return list;
  }, {})
);

const getProductData = (item) =>
  productDataList.value[
    item.isVariant ? item.variant?.reference_key : item.productId
  ] ?? {};

const updateSelectedProduct = (products) => {
  selectedProducts.value = products?.map((item) => ({
    users_product_id: item.productId,
    product_name: item.productTitle,
    image_url: item.isVariant ? item.variant.image_url : item.productImagePath,
    weight: item.isVariant ? item.variant.weight : item.weight,
    SKU: item.isVariant ? item.variant.sku : item.SKU,
    max: item.isVariant ? item.variant.quantity : item.quantity,
    quantity: getProductData(item).quantity ?? 1,
    originalQuantity: getProductData(item).originalQuantity ?? 0,
    orderDetaiId: getProductData(item).orderDetailId ?? 0,
    unit_price: item.isVariant
      ? parseFloat(item.variant.price)
      : parseFloat(item.originalPrice),
    total:
      (item.isVariant
        ? parseFloat(item.variant.price)
        : parseFloat(item.originalPrice)) * 1,
    is_virtual: item.type !== 'physical',
    variant: JSON.stringify(
      Object.values(item.variant_values ?? {}).map((value, index) => ({
        id: item.variant?.id,
        label: value.name,
        value: value.valueArray.find(
          (v) =>
            v.id === (item.variant && item.variant[`option_${index + 1}_id`])
        )?.variant_value,
      }))
    ),
    variant_combination_id: item.variant?.reference_key,
    variant_name: item.variant?.title,
    customization: '[]',
    is_taxable: item.isTaxable,
    tax: 0,
    discount: 0,
    discount_details: '[]',
    is_discount_applied: false,
    payment_status: 'Unpaid',
    fulfillment_status:
      getProductData(item).fulfillmentStatus ??
      (item.type === 'physical' ? 'Unfulfilled' : 'Fulfilled'),
    ...getStoredProductDetail(
      item.productId,
      item.variant?.reference_key,
      item.isVariant
    ),
  }));
  productDetailList.value = [];
  orderDetails.value = selectedProducts.value;
};

const removedProduct = ref(null);

const fuseSearch = (options, search) =>
  search.length
    ? options.filter(
        (e) => e.fname?.includes(search) || e.email?.includes(search)
      )
    : options;

const addCustomerModal = ref(null);
const addCustomer = (event) => {
  if (!event || Object.keys(event).length === 0) return;
  if (event.name === 'Create Customer') {
    addCustomerModal.value.show();
    return;
  }
  store.commit('orders/setState', {
    key: 'customer',
    value: {
      name: event.displayName,
      email: event.email,
      phoneNo: event.phone_number,
      processedRandomId: event.contactRandomId,
    },
  });
};
const saveCustomer = (formDetail) => {
  store.commit('orders/setErrors', { key: 'customerInfo', value: {} });
  const form = formDetail.customerInfo;

  axios
    .post('/people/profile/addContact', {
      ...form,
      phone_number: form.phoneNo,
      newCustomField: [],
    })
    .then(({ data }) => {
      store.commit('orders/setState', {
        key: 'customer',
        value: {
          name: `${form.fname} ${form.lname}`,
          fname: form.fname,
          lname: form.lname,
          email: form.email,
          phoneNo: form.phoneNo,
          processedRandomId: data.contactRandomId,
        },
      });
      addCustomerModal.value.hide();
    })
    .catch((error) => {
      const errors = error?.response?.data?.errors;
      if (Object.keys(errors ?? {}).length) {
        store.commit('orders/setErrors', {
          key: 'customerInfo',
          value: errors,
        });
        $toast.error(
          'Error',
          'Pleasee make sure all fields are filled in correctly'
        );
        return;
      }
      $toast.error(
        'Error',
        error?.response?.data?.message ?? 'Unexpected Error Occured'
      );
    });
};

const orderNote = ref(null);
const customerNoteModal = ref(null);
const saveNote = (note) => {
  orderNote.value = note;
  customerNoteModal.value.hide();
};

const orderDiscount = ref({});
const hasOrderDiscount = computed(
  () => Object.keys(orderDiscount.value ?? {}).length > 0
);
const orderDiscountModal = ref(null);
const applyDiscount = (discount) => {
  discount.hasDiscount = true;
  orderDiscount.value = discount;
  orderDiscountModal.value.hide();
};

const orderShipping = ref({});
const hasOrderShipping = computed(
  () => Object.keys(orderShipping.value ?? {}).length > 0
);
const orderShippingModal = ref(null);
const saveShipping = (shipping) => {
  orderShipping.value = shipping;
  orderShippingModal.value.hide();
};

const orderTax = ref({});
const hasOrderTax = computed(
  () => Object.keys(orderTax.value ?? {}).length > 0
);
const orderTaxModal = ref(null);
const saveTax = (tax) => {
  orderTax.value = tax;
  orderTaxModal.value.hide();
};

// Transform to standard form detail format
const formattedFormDetail = () => {
  const { customer: customerInfo, shipping, billing } = store.state.orders;
  return {
    customerInfo: {
      fullName: customerInfo.name,
      email: customerInfo.email,
      phoneNumber: customerInfo.phoneNo,
    },
    shipping: {
      fullName: shipping.fullname,
      companyName: shipping.company,
      address: shipping.address,
      city: shipping.city,
      country: shipping.country,
      state: shipping.state,
      zipCode: shipping.zip,
      phoneNumber: shipping.phoneNo,
    },
    billing: {
      fullName: billing.fullname,
      companyName: billing.company,
      address: billing.address,
      city: billing.city,
      country: billing.country,
      state: billing.state,
      zipCode: billing.zip,
    },
  };
};

const displayPrice = (price, isDeduct = false) =>
  `${isDeduct ? '-' : ''} ${props.currency} ${convertPrice(
    props.currency,
    price
  )}`;
const subTotal = computed(() =>
  orderDetails.value?.reduce((total, item) => total + parseFloat(item.total), 0)
);
const taxableProductTotal = computed(() =>
  orderDetails.value.reduce(
    (total, item) => total + (item.is_taxable ? item.total : 0),
    0
  )
);
const grandTotal = computed(() => {
  const discount = hasOrderDiscount.value
    ? orderDiscount.value.getDiscountPrice(subTotal.value)
    : 0;
  const shipping = hasOrderShipping.value ? orderShipping.value.rate : 0;
  const tax = hasOrderTax.value
    ? orderTax.value.getTotalTax(taxableProductTotal.value)
    : 0;
  return subTotal.value - discount + shipping + tax;
});

const isOrderProcessing = ref(false);
const orderRef = ref(null);
const markAsPaidModal = ref(null);

const saveOrder = (isPaid = false) => {
  const hideMarkAsPaidModal = () => {
    if (isPaid) markAsPaidModal.value.hide();
  };

  const formDetail = formattedFormDetail();
  const hasCustomer = Object.values(formDetail.customerInfo).some((e) => e);
  if (!hasCustomer) {
    hideMarkAsPaidModal();
    $toast.error('Error', 'Please select customer');
    return;
  }

  if (orderDetails.value.find((e) => e.quantity <= 0)) {
    hideMarkAsPaidModal();
    $toast.error('Error', 'Please make sure quantity not less than 1');
    return;
  }

  isOrderProcessing.value = true;
  orderAPI
    .update({
      formDetail,
      products: orderDetails.value,
      notes: orderNote.value,
      isPaid,
      isEdit: props.isEdit,
      orderRef: orderRef.value,
      discount: orderDiscount.value,
      shipping: orderShipping.value,
      tax: orderTax.value,
    })
    .then(({ data }) => {
      hideMarkAsPaidModal();
      $toast.success('Success', 'Successfully saved order');
      window.location.href = `/orders/details/${data.orderRef}`;
    })
    .catch((error) => {
      hideMarkAsPaidModal();
      $toast.error('Error', error.response.data.message);
      isOrderProcessing.value = false;
    });
};

const productModal = ref(null);
onMounted(() => {
  if (props.isEdit) {
    selectedProducts.value = Object.values(props.order.order_details).map(
      (m) => ({
        ...m,
        ...getStoredProductDetail(
          m.users_product_id,
          m.variant_combination_id,
          m.variant !== '[]'
        ),
      })
    );
    productDetailList.value = [];
    addCustomer(props.selectedProcessedContact);
    orderNote.value = props.order.notes;
    orderRef.value = props.orderReferenceKey;
  }

  eventBus.$on('base-modal-mounted', () => {
    bootstrap?.then(({ Modal }) => {
      const modalInstance = (modalId) =>
        new Modal(document.getElementById(modalId));

      productModal.value = modalInstance('browseProductModal');
      addCustomerModal.value = modalInstance('createCustomerModal');
      markAsPaidModal.value = modalInstance('markAsPaidModal');
      customerNoteModal.value = modalInstance('customerNoteModal');
      orderDiscountModal.value = modalInstance('orderDiscountModal');
      orderShippingModal.value = modalInstance('orderShippingModal');
      orderTaxModal.value = modalInstance('orderTaxModal');
    });
  });
});
</script>

<style scoped>
.order-summary {
  width: 100%;
}
.order-summary td:first-child {
  width: 15%;
}
.order-summary td:last-child {
  text-align: right;
}
</style>

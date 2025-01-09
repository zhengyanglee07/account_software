<template>
  <div class="right_container_content_inner-page">
    <OrderHeaderLayout
      :order="orders"
      main-title="Edit Order"
    />
    <!--  End of Contatiner Header -->

    <div class="container-add-product">
      <div class="row mb-5">
        <div
          class="col-lg-8"
          style="padding-left: 0 !important"
        >
          <div class="general-card">
            <div class="general-card__body pb-0">
              <h4 class="general-card__section-title h-five">
                Add product
              </h4>
              <div class="input-group-app-prep">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="fas fa-search gray_icon" />
                    </span>
                  </div>

                  <input
                    v-model="searchInput"
                    type="text"
                    class="form-control"
                    data-bs-target="#browseProductModal"
                    placeholder="Search product to add"
                    @keyup="keyUpTriggerModal"
                  >

                  <div class="input-group-append">
                    <button
                      class="browse-btn"
                      @click="setFocus"
                    >
                      Browse
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div
              v-if="
                existedProduct.length !== 0 ||
                  orderProduct.length !== 0 ||
                  removedProduct.length !== 0
              "
              class="general-card__body card-custom-product pb-0"
              style="border-top-color: #fff"
            >
              <div class="content">
                <div class="row row-status">
                  <div class="col-md-10">
                    <h5
                      v-if="
                        existedProduct.length !== 0 ||
                          orderProduct.length !== 0 ||
                          removedProduct.length !== 0
                      "
                      class="general-card__section-title h-five"
                    >
                      Unfulfilled
                    </h5>
                  </div>
                </div>
                <div class="content-wrapper">
                  <DisplayProduct
                    :items="orderProduct"
                    :currency="orders.currency"
                  >
                    <template #expander="scopedData">
                      <div style="text-align: left">
                        <li>
                          <span class="p-two">
                            {{ scopedData.item.quantity }} X
                            {{ orders.currency }}
                            {{ scopedData.item.unit_price }}
                          </span>
                        </li>
                        <li
                          style="list-style: disc"
                          class="p-two"
                        >
                          Added
                        </li>
                      </div>
                      <div style="text-align: left; padding-top: 10px">
                        <li>
                          <button
                            class="h_link p-two"
                            style="padding-right: 20px"
                            @click="
                              adjustQuantity(
                                scopedData.item,
                                scopedData.index,
                                (type = 'new')
                              )
                            "
                          >
                            Adjust quantity
                          </button>
                          <button
                            class="h_link p-two"
                            @click="
                              removeOProduct(scopedData.item, scopedData.index)
                            "
                          >
                            Remove item
                          </button>
                        </li>
                      </div>
                    </template>
                    <template #right="scopedData">
                      <div class="d-flex justify-content-end">
                        <li class="list-item p-two">
                          {{ orders.currency }}
                          {{ calculateAmount(scopedData.item).toFixed(2) }}
                        </li>
                      </div>
                    </template>
                  </DisplayProduct>

                  <!-- -------------------- -->
                  <div
                    v-if="unfulfilledArr.length !== 0"
                    class="firstElement"
                  >
                    <DisplayProduct
                      :items="existedProduct"
                      :currency="orders.currency"
                    >
                      <template #expander="scopedData">
                        <div
                          class="pe-2"
                          style="text-align: left"
                        >
                          <span class="p-two">
                            {{ scopedData.item.quantity }} X
                            {{ orders.currency }}
                            {{ scopedData.item.unit_price }}
                          </span>
                        </div>
                        <div style="text-align: left; padding-top: 10px">
                          <li>
                            <button
                              class="h_link p-two"
                              style="text-align: left; padding-right: 20px"
                              @click="
                                adjustQuantity(
                                  scopedData.item,
                                  scopedData.index,
                                  (type = 'edit')
                                )
                              "
                            >
                              Adjust quantity
                            </button>
                            <button
                              class="h_link p-two"
                              style="text-align: left"
                              @click="
                                removeEProduct(
                                  scopedData.item,
                                  scopedData.index
                                )
                              "
                            >
                              Remove item
                            </button>
                          </li>
                        </div>
                      </template>
                      <template #right="scopedData">
                        <div
                          class="d-flex"
                          style="
                            justify-content: flex-end;
                            padding-left: 10px !important;
                          "
                        >
                          <li
                            class="list-item p-two"
                            style="text-align: right"
                          >
                            {{ orders.currency }}
                            {{ calculateAmount(scopedData.item).toFixed(2) }}
                          </li>
                        </div>
                      </template>
                    </DisplayProduct>
                  </div>
                  <!-- -------------------- -->
                  <div v-if="removedProduct.length !== 0">
                    <DisplayProduct
                      :items="removedProduct"
                      :currency="orders.currency"
                    >
                      <template #expander="scopedData">
                        <div>
                          <li
                            class="list-item"
                            style="text-align: left"
                          >
                            <span
                              class="p-two"
                              style="text-align: left"
                            >
                              {{ scopedData.item.quantity }} X
                              {{ orders.currency }}
                              {{ scopedData.item.unit_price }}</span>
                          </li>
                        </div>

                        <div style="text-align: left; padding-top: 10px">
                          <li>
                            <span class="p-two">
                              Original quantity:
                              {{ scopedData.item.original_quantity }}
                            </span>
                          </li>

                          <li>
                            <button
                              class="h_link p-two"
                              style="text-align: left; padding-left: 20px"
                              @click="
                                adjustQuantity(
                                  scopedData.item,
                                  scopedData.index,
                                  (type = 'removed')
                                )
                              "
                            >
                              Adjust quantity
                            </button>
                          </li>
                        </div>
                      </template>
                      <template #right="scopedData">
                        <div>
                          <li
                            class="list-item p-two"
                            style="text-align: right"
                          >
                            {{ orders.currency }}
                            {{ calculateAmount(scopedData.item).toFixed(2) }}
                          </li>
                        </div>
                      </template>
                    </DisplayProduct>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="general-card__calculation-container-content footer-button"
            >
              <div class="row">
                <div class="col-12 main-button-container">
                  <button
                    class="primary-square-button"
                    :disabled="!isChangedMade"
                    @click="updateOrder()"
                  >
                    Update Order
                  </button>
                </div>
              </div>
              <div
                v-if="isChangedMade && !checkCondition"
                class="row pt-3"
              >
                <div
                  class="col-md-12"
                  style="text-align: center"
                >
                  <p class="p-two">
                    You'll need to refund your customer after you update this
                    order.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div
            v-if="fulfilledArr.length !== 0"
            class="general-card mt-3"
          >
            <div class="general-card__body card-custom">
              <div class="row row-status">
                <div class="col-md-10">
                  <h5 class="general-card__section-title h-five">
                    Fulfilled({{ fulfilledArr.length }})
                  </h5>
                </div>
              </div>
              <div class="content-wrapper">
                <DIsplayProduct
                  :items="fulfilledArr"
                  :currency="orders.currency"
                >
                  <template #expander="scopedData">
                    <div>
                      <li>
                        <span class="p-two">{{ scopedData.item.quantity }} X
                          {{ orders.currency }}
                          {{ scopedData.item.unit_price }}
                        </span>
                      </li>
                    </div>
                  </template>
                  <template #right="scopedData">
                    <div style="padding-left: 10px !important">
                      <li
                        class="list-item p-two"
                        style="text-align: right"
                      >
                        {{ orders.currency }}
                        {{ calculateAmount(scopedData.item).toFixed(2) }}
                      </li>
                    </div>
                  </template>
                </DIsplayProduct>
              </div>
              <div
                class="row card-title-custom"
                style="border-top: none !important"
              >
                <div
                  class="col-md-12"
                  style="text-align: center"
                >
                  <p class="p-two">
                    Fulfilled items can't be edited
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 right-layout-lg">
          <div class="general-card">
            <div class="general-card__body">
              <h4 class="general-card__section-title h-five">
                Summary
              </h4>
              <div
                v-if="!isChangedMade"
                class="row row-custom"
              >
                <div class="col ps-0">
                  <p
                    style="color: grey"
                    class="p-two"
                  >
                    No changes have made.
                  </p>
                </div>
              </div>
              <div v-else>
                <div
                  v-if="updatedTax !== 0 || updatedShipping !== 0"
                  class="row row-custom"
                >
                  <div class="col-8 ps-0">
                    <p class="card-text p-two">
                      Subtotal
                      <span v-if="orders.is_product_include_tax">( Product price include tax )</span>
                    </p>
                  </div>
                  <div
                    class="col-4"
                    style="text-align: right"
                  >
                    <p class="card-text p-two">
                      {{ orders.currency }} {{ updatedSubTotal.toFixed(2) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="updatedShipping !== 0"
                  class="row row-custom"
                >
                  <div class="col ps-0">
                    <p class="card-text p-two">
                      Shipping
                    </p>
                  </div>
                  <div
                    class="col"
                    style="text-align: right"
                  >
                    <p class="card-text p-two">
                      {{ orders.currency }} {{ updatedShipping.toFixed(2) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="updatedTax !== 0"
                  class="row row-custom"
                >
                  <div class="col-8 ps-0">
                    <p class="card-text p-two">
                      Tax
                      <span class="p-two">({{ orders.tax_name }} {{ orders.tax_rate }}%)</span>
                      <span
                        v-if="orders.is_shipping_fee_taxable"
                        class="p-two"
                      >(include shipping tax)</span>
                    </p>
                  </div>
                  <div
                    class="col-4"
                    style="text-align: right"
                  >
                    <p class="card-text p-two">
                      {{ currency }} {{ updatedTax.toFixed(2) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="updatedTax !== 0 || updatedShipping !== 0"
                  class="card-title-btmLine"
                  style="margin-bottom: 20px; margin-top: 20px"
                />
                <div class="row row-custom">
                  <div class="col ps-0">
                    <p class="card-text p-two">
                      Updated Total
                    </p>
                  </div>
                  <div
                    class="col"
                    style="text-align: right"
                  >
                    <p class="card-text p-two">
                      {{ orders.currency }} {{ updatedTotal.toFixed(2) }}
                    </p>
                  </div>
                </div>
                <div class="row row-custom">
                  <div class="col ps-0">
                    <p
                      v-if="parseFloat(netPayment, 10) !== 0"
                      class="card-text p-two"
                    >
                      Paid by customer
                    </p>
                    <p
                      v-else
                      class="card-text p-two"
                    >
                      Net Payment
                    </p>
                  </div>
                  <div
                    class="col"
                    style="text-align: right"
                  >
                    <p
                      v-if="parseFloat(netPayment, 10) !== 0"
                      class="card-text p-two"
                    >
                      {{ orders.currency }} {{ orders.paided_by_customer }}
                    </p>
                    <p
                      v-else
                      class="card-text p-two"
                    >
                      {{ orders.currency }} {{ netPayment }}
                    </p>
                  </div>
                </div>
                <div
                  class="card-title-btmLine"
                  style="margin-top: 20px; margin-bottom: 20px"
                />
                <div class="row row-custom">
                  <div class="col ps-0">
                    <p
                      v-if="checkCondition"
                      class="card-text h-five"
                      style="font-weight: bold"
                    >
                      Amount to collect
                    </p>
                    <p
                      v-else
                      class="card-text h-five"
                    >
                      Amount to refund
                    </p>
                  </div>
                  <div
                    class="col"
                    style="text-align: right"
                  >
                    <p class="card-text p-two">
                      {{ orders.currency }} {{ amountToCollect().toFixed(2) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- add product row -->
    </div>

    <!-- Browse Product Modal -->

    <ModalTemplate
      id="browseProductModal"
      title="Add Products"
      button_title="Save"
      @save-button="saveProduct"
      @close-button="searchInput = ''"
      @cancel-button="searchInput = ''"
    >
      <template #body>
        <div class="add-product-modal-body">
          <div class="modal-search-wrapper">
            <div class="input-group-prepend">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="fas fa-search gray_icon" />
                  </span>
                </div>
                <input
                  ref="search"
                  v-model="searchInput"
                  type="text"
                  class="form-control"
                  placeholder="Search products"
                >
              </div>
            </div>
          </div>

          <div class="modal-body browse-content">
            <template v-if="!filteredItems.length">
              <div class="wrapper">
                <span class="flex-center">No results found...</span>
              </div>
            </template>
            <ul class="list-group">
              <li
                v-for="(product, index) in filteredItems"
                :key="index + 1"
                class="list-group-item list-group-item-action"
                @click="addTheProduct(index)"
              >
                <div
                  class="row"
                  :class="{ 'ms-4': !product.hasVariant }"
                  style="align-items: center"
                >
                  <div class="col-1">
                    <input
                      type="checkbox"
                      :value="product"
                      class="me-1 black-checkbox"
                      :disabled="product.disabled"
                      :checked="product.isChecked"
                    >
                    <span />
                  </div>
                  <div class="col-2">
                    <!-- v-if="product.hasVariant == null" -->
                    <!-- <span >
                    <img
                      :src="'/images/product-default-image.png'"
                      class="me-4"
                      style="object-fit: cover;"
                      width="40"
                      height="40"
                    />
                  </span> -->
                    <span>
                      <img
                        :src="product.image_url || productDefaultImages"
                        width="40"
                        height="40"
                        style="object-fit: cover"
                      >
                    </span>
                  </div>
                  <div
                    class="col-5 pe-1"
                    style="text-overflow: ellipsis"
                  >
                    <span
                      style="padding-left: 2px"
                      class="p-two"
                    >{{
                      product.product_name
                    }}</span>
                    <!-- v-if="product.hasVariant !== null" -->
                    <!-- <span v-else>{{ product.variant_name }}</span> -->
                    <!-- v-if="product.hasVariant !== null" -->
                  </div>
                  <div
                    class="col-4 p-two p-two"
                    style="text-align: right"
                  >
                    <span>{{ orders.currency }}
                      {{ (product.productPrice * exchangeRate).toFixed(2) }}
                    </span>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </template>
    </ModalTemplate>

    <!--Adjust Quantity Modal -->
    <ModalTemplate
      id="adjustQtyModal"
      title="Adjust Quantity"
      button_title="Save"
      @save-button="saveAdjust"
    >
      <template #body>
        <span class="p-two">Adjust the quantity for {{ selectedProduct.product_name }}</span>
        <br>
        <br>
        <p
          class="m-container__text p-two"
          style="color: #808080"
        >
          Quantity:
        </p>
        <input
          type="number"
          class="form-control m-container__input"
          min="0"
          :value="selectedProduct.quantity"
          @input="limitInput($event)"
          @keyup.enter="saveAdjust"
        >
      </template>
    </ModalTemplate>
  </div>
</template>

<script>
import DisplayProduct from '@order/components/OrderDisplayProduct.vue';
import { Modal } from 'bootstrap';
import orderAPI from '@order/api/orderAPI.js';
import productDefaultImages from '@shared/assets/media/product-default-image.png';

export default {
  name: 'EditOrder',
  components: {
    DisplayProduct,
  },
  props: {
    orders: { type: Object, default: () => {} },
    orderDetail: { type: Array, default: () => [] },
    userProducts: { type: Array, default: () => [] },
    currency: { type: String, default: 'RM' },
    exchangeRate: { type: String, default: '' },
    variationCombination: { type: Array, default: () => [] },
  },

  data() {
    return {
      productDefaultImages,
      unit_quantity: '',
      orderProduct: [],
      addedProduct: [],
      searchInput: '',
      inputQuantity: 0,
      name: '',
      productIndex: '',
      state: 'NoChanged',
      total: '',
      amountCollect: '',
      allProductArr: [],
      removedProduct: [],
      isChangedMade: false,
      existedProduct: [],
      totalTax: 0.0,
      shippingTax: 0.0,
      products: [],
      selectedProduct: {},
      selectedIndex: '',
      selectedType: '',
    };
  },

  computed: {
    filteredItems() {
      return this.products.filter((product) => {
        return product.product_name
          .toString()
          .toLowerCase()
          .includes(this.searchInput.toLowerCase());
      });
    },

    fulfilledArr() {
      return this.orderDetail.filter((item) => {
        return item.fulfillment_status === 'Fulfilled';
      });
    },

    checkCondition() {
      const netPayment = this.orders.paided_by_customer - this.orders.refunded;
      if (this.orders.payment_status === 'Unpaid') {
        return this.updatedTotal >= netPayment;
      }
      return this.updatedTotal >= parseInt(this.orders.paided_by_customer, 10);
    },

    unfulfilledArr() {
      return this.orderDetail.filter((item) => {
        return item.fulfillment_status === 'Unfulfilled';
      });
    },

    netPayment() {
      return (this.orders.paided_by_customer - this.orders.refunded).toFixed(2);
    },

    updatedTax() {
      const combineArr = this.allProductArr.concat(
        this.existedProduct,
        this.orderProduct,
        this.fulfilledArr
      );
      //   console.log(combineArr);
      const calculateTax = combineArr.reduce((tax, item) => {
        if (item.is_taxable) {
          const currentPrice =
            parseFloat(item.unit_price, 10) * parseFloat(item.quantity, 10);
          const afterTax =
            currentPrice * (parseFloat(this.orders.tax_rate, 10) / 100);
          const taxRate = parseFloat(this.orders.tax_rate, 10) / 100;
          if (this.orders.is_product_include_tax) {
            const productIncludeTax = afterTax / (1 + taxRate);
            return tax + productIncludeTax;
          }
          return tax + afterTax;
        }
        return tax + 0.0;
      }, 0);

      return calculateTax + this.shippingTax;
    },

    updatedSubTotal() {
      const combineArr = this.allProductArr.concat(
        this.existedProduct,
        this.orderProduct,
        this.fulfilledArr
      );

      //   console.log(combineArr);

      return combineArr.reduce((total, item) => {
        return parseFloat(item.total, 10) + total;
      }, 0);
      // return 0.00;
    },

    updatedShipping() {
      return parseFloat(this.orders.shipping, 10) || 0.0;
    },

    updatedTotal() {
      if (
        this.orders.is_product_include_tax &&
        this.orders.is_shipping_fee_taxable
      ) {
        return (
          this.updatedSubTotal + this.updatedShipping + this.shippingTax || 0.0
        );
      }
      if (this.orders.is_product_include_tax) {
        return this.updatedSubTotal + this.updatedShipping || 0.0;
      }
      return (
        this.updatedSubTotal + this.updatedShipping + this.updatedTax || 0.0
      );
    },
  },

  watch: {
    orders: {
      deep: true,
      handler(newVal) {
        if (newVal.is_shipping_fee_taxable) {
          this.shippingTax =
            parseFloat(newVal.shipping, 10) *
            (parseFloat(newVal.tax_rate, 10) / 100);
        }
      },
    },
    //   removedProduct(){
    //       this.$forceUpdate();
    //   }
  },

  created() {
    this.existedProduct = this.unfulfilledArr;
    this.mapToProducts();
  },

  methods: {
    mapToProducts() {
      this.products = this.userProducts.map((item) => {
        return {
          productID: item.id,
          product_name: item.productTitle,
          variant_name: null,
          productPrice: item.productPrice,
          unit_price: item.productPrice,
          quantity: 1,
          variant: [],
          customization: [],
          total: item.productPrice,
          paymentStatus: 'Unpaid',
          fulfillmentStatus: 'Unfulfilled',
          image_url: item.productImagePath,
          is_taxable: item.isTaxable,
          weight: item.weight,
          hasVariant: false,
          isChecked: false,
          disabled: false,
        };
      });
      this.products.map((m) => {
        const value = m;
        const isUnfulfill = this.unfulfilledArr.some(
          (product) => product.users_product_id === value.productID
        );
        value.disabled = isUnfulfill;
        value.isChecked = isUnfulfill;
        return value;
      });
    },
    limitInput(event) {
      const data = event;
      if (data.target.value <= 0) {
        data.target.value = 0;
      } else {
        data.target.value = parseInt(data.target.value.replace(/^0/, ''), 10);
      }

      this.inputQuantity = data.target.value;
    },
    addTheProduct(index) {
      const product = this.filteredItems[index];
      if (product.disabled) return;
      // toggle isChecked between true and false
      product.isChecked = !product.isChecked;
      if (product.hasVariant === 1) {
        /* for product with variant */
        const uncheckedProducts = this.products.filter(
          (item) => item.productID === product.productID
        );
        if (product.isChecked) {
          // should make all variant isChecked = true
          uncheckedProducts.forEach((item) => {
            const data = item;
            if (this.addedProduct.indexOf(data) === -1) {
              data.isChecked = true;
              this.addedProduct.push(data);
            }
          });
        } else {
          // should make all variant isChecked = false
          uncheckedProducts.forEach((item) => {
            const data = item;
            const addedProductIndex = this.addedProduct.indexOf(data);
            if (addedProductIndex !== -1) {
              data.isChecked = false;
              this.addedProduct.splice(addedProductIndex, 1);
            }
          });
        }
      } else {
        /* for product without variant */
        if (product.isChecked) {
          // if the product is checked, add it into addedProduct array
          this.addedProduct.push(product);
        } else {
          // if the product is unchecked, remove it from addedProduct array
          const productIndex = this.addedProduct.indexOf(product);
          this.addedProduct.splice(productIndex, 1);
        }
        if (product.variant_name) {
          const parent = this.products.find(
            (item) => item.productID === product.productID
          );

          const allVariant = this.products.filter(
            (item) =>
              item.productID === product.productID && item.hasVariant !== 1
          );
          const isAllVariantChecked = allVariant.every(
            (item) => item.isChecked
          );
          if (this.addedProduct.indexOf(parent) === -1) {
            // console.log('Parent should be added');
            this.addedProduct.push(parent);
          }
          parent.isChecked = isAllVariantChecked;
        }
        // at this stage, product variant parent will be added into addedProduct array
      }
    },

    saveProduct() {
      this.isChangedMade = true;
      this.addedProduct.forEach((item, i) => {
        let index = i;
        const allArray = this.orderProduct
          .concat(this.existedProduct)
          .concat(this.removedProduct);
        const checkedDuplicate = allArray.some(
          (e) =>
            (e.productID || e.id) === e.productID &&
            e.variant_name === item.variant_name
        );
        if (!checkedDuplicate && item.hasVariant !== 1) {
          this.orderProduct.unshift(item);
        }
        this.addedProduct.splice(index, 1);
        index -= 1; // decrement i IF we remove an item
      });
      this.searchInput = '';

      this.browseProductModal.hide();
    },

    setFocus() {
      this.browseProductModal = new Modal(
        document.getElementById('browseProductModal')
      );
      this.browseProductModal.show();

      this.$refs.search.focus();
    },

    amountToCollect() {
      const netPayment = this.orders.paided_by_customer - this.orders.refunded;
      const paymentStatus = this.orders.payment_status;
      const currentTotal = this.updatedTotal;
      const paidedByCustomer = this.orders.paided_by_customer;
      let amountCollect = 0;

      if (paymentStatus === 'Unpaid') {
        if (currentTotal >= netPayment) {
          amountCollect = currentTotal - netPayment;
        }
      } else {
        amountCollect =
          currentTotal >= paidedByCustomer
            ? currentTotal - paidedByCustomer
            : paidedByCustomer - currentTotal;
      }
      return amountCollect;
    },

    calculateAmount(product) {
      const productData = product;
      productData.total =
        parseFloat(product.quantity, 10) * parseFloat(product.unit_price, 10);
      return productData.total;
    },

    removeOProduct(product, index) {
      const productDetail = product;
      if (product.hasVariant === null) {
        const parent = this.products.find((item) => {
          return item.productID === product.productID && item.hasVariant === 1;
        });
        parent.disabled = false;
        parent.isChecked = false;
      }
      productDetail.disabled = false;
      productDetail.isChecked = false;
      productDetail.quantity = 1;
    },

    removeEProduct(product, index) {
      const productDetail = product;
      this.isChangedMade = true;
      if (product.hasVariant === null) {
        const parent = this.products.find((item) => {
          return item.productID === product.productID && item.hasVariant === 1;
        });
        parent.disabled = true;
        parent.isChecked = true;
      }
      productDetail.disabled = true;
      productDetail.isChecked = true;
      productDetail.quantity = 0;
      this.removedProduct.unshift(product);
    },

    adjustQuantity(product, index, type) {
      this.selectedProduct = product;
      this.selectedIndex = index;
      this.selectedType = type;
      this.adjustQtyModal = new Modal(
        document.getElementById('adjustQtyModal')
      );
      this.adjustQtyModal.show();
    },

    saveAdjust() {
      this.isChangedMade = true;
      this.selectedProduct.quantity = this.inputQuantity;
      const checkedRemovedProduct = this.products.find((item) => {
        return (
          item.productID ===
          (this.selectedProduct?.users_product_id ??
            this.selectedProduct.productID)
        );
      });

      if (this.inputQuantity === 0 && this.selectedType !== 'removed') {
        if (this.selectedType !== 'new') {
          checkedRemovedProduct.disabled = true;
          checkedRemovedProduct.isChecked = true;
          this.removedProduct.unshift(this.selectedProduct);
          this.$delete(this.existedProduct, this.selectedIndex);
        } else {
          checkedRemovedProduct.disabled = false;
          checkedRemovedProduct.isChecked = false;
          this.$delete(this.orderProduct, this.selectedIndex);
        }
      } else if (this.inputQuantity > 0) {
        if (this.removedProduct.length > 0) {
          checkedRemovedProduct.disabled = true;
          this.existedProduct.unshift(this.selectedProduct);
          this.$delete(this.removedProduct, this.selectedIndex);
        }
      }
      this.adjustQtyModal.hide();
    },

    keyUpTriggerModal() {
      this.browseProductModal = new Modal(
        document.getElementById('browseProductModal')
      );
      this.browseProductModal.show();
    },

    updateOrder() {
      orderAPI
        .updateOrderDetail({
          orderProduct: this.orderProduct,
          existedProduct: this.existedProduct,
          removedProduct: this.removedProduct,
          ordersJs: this.orders,
          total: this.updatedTotal,
          taxes: this.updatedTax,
          shipping: this.updatedShipping,
          subTotal: this.updatedSubTotal,
          amountToCollect: this.amountToCollect,
        })
        .then((response) => {
          this.$inertia.visit(`/orders/details/${response.data.order_id}`);
        })
        .catch((error) => {});
    },
  },
};
</script>

<style lang="scss" scoped>
// New Design

div {
  font-family: $base-font-family;
  color: $base-font-color;
  font-size: $base-font-size;
}

p,
span,
label {
  font-size: $base-font-size;
  color: $base-font-color;
  font-family: $base-font-family;
}

// input {
//   font-size: $base-font-size;
//   color: $base-font-color;
//   font-family: $base-font-family;
//   border-color: #ced4da;
// }

.card-title {
  font-size: 16px;
  color: $base-font-color;
  font-family: $base-font-family;
  font-weight: 700;
  margin-bottom: 12px;
}

// .general-card__body {
//   padding-bottom:20px;
//   // @media(max-width: $md-display) {
//   //   // padding-left: 0px !important;
//   //   // padding-right: 0px !important;
//   //   &.card-custom-product{
//   //     padding-bottom: 0px !important;
//   //   }
//   // }
// }

// .container-padding {
//   @media(max-width: $md-display) {
//     padding-left: $mobile-align-left-padding !important;
//     padding-right: $mobile-align-left-padding !important;
//   }
// }

.footer-button {
  flex-direction: column;
  padding-left: 20px;
}

.general-card {
  margin-top: 0 !important;
}

.main-button-container {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
}

.btn-custom {
  overflow: hidden;
  width: 25%;
  float: right;
  text-align: center;
  text-overflow: ellipsis;
}

// .list-group-item-custom {
//   position: relative;
//   display: block;
//   padding: 0.75rem 1.25rem;
//   background-color: #fff;
//   border: 1px solid rgba(0, 0, 0, 0.125);
// }

.card-title-custom {
  border-top: 1px solid lightgrey;
  text-align: center;
  padding-top: 20px;
  margin-top: 0;
}

.card-custom-product {
  border-bottom: 1px solid lightgrey;
  border-top: 1px solid lightgrey;
  // padding: 20px 0px 0px;
  // @media (max-width: $md-display) {
  //   margin: 0 $mobile-align-left-padding;
  // }
}

.row-content {
  padding: 20px 0px;
  align-items: center;
  border-top: 1px solid lightgrey;
  @media (max-width: $md-display) {
    width: 300px;
  }
}

.row-status {
  // padding-left: 20px !important;
  // @media (max-width: $md-display) {
  //   padding-left: 0 !important;
  // }
}

.form-control:focus {
  border-color: #c2c9ce;
}

@media only screen and (max-width: 768px) {
  /* For mobile phones: */
  [class*='col-'] {
    width: 100%;
    margin: 0;
    padding: 0;
  }
}

.primary-square-button {
  @media (max-width: $sm-display) {
    width: 100% !important;
  }
}

.content-wrapper {
  overflow: auto;
  // margin: 0 20px;

  .firstElement {
    margin-top: -1px;
  }

  // add products to existing product
  // to select the last child of first display product
  &:deep
    > .display-product:first-child
    .row-product:last-child
    .product-container {
    // border-bottom: 1px solid lightgrey !important;
    border-bottom: 0;
    padding-bottom: 10px;
  }

  // to select the second div child of first content-wrapper
  &:deep > .firstElement .display-product-container {
    border-top: 1px solid lightgray;
  }

  // @media (max-width: $md-display) {
  //   margin: 0 $mobile-align-left-padding;
  // }
}

.list-group-item {
  padding: 0.25rem 0.75rem;
}

.input-group {
  border-color: $table-border-color;
  flex-wrap: unset;
  .input-group-text {
    border-right: none;
    background-color: white;
    height: 100%;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-color: $table-border-color;
  }

  .form-control {
    border-left: none;
    height: 100%;
    border-color: $table-border-color;
  }
}
</style>

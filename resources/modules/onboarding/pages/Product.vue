<template>
  <OnboardingFormLayout
    title="Step 2: Create a product"
    :back-url="
      saleChannel === 'mini-store' ? '/onboarding/mini-store/setup' : null
    "
    skip-url="/onboarding/payments"
  >
    <template #description>
      Set up the details of your first product to sell in your store
    </template>
    <template #content>
      <div style="width: 100%; text-align: start">
        <div class="product-page-whole">
          <div class="row">
            <div class="col">
              <ProductTitle
                :title="productDetails.title"
                :onboarding="true"
                @inputTitle="inputProductTitle"
              />
              <!-- end-title -->

              <!-- media -->
              <div class="express-setup">
                <p
                  class="product-section-title--editor h-five mt-6 mb-2"
                  style="font-weight: 500; margin-left: 10px"
                >
                  Media
                </p>
                <div class="general-card-section media-image-container mb-0">
                  <div class="upload-container">
                    <ProductMultiImagePreview
                      :images="productImageArray"
                      @update-index="selectedImageIndex = $event"
                      @onProductImageSwapping="
                        $nextTick(() => {
                          productImageArray = $event;
                        })
                      "
                    />
                  </div>
                </div>
              </div>
              <!-- end-media -->
              <!-- pricing -->
              <div class="express-setup">
                <ProductPricing
                  :currency="currency"
                  :price="productDetails.price"
                  :onboarding="true"
                  @input="inputProductPrice"
                />
              </div>
              <!-- end-pricing -->
              <!-- shipping -->
              <div class="express-setup">
                <ProductWeight
                  :product-details="productDetails"
                  :onboarding="true"
                  @input="inputProductWeight"
                />
              </div>
              <!-- end-shipping -->
            </div>
          </div>
        </div>
      </div>
    </template>

    <template #submit-button>
      <BaseButton @click="checkErrors">
        Create
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          fill="#fff"
          class="bi bi-arrow-right-short"
          viewBox="0 0 16 16"
          style="vertical-align: text-top"
        >
          <path
            fill-rule="evenodd"
            d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"
          />
        </svg>
      </BaseButton>
    </template>
  </OnboardingFormLayout>

  <ImageUploader
    type="default"
    @update-value="chooseImage"
  />
</template>

<script>
/* eslint-disable no-unused-expressions */
import Cookies from 'js-cookie';
import ProductTitle from '@product/components/ProductTitle.vue';
import ProductMultiImagePreview from '@product/components/ProductMultiImagePreview.vue';
import ProductPricing from '@product/components/ProductPricing.vue';
import ProductWeight from '@product/components/ProductWeight.vue';
import cloneDeep from 'lodash/cloneDeep';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';

export default {
  name: 'OnboardingProduct',
  components: {
    ProductMultiImagePreview,
    ProductTitle,
    ProductPricing,
    ProductWeight,
    ImageUploader,
  },
  layout: OnboardingLayout,
  props: ['currency'],
  data() {
    return {
      selectedImageIndex: 0,
      saleChannel: null,
      loading: false,
      productDetails: {
        title: '',
        description: '',
        status: 'active',
        imagePath: null,
        imageCollection: [],
        isPhysical: true,
        price: 0,
        SKU: '',
        quantity: 0,
        is_selling: true,
        isTaxable: true,
        comparePrice: null,
        weight: 0,
        categories: [],
        payment_type: 'subscription_and_otp',
        sale_channels: ['funnel', 'online-store', 'mini-store'],
      },
      productImageArray: [],
      disabled: false,
    };
  },
  mounted() {
    if (Cookies.get('saleChannel'))
      this.saleChannel = Cookies.get('saleChannel');
    if (localStorage.getItem('onboardingProduct')) {
      this.productDetails = JSON.parse(localStorage.onboardingProduct);
      const images = [
        ...[this.productDetails?.imagePath],
        ...(this.productDetails?.imageCollection ?? []),
      ];
      this.productImageArray = images;
    }

    eventBus.$on('deleteMultiProductImage', (data) => {
      this.productImageArray.splice(data, 1);
    });

    // $(document).on('wheel', 'input[type=number]', function (e) {
    //   $(this).blur();
    // });
  },

  methods: {
    chooseImage(e) {
      this.productImageArray[this.selectedImageIndex] = e;
    },
    inputProductTitle(e) {
      if (e) {
        this.productDetails.title = e;
      }
    },
    inputProductPrice(e) {
      if (e.target) {
        this.productDetails.price = e.target.value;
      }
    },
    inputProductWeight(e) {
      if (e.target) {
        this.productDetails.weight = e.target.value;
      }
    },
    checkErrors() {
      const product = this.productDetails;

      if (!product.title || product.title.trim() === '') {
        this.$toast.error('Save failed', 'Product Title cannot be empty');
      } else if (this.productDetails.price < 0) {
        this.$toast.error(
          'Save failed',
          'Product Price cannot be equal or less than 0'
        );
      } else if (
        product.comparePrice !== null &&
        product.comparePrice !== '' &&
        (product.price || 0) >= product.comparePrice
      ) {
        this.$toast.error(
          'Save failed',
          'Product original price must bigger than selling price'
        );
      } else if (product.isPhysical && product.weight == null) {
        this.$toast.error(
          'Save failed',
          'Weight cannot be blank if the product is physical'
        );
      } else {
        this.saveProduct();
      }
    },

    saveProduct() {
      this.loading = true;
      if (this.productImageArray.length !== 0) {
        const images = cloneDeep(this.productImageArray);
        this.productDetails.imageCollection = images;
        this.productDetails.imagePath = images.shift();
      } else {
        this.productDetails.imagePath = '/images/product-default-image.png';
        this.productDetails.imageCollection = [];
      }

      const promise1 = new Promise((resolve, reject) => {
        resolve(
          localStorage.setItem(
            'onboardingProduct',
            JSON.stringify(this.productDetails)
          )
        );
      });

      promise1
        .then(() => {
          this.isSaved = true;
          // this.$toast.success('Success', 'Successful Added Product');
          this.$inertia.visit('/onboarding/payments');
        })
        .catch((error) => {
          this.$toast.error('Error', error);
        })
        .finally(() => {
          this.loading = false;
          this.disabled = false;
        });
    },
  },
};
</script>

<style scoped lang="scss">
.express-setup {
  padding-bottom: 20px;
}

.row > * {
  padding-left: 0;
  padding-right: 0;
}

p {
  margin-bottom: 0;
}

.span::before {
  margin-top: 0 !important;
}

.preview {
  padding: 0 2rem;
}

.general-card {
  min-height: 100px;
}

.general-card__body {
  padding-bottom: 20px !important;
}

@media (max-width: 1338px) and (min-width: 769px) {
  .general-card:nth-child(4) .row {
    display: block;
    .col-md-6 {
      max-width: none;
      span {
        white-space: nowrap;
      }
    }

    .col-left,
    .col-right {
      padding: 0;
    }

    .col-left {
      padding-bottom: 20px;
    }
  }
}

@media (max-width: $md-display) {
  .general-card .general-card__body:nth-child(2) {
    padding-top: 0 !important;
  }
}

.general-card-section {
  margin-bottom: 0rem !important;
}

.col-left,
.col-right {
  // @media (max-width: $md-display) {
  padding-bottom: 0;
  // }
}

.col-left {
  @media (max-width: $md-display) {
    padding-bottom: 20px;
  }
}

.media-image-container {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.upload-container {
  width: 100%;
  // min-height: 300px;
  border: 2px dashed grey;
  border-radius: 3px;
  margin-left: 10px;
  margin-right: 10px;
}

@media (max-width: $md-display) {
  .upload-container {
    height: auto;
  }

  .col-md-4,
  .col-md-8 {
    padding: 0;
  }
}

// .table_header_row {
//   border-top: thin solid #e0e0e0;
// }

.table {
  // font-size: 10px;
  width: 1350px;
  table-layout: fixed;

  thead {
    border-bottom: thin solid #e0e0e0;
  }

  th {
    min-width: 150px;
    border-top: 0;
    font-size: 14px !important;
    white-space: normal;
  }

  tbody tr:last-child,
  tr:last-child td {
    border-bottom: 0;
  }
  tbody tr:first-child {
    border-top: 0;
  }

  td {
    font-size: 1rem;
    max-width: 150px;
    max-height: 48px;
    padding-top: 8px;
    padding-bottom: 8px;
  }
}

.table th > div {
  text-align: left;
  // font-size: 10px;
}

.table th:nth-child(5) > div,
.table th:nth-child(4) > div {
  text-align: right;
}

.table td:first-child,
.table th:first-child {
  min-width: 150px;
  width: 150px;
}

.table td:nth-child(9) > div,
.table td:nth-child(10) > div {
  display: flex;
  justify-content: center;
}

.table td > div,
.table th > div {
  cursor: context-menu;
  width: 150px;
}

.table td {
  padding-bottom: 16px;
  padding-top: 16px;
  min-width: 150px;
}

input {
  color: $base-font-color;
  font-family: $base-font-family;
  padding: 0 12px;
  height: 36px;

  &:active,
  &:focus,
  &:hover {
    outline: none;
  }
}

textarea {
  color: $base-font-color;
  font-family: $base-font-family;
  padding: 6px 12px;
}

.input-group {
  border-color: $table-border-color;
  flex-wrap: unset;
  .input-group-text {
    // border-left: 1px solid !important;
    background-color: white;
    height: 100%;
    border-radius: 3px 0 0 3px;
    border-color: $table-border-color !important;
  }

  .form-control {
    border-left: none;
    height: 100%;
    border-color: $table-border-color;
  }
}

#weight.input-group-text {
  background: white !important;
  border: 1px soli;
  border-left: 0px solid white !important;
  border-radius: 0 3px 3px 0;
  padding: 0 12px;
}

.prepand-no-left-border {
  border-left: 0px;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.append-no-right-border {
  border-right: 0px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

textarea:focus {
  outline: none;
}

::placeholder {
  font-size: 1rem;
  color: lightslategray;
}

.footer-container {
  margin: 0 auto;
  border-top: 0.1rem solid var(--p-border-subdued, #dfe3e8);
  display: flex;
  justify-content: flex-end;

  @media (max-width: $sm-display) {
    flex-direction: column-reverse;
  }

  &__cancel-btn {
    color: #6c757d;
    text-decoration: underline;
    display: flex;
    justify-content: center;
    align-items: center;

    &:hover {
      cursor: pointer;
    }
  }

  &__button {
    margin-left: 20px;
  }
}

.primary-small-square-button,
.primary-small-white-button {
  @media (max-width: $sm-display) {
    width: 100%;
    margin: 0 !important;

    &:not(:first-child) {
      margin-bottom: 8px !important;
    }
  }
}

.cancel-button {
  padding-top: 8px;
  @media (max-width: $sm-display) {
    width: 100%;
    display: flex;
    justify-content: center;
    padding-right: 0;
  }
}

.edit_options_wrapper {
  //   padding: 1.4rem 2rem 1rem 2rem;
  display: flex;
  justify-content: space-between;
  @media (max-width: $md-display) {
    padding: 0;
  }
}

.image-container {
  height: 50px;
  width: 60px;
  border-radius: 4px;
  border-color: black;
}

div.vc-chrome-body {
  padding: 12px 14px 10px !important;
}

.tax-label {
  color: grey;
  margin-bottom: 4px;
  //   opacity: 100%;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}

.form-control {
  padding-top: 8px;
  padding-bottom: 8px;

  &::placeholder {
    font-size: 12px;
  }
  &:hover,
  &:active,
  &:focus {
    outline: none !important;
  }
}
.col-10 {
  &:hover,
  &:active,
  &:focus {
    outline: none !important;
  }
}
.modal-footer {
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: flex-end;
  border-top: none;
}

.add_variant_label {
  padding-bottom: 16px;
}

.delete-icon {
  position: absolute;
  right: 5px;
  top: 5px;
  font-size: 11px;
  width: 20px;
  height: 20px;
  padding: 4px;
  margin: 10px;
  background-color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  float: right;
  color: gray;

  &:hover {
    cursor: pointer;
  }
}

.td-zero {
  display: none !important;
}

// sticky table
.stickyHeader {
  position: sticky;
  top: 0;
  background-color: white;
  border-bottom: thin solid #e0e0e0c0;
  //   border-top: thin solid #E0E0E0;
  border-top: thin solid #e0e0e0c0;
  // z-index: 3;
  width: 150px;
}

.text-align-center {
  justify-content: center;
  text-align: center !important;
}

.addProductOption {
  float: right;
  @media (max-width: $md-display) {
    float: left;
  }
}

.xbuttontags {
  color: #909294;
  padding-left: 10px;
}

/* width */
::-webkit-scrollbar {
  width: 0.5px;
}

@media (max-width: $sm-display) {
  ::-webkit-scrollbar {
    width: 7.5px !important;
  }
}

/* Track */
::-webkit-scrollbar-track {
  background-color: #f2f2f2;
  border-radius: 10px;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #d0d2d3;
  border-radius: 10px;
  border: 2.5px solid #f2f2f2;
}

.continue-selling-or-purchasable {
  text-align: center !important;
  white-space: normal;
  margin: auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.product-page-whole,
.product-page-whole span {
  font-family: 'Roboto';
  font-size: 14px;
}

input[type='checkbox'].purple-checkbox {
  position: absolute;
  opacity: 0;
  z-index: -1;
}

input[type='checkbox'].purple-checkbox + span:before {
  content: '';
  border: 1px solid grey;
  border-radius: 3px;
  display: inline-block;
  width: 16px;
  height: 16px;
  margin-right: 0.5em;
  margin-top: 0.5em;
  vertical-align: -2px;
  cursor: pointer;
}

input[type='checkbox'].purple-checkbox:checked + span:before {
  background-image: url('/FontAwesomeSVG/check-white.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: 12px;
  border-radius: 2px;
  background-color: #7766f7;
  color: white;
  cursor: pointer;
}

input:focus {
  outline: none !important;
  border: 1px solid #ced4da;
}

//to select the first td placed if td-zero is placed at first in tr
tr > td.td-zero:first-child + td {
  padding-left: 25px;
}

:deep(.card-body) {
  margin: 0px !important;
}

:deep(.card-flush) {
  margin: 0px !important;
  padding: 0px !important;
}

:deep(.form-group) {
  margin: 0px !important;
}
</style>

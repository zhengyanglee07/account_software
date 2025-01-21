<template>
  <BasePageLayout
    :page-name="type === 'new' ? 'Add Product' : 'Update Product'"
    back-to="/products"
  >
    <BaseTab
      v-if="productDetails?.type !== 'physical'"
      :tabs="tabs"
      @click="toggleTab"
    />

    <template v-if="selectedTab === 'setting'" #left>
      <ProductTitle
        :submit="submit"
        :title="productDetails?.title ?? ''"
        @inputTitle="inputProductTitle"
      >
        <template #description>
          <BaseFormGroup label="Description">
            <ProductDescriptionEditor
              ref="editor"
              :data="productDetails"
              :property="'description'"
              @input="inputProductDescription"
              @inputImage="sectionType = 'description'"
            />
          </BaseFormGroup>
        </template>
      </ProductTitle>
      <!-- end-title -->

      <!-- media -->
      <BaseCard has-header title="Media">
        <BaseFormGroup>
          <ProductMultiImagePreview
            :images="productImageArray"
            @update-index="selectedImageIndex = $event"
            @onProductImageSwapping="
              $nextTick(() => {
                productImageArray = $event;
              })
            "
            @inputImage="sectionType = 'default'"
          />
        </BaseFormGroup>
      </BaseCard>
      <!-- end-media -->

      <!-- asset -->
      <BaseCard v-if="isVirtual" has-header title="Asset URL">
        <BaseFormGroup>
          <BaseFormInput
            v-model="productDetails.asset_url"
            placeholder="https://example.com"
            type="url"
          />
        </BaseFormGroup>
      </BaseCard>
      <!-- end-asset -->

      <!-- pricing -->
      <ProductPricing
        :currency="currency"
        :price="productDetails.price"
        :taxable="productDetails.isTaxable"
        @inputPrice="
          $nextTick(() => {
            productDetails.price = $event?.target?.value ?? $event;
          })
        "
      >
        <!-- <template #taxlabel>
              <span
                v-if="productDetails.isTaxable"
                class="tax-label me-2"
              >( Price not include tax )</span>
            </template>
            <template #exceptDefault>
              <img
                v-if="exceptDefault.length !== 0 && false"
                src="@product/assets/media/pricing-conversion-icon.svg"
                style="
                  cursor: pointer;
                  width: 1rem;
                  transform: scale(2.7);
                  margin-bottom: 4px;
                "
                data-bs-toggle="modal"
                data-bs-target="#product-pricing-modal"
              >
            </template> -->
        <template #isTaxable>
          <BaseFormGroup col="6">
            <BaseFormCheckBox
              v-model="productDetails.isTaxable"
              :value="true"
              inline
            >
              Taxable product
            </BaseFormCheckBox>
          </BaseFormGroup>
        </template>
        <template #comparePrice>
          <BaseFormGroup label="Price Before Discount" col="md-6">
            <BaseFormInput
              v-model.number="productDetails.comparePrice"
              placeholder="0.00"
              min="0.00"
              max="10000.00"
              step="1"
              type="number"
              :prepend="currency === 'MYR' ? 'RM' : currency"
            >
              <template #prepend>
                {{ currency === 'MYR' ? 'RM' : currency }}
              </template>
            </BaseFormInput>
          </BaseFormGroup>
        </template>
      </ProductPricing>

      <ProductPricingModal
        :modal-id="'product-pricing-modal'"
        :product-price="[productDetails.price, productDetails.comparePrice]"
        :account-id="product.account_id"
      />
      <!-- end-pricing -->

      <!-- curriculum -->
      <ProductCurriculum v-if="isCourse" v-model="productDetails.curriculum" />
      <!-- end-curriculum -->

      <!-- Course Period -->
      <ProductCoursePeriod
        v-if="isCourse"
        v-model="productDetails.coursePeriod"
      />
      <!-- end-Course Period -->

      <!-- shipping -->
      <ProductWeight
        v-if="productDetails.isPhysical"
        :product-details="productDetails"
        @inputWeight="
          $nextTick(() => {
            productDetails.weight = $event?.target?.value ?? $event;
          })
        "
      >
        <template v-if="productDetails.isPhysical" #dimension>
          <BaseFormGroup col="md-4" label="Width">
            <BaseFormInput
              id="product-width"
              v-model.number="productDetails.width"
              type="number"
              name="product-width"
              :min="0"
            >
              <template #append> CM </template>
            </BaseFormInput>
          </BaseFormGroup>
          <BaseFormGroup col="md-4" label="Length">
            <BaseFormInput
              id="product-length"
              v-model.number="productDetails.length"
              type="number"
              name="product-length"
              :min="0"
            >
              <template #append> CM </template>
            </BaseFormInput>
          </BaseFormGroup>
          <BaseFormGroup col="md-4" label="Height">
            <BaseFormInput
              id="product-height"
              v-model.number="productDetails.height"
              type="number"
              name="product-height"
              :min="0"
            >
              <template #append> CM </template>
            </BaseFormInput>
          </BaseFormGroup>
        </template>
      </ProductWeight>
      <!-- end-shipping -->

      <!-- inventory -->
      <ProductInventory
        :product="productDetails"
        :except-default="exceptDefault"
        @fetchInventory="inputInventory"
      />
      <!-- end-inventory -->

      <!-- classification code -->
      <BaseCard has-header title="Classification Code">
        <BaseFormGroup col="6" label="Classification Code" required>
          <BaseMultiSelect
            v-model="productDetails.classification_code"
            :options="classificationCodes.sort((a, b) => a.Description.localeCompare(b.Description))"
            label="Description"
            :reduce="(option) => option.Code"
          />
        </BaseFormGroup>
      </BaseCard>
      <!-- end-classification code -->

      <!-- unit of measurements -->
      <BaseCard has-header title="Unit of Measurements">
        <BaseFormGroup col="6" label="Base Unit Label" required>
         <BaseMultiSelect
            v-model="productDetails.unit_type"
            :options="unitTypes.sort((a, b) => a.Name.localeCompare(b.Name))"
            label="Name"
            :reduce="(option) => option.Code"
        />
        </BaseFormGroup>
      </BaseCard>
      <!-- end-unit of measurements -->



      <!-- variant -->
      <BaseCard v-if="false &&!isCourse" has-header has-toolbar title="Variations">
        <BaseFormGroup
          :label="
            !variantOptionValueArrayForDatatable.length
              ? 'Add variants if this product comes in multiple versions, like different sizes or colors.'
              : ''
          "
        >
          <ProductVariantDatatable
            v-if="variantOptionValueArrayForDatatable.length"
            :current-variants="variantOptionValueArrayForDatatable"
            :swap-variant="swapVariant"
          />
          <BaseDatatable
            v-if="computedCombination.length"
            no-header
            no-edit-action
            no-search
            no-sorting
            no-action
            :table-headers="computedCombinationHeaders"
            :table-datas="computedCombination"
          >
            <template #cell-image="{ row: item }">
              <BaseImagePreview
                modal-id="variant-modal"
                no-hover-message
                :default-image="
                  item.image_url ??
                  'https://cdn.hypershapes.com/assets/product-default-image.png'
                "
                size="sm"
                @mouseenter="sectionType = 'variant'"
                @click="selectedCombinationIndex(item.name)"
                @delete="
                  updateCombinationImage(
                    item.name,
                    'https://cdn.hypershapes.com/assets/product-default-image.png'
                  )
                "
              />
            </template>
            <template #cell-variants="{ row: item }">
              {{ item.name }}
            </template>
            <template #cell-sku="{ row: item }">
              <BaseFormInput
                :id="`${item.name}-sku`"
                v-model="variationCombination[item.name].sku"
                type="text"
              />
            </template>
            <template #cell-selling-price="{ row: item }">
              <BaseFormInput
                :id="`${item.name}-price`"
                v-model.number="variationCombination[item.name].price"
                placeholder="0.00"
                :min="0.0"
                :step="1"
                type="number"
              />
            </template>
            <template #cell-compare-price="{ row: item }">
              <BaseFormInput
                :id="`${item.name}-compare-price`"
                v-model.number="
                  variationCombination[item.name].compared_at_price
                "
                placeholder="0.00"
                :min="0.0"
                :step="1"
                type="number"
              />
            </template>
            <template #cell-weight="{ row: item }">
              <BaseFormInput
                :id="`${item.name}-weight`"
                v-model.number="variationCombination[item.name].weight"
                placeholder="0.00"
                :min="0.0"
                :max="10000.0"
                :step="1"
                type="number"
              />
            </template>
            <template #cell-quantity="{ row: item }">
              <BaseFormInput
                :id="`${item.name}-quantity`"
                v-model.number="variationCombination[item.name].quantity"
                placeholder="0"
                :min="0"
                :max="9999"
                :step="1"
                type="number"
              />
            </template>
            <template #cell-is-selling="{ row: item }">
              <BaseFormCheckBox
                :id="`${item.name}-is-selling`"
                v-model="variationCombination[item.name].is_selling"
                :value="true"
                inline
              />
            </template>
            <template #cell-is-visible="{ row: item }">
              <BaseFormCheckBox
                :id="`${item.name}-is-visible`"
                v-model="variationCombination[item.name].is_visible"
                :value="true"
                inline
              />
            </template>
          </BaseDatatable>
        </BaseFormGroup>
        <template #toolbar>
          <BaseButton size="sm" @click="addNewProductVariant">
            {{
              !validVariantOptions?.length ? 'Add Variation' : 'Edit Variation'
            }}
          </BaseButton>
        </template>
      </BaseCard>
      <!-- end-variant -->

      <!-- product seo setting  -->
      <BaseCard v-if="false" has-header has-toolbar title="Search Engine Optimization">
        <BaseFormGroup v-if="seo.title">
          <p
            style="
              word-break: break-word;
              color: #333;
              font-size: 1rem;
              line-height: 1.7;
            "
          >
            <a class="color-blue">{{ seo.title }}</a>
            <br />
            <a
              ><span style="color: #14b17a"
                >{{ onlineStoreDomain || miniStoreDomain }}/products/{{
                  seo?.path
                }}</span
              ></a
            >
            <br />
            <span>{{ seo?.description || '' }}</span>
          </p>
        </BaseFormGroup>
        <template #toolbar>
          <BaseButton
            size="sm"
            @click="
              showSeoSettings = !showSeoSettings;
              seoIndex += 1;
            "
          >
            {{ !showSeoSettings ? 'Edit' : 'Hide' }} Product SEO
          </BaseButton>
        </template>
        <section v-if="showSeoSettings">
          <BaseFormGroup label="Page Title" required>
            <BaseFormInput id="page-title" v-model="seo.title" :max="60" />
          </BaseFormGroup>
          <BaseFormGroup label="Meta Description">
            <BaseFormTextarea
              id="meta-description"
              v-model="seo.description"
              :rows="3"
              :maxlength="160"
            />
          </BaseFormGroup>
          <BaseFormGroup label="URL Handle" required>
            <BaseFormInput
              id="url-handle"
              v-model="seo.path"
              @keyup="getPath($event.target.value)"
            >
              <template #prepend>
                {{ onlineStoreDomain || miniStoreDomain }}/products/
              </template>
            </BaseFormInput>
          </BaseFormGroup>
        </section>
      </BaseCard>
      <!-- end of product seo setting  -->
    </template>

    <template v-if="selectedTab === 'setting'" #right>
      <BaseCard has-header title="Type">
        <BaseFormGroup>
          <p class="text-capitalize mb-0">
            {{ productDetails.type }}
          </p>
        </BaseFormGroup>
      </BaseCard>
      <!-- sale channels -->
      <BaseCard has-header title="Sales Channels">
        <BaseFormGroup>
          <BaseFormCheckBox
            v-model="productDetails.sale_channels"
            value="funnel"
          >
            Funnel
          </BaseFormCheckBox>
          <BaseFormCheckBox
            v-model="productDetails.sale_channels"
            value="online-store"
          >
            Online Store
          </BaseFormCheckBox>
          <!-- <BaseFormCheckBox
            v-model="productDetails.sale_channels"
            value="mini-store"
          >
            Mini Store
          </BaseFormCheckBox> -->
        </BaseFormGroup>
      </BaseCard>
      <!-- end sale channels -->
      <!-- status -->
      <BaseCard has-header title="Status">
        <BaseFormGroup>
          <BaseFormSelect
            v-model="productDetails.status"
            label-key="name"
            value-key="value"
            :options="[
              { name: 'Draft', value: 'draft' },
              { name: 'Active', value: 'active' },
            ]"
            @change="checkSaleChannels"
          />
        </BaseFormGroup>
      </BaseCard>
      <!-- end-status -->
      <!-- category -->
      <ProductCategorySection
        :all-categories="allCategories"
        :selected-categories="productDetails?.categories"
        @addCategory="addCategory"
        @removeCategory="removeCategory"
      />
      <!-- end-category -->
    </template>

    <template v-if="selectedTab == 'list'">
      <!-- student -->
      <ProductCourseStudent
        v-if="isCourse"
        :course="productDetails"
        :contacts="paginatedContacts"
        :existed-student-ids="productCourseStudentIds"
      />
      <!-- end-student -->
      <!-- access list -->
      <ProductAccessList
        v-if="isVirtual"
        :product="productDetails"
        :contacts="paginatedContacts"
        :paginated-access-list="paginatedAccessListData"
        :existed-access-list-ids="productAccessListIds"
      />
      <!-- end-access-list -->
    </template>

    <template v-if="selectedTab === 'setting'" #footer>
      <BaseButton type="link" class="me-5" href="/products">
        Cancel
      </BaseButton>
      <BaseButton :disabled="loading" @click="checkErrors"> Save </BaseButton>
    </template>
  </BasePageLayout>
  <ImageUploader :type="sectionType" @update-value="chooseImage" />

  <!-- add edit product customization modal  -->
  <AddProductModal
    ref="customizations"
    modal-id="add-product-customization-modal"
    title="Configure Customizations"
  >
    <template #body>
      <ProductCustomization :currency="currency" @save="SaveProductOption()" />
    </template>
    <template #footer>
      <BaseButton @click="SaveProductOption()"> Save </BaseButton>
    </template>
  </AddProductModal>
  <!-- end of customization modal  -->

  <!-- product variant modal  -->
  <AddProductModal
    modal-id="add-product-variant-modal"
    title="Configure variations"
  >
    <template #body>
      <AddVariantModalComponent
        ref="variation"
        :current-variants="currentVariants"
      />
    </template>

    <template #footer>
      <BaseButton @click="checkVariantError()"> Save </BaseButton>
    </template>
  </AddProductModal>
  <!-- end of product variant modal  -->
</template>

<script>
/* eslint-disable no-unused-expressions */
/* eslint-disable no-return-assign */
/* eslint consistent-return: 1 */
/* eslint no-restricted-syntax: 1 */
import { mapState, mapMutations } from 'vuex';
import ProductDescriptionEditor from '@product/components/ProductDescriptionEditor.vue';
import ProductPricingModal from '@product/components/ProductPricingModal.vue';

import ProductVariantDatatable from '@product/components/ProductVariantDatatable.vue';
import AddProductModal from '@product/components/AddProductModal.vue';
import AddVariantModalComponent from '@product/components/AddVariantModalComponent.vue';
import ProductCustomization from '@product/components/ProductCustomization.vue';
import ProductCustomizationTable from '@product/components/ProductCustomizationTable.vue';
import ProductCurriculum from '@product/components/ProductCurriculum.vue';
import ProductCoursePeriod from '@product/components/ProductCoursePeriod.vue';
import ProductCourseStudent from '@product/components/ProductCourseStudent.vue';
import ProductAccessList from '@product/components/ProductAccessList.vue';

import ProductTitle from '@product/components/ProductTitle.vue';
import ProductInventory from '@product/components/ProductInventory.vue';
import ProductPricing from '@product/components/ProductPricing.vue';
import ProductWeight from '@product/components/ProductWeight.vue';
import ProductCategorySection from '@product/components/ProductCategorySection.vue';

import ProductMultiImagePreview from '@product/components/ProductMultiImagePreview.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';

import cloneDeep from 'lodash/cloneDeep';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

// import { router } from '@inertiajs/vue3'

export default {
  components: {
    ProductMultiImagePreview,
    ProductPricingModal,
    AddProductModal,
    ProductVariantDatatable,
    AddVariantModalComponent,
    ProductCustomization,
    ProductCustomizationTable,
    ProductInventory,
    ProductCategorySection,
    ProductDescriptionEditor,
    ProductTitle,
    ProductPricing,
    ProductWeight,
    ImageUploader,
    ProductCurriculum,
    ProductCoursePeriod,
    ProductCourseStudent,
    ProductAccessList,
  },
  mixins: [specialCurrencyCalculationMixin],

  props: [
    'product',
    'currency',
    'type',
    'productSales',
    'imageGallery',
    'variantArray',
    'variantCombination',
    'variantNameArray',
    'productOptionArray',
    'taxSetting',
    'exceptDefault',
    'allCategories',
    'productCategory',
    'productSubscription',
    'productSaleChannels',
    'activeSaleChannels',
    'miniStoreDomain',
    'onlineStoreDomain',
    'productCourse',
    'paginatedCourseStudent',
    'paginatedContacts',
    'productCourseStudentIds',
    'paginatedAccessList',
    'productAccessListIds',
    'unitTypes',
    'classificationCodes',
  ],

  data() {
    return {
      sectionType: 'default',
      selectedImageIndex: 0,
      customizationArray: [],
      showLimitModal: false,
      submit: false,
      loading: false,
      productDetails: {
        title: '',
        description: '',
        status: 'active',
        imagePath: null,
        imageCollection: [],
        isPhysical: true,
        price: null,
        SKU: '',
        quantity: 0,
        is_selling: true,
        isTaxable: true,
        comparePrice: null,
        weight: 0.0,
        categories: [],
        payment_type: 'subscription_and_otp',
        sale_channels: ['funnel', 'online-store', 'mini-store'],
        height: 0,
        length: 0,
        width: 0,
        curriculum: [],
        coursePeriod: {
          duration: 'lifetime',
          period: 0,
        },
        courseStudent: [],
        type: '',
        asset_url: '',
        classification_code: '',
        unit_type: '',
      },
      productImageArray: [],
      hasProductVariation: true,
      hasProductOption: false,
      variantOptionValueArrayForDatatable: [],
      variationCombination: {},
      tempCombination: {},
      combinationVariantName: {},
      disabled: false,
      variantTableHeaders: [
        { name: 'Image', key: 'image', custom: true },
        { name: 'Variants', key: 'variants', custom: true },
        { name: 'SKU', key: 'sku', custom: true, width: '160px' },
        {
          name: `Selling Price ( ${
            this.currency === 'MYR' ? 'RM' : this.currency
          } )`,
          key: 'selling-price',
          custom: true,
          width: '160px',
        },
        {
          name: `Original Price ( ${
            this.currency === 'MYR' ? 'RM' : this.currency
          } )`,
          key: 'compare-price',
          custom: true,
          width: '160px',
        },
        { name: 'Weight ( kg )', key: 'weight', custom: true, width: '120px' },
        { name: 'Quantity', key: 'quantity', custom: true, width: '120px' },
        {
          name: 'Sell on stock-out',
          key: 'is-selling',
          custom: true,
          width: '150px',
        },
        { name: 'Purchasable', key: 'is-visible', custom: true },
      ],
      modalId: 'all-product-delete-modal',

      // variant
      selectedVariantIndex: 0,
      selectedVariantValueIndex: 0,
      selectedDetailsIndex: '',
      deletedVariantId: [],
      deletedVariantValueId: [],
      deletedCategoryId: [],
      deletedVariantIdForProductVariation: [],
      currentVariants: [],
      isComponentMounted: false,
      isSaved: false,

      typeOfPayment: 'subscription_and_otp',

      seo: {
        title: '',
        description: '',
        path: '',
      },
      showSeoSettings: false,
      seoIndex: 0,
      selectedTab: 'setting',
      paginatedAccessListData: null,
    };
  },

  computed: {
    ...mapState('product', [
      'inputOptions',
      'deletedInputOptionsId',
      'deletedInputId',
      'deletedSharedInputOption',
      'variantOptionArray',
      'deletedVariantIdBuffer',
      'deletedVariantValueIdBuffer',
      'deletedVariantIdForProductVariationBuffer',
      'isProductEdited',
      'currentArray',
      // subscrption
      'subscriptionArray',
      'savedSubscriptionArray',
      'isCustomizationUpdated',
      'isVariantUpdated',
      'optionIndex',
      'optionValueIndex',
      'validVariantOptions',
      'customizationsIsValid',
    ]),
    tabs() {
      return [
        {
          label: 'Settings',
          target: 'setting',
        },
        {
          label: this.isCourse ? 'Student List' : 'Access List',
          target: 'list',
        },
      ];
    },
    isCourse() {
      return this.productDetails.type === 'course';
    },
    isVirtual() {
      return this.productDetails.type === 'virtual';
    },
    isProductDelete() {
      return (
        this.customerSubscription.filter(
          (subscription) => subscription.status === 'active'
        ).length === 0
      );
    },
    inputOptionId() {
      const ids = [];
      const filteredArray = this.inputOptions.filter(
        (option) => option.is_shared === 0
      );
      filteredArray.forEach((option) => {
        ids.push(option.id);
      });
      return ids;
    },
    sharedInputOptionId() {
      const ids = [];
      const filteredArray = this.inputOptions.filter(
        (option) => option.is_shared === 1
      );
      filteredArray.forEach((option) => {
        ids.push(option.id);
      });
      return ids;
    },

    dynamicTaxLabel() {
      return this.taxSetting.is_product_include_tax
        ? 'Price inclusive tax'
        : 'Price not include tax';
    },

    productInsight() {
      return this.productSales;
    },

    variationTotalCount() {
      const { length } = Object.keys(this.variationCombination);
      if (length > 200) {
        this.$toast.error(
          "You can't have more than 200 product variants.",
          'To save product, remove some options to keep variants under 200.'
        );
      }
      return length;
    },

    defaultObjItem() {
      return {
        image_url: this.productDetails.imagePath,
        ref: null,
        title: '',
        sku: this.productDetails.SKU,
        price: Number.isNaN(this.productDetails.price)
          ? null
          : this.productDetails.price,
        compared_at_price: Number.isNaN(this.productDetails.comparePrice)
          ? null
          : this.productDetails.comparePrice,
        weight: parseFloat(this.productDetails.weight),
        quantity: this.productDetails.quantity,
        is_selling: this.productDetails.is_selling,
        is_visible: true,
        payment_type: this.productDetails.payment_type,
      };
    },

    isVariationEmpty() {
      return this.variantOptionArray.reduce((total, current) => {
        return (total += current.valueArray.length);
      }, 0);
    },
    isPhysicalVariant() {
      return this.hasProductVariation && this.isVariationEmpty > 0;
    },

    isVariantNameEmpty() {
      const isAnyNameEmpty = this.variantOptionArray.some((item) => {
        const isNull = item.name === null;
        let isEmpty = item.name === '';
        if (item.name) {
          isEmpty = item.name.replace(/\s+/g, '') === '';
        }
        return isNull || isEmpty;
      });
      return (
        this.productDetails.isPhysical &&
        this.hasProductVariation &&
        isAnyNameEmpty
      );
    },

    computedCombination() {
      const obj = cloneDeep(this.variationCombination ?? {});
      const arr = Object.keys(obj)?.map((key) => ({
        ...this.variationCombination[key],
        name: key,
      }));
      return arr;
    },

    computedCombinationHeaders() {
      const tempHeader = [...this.variantTableHeaders];
      if (!this.productDetails.isPhysical) {
        tempHeader.splice(5, 1);
        tempHeader.splice(2, 1);
      }
      return tempHeader;
    },

    computedVariantLength() {
      return this.variantOptionArray.length;
    },

    computedVariantValueLength() {
      return this.variantOptionArray[this.computedVariantLength - 1].valueArray
        .length;
    },
  },
  watch: {
    /**
     * Watchers for productDetails.PROPERTY :
     *    1 - productDetails.price
     *    2 - productDetails.quantity
     *    3 - productDetails.comparePrice
     *    4 - productDetials.weight
     *
     * NOTE: These watchers cannot be combined into single watcher
     *        as the reactivity of (deep: true) Vue's Watcher
     *        is not reactive as it seems to be.
     *
     * USAGE: Listen to changes of certain PROPERTY
     *          and apply the changes to variationCombination
     */
    'productDetails.price': {
      handler(newVal, oldVal) {
        for (const [key, value] of Object.entries(this.variationCombination)) {
          const temp = oldVal || 0;
          if (value.price === temp || value.price === null) {
            value.price = newVal;
          }
        }
      },
    },
    'productDetails.quantity': {
      handler(newVal, oldVal) {
        for (const [key, value] of Object.entries(this.variationCombination)) {
          const temp = oldVal !== 0 ? oldVal : null;
          if (value.quantity === temp || value.quantity === null) {
            value.quantity = newVal;
          }
        }
      },
    },
    'productDetails.comparePrice': {
      handler(newVal, oldVal) {
        for (const [key, value] of Object.entries(this.variationCombination)) {
          const temp = oldVal || 0;
          if (
            value.compared_at_price === temp ||
            value.compared_at_price === null
          ) {
            value.compared_at_price = newVal;
          }
        }
      },
    },
    'productDetails.weight': {
      handler(newVal, oldVal) {
        for (const [key, value] of Object.entries(this.variationCombination)) {
          if (value.weight === oldVal) {
            value.weight = newVal;
          }
        }
      },
    },

    /**
     * Watchers for listening changes to product page
     *    1 - productDetails
     *    2 - variationCombination
     *
     * Detect the changes on product. If there is a changes,
     * update the edit status to true to prevent user from accidently 'close' this page
     */
    productDetails: {
      deep: true,
      handler(newVal) {
        if (this.isComponentMounted === true) {
          this.updateProductEditStatus(true);
        }
      },
    },
    variationCombination: {
      deep: true,
      handler(nv) {
        if (this.isComponentMounted === true) {
          this.updateProductEditStatus(true);
        }
      },
    },

    currentArray: {
      deep: true,
      //   immediate: true,
      handler(newVal) {
        if (Object.keys(newVal).length !== 0) {
          this.customizationArray = newVal;
        }
      },
    },
  },

  mounted() {
    this.$store.commit('product/emitCurrency', this.currency);
    if (this.type !== 'new') {
      this.setDefaultValue();
    }
    this.productDetails.type =
      this.product?.type ?? localStorage.productType ?? 'physical';
    this.hasProductVariation = cloneDeep(this.variantArray).length !== 0;
    this.productDetails.isPhysical = this.productDetails.type === 'physical';
    this.calculateCombination();
    this.updateProductEditStatus(false);
    this.setSeperateProductImagesIntoArray();

    eventBus.$on('inputProductPrice', (data) => {
      this.calculateCombination();
      if (data.objKey === undefined) {
        data.type === 'Selling Price'
          ? (this.productDetails.price = data.value)
          : (this.productDetails.comparePrice = data.value);
      } else {
        data.type === 'Selling Price'
          ? (this.variationCombination[data.objKey].price = data.value)
          : (this.variationCombination[data.objKey].compared_at_price =
              data.value);
      }
    });
    if (cloneDeep(this.productOptionArray.length) > 0) {
      this.customizationArray = cloneDeep(this.productOptionArray);
      this.$store.dispatch('product/fetchProductOption', {
        productOptionArray: cloneDeep(this.productOptionArray),
      });
      this.emitCurrentArray();
    }

    if (this.variantOptionArray.length !== 0) {
      this.variantOptionArray.forEach((variant) => {
        this.currentVariants.push(variant.id);
      });
      this.variantOptionValueArrayForDatatable = cloneDeep(
        this.variantOptionArray
      );
      this.emitCurrentVariants();
    }

    eventBus.$on('deleteMultiProductImage', (data) => {
      this.productImageArray.splice(data, 1);
    });

    eventBus.$on('resetInputValuesForVariant', () => {
      const clonnedVariants = cloneDeep(
        this.variantOptionValueArrayForDatatable
      );
      this.initiateVariants(clonnedVariants);
    });
    // $(document).on('wheel', 'input[type=number]', function (e) {
    //   $(this).blur();
    // });

    eventBus.$on('update-course-student', (student) => {
      this.productDetails.courseStudent = student;
    });

    this.paginatedAccessListData = this.paginatedAccessList;
    eventBus.$on('update-access-list', (lists) => {
      this.paginatedAccessListData = lists;
    });

    setTimeout(() => {
      this.isComponentMounted = true;
      const cusModal = document.getElementById(
        'add-product-customization-modal'
      );

      cusModal.addEventListener('show.bs.modal', () => {
        this.currentProductOption(cloneDeep(this.currentArray));
        return this.resetCustomizationErrors();
      });

      const varModal = document.getElementById('add-product-variant-modal');

      varModal.addEventListener('show.bs.modal', () => {
        this.currentProductVariant(cloneDeep(this.validVariantOptions));
        if (!this.validVariantOptions?.length) {
          return this.$refs.variation.addNewVariant({
            type: 'new',
            variant: null,
          });
        }
        return 0;
      });
    }, 1000);
  },

  beforeMount() {
    window.addEventListener('beforeunload', this.askBeforeLeave);
  },

  beforeUnmount() {
    window.removeEventListener('beforeunload', this.askBeforeLeave);
  },

  methods: {
    ...mapMutations('product', [
      'pushOptionArray',
      'resetOptionArray',
      'resetCustomizationErrors',
      'resetDeletedArray',
      'checkError',
      'initiateVariants',
      'updateVariant',
      'updateVariantValue',
      'addNewVariant',
      'deleteVariantByIndex',
      'clearAllVariantBuffers',
      'updateProductEditStatus',
      'emitCurrentArray',
      'addSubscriptionOption',
      'saveSubscriptionOption',
      'closeSubscriptionModal',
      'currentProductOption',
      'emitCurrentVariants',
      'currentProductVariant',
    ]),
    getPath(title) {
      const path = title
        .replace(/[.!`_"“=;<>*+?^$&%~@#{}()（）|[/\]\\]/g, '')
        .replace(/[^\w\s]+/u, '')
        .replace(/  +/g, ' ')
        .toLowerCase();
      this.seo.path = path.replaceAll(' ', '-');
      return this.seo.path;
    },
    chooseImage(e) {
      switch (this.sectionType) {
        case 'description':
          this.$refs.editor.chooseImage(e);
          break;
        case 'variant':
          this.updateCombinationImage(this.selectedDetailsIndex, e);
          break;
        case 'variantOption':
          eventBus.$emit('productVariantValueImageLocal', e);
          break;
        case 'customOption':
          eventBus.$emit(
            `fetchCustomizationImage-${this.optionIndex}-${this.optionValueIndex}`,
            e
          );
          break;
        default:
          this.productImageArray[this.selectedImageIndex] = e;
      }
    },
    async inputProductTitle(e) {
      // eslint-disable-next-line vue/valid-next-tick
      await this.$nextTick(() => {
        this.productDetails.title = e;
        this.seo.title = this.productDetails.title;
        if (!this.seoIndex) {
          this.seo.path = this.getPath(this.productDetails.title);
          // this.seo.description = `Shop for ${this.productDetails.title} at ${
          //   this.onlineStoreDomain || this.miniStoreDomain
          // }`;
        }
      });
    },
    inputProductDescription(e) {
      this.$nextTick(() => {
        this.productDetails.description = e.target.value;
      });
    },
    saveProductSaleChannel(type) {
      if (!this.productDetails.sale_channels.includes(type)) {
        this.productDetails.sale_channels.push(type);
      } else {
        const index = this.productDetails.sale_channels.findIndex(
          (selectedType) => selectedType === type
        );
        this.productDetails.sale_channels.splice(index, 1);
      }
      if (this.productDetails.sale_channels.length === 0)
        this.productDetails.status = 'draft';
    },
    checkSaleChannels() {
      if (this.productDetails.status === 'draft') {
        this.productDetails.sale_channels = [];
      }
    },
    SaveProductOption() {
      this.updateProductEditStatus(true);
      this.checkError({ type: this.optionType });
      const status = this.customizationsIsValid;
      if (status) {
        this.emitCurrentArray();
        const deletedId = this.deletedInputOptionsId.concat(
          this.deletedSharedInputOption
        );
        this.customizationArray.forEach((option, index) => {
          if (deletedId.includes(option.id)) {
            this.customizationArray.splice(index, 1);
          }
        });
        this.hasProductOption = this.inputOptions.length !== 0;
        Modal.getInstance(
          document.getElementById('add-product-customization-modal')
        ).hide();
      }
    },
    editCustomization() {
      this.sectionType = 'customOption';
      const array = [];
      this.inputOptions.forEach((custom) => array.push(custom));
      this.$store.dispatch('product/fetchProductOption', {
        productOptionArray: array,
      });
      this.resetDeletedArray();
      // new Modal(
      //   document.getElementById('add-product-customization-modal')
      // ).show();
      new Modal(document.getElementById('shared-option-modal')).show();
    },

    addNewProductCustomization() {
      this.sectionType = 'customOption';
      // new Modal(
      //   document.getElementById('add-product-customization-modal')
      // ).show();
      new Modal(document.getElementById('shared-option-modal')).show();
    },

    addNewProductVariant() {
      this.sectionType = 'variantOption';
      this.$nextTick(() => {
        new Modal(document.getElementById('add-product-variant-modal')).show();
      });
    },

    // subscription
    addSubscription() {
      this.addSubscriptionOption();
      new Modal(document.getElementById('add-subscription-modal')).show();
    },

    resetProductOption() {
      if (this.hasProductOption) {
        this.delete_all_customization_modal = new Modal(
          document.getElementById('delete-all-customization-modal')
        );
        this.delete_all_customization_modal.show();
      }
    },

    /** ************************************ Category Section ************************************ */
    addCategory(item) {
      this.$nextTick(() => {
        this.productDetails.categories.push(item);
      });
    },
    removeCategory(id) {
      if (!this.deletedCategoryId.includes(id)) this.deletedCategoryId.push(id);
      this.$nextTick(() => {
        this.productDetails.categories = this.productDetails.categories.filter(
          (item, i) => item.id !== id
        );
      });
    },

    /** ************************************* Product Image Section ******************************** */
    // Combine product main-image and image-collections into productImageArray
    setSeperateProductImagesIntoArray() {
      this.productImageArray = [];
      let imgArr = [];
      this.productDetails.imagePath === null
        ? (imgArr = [])
        : imgArr.push(this.productDetails.imagePath);

      if (this.productDetails.imageCollection === null) {
        this.productDetails.imageCollection = [];
      }
      this.productDetails.imageCollection.length !== 0
        ? (imgArr = [...imgArr, ...this.productDetails.imageCollection])
        : imgArr;

      if (imgArr.length !== 0) {
        this.productImageArray = [...imgArr];
      }
    },

    deleteImage() {
      this.updateProductEditStatus(true);
      this.productDetails.imagePath = '';
      eventBus.$emit('emitProductImage', this.productDetails.imagePath);
    },
    selectedCombinationIndex(index) {
      this.selectedDetailsIndex = index;
    },
    updateCombinationImage(index, e) {
      this.updateProductEditStatus(true);
      this.variationCombination[index].image_url = e;
      this.calculateCombination();
    },

    /** ******************************* Product Customizayion Reorder Section *************************** */
    reorderCustomization(e) {
      this.currentProductOption(e);
      this.customizationArray = e;
    },

    /** ******************************* Product Description Editor Section *************************** */
    updateProductDescription(value) {
      this.productDetails.description = value;
    },

    /** ****************************************** Product Saving Section **************************** */
    checkErrors() {
      this.submit = true;
      const product = cloneDeep(this.productDetails);
      let noVariantError = true;
      for (const [key, value] of Object.entries(this.variationCombination)) {
        if (parseFloat(value.price) >= parseFloat(value.compared_at_price)) {
          noVariantError = false;
          break;
        }
      }
      if (!product.title?.trim()?.length) {
        this.$toast.error('Save failed', 'Product Title cannot be empty');
      } else if (this.variationTotalCount > 200) {
        this.$toast.error(
          "You can't have more than 200 product variants.",
          'To save product, remove some options to keep variants under 200.'
        );
      } else if (this.isVariantNameEmpty) {
        this.$toast.error('Save failed', 'Variant option name cannot be empty');
      } else if (this.productDetails.price < 0) {
        this.$toast.error(
          'Save failed',
          'Product Price cannot be equal or less than 0'
        );
      } else if (this.hasVariant && this.computedVariantValueLength === 0) {
        this.updateVariant({
          index: this.computedVariantLength,
          type: 'errorMessage',
          value: 'Value cannot be empty!',
        });
        this.$toast.error('Save failed', 'Value cannot be empty');
      } else if (
        product.comparePrice !== null &&
        product.comparePrice !== '' &&
        (product.price || 0) >= product.comparePrice
      ) {
        this.$toast.error(
          'Save failed',
          'Product original price must bigger than selling price'
        );
      } else if (
        product.isPhysical &&
        !this.hasProductVariation &&
        product.weight === null
      ) {
        this.$toast.error(
          'Save failed',
          'Weight cannot be blank if the product is physical'
        );
      } else if (!noVariantError) {
        this.$toast.error(
          'Save failed',
          'Product variation price cannot be higher than original price'
        );
      } else if (
        product.status === 'active' &&
        product.sale_channels.length === 0
      ) {
        this.$toast.error(
          'Save failed',
          'Please select at least one sale channel for active product!'
        );
      } else if (
        product.type === 'course' &&
        product.coursePeriod?.duration !== 'lifetime' &&
        product.coursePeriod?.period < 1
      ) {
        this.$toast.error(
          'Save failed',
          `Course period cannot less than 1 ${[product.coursePeriod.duration]}`
        );
      } else if (
        product.type === 'course' &&
        !product.curriculum.some(
          (e) => e.isPublished && e.elements.some((ee) => ee.isPublished)
        )
      ) {
        this.$toast.error(
          'Save failed',
          'Must have at least one published module and lesson'
        );
      } else {
        this.checkError({ type: this.optionType });
        const status = this.customizationsIsValid;
        if (status || this.inputOptions.length === 0) {
          this.saveProduct();
        } else {
          this.$toast.error(
            'Save failed',
            'Fail To Save Product Customization'
          );
        }
      }
    },

    saveProduct() {
      this.loading = true;
      if (this.productImageArray.length !== 0) {
        this.productDetails.imagePath = this.productImageArray.shift();
        this.productDetails.imageCollection = this.productImageArray;
      } else {
        this.productDetails.imagePath =
          'https://cdn.hypershapes.com/assets/product-default-image.png';
        this.productDetails.imageCollection = [];
      }

      axios
        .post('/addProduct', {
          productTitle: this.productDetails.title,
          path: this.seo.path,
          type: this.type,
          details: this.productDetails,
          productOption: this.inputOptions,
          deletedInputId: this.deletedInputId,
          deletedInputOptionsId: this.deletedInputOptionsId,
          deletedSharedInputOption: this.deletedSharedInputOption,
          hasVariant: this.isPhysicalVariant,
          variantValue: this.isPhysicalVariant ? this.variantOptionArray : null,
          optionValue: this.isPhysicalVariant
            ? this.variationCombination
            : null,
          combinationVariantName: this.isPhysicalVariant
            ? this.combinationVariantName
            : null,
          deletedVariantId: this.deletedVariantId,
          deletedVariantValueId: this.deletedVariantValueId,
          deletedVariantIdForProductVariation:
            this.deletedVariantIdForProductVariation,
          categories: this.productDetails.categories,
          deletedCategories: this.deletedCategoryId,
          savedSubscriptionArray: this.savedSubscriptionArray,
          typeOfPayment: this.typeOfPayment,
          isCustomizationUpdated: this.isCustomizationUpdated,
          isVariantUpdated: this.isVariantUpdated,
          seo: this.seo,
        })
        .then((response) => {
          this.isSaved = true;
          this.$toast.success(
            'Success',
            `Successful ${this.type === 'new' ? 'Added' : 'Updated'} Product`
          );
          return this.$inertia.visit('/products');
        })
        .catch((error) => {
          console.log(error);
          if (!error?.response) return;
          const { data } = error.response;
          if (data) {
            this.$toast.warning(
              'Warning',
              `${data.message} Check the required fields.`
            );
          }
        })
        .finally(() => {
          this.loading = false;
          this.disabled = false;
        });
    },
    deleteProduct() {
      axios
        .post('/deleteProduct', {
          id: this.type,
        })
        .then((response) => {
          if (response.data === 'fail')
            return this.$toast.error(
              'Product is not allowed to delete',
              'Kindly unsubscribe all the existing subscription related to this product in order to proceed deletion.'
            );
          this.$inertia.visit('/products');
        });
    },
    closeDeleteModal() {
      Modal.getInstance(document.getElementById(this.modalId)).hide();
    },

    /** **************************************** Product Combination Section ******************************* */
    calculateCombination() {
      this.updateProductEditStatus(true);
      this.tempCombination = cloneDeep(this.variationCombination);
      this.variationCombination = {};
      this.combinationVariantName = {};
      const arr = this.variantOptionArray;
      if (parseInt(this.isVariationEmpty) === 0) {
        this.variationCombination = {};
        return;
      }
      for (let i = 0; i < this.arrayLength(0); i++) {
        const firstItem = arr[0].valueArray[i].variant_value;
        if (!arr[1]) this.setCombinationObject(firstItem);
        for (let j = 0; j < this.arrayLength(1); j++) {
          const secondItem = arr[1].valueArray[j].variant_value;
          if (!arr[2]) this.setCombinationObject(firstItem, secondItem);
          for (let x = 0; x < this.arrayLength(2); x++) {
            const thirdItem = arr[2].valueArray[x].variant_value;
            if (!arr[3])
              this.setCombinationObject(firstItem, secondItem, thirdItem);
            for (let y = 0; y < this.arrayLength(3); y++) {
              const forthItem = arr[3].valueArray[y].variant_value;
              if (!arr[4])
                this.setCombinationObject(
                  firstItem,
                  secondItem,
                  thirdItem,
                  forthItem
                );
              for (let z = 0; z < this.arrayLength(4); z++) {
                const fifthItem = arr[4].valueArray[z].variant_value;
                this.setCombinationObject(
                  firstItem,
                  secondItem,
                  thirdItem,
                  forthItem,
                  fifthItem
                );
              }
            }
          }
        }
      }
    },

    // Helper function for calculateCombination
    arrayLength(index) {
      if (!this.variantOptionArray[index]) {
        return 0;
      }
      return this.variantOptionArray[index]?.valueArray?.length;
    },

    // Helper function for calculateCombination
    objectKey(arr) {
      return arr.join(' / ').trim();
    },

    // Helper function for calculateCOmbination
    setCombinationObject(value1, value2, value3, value4, value5) {
      const key = this.objectKey(
        [value1, value2, value3, value4, value5].filter((e) => e)
      );
      if (!Object.prototype.hasOwnProperty.call(this.tempCombination, key)) {
        this.variationCombination[key] = { ...this.defaultObjItem };
      } else {
        this.variationCombination[key] = { ...this.tempCombination[key] };
      }
      this.combinationVariantName[key] = {
        option_1: value1 !== undefined ? value1 : null,
        option_2: value2 !== undefined ? value2 : null,
        option_3: value3 !== undefined ? value3 : null,
        option_4: value4 !== undefined ? value4 : null,
        option_5: value5 !== undefined ? value5 : null,
      };
    },

    // Triggered when replacing the order of variationCombination
    // rename the combination-name(key) for variationCombination without mutating the exisitng value
    swapVariant(options) {
      this.$nextTick(() => {
        this.variantOptionValueArrayForDatatable = options;
        this.initiateVariants(options);
        const optionArray = options.map((e) => {
          return e.valueArray.map((child) => child.variant_value);
        });
        const combinationNames = this.getCombination(optionArray);
        const tempCombination = Object.keys(this.variationCombination);
        const resultObj = {};

        combinationNames.forEach((name) => {
          const nameArray = name.split(' / ');
          let selectedIndex = null;

          for (let i = 0; i < tempCombination.length; i++) {
            const tempItemArray = tempCombination[i].split(' / ');
            let isArrayEqual = true;
            tempItemArray.forEach((temp) => {
              if (!nameArray.includes(temp)) {
                isArrayEqual = false;
              }
            });
            if (isArrayEqual) {
              selectedIndex = i;
              break;
            }
          }
          resultObj[combinationName] =
            this.variationCombination[tempCombination[selectedIndex]];
        });
        this.variationCombination = resultObj;
        this.calculateCombination();
      });
    },

    // Recursive function to get combination names from variant value array
    // E.g. [["a", "b", "c"], ["a", "c", "b"], ...]
    getCombination(arr, pre) {
      pre = pre || '';
      if (!arr.length) return pre;
      let slashSeperator = ' / ';
      if (arr.length === 1) {
        slashSeperator = '';
      }
      const combinationName = arr[0].reduce((name, value) => {
        return name.concat(
          this.getCombination(arr.slice(1), pre + value + slashSeperator)
        );
      }, []);
      return combinationName;
    },

    /** **************************************** Product Variants Section *********************************** */
    // validate for variation inputs before calculate for product combination
    checkVariantError() {
      this.updateProductEditStatus(true);
      let noError = true;
      // const usedVariantNames = [];
      // const usedValueNames = [];

      this.variantOptionArray.forEach((variant, index) => {
        // if (usedVariantNames.length === 0) {
        //   usedVariantNames.push(variant.name.toLowerCase());
        // } else {
        //   if (
        //     usedVariantNames.includes(variant.name.toLowerCase().trim()) &&
        //     variant.name !== ''
        //   ) {
        //     noError = false;
        //     this.updateVariant({
        //       index,
        //       type: 'errorMessage',
        //       value: 'Duplicate name detected',
        //     });
        //   }
        //   usedVariantNames.push(variant.name.toLowerCase());
        // }

        if (variant.name?.trim() === '') {
          noError = false;
          this.updateVariant({
            index,
            type: 'errorMessage',
            value: 'Display name is required',
          });
        }

        if (variant.valueArray.length === 0) {
          this.updateVariant({
            index,
            type: 'errorMessage',
            value: 'Value is required',
          });
          noError = false;
        } else {
          variant.valueArray.forEach((value, valueIndex) => {
            if (value.variant_value?.trim() === '') {
              this.updateVariantValue({
                index,
                valueIndex,
                type: 'errorMessage',
                value: 'Value is required',
              });
              noError = false;
            } else {
              this.updateVariantValue({
                index,
                valueIndex,
                type: 'errorMessage',
                value: '',
              });
              // if (usedValueNames.length === 0) {
              //   usedValueNames.push(value.variant_value.toLowerCase());
              // } else {
              //   if (
              //     usedValueNames.includes(
              //       value.variant_value.toLowerCase().trim()
              //     )
              //   ) {
              //     noError = false;
              //     this.updateVariantValue({
              //       index,
              //       valueIndex,
              //       type: 'errorMessage',
              //       value: 'Duplicate value detected',
              //     });
              //   }
              //   usedValueNames.push(value.variant_value.toLowerCase());
              // }
            }
          });
        }
      });

      if (noError !== false) {
        this.hasProductVariation = true;
        this.variantOptionValueArrayForDatatable = cloneDeep(
          this.variantOptionArray
        );
        this.deletedVariantIdBuffer.forEach((id) => {
          if (!this.deletedVariantId.includes(id)) {
            this.deletedVariantId.push(id);
          }
        });
        this.deletedVariantValueIdBuffer.forEach((id) => {
          if (!this.deletedVariantValueId.includes(id)) {
            this.deletedVariantValueId.push(id);
          }
        });
        this.deletedVariantIdForProductVariationBuffer.forEach((id) => {
          if (!this.deletedVariantIdForProductVariation.includes(id)) {
            this.deletedVariantIdForProductVariation.push(id);
          }
        });

        this.calculateCombination();
        for (const key of Object.keys(this.variationCombination)) {
          if (this.variationCombination[key].image_url === null) {
            this.variationCombination[key].image_url =
              this.productImageArray[0] || null;
          }
        }
        this.emitCurrentVariants();
        Modal.getInstance(
          document.getElementById('add-product-variant-modal')
        ).hide();
      }

      this.clearAllVariantBuffers();
    },

    setDefaultValue() {
      const product = cloneDeep(this.product);
      this.hasProductVariation = product.hasVariant;
      this.productDetails = {
        id: product.id,
        title: product.productTitle,
        status: product.status ?? this.productDetails.status,
        imagePath: product.productImagePath,
        imageCollection: product.productImageCollection,
        description: product.productDescription,
        price:
          product.productPrice !== null
            ? parseFloat(product.productPrice)
            : null,
        comparePrice:
          product.productComparePrice !== null
            ? parseFloat(product.productComparePrice)
            : null,
        isPhysical:
          product.productComparePrice !== undefined
            ? product.type === 'physical'
            : true,
        SKU: product.SKU !== undefined ? product.SKU : null,
        quantity: product.quantity !== undefined ? product.quantity : 0,
        is_selling:
          product.is_selling !== undefined ? Boolean(product.is_selling) : true,
        weight: product.weight !== undefined ? parseFloat(product.weight) : 0.0,
        isTaxable:
          product.isTaxable !== undefined ? Boolean(product.isTaxable) : false,
        payment_type:
          product.payment_type !== undefined
            ? product.payment_type
            : 'subscription_and_otp',
        categories: cloneDeep(this.productCategory),
        sale_channels: this.productSaleChannels,
        height: this.product.height ?? 0.0,
        length: this.product.length ?? 0.0,
        width: this.product.width ?? 0.0,
        curriculum: this.productCourse?.data ?? [],
        coursePeriod: JSON.parse(this.product.access_period ?? '{}'),
        courseStudent: this.paginatedCourseStudent ?? [],
        asset_url: this.product.asset_url ?? '',
        classification_code: this.product.classification_code ?? '',
        unit_type: this.product.unit_type ?? '',
      };
      this.seo.title = product.meta_title || `${this.product.productTitle}`;
      this.seo.description = this.product.meta_description;
      // this.seo.description =
      //   this.product.meta_description ||
      //   `Shop for ${this.product?.productTitle || '{{product title}}'} at ${
      //     this.onlineStoreDomain || this.miniStoreDomain
      //   }`;
      this.seo.path =
        this.product.path || this.getPath(this.product.productTitle);
      this.seoIndex += 1;
      if (this.type !== 'new') {
        this.variantArray !== '[]'
          ? this.initiateVariants([...cloneDeep(this.variantArray)])
          : this.initiateVariants([]);

        this.variationCombination = { ...cloneDeep(this.variantCombination) };
        this.combinationVariantName = { ...cloneDeep(this.variantNameArray) };
      }
    },

    askBeforeLeave(e) {
      if (this.isProductEdited && this.isSaved !== true) {
        e.preventDefault();
      }
    },

    inputInventory(inventory) {
      // this.updateCombinationValues(
      //   inventory.type,
      //   inventory.value,
      //   this.productDetails[inventory.type]
      // );
      this.productDetails[inventory.type] = inventory.value;
    },

    updateCombinationValues(type, newValue, oldValue) {
      const parsedType = type === 'SKU' ? 'sku' : type;
      for (const [key, value] of Object.entries(this.variationCombination)) {
        if (value[parsedType] === oldValue || value[parsedType] === null) {
          this.$set(value, parsedType, newValue);
          // value[parsedType] = newValue;
        }
      }
      this.calculateCombination();
    },

    toggleTab(tab) {
      this.selectedTab = tab;
    },
  },
};
</script>

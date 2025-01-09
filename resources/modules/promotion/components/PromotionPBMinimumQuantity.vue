<template>
  <BaseCard
    has-header
    title="Customer buys"
    @keydown="clearError($event.target.name)"
  >
    <!-- <div class="row">
      <label>Customers must add the quantity of items specified below to their cart</label>
    </div> -->
    <BaseFormGroup
      label="Minimum quantity"
      :error-message="
        hasError('minimumQuantity') ? errors['minimumQuantity'][0] : ''
      "
      col="6"
    >
      <BaseFormInput
        id="promotion-minimum-quantity"
        v-model="exampleData"
        type="number"
        name="minimumQuantity"
        :model-value="setting.minimumQuantity"
        @keydown="validateInteger($event)"
        @input="updateSetting('minimumQuantity', $event.target.value)"
      />
    </BaseFormGroup>
    <BaseFormGroup
      col="6"
      label="Any items from"
    >
      <BaseFormSelect
        id="promotion-product-discount-type"
        :model-value="setting.productDiscountType"
        @input="updateSetting('productDiscountType', $event.target.value)"
      >
        <option
          v-for="discountType in productDiscountTypeOptions"
          :key="discountType.label"
          :value="discountType.value"
        >
          {{ discountType.label }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <div v-if="setting.productDiscountType != 'all-product'">
      <BaseFormGroup>
        <BaseFormInput
          id="promotion-search-product"
          v-model="searchInput"
          type="search"
          :data-target="
            setting.productDiscountType == 'specific-product'
              ? '#browseProductModal'
              : '#browseCategoryModal'
          "
          :placeholder="
            setting.productDiscountType == 'specific-category'
              ? 'Search categories'
              : 'Search products'
          "
          @keyup="setFocus"
        >
          <template #prepend>
            <i class="fas fa-search gray_icon" />
          </template>
          <template #append>
            <BaseButton
              size="md"
              type="link"
              @click="setFocus"
            >
              Browse
            </BaseButton>
          </template>
        </BaseFormInput>
      </BaseFormGroup>

      <BaseDatatable
        v-if="
          setting.productDiscountType == 'specific-product' &&
            setting.selectedProduct.length > 0
        "
        no-header
        no-search
        no-action
        :table-headers="productTableHeaders"
        :table-datas="
          setting.selectedProduct.map((m, index) => ({ ...m, index }))
        "
      >
        <template #cell-product_image="{ row: { product } }">
          <img
            :src="product.productImagePath || productDefaultImages"
            style="width: 4rem"
          >
        </template>
        <template
          #cell-product_title="{ row: { product, combinationVariation } }"
        >
          {{ product.productTitle }}
          <p v-if="product.hasVariant">
            {{ numberOfSelectVariant(combinationVariation) }}
            of {{ combinationVariation.length }} variants selected
          </p>
        </template>
        <template #cell-remove="{ row }">
          <BaseButton
            type="close"
            size="sm"
            aria-label="Close"
            @click="removeProduct(row)"
          />
        </template>
      </BaseDatatable>

      <BaseDatatable
        v-if="
          setting.productDiscountType == 'specific-category' &&
            setting.selectedCategory.length > 0
        "
        no-header
        no-search
        no-action
        :table-headers="categoryTableHeaders"
        :table-datas="
          setting.selectedCategory.map((m, index) => ({ ...m, index }))
        "
      >
        <template #cell-category_name="{ row: { name, products } }">
          {{ name }} <br>
          {{ products.length }} products
        </template>
        <template #cell-remove="{ row }">
          <BaseButton
            type="close"
            size="sm"
            aria-label="Close"
            @click="removeCategory(row)"
          />
        </template>
      </BaseDatatable>
    </div>
  </BaseCard>
  <PromotionProductModal
    modal-id="browseProductModal"
    :modal-title="
      setting.selectedProduct.length > 0 ? 'Edit products' : 'Add products'
    "
    :button-title="setting.selectedProduct.length > 0 ? 'Done' : 'Add'"
    @selectedProduct="updateSelectedProduct"
  />
  <PromotionCategoryModal
    modal-id="browseCategoryModal"
    :modal-title="
      setting.selectedProduct.length > 0 ? 'Edit categories' : 'Add categories'
    "
    :button-title="setting.selectedProduct.length > 0 ? 'Done' : 'Add'"
    @selectedCategories="updateSelectedCategory"
  />
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import PromotionProductModal from '@promotion/components/PromotionProductModal.vue';
import PromotionCategoryModal from '@promotion/components/PromotionCategoryModal.vue';
import { mapState, mapMutations } from 'vuex';
import productDefaultImages from '@shared/assets/media/product-default-image.png';

export default {
  name: 'PBMinimumQuantity',
  components: {
    PromotionProductModal,
    PromotionCategoryModal,
  },
  mixins: [PromotionCentraliseMixins],
  data() {
    return {
      productDefaultImages,
      searchInput: '',
      modal: null,
      productDiscountTypeOptions: [
        { label: 'All products', value: 'all-product' },
        { label: 'Specific products', value: 'specific-product' },
        { label: 'Specific categories', value: 'specific-category' },
      ],
      productTableHeaders: [
        { name: 'Images', key: 'product_image', custom: true },
        { name: 'Title', key: 'product_title', custom: true },
        { name: 'Action', key: 'remove', custom: true },
      ],
      categoryTableHeaders: [
        { name: 'Title', key: 'category_name', custom: true },
        { name: 'Action', key: 'remove', custom: true },
      ],
    };
  },
  computed: {
    ...mapState('promotions', ['allProducts']),
  },
  methods: {
    ...mapMutations('promotions', [
      'deleteSelectedProduct',
      'updateCheckKey',
      'deleteSelectedCategory',
    ]),
    setFocus() {
      if (this.setting.productDiscountType === 'specific-category') {
        this.triggerModal('browseCategoryModal');
      } else if (this.setting.productDiscountType === 'specific-product') {
        this.triggerModal('browseProductModal');
      }
    },
    updateSelectedProduct(value) {
      this.updateSetting('selectedProduct', [...value]);
      this.modal.hide();
    },
    updateSelectedCategory(value) {
      this.updateSetting('selectedCategory', [...value]);
      this.modal.hide();
    },
    removeProduct(product) {
      this.deleteSelectedProduct({ index: product.index, product });
    },
    removeCategory(category) {
      this.deleteSelectedCategory({ index: category.index, category });
    },
    numberOfSelectVariant(combination) {
      return combination.filter((item) => item.value.isChecked).length;
    },
    totalProductInCategory(category) {
      return Object.keys(category).length > 0 ? category.products.length : 0;
    },
  },
};
</script>

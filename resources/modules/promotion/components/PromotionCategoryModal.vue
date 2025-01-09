<template>
  <BaseModal
    :title="modalTitle"
    :modal-id="modalId"
  >
    <BaseDatatable
      title="categories"
      no-action
      :table-headers="categoryTableHeaders"
      :table-datas="filteredItems"
    >
      <template
        #cell-category_checkbox="{ row: { isDisabled, isChecked, index } }"
      >
        <BaseFormGroup>
          <BaseFormCheckBox
            :id="`promotion-category-select-${index}`"
            :is-single="false"
            :value="true"
            :model-value="isChecked"
            :disabled="isDisabled"
            @click="addProduct(isDisabled, isChecked, index)"
          />
        </BaseFormGroup>
      </template>
      <template #cell-available_product="{ row: { products } }">
        {{ products.length > 0 ? `${products.length} products` : '' }}
      </template>
    </BaseDatatable>

    <BaseCard
      v-if="allCategories.length === 0"
      has-header
      title="No category yet..."
    >
      <BaseButton
        type="link"
        href="/product/category"
      >
        Click here to Create Category
      </BaseButton>
    </BaseCard>

    <template #footer>
      <BaseButton
        data-bs-dismiss="modal"
        @click="save"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import { mapMutations, mapState } from 'vuex';
import promotionAPI from '@promotion/api/promotionAPI.js';

export default {
  name: 'BrowseCategoryModal',
  mixins: [PromotionCentraliseMixins],
  props: ['modalId', 'modalTitle', 'buttonTitle'],
  data() {
    return {
      searchInput: '',
      selectedProduct: [],
      categoryTableHeaders: [
        { name: '', key: 'category_checkbox', custom: true },
        { name: 'Category', key: 'name' },
        { name: 'Available', key: 'available_product', custom: true },
      ],
    };
  },
  computed: {
    ...mapState('promotions', ['allCategories']),
    filteredItems() {
      return this.allCategories.map((m, index) => ({ ...m, index }));
    },
  },
  mounted() {
    this.loadAllCategory();
  },
  methods: {
    ...mapMutations('promotions', [
      'setAllCategories',
      'updateSelectedCategory',
    ]),
    closeModal() {
      document.getElementById(`${this.modalId}-close-button`)?.click();
    },
    save() {
      const isCheckedCategory = this.allCategories.filter((item) => {
        return item.isChecked;
      });
      this.$emit('selectedCategories', isCheckedCategory);
    },
    loadAllCategory() {
      promotionAPI
        .getCategories()
        .then((response) => {
          // this.products = response.data;
          this.setAllCategories(response.data);
          if (this.setting.selectedCategory.length > 0) {
            this.setting.selectedCategory.forEach((category) => {
              const indexOf = this.allCategories
                .map((item) => item.id)
                .indexOf(category.id);
              if (indexOf !== -1) {
                this.updateSelectedCategory({
                  value: true,
                  index: indexOf,
                  type: 'isChecked',
                });
              }
            });
          }
        })
        .catch((error) => console.log(error));
    },
    addProduct(isDisabled, isChecked, index) {
      if (isDisabled) return;
      this.updateSelectedCategory({
        type: 'isChecked',
        value: !isChecked,
        index,
      });
    },
    updateSearchInput(value) {
      this.searchInput = value;
    },
  },
};
</script>

<style lang="scss" scoped></style>

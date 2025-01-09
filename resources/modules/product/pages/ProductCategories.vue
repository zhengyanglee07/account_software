<template>
  <BaseDatatable
    no-hover
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="datas"
    :empty-state="emptyState"
    title="categories"
    @delete="removeCategory"
  >
    <template #action-button>
      <!-- <BaseButton
        type="secondary"
        has-edit-icon
        data-bs-toggle="modal"
        data-bs-target="#edit-priority-modal"
      >
        Edit Priority
      </BaseButton> -->
      <BaseButton
        has-add-icon
        @click="toggleCategoryModal('add', null)"
      >
        Add Product Category
      </BaseButton>
    </template>

    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        @click="toggleCategoryModal('edit', item)"
      />
    </template>
  </BaseDatatable>

  <AddProductCategoryModal
    :modal-id="actionModalId"
    :type="computedActionType"
    :data="category"
  />

  <EditPriorityModal
    :items="categoriesData"
    type="category"
    @updatePriority="updateCategory"
  />
</template>

<script>
/* eslint-disable no-unused-expressions */
import AddProductCategoryModal from '@product/components/AddProductCategoryModal.vue';
import EditPriorityModal from '@product/components/EditPriorityModal.vue';

import { Modal } from 'bootstrap';
import cloneDeep from 'lodash/cloneDeep';

export default {
  components: {
    AddProductCategoryModal,
    EditPriorityModal,
  },
  props: {
    categories: {
      type: [Object, Array],
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        /**
         * @param name : column header title
         * @param value : datas column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : text align, default => left
         */
        { name: 'Priority', key: 'priority', order: 1, width: '10%' },
        { name: 'Category Name', key: 'productCategory', order: 0 },
        { name: 'path', key: 'path', order: 0 },
        { name: 'Products', key: 'productCounts', order: 0 },
      ],
      modalId: 'product-category-delete-modal',
      actionModalId: 'configure-product-category-modal',
      selectedId: '',
      actionType: 'add',
      category: null,
      emptyState: {
        title: 'product category',
        description: 'product categories',
      },
      categoriesData: [],
    };
  },
  computed: {
    datas() {
      return this.categoriesData?.map((item, index) => ({
        id: item.id,
        priority: index + 1,
        productCategory: item.name,
        productCounts: item.count,
        path: `/${item.path}`,
      }));
    },
    computedActionType() {
      return this.actionType;
    },
  },

  mounted() {
    this.categoriesData = cloneDeep(this.categories);
  },
  methods: {
    toggleCategoryModal(action, data) {
      this.actionType = action;
      action === 'add'
        ? (this.category = null)
        : (this.category = { name: data.productCategory, id: data.id });

      this.configure_product_category_modal = new Modal(
        document.getElementById('configure-product-category-modal')
      );
      this.configure_product_category_modal.show();
    },
    removeCategory(id) {
      axios.delete(`/product/category/delete/${id}`).then(({ data }) => {
        if (data.status.includes('Error'))
          this.$toast.error('Error', 'Something went wrong!');
        else {
          this.$toast.success('Success', 'Category Deleted.');
          this.$inertia.visit('/product/category');
        }
      });
    },
    updateCategory(e) {
      this.categoriesData = e;
    },
  },
};
</script>

<style lang="scss" scoped>
*:not(i) {
  font-family: $base-font-family;
  color: $base-font-color;
}
</style>

<template>
  <div class="table-container">
    <BaseDatatable
      no-action
      :table-headers="tableHeaders"
      :table-datas="inventory"
      title="product"
    >
      <template #cell-product_image="{ row: { product_image } }">
        <img
          :src="product_image"
          style="object-fit: scale-down; width: 45px; height: 45px"
        >
      </template>

      <template #cell-available_stock="{ row: scopedData }">
        {{ scopedData.available_stock }}
        <span
          v-if="
            scopedData.type === 'Add' &&
              scopedData.available_stock !==
              scopedData.available_stock + scopedData.modify_quantity
          "
        >
          >
          {{ scopedData.available_stock + scopedData.modify_quantity }}
        </span>
        <span
          v-if="
            scopedData.type === 'Set' &&
              scopedData.available_stock !== scopedData.modify_quantity
          "
        >
          > {{ scopedData.modify_quantity }}
        </span>
      </template>

      <template #cell-quantity="{ row: scopedData }">
        <div class="d-flex justify-content-end">
          <div class="d-flex align-items-center">
            <div class="mt-3">
              <BaseFormRadio
                id="inline-checkbox-1"
                v-model="scopedData.type"
                class="p-1"
                value="Add"
                inline
              >
                Add
              </BaseFormRadio>
              <BaseFormRadio
                id="inline-checkbox-2"
                v-model="scopedData.type"
                class="p-1"
                value="Set"
                inline
              >
                Set
              </BaseFormRadio>
            </div>
            <div class="d-flex flex-shrink-0">
              <BaseFormInput
                v-model.number="scopedData.modify_quantity"
                oninput="(value='0');"
                type="number"
                min="0"
              />
              <BaseButton
                style="margin-left: 5px"
                @click="saveInventory(scopedData)"
              >
                Save
              </BaseButton>
            </div>
          </div>
        </div>
      </template>
    </BaseDatatable>
  </div>
</template>

<script>
export default {
  name: 'ProductInventoryPage',

  props: {
    productArray: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      modalId: 'product-page-delete-modal',
      inventory: [],
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : datas column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : text align, default => left
         */
        { name: 'Image', key: 'product_image', sortable: false, custom: true },
        {
          name: 'Product Title',
          key: 'product_title',
          order: 0,
        },
        { name: 'variant', key: 'variant', order: 0 },
        { name: 'SKU', key: 'SKU', order: 0 },
        {
          name: 'When sold out',
          key: 'continue_sold',
          order: 0,
        },
        { name: 'available', key: 'available_stock', custom: true },
        { name: 'edit quantity', key: 'quantity', custom: true },
      ],
    };
  },
  mounted() {
    this.inventory = this.productArray;
  },
  methods: {
    saveInventory(modifyItem) {
      axios
        .post('/save/product/inventory', {
          modifyItem,
        })
        .then((response) => {
          this.$toast.success('Success', 'Successful update inventory');
          this.$inertia.visit('/product/inventory');
        })
        .catch((error) => {
          this.$toast.error('Error', 'Fail to update inventory');
        });
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

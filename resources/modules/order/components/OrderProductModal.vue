<template>
  <BaseModal
    :title="modalTitle"
    :modal-id="modalId"
  >
    <div class="d-flex align-items-center position-relative my-1">
      <span class="svg-icon svg-icon-1 position-absolute ms-6">
        <i class="fa-solid fa-magnifying-glass" />
      </span>
      <input
        v-model="search"
        type="text"
        class="form-control form-control-solid w-250px ps-15"
        placeholder="Search Products"
      >
    </div>
    <BaseDatatable
      title="products"
      no-header
      no-search
      no-sorting
      no-action
      :table-headers="productTableHeaders"
      :table-datas="filteredProductList"
    >
      <template #cell-productCheckbox="{ row }">
        <BaseFormGroup>
          <BaseFormCheckBox
            :id="`promotion-product-select-${row.index}`"
            :class="row.isVariant ? 'ms-5' : ''"
            :value="true"
            :model-value="row.isChecked"
            :disabled="row.isDisabled"
            @click="selectProduct(row)"
          />
        </BaseFormGroup>
      </template>
      <template #cell-productImage="{ row }">
        <img
          :src="getProductImages(row)"
          style="width: 4rem"
        >
      </template>
      <template
        #cell-productTitle="{ row: { productTitle, isVariant, variant } }"
      >
        {{ isVariant ? variant.title : productTitle }}
      </template>
      <template
        #cell-productStock="{
          row: { is_selling, quantity, isVariant, hasVariant },
        }"
      >
        <div v-if="!hasVariant">
          {{ is_selling && quantity <= 0 ? '∞' : quantity }}
        </div>
        <div v-else-if="isVariant">
          {{ is_selling && quantity <= 0 ? 'of ∞' : quantity }}
        </div>
      </template>
      <template
        #cell-productPrice="{
          row: { productPrice, isVariant, variant, hasVariant },
        }"
      >
        <div v-if="!hasVariant">
          {{ currency }} {{ productPrice }}
        </div>
        <div v-else-if="isVariant">
          {{ currency }} {{ variant.price }}
        </div>
      </template>
    </BaseDatatable>

    <BaseCard
      v-if="allProducts.length === 0"
      has-header
      title="No product yet..."
    >
      <BaseButton
        type="link"
        href="/product/create"
      >
        Click here to Create Product
      </BaseButton>
    </BaseCard>

    <template #footer>
      <BaseButton
        data-bs-dismiss="modal"
        @click="saveSelectedProduct"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
  modalId: { type: String, default: 'browseProductModal' },
  modalTitle: { type: String, default: 'Add Products' },
  searchInput: { type: String, default: '' },
  currency: { type: String, default: 'RM' },
  allProducts: { type: Array, default: () => [] },
  selectedProducts: { type: Array, default: () => [] },
  removedProduct: { type: Object, default: () => {} },
});

const emits = defineEmits(['selectedProduct']);

const productTableHeaders = [
  { name: '', key: 'productCheckbox', custom: true },
  { name: 'Images', key: 'productImage', custom: true },
  { name: 'Title', key: 'productTitle', custom: true },
  { name: 'Stock', key: 'productStock', custom: true },
  { name: 'Price', key: 'productPrice', custom: true },
];

const search = ref(null);

watch(
  () => props.searchInput,
  (val) => {
    search.value = val;
  }
);

const productList = ref([]);

const filteredProductList = computed(() =>
  productList.value.filter(
    (e) =>
      !search.value ||
      e.productTitle.toLowerCase().includes(search.value.toLowerCase())
  )
);

const setProductList = () => {
  const products = props.allProducts.reduce(
    (list, item) => [
      ...list,
      { ...item },
      ...item.variant_details.map((m) => ({
        ...item,
        variant: m,
        isVariant: true,
        quantity: m.quantity,
      })),
    ],
    []
  );

  productList.value = products.reverse().map((m, index) => ({
    ...m,
    productId: m.id,
    id: index,
    isChecked: props.selectedProducts.some((e) =>
      m.hasVariant
        ? e.variant_combination_id === m.variant?.reference_key
        : e.users_product_id === m.id
    ),
  }));
};
const getProductImages = ({ isVariant, variant, productImagePath }) => {
  const image = isVariant ? variant.image_url : productImagePath;
  return !image || image === '/images/product-default-image.png'
    ? 'https://cdn.hypershapes.com/assets/product-default-image.png'
    : image;
};

const selectProduct = ({ productId, isChecked, isVariant, variant }) => {
  const groupByProductId = productList.value.reduce((group, product) => {
    const { productId: id } = product;
    group[id] = group[id] ?? [];
    group[id].push(product);
    return group;
  }, {});

  const products = [];
  Object.entries(groupByProductId).forEach(([id, group]) => {
    let tempProducts = [];
    if (parseInt(id) !== productId) {
      products.push(...group);
      return;
    }
    if (!isVariant)
      tempProducts = group.map((m) => ({
        ...m,
        isChecked: !isChecked,
      }));
    else {
      const checkedVariant = group.filter((e) => {
        if (e.variant?.id === variant.id) e.isChecked = !isChecked;
        return e.isVariant && e.isChecked;
      });
      const isAllVariantChecked = checkedVariant.length === group.length - 1;
      const isAllVariantUnchecked = checkedVariant.length === 0;
      const isAllVariantSame = isAllVariantChecked || isAllVariantUnchecked;
      tempProducts = group.map((m) => {
        if (isAllVariantSame || !m.isVariant || m.variant?.id === variant.id) {
          m.isChecked = !m.isVariant ? isAllVariantChecked : !isChecked;
        }
        return m;
      });
    }
    products.push(...tempProducts);
  });
  productList.value = products;
};

const saveSelectedProduct = () => {
  const availableProduct = productList.value.filter(
    (e) => e.isChecked && (!e.hasVariant || e.isVariant)
  );
  emits('selectedProduct', availableProduct);
};

watch(
  () => props.removedProduct,
  ({
    variant,
    users_product_id: productId,
    variant_combination_id: variantRef,
  }) => {
    productList.value.forEach((product) => {
      if (
        product.isVariant
          ? product.variant?.reference_key === variantRef
          : product.productId === productId
      )
        product.isChecked = false;
    });
    saveSelectedProduct();
  }
);

onMounted(() => {
  setProductList();
});
</script>

<style scoped lang="scss"></style>

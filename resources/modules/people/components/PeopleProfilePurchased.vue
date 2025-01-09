<template>
  <BaseDatatable
    no-header
    no-action
    custom-description="This contact doesn't purchase anything yet"
    :table-headers="tableHeaders"
    :table-datas="filteredProduct"
  >
    <template #cell-image_url="{ row: { image_url: imageUrl } }">
      <img
        class="p-2 rounded"
        :src="imageUrl"
      >
    </template>
  </BaseDatatable>
  <!-- <table class="people-profile-table__table m-0 w-100">
    <tr class="h-five">
      <th>Product Image</th>
      <th>Product Title</th>
      <th>Variation</th>
      <th
        @mouseover="showSortingButton"
        @mouseleave="hideSortingButton"
      >
        <p
          class="m-0 p-0"
          @click="sortingWithAscd = !sortingWithAscd"
        >
          Purchased<i
            :class="`fas fa-arrow-${!sortingWithAscd ? 'up' : 'down'}`"
          />
        </p>
      </th>
    </tr>
    <tr
      v-for="(product, index) in filteredProduct"
      :key="index"
      class="p-two"
    >
      <td>
        <img
          class="img-container"
          :src="
            product.image_url ||
              'https://cdn.hypershapes.com/assets/product-default-image.png'
          "
        >
      </td>
      <td>{{ product.product_name }}</td>
      <td v-if="product.variant !== null">
        {{ variantDetail(product.variant) }}
      </td>
      <td v-else>
        -----
      </td>
      <td>{{ product.view_count }}</td>
    </tr>
  </table> -->
</template>

<script>
export default {
  props: {
    orders: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        { name: 'Product Image', key: 'image_url', custom: true },
        { name: 'Product Title', key: 'product_name' },
        // { name: 'Variation', key: 'variation' },
        { name: 'Quantity', key: 'view_count' },
      ],
    };
  },

  computed: {
    filteredProduct() {
      const normalProduct = this.orders.filter(
        (order) => !order.variant_combination_id
      );
      const variantProduct = this.orders.filter(
        (order) => order.variant_combination_id
      );
      const nameArray = [];
      const productArray = [];
      normalProduct.forEach((product) => {
        if (!nameArray.includes(product.product_name)) {
          nameArray.push(product.product_name);
          product.view_count = product.quantity;
          productArray.push(product);
        } else {
          productArray[nameArray.indexOf(product.product_name)].view_count +=
            product.quantity;
        }
      });
      variantProduct.forEach((product) => {
        if (
          !nameArray.includes(
            `${product.product_name}-${product.variant_combination_id}`
          )
        ) {
          nameArray.push(
            `${product.product_name}-${product.variant_combination_id}`
          );
          product.view_count = product.quantity;
          productArray.push(product);
        } else {
          productArray[
            nameArray.indexOf(
              `${product.product_name}-${product.variant_combination_id}`
            )
          ].view_count += product.quantity;
        }
      });
      return productArray.sort((a, b) =>
        !this.sortingWithAscd
          ? b.view_count - a.view_count
          : a.view_count - b.view_count
      );
    },
  },
  methods: {
    variantDetail(param) {
      const variants = JSON.parse(param);
      const variantValue = variants.map((variant) => variant.value);
      return variantValue.join('/');
    },
  },
};
</script>

<style scoped lang="scss">
img {
  width: 80px;
}
</style>

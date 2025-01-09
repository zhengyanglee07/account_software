<template>
  <BaseCard
    has-header
    title="Inventory"
  >
    <BaseFormGroup
      v-if="product.type === 'physical'"
      label="SKU"
      class="w-xs-100 w-md-50"
    >
      <BaseFormInput
        v-model="sku"
        type="text"
        @input="input('SKU', sku)"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Available Stock"
      class="w-xs-100 w-md-50"
    >
      <BaseFormInput
        v-model.number="quantity"
        min="0"
        type="number"
        @input="input('quantity', quantity)"
      />
    </BaseFormGroup>
    <BaseFormGroup>
      <BaseFormCheckBox
        v-model="isSelling"
        :value="true"
        inline
        @change="input('is_selling', isSelling)"
      >
        Continue selling when out of stock
      </BaseFormCheckBox>
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
export default {
  name: 'ProductInventory',
  props: ['product'],
  data() {
    return {
      isSelling: true,
      quantity: 0,
      sku: '',
    };
  },
  watch: {
    product: {
      deep: true,
      handler(newValue) {
        if (newValue) {
          this.sku = newValue?.SKU ?? '';
          this.quantity = newValue?.quantity ?? 0;
          this.isSelling = newValue?.is_selling ?? true;
        }
      },
    },
  },
  mounted() {
    this.isSelling = this.product.is_selling;
    this.quantity = this.product.quantity;
    this.sku = this.product.SKU;
  },
  methods: {
    input(type, value) {
      this.$emit('fetchInventory', { type, value });
    },
  },
};
</script>

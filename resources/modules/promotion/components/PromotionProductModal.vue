<template>
  <BaseModal
    :title="modalTitle"
    :modal-id="modalId"
  >
    <BaseDatatable
      title="products"
      no-header
      no-search
      no-sorting
      no-action
      :table-headers="productTableHeaders"
      :table-datas="filteredItems"
    >
      <template #cell-product_checkbox="{ row }">
        <BaseFormGroup>
          <BaseFormCheckBox
            v-if="!row.isVariant"
            :id="`promotion-product-select-${row.index}`"
            v-indeterminate="row.product.indeterminate"
            :value="true"
            :model-value="row.product.isCheckedAll"
            :disabled="row.product.isDisabledAll"
            @click="addProduct(row, row.index)"
          />
          <BaseFormCheckBox
            v-else
            :id="`promotion-product-variant-select-${row.index}`"
            class="ms-5"
            :value="true"
            :model-value="row.value.isChecked"
            :disabled="row.value.isDisabled"
            @click="addVariant(row, row.comIdx, row.index)"
          />
        </BaseFormGroup>
      </template>
      <template #cell-product_image="{ row: { product, isVariant, value } }">
        <img
          v-if="!isVariant"
          :src="product.productImagePath || productDefaultImages"
          style="width: 4rem"
        >
        <img
          v-else
          :src="value.image_url"
          style="width: 4rem"
        >
      </template>
      <template #cell-product_title="{ row: { product, isVariant, key } }">
        {{ isVariant ? key : product.productTitle }}
      </template>
      <template #cell-product_stock="{ row: { product, value } }">
        {{ `${value?.quantity ?? product.quantity} available` }}
      </template>
      <template #cell-product_price="{ row: { product, value } }">
        {{ currency }} {{ value?.price ?? product.productPrice }}
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
        @click="save"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import { mapActions, mapMutations, mapState } from 'vuex';
import productDefaultImages from '@shared/assets/media/product-default-image.png';
import clone from 'clone';

export default {
  name: 'BrowseProductModal',
  mixins: [PromotionCentraliseMixins],
  props: ['modalId', 'modalTitle', 'buttonTitle'],
  data() {
    return {
      productDefaultImages,
      //  products:[],
      searchInput: '',
      selectedProduct: [],
      productTableHeaders: [
        { name: '', key: 'product_checkbox', custom: true },
        { name: 'Images', key: 'product_image', custom: true },
        { name: 'Title', key: 'product_title', custom: true },
        { name: 'Stock', key: 'product_stock', custom: true },
        { name: 'Price', key: 'product_price', custom: true },
      ],
    };
  },
  computed: {
    ...mapState('promotions', ['allProducts', 'currency']),
    filteredProducts() {
      const products = [];
      clone(this.allProducts ?? []).forEach((e, index) => {
        e.index = index;
        products.push(e);
        if (e.combinationVariation.length > 0) {
          products.push(
            ...e.combinationVariation.map((m, idx) => ({
              ...m,
              index,
              comIdx: idx,
              isVariant: true,
            }))
          );
        }
      });
      return products;
    },
    filteredItems() {
      return this.filteredProducts?.filter(
        function (product) {
          if (product.isVariant) return !this.searchInput;
          return product.product.productTitle
            .toString()
            .toLowerCase()
            .includes(this.searchInput.toLowerCase());
        }.bind(this)
      );
    },
  },
  directives: {
    indeterminate(el, binding) {
      el.indeterminate = Boolean(binding.value);
    },
  },
  methods: {
    ...mapMutations('promotions', [
      'setAllProducts',
      'updateCheckKey',
      'checkAllProduct',
    ]),
    ...mapActions('promotions', ['loadAllProduct']),
    closeModal() {
      document.getElementById(`${this.modalId}-close-button`)?.click();
    },
    save() {
      const isCheckedProduct = this.allProducts
        .filter((item) => {
          return item.product.isCheckedAll || item.product.indeterminate;
        })
        .map((data) => {
          return {
            product: data.product,
            combinationVariation: data.combinationVariation.filter(
              (combination) => combination.value.isChecked
            ),
          };
        });

      this.$emit('selectedProduct', isCheckedProduct);
    },
    // loadAllProduct(){
    //     axios.get('/product/getallproduct')
    //     .then(response =>{
    //         this.setAllProducts(response.data);
    //          if(this.setting.selectedProduct.length > 0 ){
    //             this.setting.selectedProduct.forEach(product =>{
    //                 let indexOf =  this.allProducts.map(item => item.product.id).indexOf(product.product.id);
    //                 let isFound = this.allProducts.find(item => item.product.id == product.product.id);
    //                 if(indexOf != -1 ){
    //                     if(product.product.hasVariant){
    //                         product.combinationVariation.forEach(comb =>{
    //                             let findCombination = isFound.combinationVariation.find(combination =>{
    //                                 let allCombination = [...combination.combination_id];
    //                                 let selectedCombination= [...comb.combination_id];
    //                                 let arr1 = selectedCombination.slice().sort();
    //                                 let arr2 = allCombination.slice().sort();
    //                                 return arr1.length === arr2.length && arr1.every((value,index)=>{
    //                                     return value === arr2[index];
    //                                 } )
    //                             })
    //                             console.log(findCombination,'findComb');
    //                             if(findCombination !== undefined){

    //                             }
    //                         })

    //                     }else{
    //                         this.updateCheckKey({
    //                             index: indexOf,
    //                             value: true,
    //                             key :'product',
    //                             checkKey : 'isCheckedAll'
    //                         })
    //                     }

    //                 }
    //             })
    //         }

    //    product.combinationVariant.forEach(comb =>{
    //     let findCombination = isFound.combinationVariation.find(combination =>{
    //         let selectedCombination = [...combination.combination_id];
    //         let allCombination = [...comb.combinationId];
    //         let arr1 = selectedCombination.slice().sort();
    //         let arr2 = allCombination.slice().sort();

    //     });
    //     if(findCombination !== undefined){
    //         findCombination.value.isChecked = true;
    //     }
    //     console.log(findCombination);
    // })

    //     })
    //     .catch(error => console.log(error));
    // },
    addProduct(item, index) {
      if (item.product.isDisabledAll) return;
      this.updateCheckKey({
        index,
        value: !item.product.isCheckedAll,
        key: 'product',
        checkKey: 'isCheckedAll',
      });
      // Check all variant of selected product
      if (item.product.hasVariant) {
        // console.log(currentProducts,'unchecked product');
        if (!item.product.isCheckedAll) {
          this.filteredItems.map((m) => {
            if (m.key && m.index === index) m.value.isChecked = true;
            return m;
          });
          item.combinationVariation.forEach((combination, idx) => {
            this.updateCheckKey({
              index,
              combIdx: idx,
              checkKey: 'isChecked',
              key: 'combinationVariation',
              combKey: 'value',
              value: true,
            });
          });
        } else {
          item.combinationVariation.forEach((combination, idx) => {
            this.updateCheckKey({
              index,
              combIdx: idx,
              checkKey: 'isChecked',
              key: 'combinationVariation',
              combKey: 'value',
              value: false,
            });
          });
        }
      }
      this.updateCheckKey({
        index,
        value: false,
        key: 'product',
        checkKey: 'indeterminate',
      });
    },
    addVariant(combination, combIdx, index) {
      this.updateCheckKey({
        index,
        combIdx,
        checkKey: 'isChecked',
        key: 'combinationVariation',
        combKey: 'value',
        value: !combination.value.isChecked,
      });
      // combination.value.isChecked = !combination.value.isChecked;
      const item = this.allProducts[index];
      const isVariationChecked = item.combinationVariation.filter(
        (comb) => comb.value.isChecked
      );
      const totalCombination = item.combinationVariation.length;
      if (isVariationChecked.length === totalCombination) {
        this.updateCheckKey({
          index,
          value: false,
          key: 'product',
          checkKey: 'indeterminate',
        });
        this.updateCheckKey({
          index,
          value: true,
          key: 'product',
          checkKey: 'isCheckedAll',
        });
        // item.product.indeterminate = false;
        // item.product.isCheckedAll = true;
      } else if (isVariationChecked.length > 0) {
        this.updateCheckKey({
          index,
          value: true,
          key: 'product',
          checkKey: 'indeterminate',
        });
        this.updateCheckKey({
          index,
          value: false,
          key: 'product',
          checkKey: 'isCheckedAll',
        });
        // item.product.indeterminate = true;
        // item.product.isCheckedAll = false
      } else {
        this.updateCheckKey({
          index,
          value: false,
          key: 'product',
          checkKey: 'indeterminate',
        });
        this.updateCheckKey({
          index,
          value: false,
          key: 'product',
          checkKey: 'isCheckedAll',
        });
        // item.product.indeterminate = false;
        // item.product.isCheckedAll = false
      }
      console.log('added variant');
    },
    updateSearchInput(value) {
      this.searchInput = value;
    },
  },
  watch: {},
  mounted() {
    this.loadAllProduct();
  },
};
</script>

<style lang="scss" scoped></style>

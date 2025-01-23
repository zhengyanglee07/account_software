<template>
  <BaseModal :modal-id="modalId" :title="modalTitle" :button_title="buttonTitle">
    <div class="row">
      <div class="col-md-12 mb-3" style="padding: 0 !important">
        <div class="form-group-prepend">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="fas fa-search gray_icon" />
              </span>
            </div>
            <input ref="search" v-model="searchInput" type="text" class="form-control firstInput"
              :placeholder="'Search Products'" />
          </div>
        </div>
      </div>
    </div>
    <template v-if="allProducts.length > 0">
      <template v-if="!filteredItems.length">
        <div>
          <span class="flex-center">No results found...</span>
        </div>
      </template>
      <template v-for="(item, index) in filteredItems" :key="index">
        <div class="list-group list-group-item list-group-item-action">
          <div class="row" style="align-items: center" @click="addProduct(item, index)">
            <div class="col-1 px-0">
              <input :id="`${index}-checkbox`" type="checkbox" class="me-1 black-checkbox"
                :checked="selected.includes(item.id)" />
              <span />
            </div>
            <div class="col-2 px-0">
              <span>
                <img :src="item.productImagePath ||
                  'https://cdn.hypershapes.com/assets/product-default-image.png'
                  " width="40" height="40" />
              </span>
            </div>
            <div class="col-5 px-0" style="text-overflow: ellipsis; cursor: pointer">
              <span style="padding-left: 2px">{{ item.productTitle }}</span>
            </div>
            <!-- <div class="col-2">
                <span>{{ item.quantity }} available</span>
              </div> -->
            <div class="col-2 px-0" style="text-align: right">
              <span>{{ currency === 'MYR' ? 'RM' : currency }}
                {{ item.productPrice }}
              </span>
            </div>
          </div>
        </div>
      </template>
    </template>
    <template v-else>
      <div class="wrapper">
        <span class="flex-center" style="color: grey">No product yet...</span>
        <a class="flex-center h_link" style="font-size: 1rem" href="/product/create">Click here to Create Product</a>
      </div>
    </template>

    <template #footer>
      <BaseButton data-bs-dismiss="modal" @click="save"> Save </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'BrowseProductModal',

  props: [
    'modalId',
    'modalTitle',
    'buttonTitle',
    'allProducts',
    'currency',
    'selectedProduct',
  ],

  data() {
    return {
      searchInput: '',
      selected: [],
      showSelected: [],
    };
  },

  computed: {
    filteredItems() {
      return this.allProducts.filter(
        function (product) {
          return product.productTitle
            .toString()
            .toLowerCase()
            .includes(this.searchInput.toLowerCase());
        }.bind(this)
      );
    },
  },

  watch: {
    selectedProduct(newValue) {
      if (newValue) {
        this.selected = newValue;
      }
    },
  },

  mounted() {
    this.selected = this.selectedProduct;
  },

  methods: {
    save() {
      this.showSelected = [];
      this.selected.forEach((id) => {
        const selectedItem = this.allProducts.find((product) => {
          return product.id === id;
        });
        this.showSelected.push(selectedItem);
      });
      this.$emit('save', this.selected);
      this.searchInput = '';
      Modal.getInstance(document.getElementById(this.modalId)).hide();
    },

    addProduct(item, index) {
      if (!this.selected.includes(item.id)) {
        // document.getElementById(index + '-checkbox').checked = true;
        this.selected.push(item.id);
      } else {
        const i = this.selected.findIndex((element) => element === item.id);
        this.selected.splice(i, 1);
        // document.getElementById(index + '-checkbox').checked = false;
      }
    },
  },
};
</script>

<style lang="scss" scoped></style>

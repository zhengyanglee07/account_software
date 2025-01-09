<template>
  <VueJqModal
    :modal-id="modalId"
    width="40%"
    height="auto"
    :scrollable="false"
    is-overflow-visible
  >
    <template #title>
      Add Menu Item
    </template>

    <template #body>
      <div
        class="card w-100"
        style="border: none"
      >
        <div class="">
          <div class="m-container__text">
            Name
          </div>
        </div>
        <div class="list-element-setting-option">
          <input
            v-model="newMenuItemName"
            class="form-control firstInput"
            style="width: 100%"
            placeholder="e.g. About us"
          >
        </div>

        <div class="">
          <div class="m-container__text">
            Type
          </div>
        </div>

        <div class="list-element-setting-option">
          <VSelect
            :options="['Page', 'Product', 'External Link', 'Policy']"
            :model-value="linkType"
            placeholder="Select a type"
            @option:selected="setSelected"
          />
        </div>

        <div
          v-show="linkType == 'Page'"
          class=""
        >
          <div class="m-container__text">
            Page
          </div>
        </div>
        <div
          v-show="linkType == 'Page'"
          class="list-element-setting-option mb-0 pb-0"
        >
          <VSelect
            v-model="newMenuItemLink"
            class="menu-item-page-product-dropdown"
            placeholder="Select a page"
            :options="parsedAllPages"
            label="name"
          />
        </div>

        <div
          v-show="linkType == 'Product'"
          class=""
        >
          <div class="m-container__text">
            Product
          </div>
        </div>
        <div
          v-show="linkType == 'Product'"
          class="list-element-setting-option pb-0"
        >
          <VSelect
            v-model="newMenuItemLink"
            class="menu-item-page-product-dropdown"
            placeholder="Select a product"
            :options="parsedAllProducts"
            label="name"
          />
        </div>

        <div
          v-show="linkType == 'External Link'"
          class=""
        >
          <div class="m-container__text">
            External Link
          </div>
        </div>
        <div
          v-show="linkType == 'External Link'"
          class="pb-0"
        >
          <input
            v-model="externalLink"
            type="text"
            placeholder="https://example.com"
            class="w-100 form-control"
          >
        </div>

        <div
          v-show="linkType == 'Policy'"
          class=""
        >
          <div class="m-container__text">
            Policy
          </div>
        </div>
        <div
          v-show="linkType == 'Policy'"
          class="list-element-setting-option mb-0 pb-0"
        >
          <VSelect
            v-model="newMenuItemLink"
            class="menu-item-page-product-dropdown"
            placeholder="Select a policy"
            :options="policyOptionArray"
            label="name"
          />
        </div>
      </div>
    </template>

    <template #footer>
      <button
        class="cancel-button"
        @click="
          newMenuItemName = '';
          linkType = '';
          externalLink = '';
          newMenuItemLink = null;
          name_blank_error = false;
          link_blank_error = false;
          hideModal();
        "
      >
        Cancel
      </button>
      <button
        class="primary-small-square-button"
        @click="createNewMenuItem()"
      >
        Save
      </button>
    </template>
  </VueJqModal>
</template>

<script>
import {
  store,
  nested,
  mutations,
} from '@onlineStore/lib/ecommerceNavigation.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  name: 'CreateNavigationModal',

  props: ['referenceKey', 'modalId', 'policyOptionArray'],

  emits: ['updateElements'],

  data() {
    return {
      newElement: '',
      isModalActivated: false,
      toggleSettings: false,
      newMenuItemName: '',
      newMenuItemLink: null,
      name_blank_error: false,
      link_blank_error: false,
      linkType: '',
      externalLink: '',
      modal: null,
    };
  },

  computed: {
    parsedAllPages() {
      const tempArray = [];
      for (let index = 0; index < store.allPages.length; index++) {
        const { path } = store.allPages[index];

        tempArray.push({
          id: store.allPages[index].id,
          type: 'Page',
          name: store.allPages[index].name,
          path: `/${path}`,
        });
      }
      return tempArray;
    },

    parsedAllProducts() {
      const tempArray = [];
      tempArray.push({
        type: 'Product',
        name: 'All Products',
        path: '/products/all',
      });
      for (let index = 0; index < store.allProducts.length; index++) {
        tempArray.push({
          type: 'Product',
          name: store.allProducts[index].productTitle,
          id: store.allProducts[index].id,
          path: `/products/${store.allProducts[index].path}`,
        });
      }
      return tempArray;
    },

    selectOptionsArray() {
      return [
        {
          path: 'page',
          name: 'Page',
          subOptions: this.parsedAllPages,
        },
        {
          path: 'product',
          name: 'Product',
          subOptions: this.parsedAllProducts,
        },
      ];
    },
  },

  watch: {
    linkType(newValue) {
      if (newValue === 'Policy') {
        this.newMenuItemLink = this.policyOptionArray.find(
          (e) => e.name === 'Privacy Policy'
        );
      }
    },
  },

  methods: {
    createNewMenuItem() {
      if (this.newMenuItemName === '') {
        this.$toast.error('Error', 'Name is required.');
        return;
      }
      if (this.linkType === '') {
        this.$toast.error('Error', 'Type is required.');
        return;
      }
      if (this.newMenuItemLink == null && this.linkType === 'Product') {
        this.$toast.error('Error', 'Product link is required.');
        return;
      }

      if (this.newMenuItemLink == null && this.linkType === 'Page') {
        this.$toast.error('Error', 'Page link is required.');
        return;
      }

      if (this.linkType === 'External Link') {
        if (this.externalLink === '') {
          this.$toast.error('Error', 'External link is required.');
          return;
        }
        if (!this.externalLink.startsWith('https://')) {
          this.$toast.error('Error', 'External link must contain https://');
          return;
        }
      }

      if (this.linkType === 'External Link') {
        this.newMenuItemLink = {
          path: this.externalLink,
          type: 'External Link',
        };
      }
      mutations.addMenuItem({
        name: this.newMenuItemName,
        link: this.newMenuItemLink,
        refKey: this.referenceKey,
      });

      this.$emit('updateElements', nested.elements);

      this.newMenuItemName = '';
      this.newMenuItemLink = null;
      this.linkType = '';
      this.externalLink = '';
      this.hideModal();
    },

    setSelected(value) {
      this.newMenuItemLink = null;
      this.linkType = value;
    },

    commitChanges(setting, value) {
      const index = this.itemListLength;
      mutations.editMenuItem({ index, setting, value });
    },

    hideModal() {
      bootstrap?.then(({ Modal }) => {
        new Modal(document.getElementById(this.modalId)).hide();
      });
    },
  },
};
</script>

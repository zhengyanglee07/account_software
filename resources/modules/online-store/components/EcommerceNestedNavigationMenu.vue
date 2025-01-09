<template>
  <Draggable
    tag="tbody"
    :list="modelValue"
    @change="emitter(modelValue)"
  >
    <template #item="{ element }">
      <tr>
        <EcommerceNavigationMenuItemLightBox
          :index="element.refKey"
          :menu-item="element"
          :item-list-length="itemListLength"
          :options-array="selectOptionsArray"
          :parsed-all-pages="parsedAllPages"
          :parsed-all-products="parsedAllProducts"
          :policy-option-array="policyOptionArray"
          @update-elements="emitter"
        />
        <NestedNavigationMenu
          v-if="level < 3"
          class="item-sub"
          :list="element.elements"
          :level="level + 1"
        />
        <!-- <BaseButton
          v-show="index + 1 === realValue.length"
          type="link"
          has-add-icon
          data-bs-toggle="modal"
          :data-bs-target="
            `#ecommerce-navigation-save-menu-item` + element.refKey
          "
        >
          Add Menu Item
        </BaseButton> -->
        <CreateNavigationModal
          :reference-key="element.refKey"
          :modal-id="'ecommerce-navigation-save-menu-item' + element.refKey"
          :policy-option-array="policyOptionArray"
          @update-elements="emitter"
        />
      </tr>
    </template>
  </Draggable>
</template>
<script>
import EcommerceNavigationMenuItemLightBox from '@onlineStore/components/EcommerceNavigationMenuItemLightBox.vue';
import CreateNavigationModal from '@onlineStore/components/EcommerceCreateNavigationModal.vue';
import Draggable from 'vuedraggable';
import { store, mutations } from '@onlineStore/lib/ecommerceNavigation.js';

export default {
  name: 'NestedNavigationMenu',
  components: {
    EcommerceNavigationMenuItemLightBox,
    Draggable,
    CreateNavigationModal,
  },
  props: {
    toggleSettings: Boolean,
    // tasks: Array,
    modelValue: {
      required: false,
      type: Array,
      default: null,
    },
    list: {
      required: false,
      type: Array,
      default: null,
    },
    level: {
      type: Number,
      default: 1,
    },
    allLegalPolicy: {
      type: Array,
      required: false,
      default: () => [],
    },
  },

  data() {
    return {
      toggle: false,
      editedElement: '',
    };
  },
  computed: {
    parsedMenuItemList() {
      return store.menuItemList;
    },

    itemListLength() {
      // return store.menuItemList?.length ?? 0;
      return this.modelValue ? this.modelValue.length : this.list.length;
    },

    allMenuItemList: {
      get() {
        return this.parsedMenuItemList;
      },
      set(newArray) {
        mutations.reorderMenuItemList(newArray);
      },
    },

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
    policyOptionArray() {
      return this.allLegalPolicy?.map((m) => ({
        ...m,
        type: 'Policy',
      }));
    },
    dragOptions() {
      return {
        animation: 0,
        group: 'description',
        disabled: false,
        ghostClass: 'ghost',
      };
    },
    // this.value when input = v-model
    // this.list  when input != v-model
    realValue() {
      return this.modelValue ? this.modelValue : this.list;
    },
  },
  methods: {
    emitter(value) {
      mutations.updateElements([...value]);
      this.$emit('update:modelValue', [...value]);
    },
  },
};
</script>
<style scoped>
.dragArea {
  min-height: 50px;
  outline: 1px dashed;
}
.item-container {
  max-width: 60rem;
  margin: 0;
  padding-right: 0;
}
.item {
  padding: 1rem;
  border: solid black 1px;
  background-color: #fefefe;
}
.item-sub {
  margin: 0 0 0 1rem;
}
.col-8 {
  flex: 0 0 auto;
  width: 100%;
}
</style>

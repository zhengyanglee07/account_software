<template>
  <BasePageLayout
    is-setting
    page-name="Menu"
    back-to="/online-store/menu"
  >
    <BaseCard title="General">
      <BaseFormGroup label="Menu Name">
        <BaseFormInput
          id="menu-name"
          v-model="inputTitle"
          type="text"
          placeholder="e.g. Header Menu"
        />
      </BaseFormGroup>
    </BaseCard>

    <BaseCard title="Menu Items">
      <NavMenuContainer
        :menu-item-list="menuListArray"
        :account-id="accountId"
        :all-products="allProductsArray"
        :all-legal-policy="allLegalPolicy"
        :all-categories="allCategoriesArray"
        @add-menu-list="addMenuList"
        @del-menu-list="delMenuList"
        @update-menu-list-array="reorderMenuList"
        @edit-menu-list="editMenuList"
      />
    </BaseCard>

    <template #footer>
      <BaseButton
        type="secondary"
        class="me-3"
        @click="redirectBack()"
      >
        Cancel
      </BaseButton>
      <BaseButton
        type="primary"
        @click="saveMenuList()"
      >
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script>
import NavMenuContainer from '@onlineStore/components/EcommerceNavMenuContainer.vue';
import { nested, mutations } from '@onlineStore/lib/ecommerceNavigation.js';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';
import { useStore } from 'vuex';
import { computed, reactive, ref, onMounted, toRefs } from 'vue';

export default {
  name: 'EcommerceNavigationMenu',

  components: {
    NavMenuContainer,
  },

  props: {
    menu: { type: Object, default: () => {} },
    accountId: { type: Number, default: null },
    allPagesArray: { type: Array, default: () => [] },
    allProductsArray: { type: Array, default: () => [] },
    allLegalPolicy: { type: Array, default: () => [] },
    allCategoriesArray: { type: Array, default: () => [] },
  },

  setup(props) {
    const store = useStore();
    store.commit('onlineStore/setMenuPages', props.allPagesArray);
    store.commit('onlineStore/setMenuProducts', props.allProductsArray);
    store.commit('onlineStore/setMenuAllLegalPolicy', props.allLegalPolicy);
    store.commit('onlineStore/setAllCategories', props.allCategoriesArray);

    const menuArray = computed(() => {
      return props.menu === {} ? { title: '', menu_items: '' } : props.menu;
    });

    const checkContainMenuItems = menuArray.value.menu_items;

    store.commit(
      'onlineStore/setMenuList',
      menuArray.value.menu_items ? JSON.parse(menuArray.value.menu_items) : []
    );

    const menuListArray = checkContainMenuItems
      ? ref(JSON.parse(menuArray?.value?.menu_items))
      : ref([]);

    const modifyOldData = (data) => {
      data?.forEach((item, index) => {
        if (!('nested' in item)) {
          const newItem = {
            ...item,
            nested: [],
          };
          menuListArray.value[index] = newItem;
        }
      });
    };

    modifyOldData(menuListArray.value);

    const addMenuList = (data) => {
      menuListArray.value.push(data);
    };

    const delMenuList = (index) => {
      const level = index[0] - 1;
      const nestIndex = index[1];
      const refIndex = [];

      for (let i = 0; i < nestIndex.length; i++) {
        refIndex[i] = nestIndex[i] - 1;
      }
      if (level === 2)
        menuListArray.value[refIndex[0]].nested[refIndex[1]].nested.splice(
          refIndex[2],
          1
        );
      if (level === 1)
        menuListArray.value[refIndex[0]].nested.splice(refIndex[1], 1);
      if (level === 0) menuListArray.value.splice(refIndex[0], 1);

      // menuListArray.value.splice(index, 1);
    };

    const editMenuList = (data) => {
      const payload = {
        ...data[0],
        nested: [],
      };
      const level = data[1] - 1;
      const index = data[2];
      const refIndex = [];

      for (let i = 0; i < index.length; i++) {
        refIndex[i] = index[i] - 1;
      }

      if (level === 2) {
        payload.nested =
          menuListArray.value[refIndex[0]].nested[refIndex[1]].nested[
            refIndex[2]
          ].nested;
        menuListArray.value[refIndex[0]].nested[refIndex[1]].nested[
          refIndex[2]
        ] = payload;
      }
      if (level === 1) {
        payload.nested =
          menuListArray.value[refIndex[0]].nested[refIndex[1]].nested;
        menuListArray.value[refIndex[0]].nested[refIndex[1]] = payload;
      }
      if (level === 0) {
        payload.nested = menuListArray.value[refIndex[0]].nested;
        menuListArray.value[refIndex[0]] = payload;
      }
    };

    const reorderMenuList = (data) => {
      menuListArray.value = data;
    };

    const isMenuEmpty = computed(() => {
      return Object.keys(props.menu ?? {}).length === 0;
    });

    const inputTitle = ref(menuArray.value.title);

    const triggerVuexCommit = (func, data) => {
      // func must be a string
      // data can be an object proxy
      store.commit(func, data);
    };

    return {
      store,
      menuArray,
      menuListArray,
      isMenuEmpty,
      inputTitle,
      addMenuList,
      delMenuList,
      editMenuList,
      reorderMenuList,
      triggerVuexCommit,
    };
  },

  methods: {
    redirectBack() {
      this.$inertia.visit('/online-store/menu');
    },

    saveMenuList() {
      // payloadData is an array, while the menuListArray is a proxy object
      const payloadData = JSON.parse(JSON.stringify(this.menuListArray));
      const hasInvalidMenu = document.querySelector(
        '.invalid-nav-menu-container'
      );
      if (hasInvalidMenu) {
        this.$toast.error('Error', 'Maximum allowed nested container is 3 ');
        return;
      }
      this.triggerVuexCommit('onlineStore/updateMenuList', this.menuListArray);

      if (this.inputTitle === '' || this.inputTitle == null) {
        this.$toast.error('Error', 'Menu title required.');
        return;
      }
      if (this.isMenuEmpty) {
        onlineStoreAPI
          .createMenu(this.inputTitle, payloadData)
          .then((response) => {
            this.$toast.success('Success', 'Menu saved successfully');
            setTimeout(() => {}, 1500);
            this.$inertia.visit('/online-store/menu');
          })
          .catch((error) => {
            this.$toast.error('Error', 'create again');
          });
      } else {
        onlineStoreAPI
          .updateMenu({
            inputTitle: this.inputTitle,
            menuItemArray: payloadData,
            id: this.menuArray.id,
          })
          .then((response) => {
            this.$toast.success('Success', 'Menu saved successfully');
            setTimeout(() => {}, 1500);
            this.$inertia.visit('/online-store/menu');
          })
          .catch((error) => {
            this.$toast.error('Error', 'Try again');
          });
      }
    },
  },
};
</script>

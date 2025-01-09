<template>
  <div class="">
    <!-- title -->
    <div>
      <h2>Menu items</h2>
    </div>

    <!-- if the menu is new / empty -->
    <div
      v-if="!navItems || navItems.length <= 0"
      class="center-container empty-container"
    />

    <!-- drag and drop reorder method -->

    <div
      v-else-if="navItems"
      class="d-flex menu-container"
    >
      <MenuList
        :menu-list="navItems"
        :level="0"
        @set-reorder-menu-list="reorderMenuItem"
        @set-add-menu-list="addMenuItem"
        @set-del-menu-list="deleteMenuItem"
        @set-edit-menu-list="editMenuItem"
      />
    </div>
    <div
      class="add-new-menu-container flex-horizontal"
      @click="addMenuItem"
    >
      <div class="text">
        <i class="fa-solid fa-plus text" />
        {{ status === 'edit' ? 'Edit Menu Item' : 'Add Menu Item' }}
      </div>
    </div>

    <!--add new menu method -->

    <!--add new menu template modal-->
    <div>
      <div>
        <BaseModal
          no-dismiss
          :title="status === 'add' ? 'Add Menu Item' : 'Edit Menu Item'"
          modal-id="ecommerce-navigation-save-menu-item"
        >
          <BaseFormGroup label="Name">
            <BaseFormInput
              id="menu-item-name"
              v-model="newMenuItemName"
              type="text"
              placeholder="e.g. Home, About us"
            />
          </BaseFormGroup>

          <BaseFormGroup label="Type">
            <BaseMultiSelect
              v-model="linkType"
              :options="[
                'Page',
                'Product',
                'Policy',
                'External Link',
                'Category',
              ]"
            />
          </BaseFormGroup>

          <BaseFormGroup
            v-if="linkType"
            :label="typeOptions.title"
          >
            <BaseMultiSelect
              v-if="linkType.toLowerCase() !== 'external link'"
              v-model="newMenuItemLink"
              label="name"
              :options="typeOptions.options"
            />

            <BaseFormInput
              v-else
              id="external-link"
              v-model="externalLink"
              type="text"
              placeholder="https://example.com"
            />
          </BaseFormGroup>

          <BaseFormGroup>
            <BaseFormCheckBox
              v-model="newTab"
              :value="true"
            >
              Open in new tab
            </BaseFormCheckBox>
          </BaseFormGroup>

          <template #footer>
            <BaseButton
              type="secondary"
              @click="
                hideModal();
                newMenuItemName = '';
                newMenuItemLink = null;
                name_blank_error = false;
                link_blank_error = false;
              "
            >
              Dismiss
            </BaseButton>
            <BaseButton @click="modalSubmit($event)">
              Save
            </BaseButton>
          </template>
        </BaseModal>
      </div>
    </div>
  </div>
</template>

<script setup>
/* eslint-disable-next-line */
import MenuList from './EcommerceNestedNavMenu.vue';
import { ref, computed, onMounted, reactive, inject, watch } from 'vue';

import { useStore, mapMutations } from 'vuex';

// vuex
const store = useStore();

// for modal form
const $toast = inject('$toast');

const bootstrap = typeof window !== `undefined` && import('bootstrap');
const modal = ref(null);
const linkType = ref('');
const newTab = ref(false);
const externalLink = ref('');
const newMenuItemName = ref('');
const newMenuItemLink = ref('');

// if user selects different link type, resets the input, otherwise don't
const isInSameModal = ref(false);

// to know which index to modify if user edits an element
const indexRef = ref(null);
// to know if the modal is editing or adding
const status = ref('');

const props = defineProps({
  menuItemList: {
    type: String,
    default: null,
  },
  allLegalPolicy: {
    type: Array,
    default: null,
  },
  allProducts: {
    type: Array,
    default: null,
  },
  allCategories: {
    type: Array,
    default: null,
  },
});

const navItems = ref([]);
// the rendered list

navItems.value = props.menuItemList ?? [
  ...store.state.onlineStore.menuListArray,
]; // eslint-disable-line

const emit = defineEmits([
  'updateMenuListArray',
  'addMenuList',
  'delMenuList',
  'editMenuList',
]);

// computed values
const policyOptionArray = computed(() => {
  return props.allLegalPolicy?.map((m) => ({
    ...m,
    type: 'Policy',
  }));
});

// dynamically loads all the pages created by the user
const setAllPages = computed(() => {
  const { menuAllPages } = store.state.onlineStore;
  // add in a 'name' key to allow items to be displayed on the dropdown

  return menuAllPages?.map((m) => ({
    ...m,
    name: m.name,
  }));
});

const setAllProducts = computed(() => {
  const { menuAllProducts } = store.state.onlineStore;

  const tempArr = [];
  // options for selecting all products
  const allProductArr = {
    type: 'Product',
    name: 'All Products',
    path: '/products/all',
  };

  tempArr.push(allProductArr);

  menuAllProducts.forEach((items) => {
    const payload = {
      type: 'Product',
      name: items.productTitle,
      id: items.id,
      path: `/products/${items.path}`,
    };
    tempArr.push(payload);
  });

  return tempArr;
});
const setAllCategories = computed(() => {
  const { allCategories = [] } = store.state.onlineStore;

  const tempArray = [];
  for (let index = 0; index < allCategories.length; index++) {
    const { id, path, name } = allCategories[index];
    tempArray.push({
      id,
      type: 'Category',
      name,
      path: `/categories/${path}`,
    });
  }
  return tempArray;
});

const typeOptions = computed(() => {
  let options = {
    title: '',
    options: [],
  };
  switch (linkType.value.toLowerCase()) {
    case 'policy':
      options = {
        title: 'Policy',
        options: [...(policyOptionArray.value ?? '')],
      };
      break;
    case 'product':
      options = {
        title: 'Product',
        options: [...(setAllProducts.value ?? [])],
      };
      break;
    case 'category':
      options = {
        title: 'Category',
        options: [...(setAllCategories.value ?? [])],
      };
      break;
    default:
      options = {
        title: 'Page',
        options: [...(setAllPages.value ?? '')],
      };
  }
  return options;
});

// for displaying the pop up modal

bootstrap?.then(({ Modal }) => {
  modal.value = new Modal(
    document.getElementById('ecommerce-navigation-save-menu-item')
  );
});

// vuex mutation
const showModal = () => {
  setTimeout(() => {
    isInSameModal.value = true;
  }, 500);
  modal.value.show();
};

const hideModal = () => {
  isInSameModal.value = false;
  modal.value.hide();
};

const clearInputForm = () => {
  linkType.value = '';
  newTab.value = false;
  externalLink.value = '';
  newMenuItemName.value = '';
  newMenuItemLink.value = '';
};

const modalSubmit = () => {
  // all validation goes through here
  // error is the error message that it will create
  // condition is the condition for validation
  // additional condition is the conditions that are depended based on the link type so it
  //    will not trigger all errors at once
  if (status.value === 'edit') {
    // set the input back
  }
  const validation = {
    nameError: {
      error: 'Name cannot be empty',
      condition: newMenuItemName.value,
      additionalCondition: '',
    },
    linkError: {
      error: 'Link cannot be empty',
      condition: linkType.value,
      additionalCondition: '',
    },
    productError: {
      error: 'Product link cannot be empty',
      condition: newMenuItemLink.value && linkType.value === 'Product',
      additionalCondition: 'Product',
    },
    categoryError: {
      error: 'Category link cannot be empty',
      condition: newMenuItemLink.value && linkType.value === 'Category',
      additionalCondition: 'Category',
    },
    pageError: {
      error: 'Page link cannot be empty',
      condition: newMenuItemLink.value && linkType.value === 'Page',
      additionalCondition: 'Page',
    },
    externalErrorAlt: {
      error: 'External link must start with https://',
      condition: externalLink.value.startsWith('https:\/\/'),
      additionalCondition: 'External Link',
    },
    externalError: {
      error: 'External link cannot be empty',
      condition: externalLink.value && linkType.value === 'External Link',
      additionalCondition: 'External Link',
    },
  };

  const validationFail = []; // check if any conditions failed
  Object.keys(validation).forEach((key) => {
    // if there are no additional conditions
    if (!validation[key].additionalCondition)
      if (!validation[key].condition) {
        validationFail.push(1);
        $toast.error('Error', validation[key].error);
        return;
      }
    // only apply the conditions that matches with the selected one
    if (validation[key].additionalCondition === linkType.value)
      if (!validation[key].condition) {
        validationFail.push(1);
        $toast.error('Error', validation[key].error);
      }
  });

  // if there are any errors, stop the function
  if (validationFail.length > 0) return;

  // mutation
  const payload = {
    name: newMenuItemName.value,
    link:
      linkType.value !== 'External Link'
        ? newMenuItemLink.value
        : externalLink.value,
    type: linkType.value,
    openInNewTab: newTab.value,
    nested: [],
  };
  // status will determine the mode of the modal is in
  if (status.value === 'add') emit('addMenuList', payload);
  if (status.value === 'edit')
    emit('editMenuList', [
      payload,
      indexRef.value.nested,
      indexRef.value.index,
    ]);

  // reset user input
  clearInputForm();
  hideModal();
};

const addMenuItem = () => {
  clearInputForm();
  status.value = 'add';
  showModal();
};

// used for comparing JS Objects
const compareObj = (obj1, obj2) => {
  return JSON.stringify(obj1) === JSON.stringify(obj2);
};

// this is to get the nested level and the index of the object
// so it will not edit the wrong element
// for delete and edit
const findObjectIndex = (array, obj, val) => {
  let index;
  let test = -1;
  /* eslint-disable */
  array.some((o, i) => {
    if (compareObj(o, obj)) return (index = `${i + 1}`);
    if ((test = findObjectIndex(o.nested || [], obj)))
      return (index = `${i + 1}${test}`);
    /* eslint-enable */
  });
  return index;
};

// get the nested level and the layered index of the target element
const getObjectProperties = (data) => {
  const array = data[0];
  const target = findObjectIndex(props.menuItemList, array, 'index');
  const objLength = Array.from(new Set(target.split(' '))).toString();

  const targetNestedLevel = objLength.length;

  return { nested: targetNestedLevel, index: target };
};

const deleteMenuItem = (data) => {
  indexRef.value = getObjectProperties(data);
  emit('delMenuList', [indexRef.value.nested, indexRef.value.index]);
};

const editMenuItem = (data) => {
  const el = JSON.parse(JSON.stringify(data[0]));
  status.value = 'edit';
  const { link, type, openInNewTab } = el;
  const menuType = type ?? link?.type;

  // know which nested to modify
  indexRef.value = getObjectProperties(data);

  linkType.value = menuType;
  newTab.value = openInNewTab ?? false;
  externalLink.value = menuType === 'External Link' ? link : '';
  newMenuItemName.value = el.name;
  newMenuItemLink.value = link;

  showModal();
};

const reorderMenuItem = (data) => {
  emit('updateMenuListArray', data);
};

onMounted(() => {
  // sometimes the bootstrap will fail for whatever reason
  bootstrap?.then(({ Modal }) => {
    modal.value = new Modal(
      document.getElementById('ecommerce-navigation-save-menu-item')
    );
  });
});
</script>

<style lang="scss" scoped>
.center-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.center-items {
  display: flex;
  align-items: center;
}

.empty-container {
  padding: 1rem 0rem;
}

.flex-horizontal {
  display: flex;
  flex-direction: row;
}

.flex-vertical {
  display: flex;
  flex-direction: column;
}

.nav-items-container {
  padding: 0.7rem 0rem;
  border-width: 1px;
  border-color: $table-border-color;
  border-style: solid;
  margin-top: 0.5rem;
  align-items: center;

  &__icon {
    cursor: pointer;
    padding: 0 1rem;

    & i {
      font-size: 1.5rem;
    }
  }

  &__flex-right {
    right: 0;
    float: right;
    margin-right: 0.5rem;
    margin-left: auto;
  }

  &__button {
    margin-right: 0.7rem;
  }
}

.menu-container > div {
  padding: 0;
}

.add-new-menu-container {
  padding: 0.7rem 0rem;
  border-width: 2px;
  border-color: $h-primary;
  border-style: solid;
  align-items: center;
  cursor: default;
  user-select: none;

  &:hover {
    cursor: pointer;
  }

  & .text {
    color: $h-primary;
    padding: 0.4rem 0.5rem;
  }
}
</style>

<template>
  <BaseDatatable
    title="menu"
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    @delete="removeRecord"
  >
    <template #action-button>
      <BaseButton
        has-add-icon
        href="/online-store/menu/create"
        @click="redirectCreate"
      >
        Add Menu
      </BaseButton>
    </template>
  </BaseDatatable>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

const props = defineProps({
  dbNavigations: {
    type: Array,
    default: () => [],
  },
});

const tableHeaders = [
  {
    name: 'Title',
    key: 'title',
    width: '55%',
  },
  {
    name: 'Last Modified',
    key: 'updated_at',
    isDateTime: true,
  },
];

const navigations = ref([...props.dbNavigations]);

const tableDatas = computed(() => {
  return navigations.value.map((nav) => {
    return {
      ...nav,
      editLink: `/online-store/menu/${nav.id}`,
    };
  });
});

const $toast = inject('$toast');

const removeRecord = (id) => {
  onlineStoreAPI
    .deleteMenu(id)
    .then(() => {
      const index = navigations.value.findIndex((nav) => nav.id === id);
      navigations.value.splice(index, 1);
      $toast.success('Success', 'Successfully deleted menu');
    })
    .catch((error) => {
      $toast.error(
        'Failed to delete menu',
        'Unexpected Error Occured. Please contact our support for help'
      );
      throw new Error(error);
    });
};
</script>

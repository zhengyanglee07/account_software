<template>
  <BaseDatatable
    title="popup"
    is-window-redirection
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    @delete="deletePopup"
  >
    <template #action-button>
      <BaseButton
        id="add-manual-promotion-button"
        class="me-2"
        has-add-icon
        data-bs-toggle="modal"
        data-bs-target="#template-name-modal"
      >
        Add popup
      </BaseButton>
    </template>
    <template #cell-is_publish="{ row: { is_publish: isPublished } }">
      <BaseBadge
        :text="isPublished ? 'Published' : 'Draft'"
        :type="isPublished ? 'success' : 'secondary'"
      />
    </template>
  </BaseDatatable>

  <TemplateNameModal
    type="popup"
    :is-save-template="false"
    @create="createPopup"
  />
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  dbPopups: {
    type: Array,
    required: true,
  },
});

const tableHeaders = [
  {
    name: 'Name',
    key: 'name',
    width: '40%',
  },
  // {
  //   name: 'Type',
  //   key: 'type',
  //   custom: true,
  // },
  // {
  //   name: 'Triggers',
  //   key: 'triggers',
  // },
  // {
  //   name: 'Status',
  //   key: 'is_publish',
  //   custom: true,
  // },
  {
    name: 'Last Modified',
    key: 'updated_at',
    isDateTime: true,
  },
];

const popups = ref([]);

onMounted(() => {
  popups.value = [...props.dbPopups];
});

const tableDatas = computed(() => {
  return (popups.value ?? []).map((popup) => {
    return {
      ...popup,
      editLink: `/builder/popup/${popup.reference_key}/editor`,
    };
  });
});

const $toast = inject('$toast');

const createPopup = (name) => {
  axios
    .post('/popup/store', {
      name,
    })
    .then(({ data: { popupRefKey } }) => {
      window.location.replace(`/builder/popup/${popupRefKey}/editor`);
    })
    .catch(({ response }) => {
      $toast.error(
        'Error',
        response?.data?.message ??
          'Something went wrong. Please contact our support'
      );
    });
};

const deletePopup = (id) => {
  axios
    .delete(`/popup/${id}`)
    .then(() => {
      const index = popups.value.findIndex((popup) => popup.id === id);
      popups.value.splice(index, 1);
      $toast.success('Success', 'Popup was successfully deleted');
    })
    .catch((err) => {
      $toast.error(
        'Something went wrong',
        'Please contact our support for help'
      );
      throw new Error(err);
    });
};
</script>

<template>
  <BaseDatatable
    title="page"
    is-window-redirection
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    @delete="deletePage"
  >
    <template #action-button>
      <BaseButton
        id="create-os-page-button"
        has-add-icon
        @click="triggerTemplateModal"
      >
        Add Page
      </BaseButton>
    </template>
    <template #cell-type="{ row: { is_published } }">
      <BaseBadge
        :text="is_published ? 'Published' : 'Draft'"
        :type="is_published ? 'success' : 'secondary'"
      />
    </template>
    <template #cell-name="{ row: { id, name } }">
      {{ name }} {{ onlineStoreHomepageId === id ? '-- Homepage' : '' }}
    </template>
    <template #action-options="{ row }">
      <BaseDropdownOption
        text="View"
        :link="
          row.is_published
            ? `http${$page.props.isLocalEnv ? '' : 's'}://${
                storeDomain?.domain
              }/${row.path}`
            : `/builder/page/${row.reference_key}/preview`
        "
        is-open-in-new-tab
      />
      <BaseDropdownOption
        v-if="onlineStoreHomepageId !== row.id"
        text="Set as Homepage"
        @click="updateStatus(row.id)"
      />
      <BaseDropdownOption
        :text="`Change to ${row.is_published ? 'Draft' : 'Publish'}`"
        @click="updateType(row)"
      />
    </template>
  </BaseDatatable>

  <TemplateModal section-type="page" />
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue';
import cloneDeep from 'lodash/cloneDeep';
import TemplateModal from '@shared/components/TemplateModal.vue';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  dbPages: {
    type: Array,
    default: () => [],
  },
  dbStoreDomain: {
    type: Object,
    default: () => ({}),
  },
});

const pages = ref([]);
const $toast = inject('$toast');

onMounted(() => {
  pages.value = cloneDeep(props.dbPages);
});

const tableHeaders = [
  {
    name: 'Name',
    key: 'name',
    custom: true,
  },
  {
    name: 'Status',
    key: 'type',
    custom: true,
  },
  {
    name: 'Last Modified',
    key: 'updated_at',
    isDateTime: true,
  },
];

const tableDatas = computed(() => {
  return pages.value.map((page) => {
    return {
      ...page,
      editLink: `/builder/page/${page.reference_key}/editor`,
    };
  });
});

const triggerTemplateModal = () => {
  bootstrap?.then(({ Modal }) => {
    new Modal(document.getElementById('template-modal')).show();
  });
};

const storeDomain = ref(props.dbStoreDomain);

const onlineStoreHomepageId = computed(() => {
  if (storeDomain.value) {
    return Number(storeDomain.value.type_id);
  }
  return null;
});

const triggerErrorToast = (error) => {
  console.error(error);
  $toast.error(
    'Error',
    'Something unexpected happened. Please contact our support'
  );
};

const deletePage = (id) => {
  onlineStoreAPI
    .axiosDelete(`/landing/delete/${id}`)
    .then(({ data: { status, message } }) => {
      $toast.success(status, message);
      const index = pages.value.findIndex((e) => e.id === id);
      pages.value.splice(index, 1);
    })
    .catch((error) => {
      triggerErrorToast(error);
    });
};

const updateStatus = (id) => {
  onlineStoreAPI
    .axiosPut(`/domain/${id}/home`)
    .then(({ data: { updatedRecords } }) => {
      storeDomain.value = updatedRecords;
      $toast.success('Success', 'Status updated successfully');
    })
    .catch((error) => {
      triggerErrorToast(error);
    });
};

const updateType = ({ id, is_published: isPublished }) => {
  onlineStoreAPI
    .updateStatus(id, !isPublished)
    .then(({ data: { status, message, updatedRecord } }) => {
      $toast.success(status, message);
      const index = pages.value.findIndex((e) => e.id === id);
      pages.value.splice(index, 1, {
        ...pages.value[index],
        is_published: updatedRecord.is_published ? 1 : 0,
      });
    })
    .catch((error) => {
      triggerErrorToast(error);
    });
};
</script>

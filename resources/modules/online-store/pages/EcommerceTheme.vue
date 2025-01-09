<template>
  <BaseTab
    :tabs="tabs"
    @click="toggleSectionType"
  />

  <BaseDatatable
    is-window-redirection
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    :title="selectedSectionType"
    @delete="deleteSection"
  >
    <template #action-button>
      <!-- <BaseButton
        has-add-icon
        @click="triggerTemplateModal('theme')"
      >
        Add Theme
      </BaseButton> -->
      <BaseButton
        has-add-icon
        @click="triggerTemplateModal('header')"
      >
        Add Header
      </BaseButton>
      <BaseButton
        has-add-icon
        @click="triggerTemplateModal('footer')"
      >
        Add Footer
      </BaseButton>
    </template>
    <template #cell-is_active="{ row: { is_active: isActive } }">
      <BaseBadge
        class="text-capitalize"
        :text="isActive ? 'active' : 'inactive'"
        :type="isActive ? 'success' : 'secondary'"
      />
    </template>
    <template #action-options="{ row }">
      <BaseDropdownOption
        text="View"
        :link="
          row.is_published
            ? 'https://' + storeDomain.domain + '/' + row.path
            : `/builder/${selectedSectionType}/${row.reference_key}/preview`
        "
        is-open-in-new-tab
      />
      <BaseDropdownOption
        v-if="row.is_active == false"
        text="Change to Active"
        @click="updateStatus(row.id, row.name)"
      />
    </template>
  </BaseDatatable>

  <TemplateModal :section-type="selectedTemplateType" />
</template>

<script setup>
import { onMounted, ref, computed, inject } from 'vue';
import cloneDeep from 'lodash/cloneDeep';
import TemplateModal from '@shared/components/TemplateModal.vue';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  dbHeaders: {
    type: Array,
    default: () => [],
  },
  dbFooters: {
    type: Array,
    default: () => [],
  },
});

const tabs = [
  {
    label: 'Headers',
    target: 'header',
  },
  {
    label: 'Footers',
    target: 'footer',
  },
];

const selectedSectionType = ref('header');
const selectedTemplateType = ref(null);
const headers = ref([]);
const footers = ref([]);
const $toast = inject('$toast');

onMounted(() => {
  headers.value = cloneDeep(props.dbHeaders);
  footers.value = cloneDeep(props.dbFooters);
});

const tableHeaders = [
  {
    name: 'Name',
    key: 'name',
  },
  {
    name: 'Status',
    key: 'is_active',
    custom: true,
  },
  {
    name: 'Last Modified',
    key: 'updated_at',
    isDateTime: true,
  },
];

const tableDatas = computed(() => {
  const sections =
    selectedSectionType.value === 'header' ? headers.value : footers.value;
  return sections.map((section) => {
    return {
      ...section,
      editLink: `/builder/${selectedSectionType.value}/${section.reference_key}/editor`,
    };
  });
});

const toggleSectionType = (type) => {
  selectedSectionType.value = type;
};

const triggerTemplateModal = (type) => {
  selectedTemplateType.value = type ?? selectedTemplateType.value;
  bootstrap?.then(({ Modal }) => {
    new Modal(document.getElementById('template-modal')).show();
  });
};

const triggerErrorToast = (error) => {
  console.error(error);
  $toast.error(
    'Error',
    'Something unexpected happened. Please contact our support'
  );
};

const deleteSection = (id) => {
  onlineStoreAPI
    .axiosDelete(`/online-store/delete/${id}`)
    .then(({ data: { status, message } }) => {
      $toast.success(status, message);
      const sections =
        selectedSectionType.value === 'header' ? headers.value : footers.value;
      const index = sections.findIndex((e) => e.id === id);
      sections.splice(index, 1);
    })
    .catch((error) => {
      triggerErrorToast(error);
    });
};

const updateStatus = (id, name) => {
  onlineStoreAPI
    .axiosPut(`/online-store/update/status/${id}`)
    .then(({ data: { updatedRecords } }) => {
      if (selectedSectionType.value === 'header') {
        headers.value = updatedRecords;
      } else {
        footers.value = updatedRecords;
      }
      $toast.success(
        'Success',
        `Status of ${selectedSectionType.value} ${name} updated successfully`
      );
    })
    .catch((error) => {
      triggerErrorToast(error);
    });
};
</script>

<template>
  <BaseCard
    has-header
    has-toolbar
    :title="`All ${formattedAccessList?.length} Access`"
  >
    <template #toolbar>
      <BaseButton has-add-icon @click="toggleAddStudentModal">
        Add Access
      </BaseButton>
    </template>

    <BaseDatatable
      no-header
      no-search
      no-action
      :table-headers="tableHeaders"
      :pagination-info="accessList"
      :is-pagination-redirect="false"
      :table-datas="formattedAccessList"
      title="Access List"
      @update-paginate="updatePaginate"
    >
      <template #cell-action_button="{ row }">
        <BaseButton
          type="light"
          size="sm"
          class="btn-active-light-primary"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          Actions
          <span class="svg-icon svg-icon-5 m-0 ms-1">
            <i class="fa-solid fa-angle-down" />
          </span>
        </BaseButton>
        <BaseDropdown id="datatable-actions">
          <BaseDropdownOption
            is-open-in-new-tab
            text="View Profile"
            :link="`/people/profile/${row.contactRandomId}`"
          />
          <BaseDropdownOption
            text="Remove"
            @click="removeFromAccessList(row)"
          />
        </BaseDropdown>
      </template>
      <template #cell-reason="{ row: { reason, notes } }">
        {{ reason }}
        <i
          v-if="notes"
          class="fa-solid fa-comment ms-2"
          data-bs-toggle="custom-tooltip"
          data-bs-placement="bottom"
          :title="notes"
        />
      </template>
    </BaseDatatable>
  </BaseCard>

  <AddAccessModal
    :product="product"
    :contacts="contacts"
    :existed-access-list-ids="existedAccessListIds"
    :removed-access-ids="removedAccessIds"
  />
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import cloneDeep from 'lodash/cloneDeep';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { Modal } from 'bootstrap';
import AddAccessModal from '@product/components/AddAccessModal.vue';
import eventBus from '@services/eventBus.js';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

const $toast = inject('$toast');

const props = defineProps({
  product: { type: Array, default: () => {} },
  contacts: { type: Array, default: () => [] },
  paginatedAccessList: { type: Array, default: () => [] },
  existedAccessListIds: { type: Array, default: () => [] },
});

const tableHeaders = [
  { name: 'Email', key: 'email' },
  { name: 'Joined at', key: 'join_at' },
  { name: 'Last access', key: 'last_access_at' },
  { name: 'Action', key: 'action_button', custom: true },
];

const accountTimezone = computed(
  () => usePage().props?.timezone ?? 'Asia/Kuala_Lumpur'
);

const formatDate = (date) => {
  if (!date) return '-';
  const pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
  if (!pattern.test(date)) return date;
  return dayjs(date)
    .tz(accountTimezone.value)
    .format('MMMM D, YYYY [at] h:mm a');
};

const accessList = ref(null);
const formattedAccessList = computed(() => {
  return (
    accessList.value?.data?.map((item) => {
      item.name = item.processed_contact?.displayName ?? '-';
      item.email = item.processed_contact?.email ?? '-';
      item.contactRandomId = item.processed_contact?.contactRandomId ?? '-';
      item.join_at = formatDate(item.join_at);
      item.last_access_at = formatDate(item.last_access_at);
      return item;
    }) ?? []
  );
});

const removedAccessIds = ref([]);
const removeFromAccessList = (item) => {
  axios
    .get(`/product/access/remove/${props.product.id}/?alid=${item.id}`)
    .then(({ data }) => {
      removedAccessIds.value.push(item.processed_contact_id);
      eventBus.$emit('update-access-list', data.accessList);
      $toast.success(
        'Success',
        `Successfully remove ${item.name} from ${props.product.title}`
      );
    })
    .catch(() => {
      $toast.error(
        'Error',
        `Failed to remove ${item.name} from ${props.product.title}`
      );
    });
};

const addAccessModal = ref(null);
const toggleAddStudentModal = () => {
  addAccessModal.value = new Modal(document.getElementById('add-access-modal'));
  addAccessModal.value.show();
};

const updatePaginate = (data) => {
  accessList.value = data;
};
onMounted(() => {
  accessList.value = props.paginatedAccessList;
  eventBus.$on('update-access-list', (lists) => {
    addAccessModal.value?.hide();
    accessList.value = lists;
  });
});
</script>

<style scoped lang="scss"></style>

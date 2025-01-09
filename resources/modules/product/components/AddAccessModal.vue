<template>
  <BaseModal
    :small="true"
    no-padding
    modal-id="add-access-modal"
    title="Access Lists"
  >
    <div class="position-relative overflow-auto">
      <div style="height: calc((80vh - 70px) - 70px)">
        <BaseDatatable
          title="contacts"
          no-header
          no-search
          no-sorting
          no-action
          :table-headers="tableHeader"
          :table-datas="paginatedContact?.data ?? []"
          :pagination-info="paginatedContact"
          :is-pagination-redirect="false"
          @update-paginate="updatePaginate"
        >
          <template #cell-access_checkbox="{ row: { id } }">
            <BaseFormGroup>
              <BaseFormCheckBox
                :id="`student-${id}-checkbox`"
                :value="true"
                :model-value="accessListIds.includes(id)"
                @change="selectAccess(id)"
              />
            </BaseFormGroup>
          </template>
          <template
            #cell-displayName="{ row: { contactRandomId, displayName } }"
          >
            <a :href="`/people/profile/${contactRandomId}`" target="blank">{{
              displayName
            }}</a>
          </template>
        </BaseDatatable>
      </div>
    </div>
    <template #footer>
      <BaseButton @click="addAccess" :disabled="isLoading">
        {{ isLoading ? 'Loading...' : 'Add' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import eventBus from '@services/eventBus.js';

const props = defineProps({
  product: { type: Object, default: () => {} },
  contacts: { type: Array, default: () => [] },
  existedAccessListIds: { type: Array, default: () => [] },
  removedAccessIds: { type: Array, default: () => [] },
});

const paginatedContact = ref(null);
const accessListIds = ref([]);

const tableHeader = [
  { name: '', key: 'access_checkbox', custom: true },
  { name: 'Name', key: 'displayName', custom: true },
  { name: 'Email', key: 'email' },
];

onMounted(() => {
  paginatedContact.value = props.contacts;
  accessListIds.value = props.existedAccessListIds;
});

watch(props.removedStudentIds, (val) => {
  accessListIds.value = accessListIds.value.filter(
    (e) => !props.removedAccessIds.includes(e)
  );
});

const selectAccess = (id) => {
  const index = accessListIds.value.findIndex((e) => e === id);
  if (index >= 0) {
    accessListIds.value.splice(index, 1);
    return;
  }
  accessListIds.value.push(id);
};

const updatePaginate = (data) => {
  paginatedContact.value = data;
};
const isLoading = ref(false);
const addAccess = () => {
  isLoading.value = true;
  axios
    .post('/product/access/add', {
      productId: props.product.id,
      accessListIds: accessListIds.value,
    })
    .then(({ data }) => {
      eventBus.$emit('update-access-list', data.accessList);
    })
    .finally(() => {
      isLoading.value = false;
    });
};
</script>

<style lang="scss" scoped></style>

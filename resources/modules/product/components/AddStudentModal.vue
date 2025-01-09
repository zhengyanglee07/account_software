<template>
  <BaseModal
    :small="true"
    no-padding
    modal-id="add-student-modal"
    title="Students"
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
          <template #cell-student_checkbox="{ row: { id } }">
            <BaseFormGroup>
              <BaseFormCheckBox
                :id="`student-${id}-checkbox`"
                :value="true"
                :model-value="studentIds.includes(id)"
                @change="selectStudent(id)"
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
      <BaseButton @click="addStudent" :disabled="isLoading">
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
  course: { type: Object, default: () => {} },
  contacts: { type: Array, default: () => [] },
  existedStudentIds: { type: Array, default: () => [] },
  removedStudentIds: { type: Array, default: () => [] },
});

const paginatedContact = ref(null);
const studentIds = ref([]);

const tableHeader = [
  { name: '', key: 'student_checkbox', custom: true },
  { name: 'Name', key: 'displayName', custom: true },
  { name: 'Email', key: 'email' },
];

onMounted(() => {
  paginatedContact.value = props.contacts;
  studentIds.value = props.existedStudentIds;
});

watch(props.removedStudentIds, (val) => {
  studentIds.value = studentIds.value.filter(
    (e) => !props.removedStudentIds.includes(e)
  );
});

const selectStudent = (id) => {
  const index = studentIds.value.findIndex((e) => e === id);
  if (index >= 0) {
    studentIds.value.splice(index, 1);
    return;
  }
  studentIds.value.push(id);
};

const updatePaginate = (data) => {
  paginatedContact.value = data;
};
const isLoading = ref(false);
const addStudent = () => {
  isLoading.value = true;
  axios
    .post('/course/add/student', {
      courseId: props.course.id,
      studentIds: studentIds.value,
    })
    .then(({ data }) => {
      eventBus.$emit('update-course-student', data.students);
    })
    .finally(() => {
      isLoading.value = false;
    });
};
</script>

<style lang="scss" scoped></style>

<template>
  <BaseCard
    has-header
    has-toolbar
    :title="`All ${studentList.length} Students`"
  >
    <template #toolbar>
      <BaseButton has-add-icon @click="toggleAddStudentModal">
        Add Student
      </BaseButton>
    </template>

    <BaseDatatable
      no-header
      no-search
      no-action
      :table-headers="tableHeaders"
      :pagination-info="paginatedStudent"
      :is-pagination-redirect="false"
      :table-datas="students"
      title="Student"
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
          <BaseDropdownOption text="Remove" @click="removeFromCourse(row)" />
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

  <AddStudentModal
    :course="course"
    :contacts="contacts"
    :existed-student-ids="existedStudentIds"
    :removed-student-ids="removedStudentIds"
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
import AddStudentModal from '@product/components/AddStudentModal.vue';
import eventBus from '@services/eventBus.js';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

const $toast = inject('$toast');

const props = defineProps({
  course: { type: Object, default: () => {} },
  contacts: { type: Array, default: () => [] },
  existedStudentIds: { type: Array, default: () => [] },
});

const tableHeaders = [
  { name: 'Email', key: 'email' },
  { name: 'Progress', key: 'progress' },
  { name: 'Joined at', key: 'joined_at' },
  { name: 'Last access', key: 'last_access' },
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

const paginatedStudent = ref(null);
const studentList = ref([]);
const students = computed(() => {
  return studentList.value?.map((item) => {
    item.contactRandomId = item.processed_contact?.contactRandomId ?? '-';
    item.progress = `${Math.round(
      (item.totalCompleted / item.totalLesson) * 100
    )} %`;
    item.joined_at = formatDate(item.join_at);
    item.last_access_at = formatDate(item.last_access_at);
    return item;
  });
});

const removedStudentIds = ref([]);
const removeFromCourse = (item) => {
  axios
    .get(`/course/people/remove/${props.course.id}/?csid=${item.id}`)
    .then(({ data }) => {
      removedStudentIds.value.push(item.processed_contact_id);
      eventBus.$emit('update-course-student', data.students);
      $toast.success(
        'Success',
        `Successfully remove ${item.name} from ${props.course.title}`
      );
    })
    .catch(() => {
      $toast.error(
        'Error',
        `Failed to remove ${item.name} from ${props.course.title}`
      );
    });
};

const addStudentModal = ref(null);
const toggleAddStudentModal = () => {
  addStudentModal.value = new Modal(
    document.getElementById('add-student-modal')
  );
  addStudentModal.value.show();
};

const updatePaginate = (data) => {
  paginatedStudent.value = data;
  studentList.value = data.data;
};
onMounted(() => {
  paginatedStudent.value = props.course.courseStudent;
  studentList.value = paginatedStudent.value.data;
  eventBus.$on('update-course-student', (student) => {
    addStudentModal.value?.hide();
    paginatedStudent.value = student;
    studentList.value = student.data;
  });
});
</script>

<style scoped lang="scss"></style>

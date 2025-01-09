<template>
  <BaseDatatable
    no-header
    no-search
    no-action
    :table-headers="tableHeaders"
    :table-datas="courses"
    title="Courses"
  >
    <template
      #cell-action_button="{ row: { id, processed_contact, products } }"
    >
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
          text="View Course"
          :link="`${productPagePrefixUrl}/${products?.path}`"
        />
        <BaseDropdownOption
          text="Remove"
          @click="removeFromCourse(id, processed_contact, products)"
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
</template>

<script setup>
import { computed, inject } from 'vue';
import { usePage } from '@inertiajs/vue3';
import cloneDeep from 'lodash/cloneDeep';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

const $toast = inject('$toast');

const props = defineProps({
  students: { type: Object, default: () => {} },
});

const accountTimezone = computed(
  () => usePage().props?.timezone ?? 'Asia/Kuala_Lumpur'
);

const productPagePrefixUrl = computed(
  () => `${usePage().props?.onlineStoreDomain}/products`
);

const formatDate = (date) => {
  if (!date) return '-';
  return dayjs(date)
    .tz(accountTimezone.value)
    .format('MMMM D, YYYY [at] h:mm a');
};

const tableHeaders = [
  { name: 'Course', key: 'course_title' },
  { name: 'Progress', key: 'progress' },
  { name: 'Joined at', key: 'join_at' },
  { name: 'Last access', key: 'last_access_at' },
  { name: 'Action', key: 'action_button', custom: true },
];

const courses = computed(() =>
  props.students?.data?.map((item) => {
    item.course_title = item.products.title;
    item.progress = `${Math.round(
      (item.totalCompleted / item.totalLesson) * 100
    )} %`;
    item.join_at = formatDate(item.join_at);
    item.last_access_at = formatDate(item.last_access_at);
    return item;
  })
);

const removeFromCourse = (studentId, processedContact, products) => {
  axios
    .get(`/course/people/remove/?csid=${studentId}`)
    .then(() => {
      $toast.success(
        'Success',
        `Successfully remove ${processedContact.displayName} from ${products.title}`
      );
      window.location.reload();
    })
    .catch(() => {
      $toast.error(
        'Error',
        `Failed to remove ${processedContact.displayName} from ${products.title}`
      );
    });
};
</script>

<style scoped lang="scss"></style>

<template>
  <BasePageLayout
    :page-name="formName"
    back-to="/people/forms"
    is-setting
  >
    <BaseDatatable
      title="data"
      no-hover
      no-edit-action
      no-delete-action
      :table-headers="tableHeaders"
      :table-datas="formData"
    >
      <template #action-button>
        <BaseButton
          type="secondary"
          is-open-in-new-tab
          :href="`/form/${referenceKey}/download`"
        >
          <i class="fa-solid fa-download me-2" />
          <span> Export to CSV </span>
        </BaseButton>
      </template>
      <template #action-options="{ row: { refKey } }">
        <BaseDropdownOption
          text="Delete"
          @click="deleteFormContent(refKey)"
        />
      </template>
    </BaseDatatable>
  </BasePageLayout>
</template>

<script setup>
/* eslint camelcase: 1 */
/* eslint no-restricted-syntax: 1 */
import { ref, computed, inject, onMounted } from 'vue';
import cloneDeep from 'lodash/cloneDeep';
import axios from 'axios';

const props = defineProps({
  labelNames: {
    type: Object,
    default: () => ({}),
  },
  dbFormContents: {
    type: Object,
    default: () => ({}),
  },
  formName: {
    type: String,
    required: true,
  },
  referenceKey: {
    type: String,
    required: true,
  },
});

const formContents = ref({});

const tableHeaders = computed(() => {
  const labels = Object.values(props.labelNames);
  return [...labels, 'Submitted At'].map((label) => {
    const obj = {
      name: label,
      key: label,
    };
    if (label === 'Submitted At') {
      return { ...obj, isDateTime: true };
    }
    return obj;
  });
});

const formData = computed(() => {
  const formdata = [];
  for (const recordsArray of Object.values(formContents.value)) {
    const obj = recordsArray.reduce((accObj, current) => {
      const labelId = current.landing_page_form_label_id;
      const labelName = props.labelNames[labelId];
      accObj[labelName] = current.landing_page_form_content;
      return accObj;
    }, {});
    const { created_at: createdAt, reference_key: refKey } = recordsArray[0];
    formdata.unshift({
      ...obj,
      'Submitted At': createdAt,
      refKey,
    });
  }
  return formdata;
});

onMounted(() => {
  formContents.value = cloneDeep(props.dbFormContents);
});

const $toast = inject('$toast');

const deleteFormContent = (refKey) => {
  // eslint-disable-next-line no-restricted-globals
  const answer = confirm('Are you sure want to delete this record?');
  if (answer) {
    axios
      .delete(`/form-content/${refKey}`)
      .then(({ data: { status, message } }) => {
        formContents.value = cloneDeep(
          Object.fromEntries(
            Object.entries(formContents.value).filter(([key]) => key !== refKey)
          )
        );
        $toast.success(status, message);
      })
      .catch((err) => {
        $toast.error(
          'Error',
          'Something went wrong. Please contact out support.'
        );
        throw new Error(err);
      });
  }
};
</script>

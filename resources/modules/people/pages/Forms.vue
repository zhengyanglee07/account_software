<template>
  <BaseDatatable
    title="form"
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
    :pagination-info="paginatedForms"
    is-server-side-search
    @delete="deleteForm"
  >
    <template #cell-pageName="{ row: { pageName, editorUrl, isDeleted } }">
      <p 
        v-if="isDeleted || !editorUrl"
        class="text-muted fst-italic mb-0"
      >
        {{ pageName }} <span v-if="editorUrl">[Deleted]</span>
      </p>
      <BaseButton
        v-else
        type="link"
        is-open-in-new-tab
        :href="editorUrl ?? '#'"
      >
        {{ pageName }}
        <i 
          class="fa-solid fa-up-right-from-square ms-2" 
        />
      </BaseButton>
    </template>
    <template #action-options="{ row: { id, viewLink } }">
      <BaseDropdownOption
        text="View"
        :link="viewLink"
      />
      <BaseDropdownOption
        text="Rename"
        @click="triggerRenameFormModal(id)"
      />
    </template>
  </BaseDatatable>

  <BaseModal
    modal-id="rename-form-modal"
    :small="true"
    title="Rename Form"
  >
    <BaseFormGroup
      for="rename-form-name"
      label="Form Name"
    >
      <BaseFormInput
        id="rename-form-name"
        v-model="state.formName"
        type="text"
      />
      <template
        v-if="showErr && v$.formName.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="renameForm">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import axios from 'axios';
import { Modal } from 'bootstrap';
import BaseModal from '@shared/components/BaseModal.vue';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators'
import { ref, computed, inject, onMounted, reactive } from 'vue';

const props = defineProps({
  paginatedForms: {
    type: Object,
    default: () => {},
  },
});

const tableHeaders = [
  {
    name: 'Form Title',
    key: 'title',
  },
  {
    name: 'Submissions',
    key: 'submission',
  },
  {
    name: "Page",
    key: 'pageName',
    custom: true,
  },
  {
    name: 'Last Updated',
    key: 'updated_at',
    isDateTime: true,
  },
];

const state = reactive({
  formName: '',
})
const rules = {
  formName : { 
    required 
  },
}

const v$ = useVuelidate(rules, state);

const forms = ref(props.paginatedForms);

const tableDatas = computed(() => {
  return forms.value.data.map((form) => {
    return {
      ...form,
      viewLink: `/form/${form.reference_key}`,
    };
  });
});

const $toast = inject('$toast');

const deleteForm = (id) => {
  axios
    .delete(`/form/${id}`)
    .then(({ data: { status, message } }) => {
      $toast.success(status, message);
      const index = forms.value.data.findIndex((e) => e.id === id);
      forms.value.data.splice(index, 1);
    })
    .catch((err) => {
      $toast.error(
        'Error',
        'Something went wrong. Please contact out support.'
      );
      throw new Error(err);
    });
};

const showErr = ref(false);
const editingFormId = ref(null);
const renameFormModal = ref(null);

const triggerRenameFormModal = (id) => {
  const form = forms.value.data.find(e => e.id === id)
  editingFormId.value = id;
  state.formName = form.title;
  renameFormModal.value.show();
}

const renameForm = () => {
  if (v$.value.$invalid) {
    showErr.value = true;
    return;
  }
  axios
    .put(`/form/${editingFormId.value}/rename`, {
      title: state.formName
    })
    .then(({ data: { status, message } }) => {
      $toast.success('Success', 'Form name successfully updated');
      const index = forms.value.data.findIndex(e => e.id === editingFormId.value)
      forms.value.data[index].title = state.formName
      state.formName = null;
      showErr.value = false;
      editingFormId.value = null;
      renameFormModal.value.hide()
    })
    .catch((err) => {
      $toast.error(
        'Error',
        'Something went wrong. Please contact out support.'
      );
      throw new Error(err);
    });
}

onMounted(() => {
  renameFormModal.value = new Modal(document.getElementById('rename-form-modal'));
})
</script>

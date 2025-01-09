<template>
  <BaseDatatable
    no-hover
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableCustomFieldData"
    title="Custom Field"
    @delete="removeRecord"
  >
    <template #action-button>
      <BaseButton
        id="add-customfield-button"
        has-add-icon
        @click="toggleAddCustomField"
      >
        Add Custom Field
      </BaseButton>
    </template>

    <template #action-options="{ row: { id } }">
      <BaseDropdownOption
        text="Edit"
        data-bs-toggle="modal"
        data-bs-target="#renameCustomFieldModal"
        @click="customFieldId = id"
      />
    </template>
  </BaseDatatable>

  <BaseModal
    modal-id="renameCustomFieldModal"
    title="Rename Custom Field"
  >
    <BaseFormGroup
      for="rename-custom-field-input"
      label="New custom field name"
      description="Only a-z, 0-9 and _ are allowed"
    >
      <BaseFormInput
        id="rename-custom-field-input"
        v-model="customFieldName"
        type="text"
        @input="clearErrorMessage"
      />

      <template
        v-if="customFieldNameErrorMessage"
        #error-message
      >
        {{ customFieldNameErrorMessage }}
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="handleRenameCustomField">
        Save
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    modal-id="addCustomFieldModal"
    title="Add Custom Field"
  >
    <BaseFormGroup
      for="add-new-custom-field-input"
      label="Custom field name"
      description="Only a-z, 0-9 and _ are allowed"
    >
      <BaseFormInput
        id="add-new-custom-field-input"
        v-model="customFieldName"
        type="text"
        @input="clearErrorMessage"
      />

      <template
        v-if="customFieldNameErrorMessage"
        #error-message
      >
        {{ customFieldNameErrorMessage }}
      </template>
    </BaseFormGroup>

    <BaseFormGroup label="Select your custom field type">
      <BaseFormSelect v-model="customFieldType">
        <option
          value=""
          disabled
        >
          Choose...
        </option>
        <option value="text">
          Text
        </option>
        <option value="email">
          Email
        </option>
        <option value="date">
          Date
        </option>
        <option value="datetime">
          Date and Time
        </option>
        <option value="number">
          Number
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="handleCreateCustomField">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
/* eslint-disable no-unused-expressions */
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { Modal, Tooltip } from 'bootstrap';
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  props: {
    allCustomFields: {
      type: Array,
      default() {
        return [];
      },
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      customFieldId: '',
      customFieldName: '',
      customFieldType: 'text',
      customFields: [],
      customFieldNameErrorMessage: null,

      tableHeaders: [
        { name: 'Name', key: 'customFieldName' },
        { name: 'Type', key: 'type' },
      ],

      modal: null,
      modalId: 'people-custom-delete-modal',
    };
  },
  validations: {
    customFieldName: {
      required,
    },
    customFieldType: {
      required,
    },
  },
  computed: {
    tableCustomFieldData() {
      return this.customFields.map((customField) => ({
        id: customField.id,
        type: customField.type,
        customFieldName: customField.custom_field_name,
      }));
    },
  },

  watch: {
    customFieldId(id) {
      this.customFieldName = this.customFields.filter(
        (cf) => cf.id === id
      )[0]?.custom_field_name;
    },
  },

  beforeMount() {
    this.customFields = [...this.allCustomFields];
  },

  mounted() {
    eventBus.$on('base-modal-mounted', () => {
      const renameCFModal = document.getElementById('renameCustomFieldModal');
      renameCFModal?.addEventListener('hidden.bs.modal', () => {
        this.customFieldNameErrorMessage = null;
        this.customFieldId = '';
      });
      const addCFModal = document.getElementById('addCustomFieldModal');
      addCFModal?.addEventListener('hidden.bs.modal', () => {
        this.customFieldNameErrorMessage = null;
        this.customFieldName = '';
      });
    });
  },

  methods: {
    clearErrorMessage() {
      this.customFieldNameErrorMessage = null;
    },

    handleRenameCustomField() {
      const regexValidation = /^[a-z0-9_]*$/;

      if (this.v$.customFieldName.$invalid) {
        this.customFieldNameErrorMessage = 'Name is required';
      }

      if (!regexValidation.test(this.customFieldName)) {
        this.customFieldNameErrorMessage = 'Only a-z, 0-9 and _ are allowed';
        return;
      }

      this.$axios
        .post('/people/profile/renameCustomField', {
          customFieldName: this.customFieldName,
          oldCustomFieldId: this.customFieldId,
        })
        .then(() => {
          this.$toast.success('Success', 'Custom Field Successfully Renamed!');
          Modal.getInstance(
            document.getElementById('renameCustomFieldModal')
          ).hide();

          this.customFields = this.customFields.map((cf) => {
            if (cf.id !== this.customFieldId) return cf;

            return {
              ...cf,
              custom_field_name: this.customFieldName,
            };
          });
        })
        .catch((error) => {
          validationFailedNotification(error);
        });
    },

    handleCreateCustomField() {
      const regexValidation = /^[a-z0-9_]*$/;

      if (
        this.v$.customFieldName.$invalid ||
        this.v$.customFieldType.$invalid
      ) {
        this.customFieldNameErrorMessage = 'Name is required';
        return;
      }

      if (!regexValidation.test(this.customFieldName)) {
        this.customFieldNameErrorMessage = 'Only a-z, 0-9 and _ are allowed';
        return;
      }

      this.$axios
        .post('/people/profile/newCustomField', {
          customFieldInput: this.customFieldName,
          customFieldType: this.customFieldType,
        })
        .then((response) => {
          this.$toast.success('Success', 'Custom Field Successfully Added');

          this.customFields = [...this.customFields, response.data.customField];
          Modal.getInstance(
            document.getElementById('addCustomFieldModal')
          ).hide();
        })
        .catch((error) => {
          validationFailedNotification(error);
        });
    },

    toggleAddCustomField() {
      this.addCustomFieldModal = new Modal(
        document.getElementById('addCustomFieldModal')
      );
      this.addCustomFieldModal.show();
    },

    removeRecord(customFields) {
      this.deletionError = false;

      this.$axios
        .post('/people/profile/deleteCustomField', {
          customFieldId: customFields,
        })
        .then((response) => {
          this.$toast.success('Success', 'Successfully deleted Custom Field.');
          this.$inertia.visit('/people/custom-fields');
        })
        .catch((err) => {
          console.error(err);
          this.$toast.error('Error', 'Failed to delete Custom Field');
        });
    },
  },
};
</script>

<style lang="scss" scoped></style>

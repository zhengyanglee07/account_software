<template>
  <div>
    <BaseModal
      :modal-id="modalId"
      :small="true"
      title="Upload File"
    >
      <BaseFormGroup
        for="csv_file"
        label="CSV file to import"
        description="Maximum upload file size is 5MB"
      >
        <!-- did not use base component, due to issue in getting refs -->
        <input
          id="csv_file"
          ref="csvFileRef"
          type="file"
          class="form-control"
          name="csv_file"
          accept=".csv, .xlsx"
        >
        <template
          v-if="showError.show"
          #error-message
        >
          {{ showError.message }}
        </template>
      </BaseFormGroup>

      <div class="form-group d-none">
        <div class="col-md-6 col-md-offset-2">
          <div
            class="checkbox"
            style="margin-left: -40px; margin-top: -10px"
          >
            <label>
              <input
                ref="headerCheckboxRef"
                type="checkbox"
                class="black-checkbox"
                name="header"
                checked
              >
              <span>File contains header row?</span>
            </label>
          </div>
        </div>
      </div>

      <template #footer>
        <BaseButton
          class="primary-small-square-button px-5"
          :disabled="uploading"
          @click="uploadImportFile"
        >
          Import
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'UploadPeopleCsvModal',

  props: {
    modalId: String,
    permissionData: Object,
  },
  data() {
    return {
      showError: { show: false, message: '' },
      showLimitModal: false,
      uploading: false,
    };
  },
  methods: {
    uploadImportFile() {
      const csvFile = this.$refs.csvFileRef.files[0];

      if (!csvFile || csvFile.size > 5000000) {
        this.showError = {
          show: true,
          message: !csvFile
            ? 'Please choose a file'
            : 'File size exceeds maximum limit 5MB.',
        };
        return;
      }

      const header = this.$refs.headerCheckboxRef.checked;
      const formData = new FormData();
      formData.append('csv_file', csvFile);
      formData.append('header', header);

      const planDetail =
        this.permissionData.permissionDetail[this.permissionData.currentPlan];

      const peopleLimit = Object.values(planDetail).find(
        (detail) => detail.context === 'people'
      ).max;

      this.uploading = true;

      this.$axios
        .post('/import_parse', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        .then(() => {
          Modal.getInstance(document.getElementById(this.modalId)).hide();
          this.$inertia.visit('/import_fields');
        })
        .catch((error) => {
          //   this.showLimitModal = error.response.data.exceed_limit;
          if (error.response?.data.status === 'exceed-limit') {
            this.$toast.error(
              'Error',
              `Contacts in the file exceeded ${peopleLimit}. Please remove unwanted contacts in the file`
            );
          } else {
            this.$toast.error('Error', 'Failed to import CSV');
          }
        })
        .finally(() => {
          this.uploading = false;
        });
    },
  },
};
</script>

<style scoped lang="scss">
.m-container__input {
  padding-left: 0;
}

.m-container__input::-webkit-file-upload-button {
  border: none;
  background-color: #fff;
  height: 100%;
  margin-right: 12px;
  border-right: 1px solid #ced4da;

  &:hover {
    cursor: pointer;
  }
}
</style>

<template>
  <div class="row align-items-center mb-4">
    <div class="col-md-4">
      <BaseButton
        has-add-icon
        class="w-150px fs-7"
        @click="triggerCreateModal"
      >
        Add Template
      </BaseButton>
      <!-- <BaseButton
        :disabled="(bulkEditTemplateIds ?? []).length === 0"
        class="ms-2 fs-7"
        has-edit-icon
        @click="toggleBulkEditModal(true)"
      >
        Bulk Edit
        <span
          v-if="(bulkEditTemplateIds ?? []).length > 0"
          class="ms-1"
        >
          {{ bulkEditTemplateIds.length }} templates
        </span>
      </BaseButton> -->
      <!-- <BaseButton
        has-add-icon
        disabled
        class="w-200px fs-7 ms-2"
        @click="triggerThemeTemplate"
      >
        Add Theme Template
      </BaseButton> -->
    </div>
    <div class="col-md-3">
      <div class="input-group mt-1 mb-1">
        <BaseFormInput
          v-model="searchInputSection"
          type="search"
          placeholder="Search"
        >
          <template #prepend>
            <i class="fas fa-search gray_icon" />
          </template>
        </BaseFormInput>
      </div>
    </div>
    <div class="col-md-5 d-flex">
      <BaseFormSelect
        v-model="tagFilter"
        data-live-search="true"
        required
      >
        <option
          value=""
          selected
          disabled
          hidden
        >
          Tag
        </option>
        <optgroup label="All">
          <option
            value=""
            data-tokens="All"
          >
            All
          </option>
        </optgroup>
        <optgroup label="Section">
          <option
            v-for="(section, index) in sectionTags"
            :key="index"
            :data-tokens="section"
          >
            {{ section }}
          </option>
        </optgroup>
        <optgroup label="Header">
          <option
            v-for="(header, index) in headerTags"
            :key="index"
            :data-tokens="header"
          >
            {{ header }}
          </option>
        </optgroup>
        <optgroup label="Page">
          <option
            v-for="(page, index) in pageTags"
            :key="index"
            :data-tokens="page"
          >
            {{ page }}
          </option>
        </optgroup>
        <optgroup label="Footer">
          <option
            v-for="(footer, index) in footerTags"
            :key="index"
            :data-tokens="footer"
          >
            {{ footer }}
          </option>
        </optgroup>
        <optgroup label="Email">
          <option
            v-for="(footer, index) in footerTags"
            :key="index"
            :data-tokens="footer"
          >
            {{ footer }}
          </option>
        </optgroup>
        <optgroup label="Theme">
          <option
            value=""
            data-tokens="All"
          >
            All
          </option>
        </optgroup>
      </BaseFormSelect>
      <BaseFormSelect
        v-model="statusFilter"
        class="ms-2"
        required
      >
        <option
          selected
          disabled
        >
          Status
        </option>
        <option
          value=""
        >
          All
        </option>
        <option
          v-for="({ label, value }, index) in statusList"
          :key="index"
          :value="value"
        >
          {{ label }}
        </option>
      </BaseFormSelect>
      <BaseFormSelect
        v-model="typeFilter"
        class="ms-2"
      >
        <option
          value=""
          selected
          disabled
          hidden
        >
          Type
        </option>
        <option
          value=""
          data-tokens="All"
        >
          All
        </option>
        <option
          v-for="(type, index) in typeList"
          :key="index"
          :value="type"
          :data-tokens="type"
        >
          {{ type }}
        </option>
      </BaseFormSelect>
    </div>
  </div>

  <div v-if="allTemplate.length === 0">
    <EmptyDataContainer />
  </div>
  <div
    v-else
    class="mansory-container"
  >
    <div
      v-for="template in filteredTemplates"
      :key="template.id"
      class="mansory-container__item"
    >
      <div
        class="mansory-container__preview"
        :style="{
          backgroundImage: `url(${template.image_path})`,
        }"
      >
        <template v-if="template.type !== 'email'">
          <img
            v-if="template.type !== 'page'"
            :src="`${template.image_path}?${template.updated_at}`"
            class="w-100"
          >
          <div
            v-else
            :style="{
              'background-image': `url('${template.image_path}?${template.updated_at}')`,
            }"
            style="min-height: 200px; background-size: cover"
          />
        </template>
        <div class="mansory-container__overlay">
          <BaseButton
            class="template-button"
            @click="triggerEditDetailsModal(template.id, template.type)"
          >
            <i
              class="fas fa-edit"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Edit Details"
            />
          </BaseButton>
          <BaseButton
            class="template-button"
            is-window-redirection
            :href="template.builder_url"
          >
            <i
              class="fas fa-paint-brush"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Edit Design"
            />
          </BaseButton>
          <BaseButton
            :href="template.preview_url"
            class="template-button"
            title="Preview Design"
            is-open-in-new-tab
          >
            <i class="fas fa-eye" />
          </BaseButton>
          <BaseButton
            class="template-button"
            @click="toggleDeleteModal(template.id, template.type)"
          >
            <i
              class="fas fa-trash"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Delete"
            />
          </BaseButton>
        </div>
      </div>
      <p class="template-title">
        {{ template.name }}
      </p>
      <div class="d-flex justify-content-center align-items-center pb-1">
        <!-- <input
          :id="`template-${template.id}`"
          v-model="bulkEditTemplateIds"
          class="me-3 mt-2 w-15px h-15px"
          type="checkbox"
          :value="template.id"
        > -->
        <BaseBadge
          class="mt-0"
          :type="template.is_published ? 'success' : 'secondary'"
          :text="template.is_published ? 'Published' : 'Draft'"
        />
        <BaseBadge
          class="mt-0 text-capitalize"
          type="info"
          :text="template.type"
        />
      </div>
    </div>
  </div>

  <BaseModal
    modal-id="template-bulk-edit-modal"
    title="Bulk Edit Templates"
    no-dismiss
  >
    <BaseFormGroup
      for="template-status"
      label="Template status :"
    >
      <BaseFormRadio
        v-model="bulkEditTemplateStatus"
        value="Draft"
      >
        Draft
      </BaseFormRadio>
      <BaseFormRadio
        v-model="bulkEditTemplateStatus"
        value="Publish"
      >
        Publish
      </BaseFormRadio>
    </BaseFormGroup>

    <div class="separator mb-5" />

    <BaseFormGroup
      :label="`Selected ${bulkEditTemplateIds.length} templates: `"
    >
      <ul class="list-group">
        <li
          v-for="template in bulkEditTemplates"
          :key="template"
          class="list-group-item d-flex"
        >
          <a
            class="me-2"
            target="_blank"
            :href="template.coverImage"
          >
            <i class="fa-solid fa-image" />
          </a>
          <a
            target="_blank"
            :href="`/builder/template/${template.id}/preview`"
            class="me-2 w-200px text-truncate mb-0"
          >
            {{ template.name }}
          </a>
          <div class="ms-auto w-150px text-end">
            <span
              class="badge mb-0 me-2"
              :class="`badge-${
                template.is_published ? 'success' : 'secondary'
              }`"
            >
              {{ template.is_published ? 'Published' : 'Draft' }}
            </span>
            <span class="badge mb-0 text-capitalize badge-info">
              {{ template.type }}
            </span>
          </div>
        </li>
      </ul>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        type="light"
        data-bs-dismiss="modal"
        @click="
          bulkEditTemplateIds = [];
          toggleBulkEditModal(false);
        "
      >
        Dismiss
      </BaseButton>
      <BaseButton @click="bulkEditTemplate">
        Save
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    no-dismiss
    modal-id="templateModal"
    :title="
      currentEditId === null ? 'Add New Template' : 'Edit Template Details'
    "
  >
    <div class="px-2">
      <BaseFormGroup
        for="add-new-tag-input"
        label="Template name"
      >
        <BaseFormInput
          id="add-new-tag-input"
          v-model="templateName"
          type="text"
          @input="clearError"
        />
        <template
          v-if="showErr && v$.templateName.required.$invalid"
          #error-message
        >
          Name is required
        </template>
      </BaseFormGroup>
      <BaseFormGroup
        for="add-new-tag-input"
        label="Template type :"
      >
        <div
          v-show="templateType !== 'theme'"
          class="row w-100"
        >
          <div class="col-md-6">
            <BaseFormRadio
              v-model="templateType"
              value="section"
            >
              Section
            </BaseFormRadio>
            <BaseFormRadio
              v-model="templateType"
              value="page"
            >
              Page / Landing Page
            </BaseFormRadio>
            <!-- <BaseFormRadio
              v-model="templateType"
              value="email"
            >
              Email
            </BaseFormRadio> -->
          </div>
          <div class="col-md-6 p-0">
            <BaseFormRadio
              v-model="templateType"
              value="header"
            >
              Header
            </BaseFormRadio>
            <BaseFormRadio
              v-model="templateType"
              value="footer"
            >
              Footer
            </BaseFormRadio>
          </div>
        </div>
        <!-- <div
          v-show="templateType === 'theme'"
          class="row w-100"
        >
          <div class="col-md-6">
            <BaseFormRadio
              v-model="templateType"
              value="theme"
            >
              Theme
            </BaseFormRadio>
          </div>
        </div> -->
      </BaseFormGroup>

      <BaseFormGroup
        for="add-new-tag-input"
        label="Template status :"
      >
        <BaseFormRadio
          v-model.number="templateStatus"
          :value="0"
        >
          Draft
        </BaseFormRadio>
      </BaseFormGroup>
      <div v-if="currentEditId !== null">
        <BaseFormRadio
          v-model.number="templateStatus"
          :value="1"
        >
          Published
        </BaseFormRadio>
      </div>
      <BaseFormGroup
        for="add-new-tag-input"
        label="Template tags :"
      >
        <BaseMultiSelect
          v-model="templateTags"
          :options="tags"
          multiple
          taggable
        />
      </BaseFormGroup>
    </div>

    <template #footer>
      <BaseButton
        type="light"
        data-bs-dismiss="modal"
        @click="
          clearInput;
          closeModal();
        "
      >
        Dismiss
      </BaseButton>
      <BaseButton @click="determineSaveFunction">
        Save
      </BaseButton>
    </template>
  </BaseModal>

  <DeleteConfirmationModal
    title="Template"
    @save="deleteTemplate"
    @cancel="toggleDeleteModal()"
  />

  <ThemeTemplateModal
    v-if="allTemplate"
    :all-templates="allTemplate"
  />
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import ThemeTemplateModal from '@template/components/ThemeTemplateModal.vue';
import { Modal } from 'bootstrap';
import templateAPI from '@template/api/templateAPI.js';
import BaseModal from '@shared/components/BaseModal.vue';

export default {
  name: 'AllTemplate',

  components: {
    BaseModal,
    ThemeTemplateModal,
  },

  props: {
    allTemplates: { type: Object, default: () => {} },
  },

  setup() {
    return { v$: useVuelidate() };
  },

  data() {
    return {
      showErr: false,
      allTemplate: [],
      templateName: null,
      templateType: 'section',
      templateTags: ['All'],
      templateStatus: 0,
      templateDetailModal: null,
      deleteModal: null,
      currentEditId: null,
      bulkEditTemplateIds: [],
      bulkEditTemplateModal: null,
      bulkEditTemplateStatus: 'Draft',
      pageTags: [
        'All',
        'Arts & Design',
        'Automative & Cars',
        'Beauty & Wellness',
        'Consulting & Coaching',
        'Education',
        'Fashion & Accessories',
        'Farming & Gardening',
        'Finance & Law',
        'Food & Beverage',
        'Home & Decor',
        'Property & Real Estate',
        'Service & Maintenance',
        'Sports & Ourdoors',
        'Society',
      ],
      sectionTags: [
        'All',
        'Achievement / Statistic',
        'About / Story',
        'Brands / Partners',
        'Countdown',
        'Contact',
        'Call To Action',
        'FAQ',
        'Feature',
        'Pricing',
        'Professional Profile',
        'Services',
        'Testimonial',
      ],
      headerTags: ['All'],
      footerTags: ['All'],
      typeFilter: '', // dropdown input
      statusFilter: "", // dropdown input
      tagFilter: 'All', // dropdown input
      searchInputSection: '',
      typeList: [
        'Section', 
        'Header', 
        'Footer', 
        'Page', 
        // 'Email', 
        // 'Theme',
      ],
      statusList: [
        {
          label: 'Draft',
          value: 0,
        },
        {
          label: 'Publish',
          value: 1,
        },
      ],
    };
  },

  validations: {
    templateName: { required },
  },

  computed: {
    filteredTemplates() {
      return (this.allTemplate ?? []).filter((template) => {
        const isMatchType = template.type.includes(this.typeFilter.toLowerCase());
        const isMatchStatus = typeof this.statusFilter === 'string' ? true : template.is_published === this.statusFilter;
        const isMatchTag = template.tags.includes(this.tagFilter);
        const isMatchSearch = (template?.name ?? "")
          .toLowerCase()
          .includes(this.searchInputSection.toLowerCase());
        return isMatchType && isMatchStatus && isMatchTag && isMatchSearch;
      });
    },

    tags() {
      switch (this.templateType) {
        case 'page':
          return this.pageTags;
        case 'header':
          return this.headerTags;
        case 'footer':
          return this.footerTags;
        // case 'email':
        //   return this.footerTags;
        default:
          return this.sectionTags;
      }
    },

    emailTemplatePreviewContainerWidth() {
      if (!document.querySelector('.page-container__page--responsive-height'))
        return 250;
      const previewContainer = document.querySelector(
        '.page-container__page--responsive-height'
      );
      const { paddingLeft, paddingRight } =
        window.getComputedStyle(previewContainer);
      return (
        previewContainer.clientWidth -
        parseFloat(paddingLeft) -
        parseFloat(paddingRight)
      );
    },

    bulkEditTemplates() {
      return this.bulkEditTemplateIds.map((id) => {
        const {
          name,
          type,
          is_published: isPublished,
          image_path: coverImage,
        } = this.filteredTemplates.find((e) => e.id === id);
        return {
          id,
          name,
          type,
          isPublished,
          coverImage,
        };
      });
    },
  },

  watch: {
    templateType() {
      this.templateTags = ['All'];
    },
  },

  mounted() {
    this.allTemplate = Object.values(this.allTemplates);
    this.templateDetailModal = new Modal(
      document.getElementById('templateModal')
    );
    this.bulkEditTemplateModal = new Modal(
      document.getElementById('template-bulk-edit-modal')
    );
    this.deleteModal = new Modal(document.getElementById('delete-modal'));
  },

  methods: {
    triggerCreateModal() {
      this.clearInput();
      this.showModal();
    },

    // triggerThemeTemplate() {
    //   new Modal(document.getElementById('theme-builder')).show();
    // },

    bulkEditTemplate() {
      templateAPI
        .bulkEdit({
          templateIds: this.bulkEditTemplateIds,
          isPublished: this.bulkEditTemplateStatus ?? 'Draft',
        })
        .then((res) => {
          this.toggleBulkEditModal(false);
          this.$toast.success(
            'Success',
            'Successfully bulk edited template status'
          );
          window.location.reload();
        })
        .catch((err) => {
          console.error(err);
          this.$toast.error(
            'Failed',
            'Execution Error. Please contact our developer for help'
          );
        });
    },

    toggleBulkEditModal(isShow = false) {
      if (isShow) {
        this.bulkEditTemplateModal.show();
      } else {
        this.bulkEditTemplateModal.hide();
      }
    },

    selectedTemplate(id = this.currentEditId, type = this.templateType) {
      return this.allTemplate.find((e) => {
        return e.type === type && e.id === id;
      });
    },

    triggerEditDetailsModal(templateId, templateType) {
      this.currentEditId = templateId;
      const { name, is_published: isPublished, type, tags } = this.selectedTemplate(
        templateId,
        templateType
      );
      this.templateName = name;
      this.templateType = type;
      this.templateStatus = Number(isPublished) ?? 0;
      this.templateTags = JSON.parse(tags ?? '["All"]');
      this.showModal();
    },

    showModal() {
      this.templateDetailModal.show();
    },

    closeModal() {
      this.templateDetailModal.hide();
    },

    toggleDeleteModal(id, type) {
      if (id) {
        this.currentEditId = id;
        this.templateType = type;
        this.deleteModal.show();
      } else {
        this.deleteModal.hide();
      }
    },

    determineSaveFunction() {
      this.showErr = this.v$.$invalid;
      if (this.showErr) return;
      if (this.templateType !== 'email') {
        this.updateOrCreateTemplate();
      }
      // if (this.currentEditId === null) {
      //   this.createEmailTemplate();
      // }
      // this.updateEmailTemplateDetails();
    },

    // async createEmailTemplate() {
    //   try {
    //     const params = {
    //       type: 'builtin-template',
    //       name: this.templateName,
    //       preview: null,
    //     };
    //     const {
    //       data: { reference_key: referenceKey },
    //     } = await templateAPI.createEmailTemplate();
    //     const {
    //       data: { email_design_reference_key: emailDesignReferenceKey },
    //     } = await templateAPI.createEmailDesign(referenceKey, params);
    //     this.closeModal();
    //     this.$inertia.visit(
    //       `/emails/${referenceKey}/design/${emailDesignReferenceKey}/edit?source=template&key=${referenceKey}`
    //     );
    //   } catch (err) {
    //     console.error(err);
    //     this.$toast.error(
    //       'Failed',
    //       'Execution Error. Please contact our developer for help'
    //     );
    //   }
    // },

    // updateEmailTemplateDetails() {
    //   const { reference_key: referenceKey } = this.selectedTemplate();
    //   templateAPI
    //     .updateEmailTemplate(
    //       referenceKey,
    //       this.templateName,
    //       this.templateStatus
    //     )
    //     .then((res) => {
    //       const { status, message } = res.data;
    //       this.closeModal();
    //       this.$toast.success(status, message);
    //       this.$inertia.reload();
    //     })
    //     .catch((err) => {
    //       console.error(err);
    //       this.$toast.error(
    //         'Failed',
    //         'Execution Error. Please contact our developer for help'
    //       );
    //     });
    // },

    updateOrCreateTemplate() {
      const postUrl =
        this.currentEditId === null
          ? '/templates/create'
          : `/templates/update/details/${this.currentEditId}`;
      templateAPI
        .updateOrCreate(postUrl, {
          type: this.templateType,
          name: this.templateName,
          is_published: Boolean(this.templateStatus) ?? false,
          tags: JSON.stringify(this.templateTags),
          element: '{}',
        })
        .then((res) => {
          const { isEditDetails, id, message, updatedTemplate } = res.data;
          this.closeModal();
          this.$toast.success('Success', message);
          if (isEditDetails) {
            const index = this.allTemplate.findIndex(
              (e) => e.id === updatedTemplate.id
            );
            this.allTemplate.splice(index, 1, updatedTemplate);
          } else this.navigateToBuilder(id);
        })
        .catch((err) => {
          throw new Error(err);
        });
    },

    deleteTemplate() {
      const { reference_key: referenceKey = null } = this.selectedTemplate();
      const url =
        this.templateType === 'email'
          ? `/emails/design/template/${referenceKey}`
          : `/templates/destroy/${this.currentEditId}`;
      templateAPI
        .delete(url)
        .then((res) => {
          this.$toast.success('Success', res.data.message);
          window.location.reload();
        })
        .catch((err) => {
          this.$toast.error('Failed', 'Fail to delete template');
          throw new Error(err);
        });
    },

    navigateToBuilder(id) {
      window.location.replace(`/builder/template/${id}/editor`);
    },

    clearError() {
      this.showErr = false;
    },

    clearInput() {
      this.currentEditId = null;
      this.templateName = null;
      this.templateTags = ['All'];
      this.templateStatus = 0;
      this.templateType = 'section';
    },

    addFilter(type) {
      const index = this.typeFilter.indexOf(type);
      if (index === -1) {
        this.typeFilter.push(type);
        return;
      }
      this.typeFilter.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
.flex-col {
  display: flex;
  flex-direction: column;
}

.flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.header-text {
  margin: 0;
  font-size: 24px;
  margin-bottom: 28px;
  font-weight: bold;
}

.mansory-container {
  column-count: 3;
  column-gap: 10px;

  &__item {
    width: 100%;
    padding: 0.5rem;
    background: #fff;
    margin-bottom: 1rem;
    display: inline-block;
    border-radius: 0.475rem;
    box-shadow: 0px 0px 20px 0px rgb(76 87 125 / 2%);
  }

  &__overlay {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    top: 0;
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: rgba(#202930, 0.8);
    opacity: 0;

    &:hover {
      opacity: 1;
      cursor: pointer;
    }
  }
}

.mansory-container__preview {
  height: auto;
  min-height: 45px;
  position: relative;

  img {
    width: 100%;
    height: auto;
  }
}

.template-title {
  margin: 0;
  margin-top: 5px;
  font-size: 14px;
  text-align: center;
}

.template-button {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  color: white;
  font-weight: bold;
  border-radius: 50%;
  text-decoration: none;
  margin-right: 10px;
  background-color: $h-primary;
  font-size: 12px;
  width: 37px;

  &__icon {
    color: white;
    padding: 0px;
  }
}

.page-container {
  display: inline-block;
  width: 100%;
  justify-content: center;
  align-items: flex-start;
  margin: 10px 0px;

  &::-webkit-scrollbar {
    display: none;
  }

  &__page {
    @extend .flex-col;
    justify-content: space-between;
    width: 180px;
    height: 230px;
    padding: 8px;
    // margin: 0 12px 12px 0;
    background: #fff;
    box-shadow: 0 0 5px rgba(#ced4da, 0.7);

    &--responsive-height {
      @extend .flex-col;
      justify-content: space-between;
      //   width: 191px;
      height: 100%;
      padding: 8px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #ced4da;
      border-radius: 2.5px;
    }
  }

  &__page-preview {
    // margin-bottom: 8px;
    background-color: #ced4da;
    position: relative;
    overflow: hidden;
  }

  &__iframe {
    position: absolute;
    width: 100%;
    height: 100%;
  }

  &__sub-title {
    font-size: 12px;
    max-height: 56px;
  }

  &__overlay {
    justify-content: center;
    display: flex;
    align-items: center;
    width: 100%;
    height: 100%;
    top: 0;
    background-color: rgba(#202930, 0.8);
    opacity: 0;
    position: absolute;
    z-index: 99;

    &:hover {
      opacity: 1;
      cursor: pointer;
    }
  }

  &__button {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    color: white;
    font-weight: bold;
    padding: 10px;
    border-radius: 50%;
    text-decoration: none;
    margin-right: 10px;
    background-color: $h-primary;
    font-size: 12px;

    &:hover {
      background-color: $h-primary-hover;
      cursor: pointer;
    }
  }

  &__icon {
    color: white;
  }
}

.form-check {
  margin-bottom: 0.5rem;
}

p.page-tab__sub-title {
  font-size: 12px;
}

.clearFilterButton {
  color: $h-secondary-pictonBlue;
  font-size: 12px;
}

.dropdown-item {
  &:hover {
    cursor: pointer;
  }
  &:active {
    color: black;
  }
}

.createNewButton,
.header {
  @media (max-width: $md-display) {
    margin-left: $mobile-align-left-padding;
  }
}

#scaled-frame {
  width: 700px;
  height: 760px;
  border: 0px;
  zoom: 0.25;
  -moz-transform: scale(0.25);
  -moz-transform-origin: 0 0;
  -o-transform: scale(0.25);
  -o-transform-origin: 0 0;
  -webkit-transform: scale(0.25);
  -webkit-transform-origin: 0 0;
}

@media screen and (-webkit-min-device-pixel-ratio: 0) {
  #scaled-frame {
    zoom: 1;
  }
}

input[type='radio'].purple-radio {
  z-index: 1 !important;
}
</style>

<style lang="scss">
.template-tags-select {
  .vs__search::placeholder {
    color: #c2c9ce;
  }

  .vs__dropdown-toggle {
    height: 36px;
    width: 100%;
    font-size: 14px;
    border: 1px solid #c2c9ce;
    border-radius: 5px;
    padding: 0 3px;
    background-color: #fff;

    .vs__selected {
      font-size: 11px;
      margin: 4px;
      padding: 0 0.3rem;
    }

    .vs__deselect {
      margin-left: 5px;
    }
  }
}

.input-group {
  width: 100%;
}
</style>

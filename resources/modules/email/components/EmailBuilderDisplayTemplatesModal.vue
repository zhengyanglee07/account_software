<template>
  <BaseModal
    modal-id="eb-display-templates-modal"
    title="Template Library"
    size="xl"
    no-footer
    manual
    scrollable
    is-image-modal
  >
    <template #tabs>
      <BaseButton
        type="link"
        :active="activeTab === 'emails'"
        @click="activeTab = 'emails'"
      >
        Emails
      </BaseButton>
      <BaseButton
        type="link"
        :active="!(activeTab === 'emails')"
        @click="activeTab = 'my template'"
      >
        My templates
      </BaseButton>
    </template>
    <!-- builtin emails tab -->
    <div v-show="activeTab === 'emails'" class="modal-body-template pages">
      <!-- Note: keep this first, I don't know which day your boss high again
                   and want back this
        -->
      <!-- <div class="category-leftbar">-->
      <!--   <button class="leftbar-item active-category">TREND</button>-->
      <!-- </div>-->

      <div class="right-panel-wrapper pt-3">
        <!-- OLD DESIGN -->
        <!-- <div class="search-input-wrapper">
            <input
              v-model="searchBuiltinTemplate"
              class="search-input"
              type="text"
              placeholder="Search"
            /><i class="fas fa-search search-icon"></i>
          </div> -->

        <!-- Page Preview -->
        <div class="page-container">
          <!-- Show this BLANK preview if funnel is newly created -->
          <div
            v-if="templateModalid === 'new-landing-template-modal'"
            class="page-container__page"
          >
            <div class="page-container__page-preview">
              <div class="page-container__overlay">
                <div
                  class="page-container__button"
                  @click="
                    chosenTemplate = '';
                    new Modal(
                      document.getElementById('template-modal-save-name')
                    ).show();
                  "
                >
                  <i class="fas fa-download page-container__icon" />
                  Insert
                </div>
              </div>
            </div>
            <p class="page-tab__sub-title">BLANK</p>
          </div>

          <!-- !!! Loop this !!! -->
          <div
            v-for="(template, index) in builtInTemplates"
            v-else
            :key="index"
            class="page-container__page"
          >
            <div class="page-container__page-preview">
              <iframe
                v-if="template.reference_key"
                id="scaled-frame"
                class="page-container__iframe"
                :src="`/emails/design/template/${template.reference_key}/preview`"
                frameborder="0"
                scrolling="no"
              />
              <!-- Overlay -->
              <div class="page-container__overlay">
                <div
                  class="page-container__button"
                  @click="useTemplate(template.name, template.preview)"
                >
                  <i class="fas fa-download page-container__icon" />
                  Insert
                </div>
              </div>

              <!-- Image here -->
              <!-- TODO: insert <preview-html /> here -->
              <!--  -->
            </div>

            <!-- Page Name -->
            <p class="page-tab__sub-title">
              {{ template.name.toUpperCase() }}
            </p>
          </div>
        </div>

        <!-- <div class="template-wrapper">
            <div
              v-for="(template, index) in filteredBuiltinTemplates"
              :key="index"
              class="template-container"
            >
              <div class="template-image-container">
                <div class="template-image-preview">
                  <i class="fas fa-plus preview-icon"></i>
                </div>

              </div>
              <div class="template-title">
                {{ template.name.toUpperCase() }}
              </div>
              <div class="template-title-preview mt-1">
                <button
                  class="primary-square-button"
                  style="font-size: 11px;"
                  @click="useTemplate(template.name, template.preview)"
                >
                  INSERT
                </button>
              </div>
            </div>
          </div> -->
      </div>
    </div>
    <div style="overflow: auto">
      <div
        v-show="activeTab === 'my template'"
        class="modal-body-template my-template pt-3"
        style="min-width: 400px"
      >
        <BaseDatatable
          title="template"
          no-edit-action
          no-delete-action
          :table-headers="templateTableHeaders"
          :table-datas="userTemplates"
        >
          <template #action-options="{ row: { name, reference_key, preview } }">
            <BaseDropdownOption
              text="Preview"
              @click="previewTemplate(reference_key, preview)"
            />
            <BaseDropdownOption
              text="Insert"
              data-bs-dismiss="modal"
              @click="useTemplate(name, preview)"
            />
            <BaseDropdownOption
              text="Delete"
              @click="deleteTemplate(reference_key)"
            />
          </template>
        </BaseDatatable>
      </div>
    </div>
  </BaseModal>
</template>

<script>
/* eslint-disable indent */
import { Modal } from 'bootstrap';
import dayjs from 'dayjs';
import axios from 'axios';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import eventBus from '@shared/services/eventBus.js';

dayjs.extend(localizedFormat);

export default {
  name: 'EmailBuilderDisplayTemplatesModal',
  props: {
    emailRefKey: {
      type: String,
      required: false,
    },
    create: {
      type: Boolean,
      required: false,
    },
  },
  data() {
    return {
      modalName: 'eb-display-templates-modal',
      activeTab: 'emails',
      searchBuiltinTemplate: '',
      searchUserTemplate: '',
      chosenTemplate: '',
      openDialogDelete: false,
      builtInTemplates: [],
      userTemplates: [],
      sortUserTemplate: 'Choose',
      templateModalid: '',
      userTemplateOption: [
        {
          name: 'Name',
          value: 'name',
          width: '200px',
        },
        {
          name: 'Date',
          value: 'date',
        },
      ],
      templateTableHeaders: [
        {
          name: 'Name',
          key: 'name',
        },

        {
          name: 'Creation Date Time',
          key: 'created_at',
          isDateTime: true,
        },
      ],
    };
  },
  computed: {
    filteredBuiltinTemplates() {
      return this.builtInTemplates.filter((template) =>
        template.name
          .toLowerCase()
          .includes(this.searchBuiltinTemplate.toLowerCase())
      );
    },
    filteredUserTemplates() {
      const sorted = [...this.userTemplates];

      // Sort by Name
      if (this.sortUserTemplate === 'name') {
        sorted.sort((x, y) => {
          const a = x.name.toLowerCase();
          const b = y.name.toLowerCase();

          return a == b ? 0 : a > b ? 1 : -1;
        });
      }

      // Sort by Date
      if (this.sortUserTemplate === 'date') {
        sorted.sort((x, y) => {
          const a = new Date(x.created_at);
          const b = new Date(y.created_at);

          return a - b;
        });
      }

      return sorted.filter((template) =>
        template.name
          .toLowerCase()
          .includes(this.searchUserTemplate.toLowerCase())
      );
    },
  },
  mounted() {
    axios
      .get('/emails/design')
      .then(({ data: { userTemplates, buildinTemplates } }) => {
        this.builtInTemplates = buildinTemplates;
        this.userTemplates = userTemplates;
      });
  },
  methods: {
    hideModal() {
      Modal.getInstance(
        document.getElementById('eb-display-templates-modal')
      ).hide();
    },
    previewTemplate(emailDesignRefKey) {
      window.open(`/email-builder/preview/${emailDesignRefKey}`, '_blank');
    },

    /**
     * Function called after pressing insert builitin/user template.
     *
     * If create is true (on e.params), then a new email design will
     * be created (in db) instead of just adding preview to Vuex.
     *
     * create=true now exists only on Design Email btn click in email
     * setup
     *
     * Edit: if template 'Blank' is used, use preview from Vuex instead
     *
     * @param templateName
     * @param preview
     */
    useTemplate(templateName, preview) {
      if (!this.create) {
        this.$store.commit('emailBuilder/updatePreview', JSON.parse(preview));
        this.hideModal();
        return;
      }

      axios
        .post(`/emails/${this.emailRefKey}/design/create`, {
          type: 'default',
          preview: templateName.toLowerCase() !== 'blank' ? preview : null,
        })
        .then(({ data: { email_design_reference_key: emailDesignRefKey } }) => {
          eventBus.$emit('email-design-created', emailDesignRefKey);
        })
        .catch(() => {
          this.$toast.error(
            'Something wrong during email design creation. Please try again later.'
          );
        })
        .finally(() => {
          this.hideModal();
        });
    },
    deleteTemplate(emailDesignRefKey) {
      axios
        .delete(`/emails/design/template/${emailDesignRefKey}`)
        .then(() => {
          this.$toast.success('Success', 'Successfully deleted template.');

          this.userTemplates = this.userTemplates.filter(
            (template) => template.reference_key !== emailDesignRefKey
          );
        })
        .catch((err) => {
          console.error(err);

          this.$toast.error('Error', 'Failed to delete template.');
        });
    },
    localizedDate(date) {
      return dayjs(date).format('lll');
    },
  },
};
</script>

<style scoped lang="scss">
.gray_icon {
  padding-right: 10px;
}
// NEW DESIGN
*:not(i) {
  font-family: $base-font-family;
  color: $base-font-color;
  font-size: $base-font-size;
  margin: 0;
  padding: 0;
}

.flex-col {
  display: flex;
  flex-direction: column;
}

.flex-row {
  display: flex;
  flex-direction: row;
}

.flex-center {
  justify-content: center;
  align-items: center;
}

.page-container {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
  margin-bottom: 16px;
  justify-content: flex-start;
  align-items: flex-start;
  overflow-y: scroll;

  &::-webkit-scrollbar {
    display: none;
  }

  &__page {
    @extend .flex-col;
    justify-content: space-between;
    width: 190px;
    height: 240px;
    padding: 8px;
    margin: 0 12px 12px 0;
    background: #fff;
    // box-shadow: 0 0 5px rgba(#ced4da, 0.7);
    border: 1px solid #ced4da;
    border-radius: 2.5px;

    &--responsive-height {
      @extend .flex-col;
      justify-content: space-between;
      width: 191px;
      padding: 8px;
      margin: 0 12px 12px 0;
      background: #fff;
      box-shadow: 0 0 5px rgba(#ced4da, 0.7);
    }
  }

  &__page-preview {
    width: 100%;
    height: 190px;
    background-color: #ced4da;
    position: relative;
  }

  &__iframe {
    width: 100%;
    height: 100%;
  }

  &__sub-title {
    text-align: left;
  }

  &__overlay {
    position: absolute;
    @extend .flex-col;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    background-color: rgba(#202930, 0.8);
    opacity: 0;
    position: absolute;
    z-index: 99;
    inset: 0;

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
    padding: 6px 16px;
    border-radius: 10rem;
    background-color: $h-primary;
    font-size: 12px;
    width: 60%;

    &:hover {
      background-color: $h-primary-hover;
      cursor: pointer;
    }

    &:not(:last-child) {
      margin-bottom: 16px;
    }
  }

  &__icon {
    color: white;
    padding-right: 12px;
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

.header-title {
  font-size: 12px;
  font-weight: bold;
  width: 33%;
}

.header-close-icon {
  width: 33%;
  text-align: right;
}

.modal-content {
  border: none;
}

.modal-header-template {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 16px;
  //height: 50px;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
  //margin-bottom: 10px;
}

.close-icon {
  color: #a4afb7;
  font-size: 18px;
}

.modal-header-template i:hover {
  color: #495157;
}

.modal-header-template i {
  cursor: pointer;
}

.header-navbar {
  display: flex;
  width: 33%;
  justify-content: center;
}

.nav-item {
  padding: 0 8px;
  margin: 0 12px;
  cursor: pointer;
  text-align: center;
  color: $base-font-color;
  line-height: 3.5;
}

.nav-item.active {
  border-bottom: 3px solid $base-font-color;
}

.leftbar-item {
  border: none;
  background-color: white;
  padding-bottom: 15px;
  font-size: 12px;
  color: gray;
  text-align: left;
  padding-left: 30px;

  &:hover {
    color: black;
    font-weight: 700;
  }
}

.active-category {
  color: black;
  font-weight: 700;
}

.category-leftbar {
  display: flex;
  flex-direction: column;
  width: 15%;
  padding: 15px 0;
}

.modal-body-template {
  display: flex;
  height: 100%;
  width: 100%;
  min-height: 500px;
}

.right-panel-wrapper {
  width: 100%;
  padding: 0 16px;
  max-height: 590px;
  overflow: auto;
  background-color: $base-background;
}

.search-input {
  border: none;
  border-bottom: 1px solid #d5dadf;
  border-radius: 0;
  font-size: 11px;
  padding: 8px 15px 8px 0;
  transition: border 0.5s;
  outline: none;
  width: 200px;
}

.template-container {
  padding: 8px;
  background-color: #fff;
  box-shadow: 0 1px 20px 0 rgba(0, 0, 0, 0.1);
  border-radius: 3px;
  width: 20%;
  margin: 15px;
}

.template-wrapper {
  display: flex;
  margin-top: 45px;
  flex-wrap: wrap;
}

.template-image-container {
  height: 200px;
  width: 100%;
  // transition: opacity 0.5s;
  position: relative;
}

.template-title {
  font-size: 12px;
  height: 31px; // this height follows INSERT button container's height
  display: flex;
  align-items: center;
}

.template-container:hover .template-title {
  display: none;
}

.template-title-preview {
  display: none;
}

.template-container:hover .template-title-preview {
  display: block;
  font-size: 11px;
  color: #39b54a;
}

.img-control {
  width: 100%;
  height: 100%;
}

.search-input-wrapper {
  float: right;
}

.search-icon {
  margin-left: -15px;
  color: lightgray;
  font-size: 11px;
}

.template-image-preview {
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  position: absolute;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.5s;
  display: flex;
  align-items: center;
  justify-content: center;
}

// .template-container:not(:hover) .template-image-preview {
//   opacity: 0;
// }

.preview-icon {
  font-size: 50px;
  color: #d5dadf;
}

.my-template {
  background-color: $base-background;
  flex-direction: column;
  padding: 0 15px;
  height: 570px;
}

.align-right {
  text-align: right;
}

.template-table-wrapper {
  font-size: 11px;
  height: 100%;
  color: #a4afb7;
  background-color: #fff;
  margin-bottom: 36px;
  border: 1px solid #ced4da;
  overflow-y: scroll;
  &::-webkit-scrollbar {
    display: none;
  }
}

.template-table-label-wrapper {
  display: flex;
  justify-content: space-between;
  padding: 16px 32px;
  border-bottom: 1px solid #ced4da;
  @media (max-width: $md-display) {
    padding: 5px;
  }
}

.bold {
  font-weight: bold;
}

.template-table-container {
  display: flex;
  flex-direction: column;
}

.template-table-item-name {
  width: 35%;
}

.template-table-item-type,
.template-table-item-creationDate {
  width: 22.5%;
}

.template-table-item-actions {
  width: 15%;
  @extend .flex-row;
  justify-content: center;
}

.template-table-item-row {
  display: flex;
  justify-content: space-between;
  //border-radius: 3px 3px 0 0;
  align-items: center;
  height: 50px;
  width: 100%;
  padding: 0 32px;
  background-color: white;
}

.template-table-item-row:hover {
  background-color: #fafbfb;
}

.action-button {
  border: none;
  background-color: #fff;
  font-size: 12px;

  &:not(:last-child) {
    margin-right: 16px;
  }
}

.button-icon {
  margin-right: 6px;
  &:hover {
    color: $h-primary;
  }
}

.fa-trash-alt:hover {
  color: $h-secondary-red;
}

.dialog-wrapper {
  position: fixed;
  height: 100%;
  width: 100%;
  bottom: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.8);
  z-index: 9999;
}

.dialog-content {
  margin: auto;
  width: 375px;
  margin-top: 10%;
  background-color: white;
  border-radius: 3px;
}

.dialog-header {
  font-size: 15px;
  color: #495157;
  padding: 30px 0 10px;
  font-weight: 500;
  text-align: center;
}

.dialog-message {
  padding: 0 30px 30px;
  min-height: 50px;
  text-align: center;
  font-size: 12px;
  color: #6d7882;
}

.dialog-buttons-wrapper {
  border-top: 1px solid #e6e9ec;
  text-align: center;
  display: flex;
}

.dialog-buttons:first-child {
  border-right: 1px solid #e6e9ec;
}

.dialog-buttons:last-child {
  color: red;
}

.dialog-buttons-wrapper button {
  font-family: $base-font-family;
  width: 50%;
  border: none;
  background: none;
  color: #6d7882;
  font-size: 15px;
  cursor: pointer;
  padding: 13px 0;
  outline: 0;
}
</style>

<template>
  <BaseDatatable
    :table-headers="tableHeaders"
    :table-datas="parsedDataArray"
    :title="activeType"
    @delete="deleteSection"
  >
    <template #action-button>
      <template v-if="activeType !== 'page'">
        <BaseButton
          class="me-2"
          @click="triggerTemplateModal('header')"
        >
          <i class="fas fa-plus" /> Add Header
        </BaseButton>
        <BaseButton
          class="me-2"
          @click="triggerTemplateModal('footer')"
        >
          <i class="fas fa-plus" /> Add Footer
        </BaseButton>
      </template>
      <template v-else>
        <BaseButton
          class="me-2"
          @click="triggerTemplateModal('page')"
        >
          <i class="fas fa-plus" /> Add Page
        </BaseButton>
      </template>

      <BaseButton
        v-show="activeType !== 'page'"
        class="me-2"
        @click="triggerTemplateModal('theme')"
      >
        <i class="fas fa-plus" /> Add Theme
      </BaseButton>
    </template>
    <template #action-options="{ row }">
      <BaseDropdownOption
        text="View"
        :link="
          row.is_published
            ? 'https://' + storeDomain.domain + '/' + row.path
            : `/builder/${activeType}/${row.reference_key}/preview`
        "
        is-open-in-new-tab
      />
      <BaseDropdownOption
        v-if="activeType !== 'page'"
        text="Rename"
        @click="triggerRenameModal(row.id, row.name)"
      />
      <BaseDropdownOption
        v-if="activeType !== 'page'"
        :text="activeType === 'page' ? 'Set as Homepage' : 'Change to Active'"
        @click="updateStatus(row.id)"
      />
      <BaseDropdownOption
        v-if="activeType === 'page'"
        :text="`Change to ${is_published ? 'Draft' : 'Published'}`"
        @click="updateType(row)"
      />
    </template>
  </BaseDatatable>

  <BaseModal modal-id="rename-section">
    <template #title> Rename </template>

    <label for="name" class="m-container__text">{{ activeType }} Name</label>

    <input
      id="newSectionName"
      v-model="newSectionName"
      type="text"
      name="newSectionName"
      class="m-container__input form-control firstInput"
      :class="{
        'error-border': showError && !$v.newSectionName.required,
      }"
      :placeholder="`New ${activeType} Name`"
      @input="clearError"
      @keyup.enter="updateSectionName"
    />
    <span
      v-if="showError && !$v.newSectionName.required"
      class="text-danger font-weight-normal"
    >
      Name is required
    </span>

    <template #footer>
      <button
        type="submit"
        class="primary-small-square-button"
        @click="updateSectionName"
      >
        Save
      </button>
    </template>
  </BaseModal>

  <TemplateModal :section-type="selectedType" />
</template>

<script>
import TemplateModal from '@shared/components/TemplateModal.vue';
import { required } from '@vuelidate/validators';
import onlineStoreAPI from '@onlineStore/api/onlineStoreAPI.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  name: 'EcommerceHomeDatatable',

  components: {
    TemplateModal,
  },

  props: {
    sectionType: {
      type: String,
      required: true,
    },
    recordsArray: {
      type: Array,
      default: () => [],
    },
    dbStoreDomain: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      sections: [],
      selectedId: null,
      selectedType: null,
      activeType: null,
      renameModal: null,
      newSectionName: null,
      showError: false,
      selectedSectionId: null,
      storeDomain: null,
      modal: null,
    };
  },

  validations: {
    newSectionName: {
      required,
    },
  },

  computed: {
    tableHeaders() {
      const baseHeader = [
        {
          name: 'Last Modified',
          key: 'updated_at',
          order: 0,
          width: '25%',
          isDateTime: true,
        },
      ];

      switch (this.activeType) {
        case 'header':
        case 'footer':
          return [
            { name: 'Name', key: 'name', order: 0, width: '45%' },
            { name: 'Status', key: 'is_active', order: 0, width: '10%' },
            ...baseHeader,
          ];
        case 'page':
          return [
            { name: 'Name', key: 'name', order: 0, width: '40%' },
            { name: 'Status', key: 'is_published', order: 0, width: '15%' },
            ...baseHeader,
          ];
        default:
          return [];
      }
    },

    parsedDataArray() {
      const sections = this.sections[this.activeType];
      if (!sections) return [];
      const capitalize = (s) => {
        return typeof s !== 'string'
          ? s
          : s.charAt(0).toUpperCase() + s.slice(1);
      };
      return sections.map((item) => {
        if (this.activeType === 'page') {
          const isHomepage = this.activeSectionId === item.id;
          return {
            name: isHomepage ? `${item.name} -- Homepage` : item.name,
            updated_at: item.updated_at,
            id: item.id,
            is_published: item.is_published ? 'Published' : 'Draft',
            account_id: item.account_id,
            path: item.path,
            reference_key: item.reference_key,
            editLink: `/builder/${this.activeType}/${item.reference_key}/editor`,
          };
        }
        return {
          name: item.name,
          updated_at: item.updated_at,
          id: item.id,
          is_active: item.is_active,
          account_id: item.account_id,
          reference_key: item.reference_key,
          actionLink: `/builder/${this.activeType}/${item.reference_key}/editor`,
        };
      });
    },

    activeSectionId() {
      if (this.isPageDatatable) {
        return this.storeDomain?.type_id;
      }
      return this.sections[this.activeType].find((e) => e.is_active === true)
        ?.id;
    },

    isPageDatatable() {
      return this.activeType === 'page';
    },

    emptyState() {
      return {
        title: this.activeType === 'header' ? 'header' : 'footer',
        description: this.activeType === 'header' ? 'headers' : 'footers',
      };
    },
  },

  mounted() {
    this.activeType =
      window.location.hash.split('#')[1] === 'footer'
        ? 'footer'
        : this.sectionType ?? 'header';
    this.storeDomain = this.dbStoreDomain;
    this.sections = this.recordsArray;
    bootstrap?.then(({ Modal }) => {
      this.renameModal = new Modal(document.getElementById('rename-section'));
      this.deleteModal = new Modal(document.getElementById('delete-modal'));
    });
  },

  methods: {
    triggerTemplateModal(type) {
      this.selectedType = type ?? this.sectionType;
      bootstrap?.then(({ Modal }) => {
        new Modal(document.getElementById('template-modal')).show();
      });
    },

    triggerRenameModal(id, name) {
      this.selectedSectionId = id;
      this.newSectionName = name;
      this.renameModal.show();
    },

    updateStatus(id) {
      const putUrl = this.isPageDatatable
        ? `/domain/${id}/home`
        : `/online-store/update/status/${id}`;
      onlineStoreAPI
        .axiosPut(putUrl)
        .then(({ data: { updatedRecords } }) => {
          if (this.isPageDatatable) {
            this.storeDomain = updatedRecords;
          } else {
            this.sections[this.activeType] = updatedRecords;
          }
          this.$toast.success('Success', 'Status updated successfully');
        })
        .catch((error) => {
          this.triggerErrorToast(error);
        });
    },

    updateSectionName() {
      if (this.$v.$invalid) {
        this.showError = true;
        return;
      }

      onlineStoreAPI
        .updateSectionName(this.selectedSectionId, this.newSectionName)
        .then(({ data: { status, message, updatedRecord } }) => {
          this.$toast.success(status, message);
          const index = this.sections[this.activeType].findIndex(
            (e) => e.id === this.selectedSectionId
          );
          this.sections[this.activeType].splice(index, 1, updatedRecord);
          this.renameModal.hide();
        })
        .catch((error) => {
          this.triggerErrorToast(error);
        });
    },

    updateType({ id, is_published: isPublished }) {
      onlineStoreAPI
        .updateStatus(id, !isPublished)
        .then(({ data: { status, message, updatedRecord } }) => {
          this.$toast.success(status, message);
          const index = this.sections[this.activeType].findIndex(
            (e) => e.id === id
          );
          this.sections[this.activeType].splice(index, 1, updatedRecord);
        })
        .catch((error) => {
          this.triggerErrorToast(error);
        });
    },

    deleteSection(id) {
      const deleteUrl = this.isPageDatatable
        ? `/landing/delete/${id}`
        : `/online-store/delete/${id}`;
      onlineStoreAPI
        .axiosDelete(deleteUrl)
        .then(({ data: { status, message } }) => {
          this.$toast.success(status, message);
          const index = this.sections[this.activeType].findIndex(
            (e) => e.id === id
          );
          this.sections[this.activeType].splice(index, 1);
          this.closeDeleteModal();
        })
        .catch((error) => {
          this.triggerErrorToast(error);
        });
    },

    triggerErrorToast(error) {
      console.error(error);
      this.$toast.error(
        'Error',
        'Something unexpected happened. Please contact our support'
      );
    },

    closeDeleteModal() {
      this.deleteModal.hide();
    },

    clearError() {
      this.showError = false;
    },
  },
};
</script>

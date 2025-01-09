<template>
  <div class="mb-4">
    <BaseButton
      has-dropdown-icon
      type="white"
      @click="isAdvancedFilterOpened = !isAdvancedFilterOpened"
    >
      <img
        src="@shared/assets/media/advance-filter.svg"
        alt="filter-svg"
      >
      Advanced Filter
    </BaseButton>
  </div>

  <div v-show="isAdvancedFilterOpened">
    <PeopleConditionsPanel />

    <div style="padding: 10px 0px">
      <PeopleFilterConditionsButtons />

      <BaseButton
        id="add-segment-button"
        @click="toggleSaveSegment"
      >
        Save Segment
      </BaseButton>
    </div>
    <div class="people-page__dividor" />
  </div>

  <BaseDatatable
    title="person"
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableContactsData"
    :pagination-info="paginatedPeople"
    is-server-side-search
    @selectAll="handleSelectAllContacts"
    @delete="toggleDeleteModal"
  >
    <template #action-button>
      <BaseButton
        id="dropdownMenuButton"
        type="secondary"
        data-disabled="true"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
        has-edit-icon
        has-dropdown-icon
      >
        Bulk Edit
      </BaseButton>
      <BaseDropdown id="dropdownMenuButton">
        <template v-if="tags.length > 0">
          <!-- Add Tag -->
          <BaseDropdownOption
            text="Add Tag"
            data-bs-toggle="modal"
            data-bs-target="#addTagModal"
          />
          <!-- Remove Tag -->
          <BaseDropdownOption
            text="Remove Tag"
            data-bs-toggle="modal"
            data-bs-target="#removeTagModal"
          />
        </template>

        <template v-else>
          <!-- Add Tag -->
          <BaseDropdownOption
            text="Add Tag"
            link="/people/tags"
          />
          <!-- Remove Tag -->
          <BaseDropdownOption
            text="Remove Tag"
            link="/people/tags"
          />
        </template>
        <!-- Delete -->
        <!-- <BaseDropdownOption
                text="Delete"
                data-bs-toggle="modal"
                data-bs-target="#bulk-delete-modal"
              /> -->
        <!-- Credit -->
        <!-- temporarily comment for 20th March 2021 my launch -->
        <BaseDropdownOption
          text="Manage Store Credit"
          data-bs-toggle="modal"
          data-bs-target="#manageCreditModal"
        />
      </BaseDropdown>
      <BaseButton
        id="import-people-button"
        type="secondary"
        aria-expanded="false"
        @click="toggleImport"
      >
        <i class="fa-solid fa-upload me-2" />
        Import
      </BaseButton>
      <BaseButton
        data-bs-toggle="modal"
        data-bs-target="#AddPeopleModal"
        aria-expanded="false"
      >
        <i class="fas fa-user me-2" />
        Add Profile
      </BaseButton>
    </template>

    <template #cell-checkbox="{ row: { id } }">
      <BaseFormCheckBox
        :model-value="checkedContactIds"
        :value="id"
        @change="handleCheckContact($event.target.value)"
      />
    </template>

    <template #action-options="{ row: { viewLink } }">
      <BaseDropdownOption
        text="View"
        :link="viewLink"
      />
    </template>
  </BaseDatatable>

  <AddPeopleModal
    modal-id="AddPeopleModal"
    :custom-fields="customField"
    :saving="savingNewContact"
    @add-people-profile="handleAddPeopleProfile"
  />
  <PeopleAddTagsModal modal-id="addTagModal" />
  <PeopleRemoveTagsModal modal-id="removeTagModal" />

  <PeopleManageCreditModal
    modal-id="manageCreditModal"
    :currency="currency"
    :exchange-rate="exchangeRate"
  />

  <UploadPeopleCsvModal
    modal-id="import"
    :permission-data="permissionData"
  />
  <SaveSegmentModal modal-id="saveSegmentName" />

  <!-- Cannot open Adv Filter when delete DeleteConfirmationModal -->
  <DeleteConfirmationModal
    modal-id="bulk-delete-modal"
    title="People"
    @cancel="closeBulkDeleteModal"
    @save="bulkDeletePeople"
  />
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import peopleProfileQueryStringsMixins from '@people/mixins/peopleProfileQueryStringsMixins';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import AddPeopleModal from '@people/components/AddPeopleModal.vue';
import PeopleConditionsPanel from '@people/components/PeopleConditionsPanel.vue';
import PeopleFilterConditionsButtons from '@people/components/PeopleFilterConditionsButtons.vue';
import PeopleAddTagsModal from '@people/components/PeopleAddTagsModal.vue';
import PeopleRemoveTagsModal from '@people/components/PeopleRemoveTagsModal.vue';
import PeopleManageCreditModal from '@people/components/PeopleManageCreditModal.vue';
import UploadPeopleCsvModal from '@people/components/UploadPeopleCsvModal.vue';
import SaveSegmentModal from '@people/components/SaveSegmentModal.vue';
import { validationFailedNotification } from '@shared/lib/validations.js';
import { Modal } from 'bootstrap';

export default {
  name: 'AllPeople',
  components: {
    AddPeopleModal,
    PeopleConditionsPanel,
    PeopleFilterConditionsButtons,
    PeopleAddTagsModal,
    PeopleRemoveTagsModal,
    PeopleManageCreditModal,
    UploadPeopleCsvModal,
    SaveSegmentModal,
  },
  mixins: [peopleProfileQueryStringsMixins, specialCurrencyCalculationMixin],
  props: {
    currency: {
      type: String,
      required: true,
    },
    contacts: {
      type: Array,
      default: () => [],
    },
    conditionJson: {
      type: Object,
      required: true,
    },
    tags: {
      type: Array,
      default: () => [],
    },
    customFieldNames: {
      type: Array,
      default: () => [],
    },
    customField: {
      type: Array,
      default: () => [],
    },
    usersProducts: {
      type: Array,
      default: () => [],
    },
    exchangeRate: {
      type: [Number, String],
      required: true,
    },
    permissionData: {
      type: Object,
      required: true,
    },
    paginatedPeople: {
      type: Object,
      required: true,
    },
    allFunnels: {
      type: Array,
      default: () => [],
    },
    allEcommercePages: {
      type: Array,
      default: () => [],
    },
    allForms: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      showLimitModal: false,
      bulkDeleteModal: null,
      // temp solution since cant do nested dropdown using bootstrap
      isAdvancedFilterOpened: false,
      isMoreActionMenuOpened: false,
      isSubMenuOpened: false,
      screenWidth: null,
      savingNewContact: false,
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         * @param textalign : justify-content, default => left
         */
        { key: 'checkbox', sortable: false },
        { name: 'Name', key: 'name', order: 0 },
        { name: 'Email', key: 'email', order: 0 },
        {
          name: `Store Credit (${
            this.currency === 'MYR' ? 'RM' : this.currency
          })`,
          key: 'storeCredit',
          order: 0,
        },
        {
          name: `Total Sales (${
            this.currency === 'MYR' ? 'RM' : this.currency
          })`,
          key: 'totalSales',
          order: 0,
          textalign: 'flex-end',
        },
      ],
      modalId: 'people-datatable-delete-modal',
      selectedId: '', // for modal purpose
    };
  },
  computed: {
    ...mapState('people', [
      'contacts',
      'tags',
      'checkedContactIds',
      'showPeopleDesc',
    ]),
    ...mapState('people', {
      localContacts: 'contacts',
    }),
    tableContactsData() {
      return this.localContacts.map((contact) => ({
        id: contact.id,
        contactRandomId: contact.contactRandomId,
        name: contact.displayName,
        email: contact.email || '-',
        totalSales: this.specialCurrencyCalculation(contact.totalSales),
        storeCredit: this.specialCurrencyCalculation(
          contact.credit_balance / 100
        ),
        viewLink: this.getViewPeopleProfileLink(contact.contactRandomId),
      }));
    },
  },

  created() {
    window.addEventListener('resize', this.onresize);
  },

  beforeMount() {
    // update data from db to Vuex
    this.updateCondition({ condition: this.conditionJson });
    this.updateContacts({ contacts: this.contacts });
    this.updateTags({ tags: this.tags });
    this.updateUsersProducts({ usersProducts: this.usersProducts });
    this.updateCustomFieldNames({ customFieldNames: this.customFieldNames });
    this.updateFunnelLists(this.allFunnels);
    this.updateEcommercePages(this.allEcommercePages);
    this.updateForms(this.allForms);
  },

  mounted() {
    this.clearCheckedContactIds();
    this.bulkDeleteModal = new Modal(
      document.getElementById('bulk-delete-modal')
    );
    this.onresize();
  },

  methods: {
    ...mapMutations('people', [
      'updateCondition',
      'updateContacts',
      'updateTags',
      'updateUsersProducts',
      'updateCustomFieldNames',
      'updateFunnelLists',
      'updateEcommercePages',
      'updateCheckedContactIds',
      'updateForms',
      'appendCheckedContactIds',
      'removeCheckedContactId',
      'clearCheckedContactIds',
    ]),

    toggleImport() {
      new Modal(document.getElementById('import')).show();
    },

    toggleDeleteModal(id) {
      this.selectedId = id;
      this.removeRecord(id);
    },

    handleAddPeopleProfile({ newContact, newAddress, newCustomFields }) {
      this.savingNewContact = true;

      this.$axios
        .post('/people/profile/addContact', {
          ...newContact,
          ...newAddress,
          newCustomField: newCustomFields,
        })
        .then((res) => {
          this.$toast.success('SUCCESS', 'Contact Successfully Added!');

          this.updateContacts({
            contacts: [
              {
                ...res.data,
                orders: [], // for total sales calc
                totalSales: '0.00',
              },
              ...this.contacts,
            ],
          });
          Modal.getInstance(document.getElementById('AddPeopleModal')).hide();
          this.$inertia.visit('/people');
        })
        .catch((err) => {
          eventBus.$emit(
            'get-taken-email',
            err?.response?.data?.errors?.email[0]
          );
        })
        // .catch((error) => {
        //   validationFailedNotification(error);

        //   if (error.response.status !== 422) {
        //     // this.$toast.error('ERROR', 'Failed to add Contact. Unknown error');
        //   }
        // })
        .finally(() => {
          this.savingNewContact = false;
        });
    },

    async bulkDeletePeople() {
      if (this.checkedContactIds.length === 0) {
        // this.$toast.warning('Warning', 'Please select at least one contact');
        return;
      }

      try {
        await this.$axios.post('/people/bulkdelete', {
          contactIds: this.checkedContactIds,
        });

        this.updateContacts({
          contacts: this.contacts.filter(
            (c) => !this.checkedContactIds.includes(c.id)
          ),
        });
      } catch (err) {
        console.error(err);
        // this.$toast.error('Error', 'Failed to bulk delete people');
      } finally {
        this.clearCheckedContactIds();
        this.closeBulkDeleteModal();
      }
    },
    /**
     * For the reason why this link is generated like this, refer to
     * the controller method responsible for /people/view/{contactRandomId} route
     *
     * @param contactRandomId
     * @returns {string}
     */
    getViewPeopleProfileLink(contactRandomId) {
      const ref = this.from === 'segment' ? `&ref=${this.refKey}` : '';

      return `/people/profile/${contactRandomId}?from=${this.from}${ref}`;
    },
    handleCheckContact(contactId) {
      if (this.checkedContactIds.includes(contactId)) {
        this.removeCheckedContactId({ contactId });
        return;
      }

      this.appendCheckedContactIds({ contactId });
    },
    handleSelectAllContacts(e) {
      const checked = e;

      if (checked) {
        this.updateCheckedContactIds({
          contactIds: this.contacts.map((contact) => contact.id),
        });
        return;
      }
      this.clearCheckedContactIds();
    },
    removeRecord(peopleId) {
      axios.delete(`/people/${peopleId}/delete`).then(() => {
        this.$toast.success('Success', 'People Deleted');
        this.deletionError = false;

        this.updateContacts({
          contacts: this.contacts.filter((c) => c.id !== peopleId),
        });
      });
      // .catch((e) => {
      //   this.$toast.error('Error', 'Failed to delete contact');
      // });
    },

    closeBulkDeleteModal() {
      this.bulkDeleteModal.hide();
    },

    toggleSaveSegment() {
      this.SaveSegmentModal = new Modal(
        document.getElementById('saveSegmentName')
      );
      this.SaveSegmentModal.show();
    },

    closeMoreAction() {
      this.isMoreActionMenuOpened = false;
    },

    onresize() {
      this.screenWidth = window.screen.width;
    },
  },
};
</script>

<style lang="scss" scoped>
img {
  width: 20px;
  transform: scale(3.1);
  margin-bottom: 2px;
  padding-right: 2.8px;
  padding-left: 1px;
}

.people-page__dividor {
  margin-bottom: 20px;
  height: 1px;
  background-color: rgb(0 0 0 / 13%);
  width: 100%;
}
</style>

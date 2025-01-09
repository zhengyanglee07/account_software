<template>
  <BaseDatatable
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableContactsData"
    title="person"
    @delete="toggleDeleteModal"
  >
    <template #cell-checkbox="{ row: { id } }">
      <BaseFormCheckBox
        v-model="checkedContactIds"
        :value="id"
      />
    </template>
    <template #action-options="{ row: { viewLink } }">
      <BaseDropdownOption
        text="View"
        :link="viewLink"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import peopleProfileQueryStringsMixins from '@people/mixins/peopleProfileQueryStringsMixins';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import { Modal, Tooltip } from 'bootstrap';

export default {
  name: 'PeoplePageDatatable',
  mixins: [peopleProfileQueryStringsMixins, specialCurrencyCalculationMixin],
  props: {
    currency: String,
    dbContact: Object,
    customFields: Array,
    exchangeRate: [Number, String],
  },
  data() {
    return {
      showLimitModal: false,
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

    tableContactsData() {
      return this.contacts.map((contact) => ({
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
  methods: {
    ...mapMutations('people', [
      'updateContacts',
      'updateCheckedContactIds',
      'appendCheckedContactIds',
      'removeCheckedContactId',
      'clearCheckedContactIds',
    ]),

    /**
     * For the reason why this link is generated like this, refer to
     * the controller method responsible for /people/view/{contactRandomId} route
     *
     * @param contactRandomId
     * @returns {string}
     */
    getViewPeopleProfileLink(contactRandomId) {
      return `/people/profile/${contactRandomId}?from=${this.from}`;
    },

    handleCheckContact(contactId) {
      if (this.checkedContactIds.includes(contactId)) {
        this.removeCheckedContactId({ contactId });
        return;
      }

      this.appendCheckedContactIds({ contactId });
    },
    handleSelectAllContacts(e) {
      const { checked } = e.target;

      if (checked) {
        this.updateCheckedContactIds({
          contactIds: this.contacts.map((contact) => contact.id),
        });
        return;
      }
      this.clearCheckedContactIds();
    },

    toggleDeleteModal(id) {
      this.selectedId = id;
      this.removeRecord(id);
      // this.people_datatable_delete_modal = new Modal(
      //   document.getElementById('people-datatable-delete-modal')
      // );
      // this.people_datatable_delete_modal.show();
    },

    removeRecord(peopleId) {
      axios.delete(`/people/${peopleId}/delete`).then(() => {
        this.$toast.success('Success', 'People Deleted');
        this.deletionError = false;
        // this.closeDeleteModal();

        this.updateContacts({
          contacts: this.contacts.filter((c) => c.id !== peopleId),
        });
      });
      // .catch((e) => {
      //   this.$toast.error('Error', 'Failed to delete contact');
      // });
    },

    // closeDeleteModal() {
    //   this.people_datatable_delete_modal.hide();
    // },

    // toggleImport() {
    //   if (this.isImport) {
    //     this.importModal = new Modal(document.getElementById('import'));
    //     this.importModal.show();
    //   } else {
    //     this.showLimitModal = !this.isImport;
    //   }
    // },
  },
};
</script>

<style lang="scss" scoped>
// .people-page-data-buttons {
//   width: 100%;
//   font-size: $base-font-size;
//   & > button {
//     font-size: 13px;
//   }
// }

// th,
// td {
//   &:first-child {
//     padding-left: 25px !important;
//     padding-right: 25px !important;

//     @media (max-width: $md-display) {
//       padding-right: 10px !important;
//       padding-left: 0px !important;
//     }
//   }
// }
</style>

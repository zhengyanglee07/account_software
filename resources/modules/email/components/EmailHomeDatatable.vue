<template>
  <BaseDatatable
    title="email"
    :table-headers="tableHeaders"
    :table-datas="tableEmailsData"
    no-edit-action
    @delete="removeRecord"
  >
    <template #action-button>
      <BaseButton
        id="bulk-edit-dropdown-button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        type="secondary"
        has-edit-icon
        has-dropdown-icon
        class="me-2 mt-2"
      >
        Assign Group
      </BaseButton>
      <BaseDropdown id="bulk-edit-dropdown-list">
        <BaseDropdownOption
          v-for="{ label, value } in bulkEditOptions"
          :key="value"
          @click="triggerEmailGroupModal(value)"
        >
          {{ label }}
        </BaseDropdownOption>
      </BaseDropdown>
      <BaseButton
        id="add-email-button"
        has-add-icon
        @click="toggleCreateEmail"
      >
        Add Email
      </BaseButton>
    </template>
    <template
      #action-options="{
        row: {
          name,
          emailDesignRefKey,
          emailDesign,
          emailRefKey,
          status,
          setupLink,
        },
      }"
    >
      <BaseDropdownOption
        v-if="status === 'Scheduled'"
        text="Cancel schedule"
        @click="cancelSchedule({ name, emailRefKey })"
      />
      <BaseDropdownOption
        v-if="status === 'Draft'"
        text="Edit"
        :link="setupLink"
      />
      <BaseDropdownOption
        text="View"
        is-open-in-new-tab
        @click="previewContent({ emailDesign, emailDesignRefKey, emailRefKey })"
      />
      <BaseDropdownOption
        text="Rename"
        @click="triggerRenameEmailModal(name, emailRefKey)"
      />
      <BaseDropdownOption
        text="Duplicate"
        @click="duplicateEmail(emailRefKey)"
      />
      <BaseDropdownOption
        text="Report"
        :link="`/emails/${emailRefKey}/report?fm=email`"
      />
    </template>

    <template #cell-checkbox="{ row: { id } }">
      <BaseFormCheckBox
        v-model="selectedEmailIds"
        :value="id"
      />
    </template>
  </BaseDatatable>
  <div>
    <CreateNewEmailModal :modal-id="createNewEmailModalId" />
    <CancelScheduleModal
      modal-id="cancel-schedule-modal"
      :email-data="cancelScheduleEmailData"
      @canceled="updateEmailSchedule"
    />
    <RenameEmailModal
      :data="renameModalData"
      @updated="updateEmailName"
    />
    <EmailGroupModal
      :modal="emailGroupModal"
      :action="emailGroupAction"
    />
    <!-- <limit-modal :show_modal="showLimitModal" @close="showLimitModal = !showLimitModal"    modal_title="Only For Paid Plan" customContext="Subscribe to one of our plan to enjoy free access to this feature."></limit-modal> -->
  </div>
</template>

<script>
/* eslint-disable indent */

import CreateNewEmailModal from '@email/components/CreateNewEmailModal.vue';
import CancelScheduleModal from '@email/components/CancelScheduleModal.vue';
import RenameEmailModal from '@email/components/RenameEmailModal.vue';
import EmailGroupModal from '@email/components/EmailGroupModal.vue';
import { getFormattedTimezoneDatetime } from '@shared/lib/timezone.js';
import { Modal } from 'bootstrap';
import axios from 'axios';
import eventBus from '@services/eventBus.js';
import useEmail from '@email/hooks/useEmail.js';

export default {
  name: 'EmailHomeDatatable',
  components: {
    CreateNewEmailModal,
    CancelScheduleModal,
    RenameEmailModal,
    EmailGroupModal,
  },
  props: {
    dbEmails: Array,
    emailDesigns: Array,
  },
  data() {
    const { selectedEmailIds } = useEmail();
    return {
      showLimitModal: false,
      createNewEmailModalId: 'create-new-email-modal',
      emails: [],
      tableHeaders: [
        { key: 'checkbox', sortable: false },
        { name: 'Email Name', key: 'name', width: '200px' },
        { name: 'Details', key: 'scheduleDetails' },
        { name: 'Status', key: 'status', width: '175px' },
      ],
      modalId: 'email-home-delete-modal',
      selectedId: '',
      emptyState: {
        title: 'email',
        description: 'emails',
      },
      cancelScheduleEmailData: {
        name: '',
        refKey: '',
      },
      renameModalData: {},
      bulkEditOptions: [
        { label: 'Add Group', value: 'add' },
        { label: 'Remove Group', value: 'delete' },
      ],
      emailGroupModal: null,
      emailGroupAction: 'add',
      emailGroupModalData: {},
      selectedEmailIds,
    };
  },
  computed: {
    tableEmailsData() {
      return this.emails
        .map((email) => ({
          id: email.id,
          emailRefKey: email.reference_key,
          name: email.name,
          type: email.type,
          scheduleDetails: this.getScheduleDetails(email),
          status: email.status,
          emailDesignRefKey: email.email_design_reference_key || '',
          emailDesign: email.email_design,
          setupLink: `/emails/standard/${email.reference_key}/edit`,
          editLink:
            email.status === 'Draft'
              ? `/emails/standard/${email.reference_key}/edit`
              : null,
        }))
        .reverse();
    },
  },
  watch: {
    dbEmails() {
      this.emails = [...this.dbEmails];
    },
  },
  beforeMount() {
    this.emails = [...this.dbEmails];
  },
  methods: {
    triggerEmailGroupModal(action) {
      if (this.selectedEmailIds.length === 0) {
        this.$toast.error('Error', 'Please select at least one email');
        return;
      }
      this.emailGroupModal = new Modal(
        document.getElementById('email-group-modal')
      );
      this.emailGroupAction = action;
      this.emailGroupModal.show();
    },
    updateEmailName({ emailRef, newName }) {
      this.emails = this.emails.map((m) => {
        if (m.reference_key === emailRef) m.name = newName;
        return m;
      });
    },
    triggerRenameEmailModal(emailName, referenceKey) {
      const modal = new Modal(document.getElementById('rename-email-modal'));
      this.renameModalData = { modal, emailName, referenceKey };
      modal.show();
    },
    updateEmailSchedule({ refKey }) {
      this.emails = this.emails.map((m) => {
        if (m.reference_key === refKey) {
          m.schedule = null;
          m.status = 'Draft';
        }
        return m;
      });
    },
    cancelSchedule(email) {
      this.cancelScheduleEmailData = {
        name: email.name,
        refKey: email.emailRefKey,
      };
      this.$nextTick(() => {
        new Modal(document.getElementById('cancel-schedule-modal')).show();
      });
    },
    previewContent(reportItem) {
      const { emailDesign } = reportItem;

      if (!emailDesign) {
        this.$toast.warning('Warning', 'This report has no design to preview');
        return;
      }

      const { reference_key: emailDesignRefKey, html } = emailDesign;
      const previewUrl = `/emails/${reportItem.emailRefKey}/design/${emailDesignRefKey}/preview`;
      const putSessionUrl = `/emails/${reportItem.emailRefKey}/design/${emailDesignRefKey}/preview/session`;

      axios
        .post(putSessionUrl, {
          html,
        })
        .then(() => {
          window.open(previewUrl, '_blank');
        });
    },
    toggleCreateEmail() {
      const { featureForPaidPlan } = this.$page.props.permissionData;
      if (featureForPaidPlan.includes('add-email')) {
        eventBus.$emit('trigger-paid-plan-modal');
        return;
      }
      new Modal(document.getElementById(this.createNewEmailModalId)).show();
    },

    getScheduleDetails(email) {
      let action = email.status;
      if (action === 'Draft') action = 'Edited';
      return `${action} on ${getFormattedTimezoneDatetime(
        email.schedule ?? email.updated_at,
        email.timezone
      )}`;
    },
    removeEmailSettings(refKey) {
      if (!localStorage.emailSetting) return;
      const emailSetting = JSON.parse(localStorage.emailSetting ?? '{}');
      delete emailSetting[refKey];
      localStorage.setItem('emailSetting', JSON.stringify(emailSetting));
    },

    removeRecord(emailId) {
      const deleteEmail = this.dbEmails?.find((e) => e.id === emailId);
      const emailRefKey = deleteEmail?.reference_key;
      axios
        .delete(`/emails/${emailRefKey}/delete`)
        .then(() => {
          this.$toast.success('Success', 'Email Deleted');

          this.emails = this.emails.filter(
            (email) => email.reference_key !== emailRefKey
          );

          this.removeEmailSettings(emailRefKey);
        })
        .catch((e) => {
          console.log(e);
          this.$toast.error(
            'Error',
            'Something wrong. Please try again later.'
          );
        });
    },

    closeDeleteModal() {
      this.email_home_delete_modal = new Modal(
        document.getElementById('email-home-delete-modal')
      );
      this.email_home_delete_modal.hide();
    },

    async duplicateEmail(emailRefKey) {
      try {
        const res = await axios.post(`/emails/${emailRefKey}/duplicate`);
        this.emails = [...res.data.emails];
        this.$toast.success('Success', 'Successfully duplicated email');
      } catch (err) {
        this.$toast.error('Error', 'Failed to duplicate email.');
      }
    },
  },
};
</script>

<style scoped lang="scss">
@media (max-width: 450px) {
  :deep(th.sorting:nth-child(2)) {
    min-width: 150px !important;
  }
}
</style>

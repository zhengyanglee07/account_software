<template>
  <BaseButton
    id="email-group-dropdown-button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    type="white"
    has-edit-icon
    has-dropdown-icon
    class="me-2 mt-2 mb-5"
  >
    Group
  </BaseButton>
  <BaseDropdown id="email-group-dropdown-list">
    <BaseDropdownOption
      v-for="group in emailGroups"
      :key="group"
    >
      <BaseFormRadio
        :id="`email-group-${group}`"
        v-model="groupFilter"
        :value="group"
      >
        {{ group }}
      </BaseFormRadio>
    </BaseDropdownOption>
    <BaseDropdownOption>
      <BaseButton
        type="link"
        @click="clearFilter()"
      >
        Clear
      </BaseButton>
    </BaseDropdownOption>
  </BaseDropdown>

  <BaseDatatable
    title="email"
    no-edit-action
    no-delete-action
    no-hover
    :table-headers="tableHeaders"
    :table-datas="tableReportsData"
  >
    <template #action-options="{ row }">
      <BaseDropdownOption
        text="View"
        @click="previewContent(row)"
      />
      <BaseDropdownOption
        text="Rename"
        @click="triggerRenameEmailModal(row)"
      />
      <BaseDropdownOption
        text="Report"
        :link="`/emails/${row.emailRefKey}/report?fm=email`"
      />
    </template>
  </BaseDatatable>
  <!-- table header for button actions if existed -->

  <!--
        insert slot for action button here if existed
        access v-for item data by {{ scopedData.item.object_key_here }}
    -->
  <!-- temporarily hide individual email report -->
  <!-- <Link
        :href="`/emails/${scopedData.item.emailRefKey}/report`"
        :class="{ 'disabled-link': !isPermit }"
      >
        <button
          type="button"
          class="datatableEditButton"
        >
          <i
            class="far fa-file-alt"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Info"
          />
        </button>
      </Link> -->
  <RenameEmailModal :data="renameModalData" />
</template>

<script>
import eventBus from '@services/eventBus.js';
import RenameEmailModal from '@email/components/RenameEmailModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'EmailReports',
  components: { RenameEmailModal },
  props: {
    reports: { type: Array, default: () => [] },
    emailGroups: { type: Array, default: () => [] },
  },
  data() {
    return {
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : data column name in table
         */
        { name: 'Email Name', key: 'name', width: '40%' },
        { name: 'Send Date', key: 'details' },
        {
          name: `Sent`,
          key: 'sent',
        },
        {
          name: `Open Rate`,
          key: 'openRate',
        },
        {
          name: `Bounced Rate`,
          key: 'bouncedRate',
        },
        {
          name: `Click Rate`,
          key: 'clickRate',
        },
      ],
      groupFilter: null,
      renameModalData: {},
    };
  },
  computed: {
    tableReportsData() {
      return this.reports
        .filter(
          (e) =>
            !this.groupFilter ||
            e.group.some((ee) => this.groupFilter.includes(ee))
        )
        .map((report) => ({
          name: report.name,
          details: report.schedule,
          sent: report.sent,
          openRate: `${report.openRate}%`,
          clickRate: `${report.clickRate}%`,
          bouncedRate: `${report.bouncedRate}%`,

          // for action buttons
          emailRefKey: report.emailRefKey,
          actionLink: report.viewEmailUrl,
          emailDesign: report.emailDesign,
        }));
    },
    isPermit() {
      return !this.$page.props.permissionData.featureForPaidPlan.includes(
        'email-report'
      );
    },
  },
  mounted() {
    if (!this.isPermit) eventBus.$emit('trigger-paid-plan-modal');
  },
  methods: {
    clearFilter() {
      this.groupFilter = null;
    },
    triggerRenameEmailModal({ name: emailName, emailRefKey: referenceKey }) {
      const modal = new Modal(document.getElementById('rename-email-modal'));
      this.renameModalData = { modal, emailName, referenceKey };
      modal.show();
    },
    previewContent(reportItem) {
      const { emailDesign } = reportItem;

      // email design (preview) has some chance to be null
      // so remember to check it and provide user some feedback
      if (!emailDesign?.preview) {
        this.$toast.warning('Warning', 'This report has no design to preview');
        return;
      }

      const { reference_key: emailDesignRefKey, html } = emailDesign;
      const previewUrl = `/emails/${reportItem.emailRefKey}/design/${emailDesignRefKey}/preview`;
      const putSessionUrl = `/emails/${reportItem.emailRefKey}/design/${emailDesignRefKey}/preview/session`;

      this.$axios
        .post(putSessionUrl, {
          html,
        })
        .then(() => {
          window.open(previewUrl, '_blank');
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

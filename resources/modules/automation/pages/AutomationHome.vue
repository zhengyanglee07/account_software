<template>
  <BaseDatatable
    title="automation"
    :table-headers="tableHeaders"
    :table-datas="tableAutomationsData"
    @delete="deleteAutomation"
  >
    <template #action-button>
      <BaseButton
        id="add-automation-button"
        has-add-icon
        data-bs-target="#create-new-automation-modal"
        data-bs-toggle="modal"
        @click="toggleCreateAutomation"
      >
        Add Automation
      </BaseButton>
    </template>
    <template #cell-status="{ row: { status } }">
      <span
        class="badge"
        :class="{
          // for all available statuses please refer to automation_status db table
          'badge-secondary': status === 'draft',
          'badge-warning': status === 'paused',
          'badge-success': status === 'activated',
        }"
      >
        {{ status.toUpperCase() }}
      </span>
    </template>
  </BaseDatatable>
  <div>
    <CreateNewAutomationModal />
    <!-- <limit-modal
      :show_modal="showLimitModal"
      @close="showLimitModal = !showLimitModal"
      modal_title="Only For Paid Plan"
      customContext="Subscribe to one of our plan to enjoy free access to this feature."
    ></limit-modal> -->
  </div>
</template>

<script>
import CreateNewAutomationModal from '@automation/components/ModalsCreateNewAutomationModal.vue';
import { getFormattedTimezoneDatetime } from '@shared/lib/timezone.js';
import { Modal, Tooltip } from 'bootstrap';
import axios from 'axios';

export default {
  name: 'AutomationHome',
  components: { CreateNewAutomationModal },
  props: {
    dbAutomations: Array,
  },
  data() {
    return {
      showLimitModal: false,
      automations: [],
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */
        { name: 'Name', key: 'name' },
        {
          name: `Trigger`, // singular first, for current version (Jan 2020 - )
          key: 'triggers', // plural here just for precaution, if in the future multi-trigger is implemented
        },
        {
          name: `Entered`,
          key: 'total_entered',
        },
        {
          name: `Currently In`,
          key: 'total_pending',
        },
        {
          name: `Completed`,
          key: 'total_completed',
        },
        {
          name: 'Status',
          key: 'status',
          custom: true,
        },
      ],
      modalId: 'automation-delete-modal',
      selectedId: '',
      emptyState: {
        title: 'automation',
        description: 'automations',
      },
    };
  },
  computed: {
    tableAutomationsData() {
      return this.automations.map((automation) => ({
        ...automation,
        triggers: automation.automation_triggers
          .map((t) => t.trigger.name)
          .join(', '),
        details: `Edited on ${getFormattedTimezoneDatetime(
          automation.updated_at
        )}`,
        editLink: `/automations/${automation.reference_key}`,
      }));
    },
  },
  watch: {
    dbAutomations: {
      handler() {
        this.automations = [...this.dbAutomations];
      },
      immediate: true,
    },
  },

  created() {
    this.$nextTick(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );

      const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl, {
          trigger: 'hover',
        });
      });
    });
  },
  methods: {
    toggleDeleteModal(id) {
      this.automation_delete_modal = new Modal(
        document.getElementById('automation-delete-modal')
      );
      this.automation_delete_modal.show();
      this.selectedId = id;
    },

    async deleteAutomation(id) {
      try {
        await axios.delete(`/automations/${id}`);
      } catch (err) {
        this.$toast.error(
          'Error',
          'Failed to delete automation, please try again'
        );
        return;
      }

      this.automations = this.automations.filter(
        (automation) => automation.id !== id
      );

      this.$toast.success('Success', 'Successfully deleted automation');
    },

    closeDeleteModal() {
      this.automation_delete_modal = new Modal(
        document.getElementById('automation-delete-modal')
      );
      this.automation_delete_modal.hide();
    },

    toggleCreateAutomation() {
      if (this.isAddAutomation) {
        this.create_new_automation_modal = new Modal(
          document.getElementById('create-new-automation-modal')
        );
        this.create_new_automation_modal.show();
      } else {
        this.showLimitModal = !this.isAddAutomation;
      }
    },
  },
};
</script>

<style scoped lang="scss">
@media (max-width: 450px) {
  :deep(th.sorting:nth-child(3)) {
    min-width: 150px !important;
  }
}
</style>

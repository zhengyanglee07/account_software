<template>
  <div>
    <BaseDatatable
      no-edit-action
      :table-headers="tableHeaders"
      :table-datas="tableSegmentsData"
      :empty-state="emptyState"
      title="segment"
      @delete="removeRecord"
    >
      <template #action-button>
        <Link
          id="add-segment-button"
          :href="`/people`"
        >
          <BaseButton has-add-icon>
            Add Segment
          </BaseButton>
        </Link>
      </template>

      <template
        v-if="!isProd"
        #cell-socials="{ row: scopedData }"
      >
        <span v-if="scopedData.medias.length === 0">no media found</span>
        <i
          v-for="(m, i) in scopedData.medias"
          :key="i"
          class="fa me-3"
          :class="{
            'fa-google': m.name === 'google',
            'fa-facebook-f': m.name === 'facebook',
          }"
          style="font-size: 1.5rem"
          :style="{
            color: m.isSynced ? 'green' : '',
          }"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          :title="m.name + (m.isSynced ? ' - Synced' : '')"
        />
      </template>

      <template #action-options="{ row: { id, viewLink } }">
        <BaseDropdownOption
          text="View"
          :link="viewLink"
        />
        <BaseDropdownOption
          text="Rename"
          data-bs-toggle="modal"
          data-bs-target="#segment-rename-modal"
          @click="updateSelectedSegmentId(id)"
        />
        <BaseDropdownOption
          text="Report"
          @click="redirectToReport"
        />
      </template>
    </BaseDatatable>

    <SegmentRenameModal
      modal-id="segment-rename-modal"
      @rename-segment="handleRenameSegment"
    />
    <SegmentSyncModal
      modal-id="segment-sync-modal"
      :segment-id="selectedSegmentId"
      :available-social-medias="availableSocialMedias"
      :used-social-medias="usedSocialMedias"
      @sync-segment="handleSyncSegment"
    />
  </div>
</template>

<script>
import SegmentRenameModal from '@people/components/SegmentRenameModal.vue';
import { validationFailedNotification } from '@shared/lib/validations.js';
import SegmentSyncModal from '@people/components/SegmentSyncModal.vue';
import { Tooltip, Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

export default {
  components: { SegmentSyncModal, SegmentRenameModal },
  filters: {
    capitalize(value) {
      if (!value) return '';
      value = value.toString();
      return value.charAt(0).toUpperCase() + value.slice(1);
    },
  },
  props: {
    accountRandomId: String,
    displaySegments: Array,
    availableSocialMedias: Array,
    isProd: Boolean,
  },
  data() {
    return {
      tableHeaders: [
        { name: 'Name', key: 'segmentName', order: 0, width: '250px' },
        { name: 'Conditions', key: 'formattedConditionFilters', order: 0 },
        { name: `People`, key: 'people', order: 0, textalign: 'flex-end' },
        { name: 'Social Media', key: 'socials', custom: true },
      ],

      segments: [],
      selectedSegmentId: null,
      segmentIdToRename: '',
      modalId: 'segment-page-delete-modal',
      renameModalId: 'segment-rename-modal',
      selectedId: '',
      emptyState: {
        title: 'segment',
        description: 'segments',
      },
    };
  },
  computed: {
    tableSegmentsData() {
      return this.segments.map((segment) => ({
        id: segment.id,
        segmentName: segment.segmentName,

        formattedConditionFilters: segment.formattedConditionFilters
          .slice(1)
          .reduce((acc, val) => `${acc} ${val}\n`, ''),
        people: segment.people,
        referenceKey: segment.reference_key,
        medias: segment.used_social_medias,
        viewLink: `/segments/${segment.reference_key}`,
      }));
    },
    usedSocialMedias() {
      return this.segments
        .find((segment) => segment.id === this.selectedSegmentId)
        ?.used_social_medias.map((us) => us.name);
    },
  },
  watch: {
    displaySegments: {
      handler(newSegments) {
        this.segments = [...newSegments];
      },
      immediate: true, // basically just watch on mount
    },
    selectedSegmentId(id) {
      const segment = this.tableSegmentsData.find((e) => e.id === id);
      eventBus.$emit('get-segment-name', segment.segmentName);
    },
  },

  methods: {
    updateSelectedSegmentId(id) {
      this.selectedSegmentId = id;
    },
    handleRenameSegment(segmentName) {
      this.$axios
        .put(`/segments/${this.selectedSegmentId}/rename`, {
          segmentName,
        })
        .then(() => {
          this.$toast.success('Success', 'Successfully rename segment.');
          Modal.getInstance(
            document.getElementById('segment-rename-modal')
          ).hide();

          this.segments = this.segments.map((segment) => {
            if (segment.id === this.selectedSegmentId) {
              return {
                ...segment,
                segmentName,
              };
            }

            return segment;
          });
        })
        .catch((err) => {
          validationFailedNotification(err);
        });
    },
    // handleSyncSegment(data) {
    //   this.segments = this.segments.map((s) => {
    //     const medias = s.used_social_medias;
    //     const mediaExists = medias.find((m) => m.name === data.media.name);

    //     if (mediaExists || this.selectedSegmentId !== s.id) return s;

    //     return {
    //       ...s,
    //       used_social_medias: [...medias, data.media],
    //     };
    //   });

    //   this.$toast.success(
    //     'Success',
    //     'Successfully sync your segment. Please wait at most 1 hour for it to be effective'
    //   );

    //   Modal.getInstance(document.getElementById('segment-sync-modal')).hide();
    // },

    removeRecord(segmentId) {
      this.selectedId = segmentId;
      this.$toast.info('Info', 'Deleting segment...');

      this.$axios
        .delete(`/segments/${segmentId}`)
        .then(() => {
          this.$toast.success('Success', 'Segment Deleted');

          this.segments = this.segments.filter(
            (segment) => segment.id !== segmentId
          );
        })
        .catch((error) => {
          this.$toast.error('Error', 'Failed to delete segment.');
        });
    },

    redirectToReport() {
      window.location.href = '/report/growth';
    },
  },
};
</script>

<style scoped lang="scss"></style>

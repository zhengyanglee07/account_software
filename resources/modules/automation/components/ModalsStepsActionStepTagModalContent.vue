<template>
  <div
    class="modal-body row pb-10 px-lg-17"
    style="padding-top: 0 !important"
  >
    <ActionStepTagSelector
      v-model="selectedProcessedTagId"
      @addTag="addTag"
    />
  </div>
  <div class="modal-footer flex-center">
    <BaseButton
      v-if="isAddStepModal"
      type="light"
      @click="$emit('back', $event)"
    >
      Back
    </BaseButton>
    <BaseButton @click="saveAddOrRemoveTagActionStep">
      Save
    </BaseButton>
  </div>
</template>
<script>
import { deepSearch } from '@shared/lib/deep.js';
import { mapState, mapActions } from 'vuex';
import ActionStepTagSelector from '@automation/components/ModalsStepsActionStepTagSelector.vue';
import modalSaveBtnMixin from '@automation/mixins/modalSaveBtnMixin.js';

export default {
  name: 'ActionStepTagModalContent',
  components: {
    ActionStepTagSelector,
  },
  mixins: [modalSaveBtnMixin],
  props: {
    selectedActionKind: String,
    isAddStepModal: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      selectedProcessedTagId: null,
    };
  },
  computed: {
    ...mapState('automations', ['steps', 'modal', 'tags']),
  },
  methods: {
    ...mapActions('automations', ['insertOrUpdateStep']),
    findStep() {
      const {
        data: { id, index, parent, config },
      } = this.modal;

      // new step
      if (!id) return null;

      return deepSearch(this.steps, 'id', (k, v) => v === id);
    },

    loadSavedTagId() {
      const step = this.findStep();
      if (!step) return null;

      const { kind } = step;

      // set null if selectedAction's type is different.
      //
      // This can cater for the following situations:
      // 1. new step is created
      // 2. user changes kind of action (e.g. from add tag to remove tag)
      //    on existing step
      //
      // ON situation 2 above, this can prevent one action accidentally
      // load another action's states (tag id) upon selectedActionKind change
      this.selectedProcessedTagId =
        kind === this.selectedActionKind
          ? step.properties.processed_tag_id
          : null;
      return null;
    },

    generateTagActionDesc(tagId) {
      const tag = this.tags.find((t) => t.id === tagId);
      const tagName = tag.tagName ?? '[empty name]';
      const action =
        this.selectedActionKind === 'automationAddTagAction' ? 'Add' : 'Remove';

      return `${action} ${tagName} tag`;
    },

    async saveAddOrRemoveTagActionStep() {
      const {
        data: { id, index, parent, config },
      } = this.modal;

      if (!this.selectedProcessedTagId) {
        this.$toast.warning('Warning', 'Please select one tag before saving');
        return null;
      }

      this.saving = true;

      await this.insertOrUpdateStep({
        id,
        index,
        data: {
          type: 'action',
          kind: this.selectedActionKind,
          desc: this.generateTagActionDesc(this.selectedProcessedTagId),
          properties: {
            processed_tag_id: this.selectedProcessedTagId,
          },
        },
        parent,
        config,
      });

      this.saving = false;
      this.$emit('close-modal');
      return null;
    },
  },
  watch: {
    selectedActionKind: {
      handler() {
        this.loadSavedTagId();
      },
      immediate: true,
    },
  },
};
</script>

<style scoped lang="scss"></style>

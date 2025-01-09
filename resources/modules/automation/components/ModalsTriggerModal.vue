<template>
  <BaseModal
    :modal-id="modalId"
    title="Add Trigger"
  >
    <div class="mimic-modal-body pb-3">
      <Transition
        name="fade"
        mode="out-in"
      >
        <div
          v-if="!showSegmentConfigs"
          key="general"
        >
          <BaseFormGroup label="Which event should trigger this automation?">
            <BaseFormSelect
              v-model="selectedTrigger"
              :options="triggerOptions"
              label-key="name"
              @update:modelValue="resetTriggerProperties"
            />
            <BaseMultiSelect
              v-if="false"
              v-model="selectedTrigger"
              :options="triggerOptions"
              label="name"
              @option:selected="resetTriggerProperties"
            >
              <template #option="{ option }">
                <div class="option_wrapper">
                  <strong>{{ option.name }}</strong>
                  <p>{{ triggerDesc(option.type) }}</p>
                </div>
              </template>
            </BaseMultiSelect>
          </BaseFormGroup>

          <TriggerModalSubmitFormContent
            v-if="selectedTriggerType === 'submit_form'"
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalPurchaseProductContent
            v-if="selectedTriggerType === 'purchase_product'"
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalDateBasedContent
            v-if="selectedTriggerType === 'date_based'"
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalTagContent
            v-if="
              selectedTriggerType === 'add_tag' ||
                selectedTriggerType === 'remove_tag'
            "
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalOrderSpentContent
            v-if="selectedTriggerType === 'order_spent'"
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalPlaceAnOrderContent
            v-if="selectedTriggerType === 'place_order'"
            v-model="triggerProperties"
            class="mt-3"
          />

          <TriggerModalSegmentContent
            v-if="
              selectedTriggerType === 'enter_segment' ||
                selectedTriggerType === 'exit_segment'
            "
            v-model="triggerProperties"
            class="mt-3"
          />

          <BaseButton
            type="link"
            class="mt-5"
            @click="showSegmentConfigs = true"
          >
            <i class="fas fa-user-cog me-2" />
            Configure segment
          </BaseButton>
        </div>

        <div
          v-else
          key="segment"
        >
          <BaseButton
            type="link"
            class="mb-4"
            @click="showSegmentConfigs = false"
          >
            <i class="fa fa-angle-left me-2" />
            Back
          </BaseButton>
          <BaseFormGroup label="Which segment should enter this automation?">
            <p class="mt-1">
              Only specific contact matched your selected segment can enter this
              automation.
            </p>
            <BaseMultiSelect
              v-model="selectedSegmentId"
              label="segmentName"
              :options="segments"
              :reduce="(segment) => segment.id"
            />
          </BaseFormGroup>
        </div>
      </Transition>
    </div>

    <template #footer>
      <BaseButton @click="saveAutomationTrigger">
        Save
      </BaseButton>
      <BaseButton
        v-show="false"
        id="automation-trigger-modal-close-button"
        type="button"
        data-bs-dismiss="modal"
        aria-label="Close"
      />
    </template>
  </BaseModal>
</template>

<script>
/* eslint-disable indent */

import { mapState, mapActions } from 'vuex';
import modalDataMixin from '@automation/mixins/modalDataMixin.js';
import modalSaveBtnMixin from '@automation/mixins/modalSaveBtnMixin.js';
import TriggerModalSubmitFormContent from '@automation/components/ModalsTriggerModalSubmitFormContent.vue';
import TriggerModalPurchaseProductContent from '@automation/components/ModalsTriggerModalPurchaseProductContent.vue';
import TriggerModalDateBasedContent from '@automation/components/ModalsTriggerModalDateBasedContent.vue';
import TriggerModalTagContent from '@automation/components/ModalsTriggerModalTagContent.vue';
import TriggerModalOrderSpentContent from '@automation/components/ModalsTriggerModalOrderSpentContent.vue';
import TriggerModalPlaceAnOrderContent from '@automation/components/ModalsTriggerModalPlaceAnOrderContent.vue';
import TriggerModalSegmentContent from '@automation/components/ModalsTriggerModalSegmentContent.vue';

export default {
  name: 'TriggerModal',
  components: {
    TriggerModalOrderSpentContent,
    TriggerModalTagContent,
    TriggerModalDateBasedContent,
    TriggerModalPurchaseProductContent,
    TriggerModalSubmitFormContent,
    TriggerModalPlaceAnOrderContent,
    TriggerModalSegmentContent,
  },
  mixins: [modalDataMixin, modalSaveBtnMixin],
  data() {
    return {
      modalId: 'automation-trigger-modal',
      selectedTrigger: null,
      triggerProperties: null,

      showSegmentConfigs: false,
      selectedSegmentId: null,
    };
  },
  computed: {
    ...mapState('automations', [
      'automationTriggers',
      'triggerOptions',
      'segments',
    ]),

    selectedTriggerType() {
      return this.selectedTrigger?.type;
    },
  },

  methods: {
    ...mapActions('automations', ['createOrUpdateAutomationTrigger']),
    loadSavedTrigger(id) {
      const automationTrigger = this.automationTriggers.find(
        (at) => at.id === id
      );

      this.triggerProperties = {
        ...automationTrigger.triggerKind,
      };

      this.selectedTrigger = this.triggerOptions.find(
        (triggerOption) => triggerOption.id === automationTrigger.trigger_id
      );

      this.selectedSegmentId = automationTrigger.segment_id;
    },
    resetTriggerProperties() {
      this.triggerProperties = null;
    },

    // override mixin
    showBsModalListener() {
      this.checkModalState('trigger');

      const {
        data: { id },
      } = this.modal;

      // new delay
      if (!id) return;

      this.loadSavedTrigger(id);
    },
    hiddenBsModalListener() {
      this.resetModal();

      // reset local states
      this.selectedTrigger = null;
      this.resetTriggerProperties();
      this.showSegmentConfigs = false;
      this.selectedSegmentId = null;
    },

    triggerDesc(triggerType) {
      switch (triggerType) {
        case 'submit_form': {
          return 'This event is fired when a person submitted a form';
        }

        // case 'purchase_product': {
        //   return 'This event is fired when a person purchased a product on your website';
        // }

        case 'date_based': {
          return 'This event is fired when a specific date is met';
        }

        case 'add_tag': {
          return 'This event is fired when you add a tag on people';
        }

        case 'remove_tag': {
          return 'This event is fired when you remove a tag from people';
        }

        // case 'order_spent': {
        //   return 'This event is fired when your customer order spent met certain conditions';
        // }

        case 'abandon_cart': {
          return 'This event is fired when your customer abandons cart';
        }

        case 'place_order': {
          return 'This event is fired when an order is placed';
        }

        default: {
          break;
        }
      }
      return null;
    },

    closeModal() {
      document.getElementById('automation-trigger-modal-close-button').click();
    },

    async saveAutomationTrigger() {
      const {
        data: { id },
      } = this.modal;

      this.saving = true;

      if (!this.selectedTrigger) {
        this.$toast.error(
          'Error',
          'Failed to save. You have not select a trigger event.'
        );
        return;
      }

      if (this.triggerProperties?.segmentName)
        this.triggerProperties = { segment_id: this.triggerProperties.id };

      await this.createOrUpdateAutomationTrigger({
        id,
        triggerId: this.selectedTrigger.id,
        properties: this.triggerProperties,
        segmentId: this.selectedSegmentId,
      });

      this.saving = false;
      this.closeModal();
    },
  },
};
</script>

<style lang="scss" scoped></style>

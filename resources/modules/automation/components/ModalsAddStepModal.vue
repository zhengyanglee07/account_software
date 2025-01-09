<template>
  <BaseModal
    :modal-id="modalId"
    title="Add A New Step"
    size="xl"
    manual
    no-footer
  >
    <div
      v-if="!selectedStep"
      class="modal-body row py-10 px-lg-17"
    >
      <div class="row justify-content-between">
        <SelectionCard
          class="col-md-6 me-3"
          @click="showDelayStep"
        >
          <template #image>
            <img
              src="@automation/assets/media/automation-delay-icon.svg"
              width="40"
              alt="Delay"
            >
          </template>
          <template #title>
            Delay
          </template>
          <template #description>
            Wait for a given period of time before continuing down the path
          </template>
        </SelectionCard>
        <SelectionCard
          class="col-md"
          @click="showActionStep"
        >
          <template #image>
            <img
              src="@automation/assets/media/automation-action-icon.svg"
              width="40"
              alt="Actions"
            >
          </template>
          <template #title>
            Action
          </template>
          <template #description>
            Perform an action, such as add tag or send an one-off email
          </template>
        </SelectionCard>
        <SelectionCard
          class="col-md-6 me-3"
          @click="showDecisionStep"
        >
          <template #image>
            <img
              src="@automation/assets/media/automation-decision-icon.svg"
              width="40"
              alt="Decisions"
            >
          </template>
          <template #title>
            Decision
          </template>
          <template #description>
            Continue in 2 different ways depends on whether all the rules are
            matched
          </template>
        </SelectionCard>
        <SelectionCard
          class="col-md"
          @click="insertExitStep"
        >
          <template #image>
            <img
              src="@automation/assets/media/automation-exit-icon.svg"
              width="40"
              alt="Exit"
            >
            <!-- TODO: change img -->
          </template>
          <template #title>
            Exit
          </template>
          <template #description>
            Remove the contact from workflow once he meets this element
          </template>
        </SelectionCard>
      </div>
    </div>

    <DelayStepModalContent
      v-else-if="selectedStep === 'delay'"
      v-model="delay"
      is-add-step-modal
      @close-modal="closeModal"
      @back="backToMain"
    />

    <ActionStepModalContent
      v-else-if="selectedStep === 'action'"
      :selected-action="selectedAction"
      is-add-step-modal
      @update-selected-action="handleUpdateSelectedAction"
      @close-modal="closeModal"
      @back="backToMain"
    />

    <DecisionStepModalContent
      v-else-if="selectedStep === 'decision'"
      v-model:value="decision"
      is-add-step-modal
      @close-modal="closeModal"
      @back="backToMain"
    />
    <button
      v-show="false"
      id="automation-add-step-modal-close-button"
      type="button"
      data-bs-dismiss="modal"
      aria-label="Close"
    />
  </BaseModal>
</template>

<script>
import { mapMutations, mapActions, mapState } from 'vuex';
import modalDataMixin from '@automation/mixins/modalDataMixin.js';
import DelayStepModalContent from '@automation/components/ModalsStepsDelayStepModalContent.vue';
import ActionStepModalContent from '@automation/components/ModalsStepsActionStepModalContent.vue';
import DecisionStepModalContent from '@automation/components/ModalsStepsDecisionStepModalContent.vue';

const getDefaultDelay = () => ({
  duration: 5,
  unit: 'minute',
});

const getDefaultDecision = () => ({
  filters: [
    [
      {
        id: 1,
        name: '',
        subFilters: [],
      },
    ],
  ],
});

export default {
  name: 'AddStepModal',
  components: {
    DelayStepModalContent,
    ActionStepModalContent,
    DecisionStepModalContent,
  },
  mixins: [modalDataMixin],
  data() {
    return {
      modalId: 'automation-add-step-modal',
      selectedStep: null,

      delay: getDefaultDelay(),
      selectedAction: null,
      action: null,
      decision: getDefaultDecision(),
    };
  },
  computed: {
    ...mapState('automations', ['stepActions']),
    defaultSelectedAction() {
      return this.stepActions[0];
    },
  },
  mounted() {
    this.selectedAction = this.defaultSelectedAction;
  },
  methods: {
    ...mapActions('automations', ['insertOrUpdateStep']),
    ...mapMutations('automations', ['updateModal']),

    // override mixin
    hiddenBsModalListener() {
      this.resetModal();

      // reset local states
      this.selectedStep = null;
      this.delay = getDefaultDelay();
      this.action = null;
      this.decision = getDefaultDecision();
      this.selectedAction = this.defaultSelectedAction;
    },

    backToMain() {
      // reset local states
      this.selectedStep = null;
      this.delay = getDefaultDelay();
      this.action = null;
      this.decision = getDefaultDecision();
      this.selectedAction = this.defaultSelectedAction;
    },

    showDelayStep() {
      this.selectedStep = 'delay';
    },
    showActionStep() {
      this.selectedStep = 'action';
    },
    showDecisionStep() {
      this.selectedStep = 'decision';
    },
    async insertExitStep() {
      const {
        data: { id, index, parent, config },
      } = this.modal;

      await this.insertOrUpdateStep({
        id,
        index,
        data: {
          type: 'exit',
          kind: 'exit',
          desc: 'Remove the contact from workflow once he meets this element',
          properties: {},
        },
        parent,
        config,
      });
      document.getElementById('automation-add-step-modal-close-button').click();
    },
    handleUpdateSelectedAction(newSelectedAction) {
      this.selectedAction = { ...newSelectedAction };
    },
  },
};
</script>

<style lang="scss" scoped></style>

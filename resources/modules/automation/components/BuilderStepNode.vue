<template>
  <div>
    <div
      v-for="(step, idx) in steps"
      :key="step.id"
      class="d-flex flex-column justify-content-center align-items-center"
    >
      <template v-if="step.type !== 'decision' && step.type !== 'exit'">
        <div
          :id="`step-node-${step.id}`"
          class="step_node_div"
        >
          <div class="step_node_content">
            <button
              class="node_button"
              :class="{
                delay_border: step.type === 'delay',
                action_border: step.type === 'action',
              }"
              @mouseover="updateSelectedAction(step)"
              @click="showStepModal(step)"
            >
              <span class="btn_left_border" />

              <div
                :class="`image_container${
                  step.type === 'delay' ? '_delay' : ''
                }`"
              >
                <img
                  class="container_image"
                  :class="{
                    'container_image--new': step.type === 'delay',
                  }"
                  :src="getStepNodeImageSrc(step.type, step.kind || '')"
                  alt="img"
                >
              </div>

              <span class="divider" />

              <span class="text_wrapper">
                <span class="font-weight-bold">
                  {{ getStepName(step.type) }}
                </span>
                <span class="step-description">{{ step.desc }}</span>
              </span>

              <span class="action_button step_node_action">
                <span
                  v-if="showReport(step)"
                  class="me-2"
                  @click.stop="viewReport(step.id)"
                >
                  <i
                    class="fa-solid fa-file-lines email-action-icon"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Report"
                  />
                </span>
                <span
                  :class="{
                    'me-5': !showReport(step),
                  }"
                  @click.stop="deleteAutomationStep({ id: step.id })"
                >
                  <i
                    class="fas fa-trash-alt email-action-icon"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Delete"
                  />
                </span>
              </span>
            </button>
            <div
              v-if="step.kind === 'automationSendEmailAction'"
              class="node_statistics"
            >
              <div>
                <p>Sent</p>
                <h4>{{ getEmailStatistics(step.id, 'sent') }}</h4>
              </div>
              <div>
                <p>Opened</p>
                <h4>{{ getEmailStatistics(step.id, 'opened') }}</h4>
              </div>
              <div>
                <p>Clicked</p>
                <h4>{{ getEmailStatistics(step.id, 'clicked') }}</h4>
              </div>
            </div>
            <div
              v-else
              class="node_statistics"
            >
              <div>
                <p>Completed</p>
                <h4>{{ getBaseStatistics(step.id) }}</h4>
              </div>
            </div>
          </div>
        </div>

        <AddStepButton
          :index="idx + 1"
          :parent="parent"
          :config="config"
        />
      </template>

      <template v-if="step.type === 'exit'">
        <div
          :id="`step-node-${step.id}`"
          class="step_node_div exit-div"
        >
          <ExitNode />
        </div>
      </template>

      <template v-else-if="step.type === 'decision'">
        <div
          :id="`step-node-${step.id}`"
          class="step_node_div"
        >
          <button
            class="node_button"
            @click="showStepModal(step)"
          >
            <span class="btn_left_border" />

            <img
              class="container_image--new"
              style="margin: 0 18px"
              :src="getStepNodeImageSrc(step.type, step.kind || '')"
              alt="img"
            >

            <span class="divider" />

            <span class="text_wrapper">
              <span class="font-weight-bold">
                {{ getStepName(step.type) }}
              </span>
              <span class="step-description">{{ step.desc }}</span>
            </span>

            <span class="action_button">
              <span @click.stop="deleteAutomationStep({ id: step.id })">
                <span class="me-5">
                  <i
                    class="fas fa-trash-alt email-action-icon"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Delete"
                  />
                </span>
              </span>
            </span>
          </button>
        </div>

        <DecisionStepNode
          id="decisionStepNode"
          style="margin: 3rem"
          :yes-routes="step.yes"
          :no-routes="step.no"
          :last-btn-idx="idx + 1"
          :parent="step"
          :statistics="statistics"
        />

        <AddStepButton
          :key="idx"
          class="mt-5"
          :index="idx + 1"
          :parent="parent"
          :config="config"
        />
      </template>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import { getStepNodeImageSrc } from '@lib/automations';
import { Modal, Tooltip } from 'bootstrap';
import ExitNode from '@automation/components/BuilderExitNode.vue';
import AddStepButton from '@automation/components/BuilderAddStepButton.vue';
import DecisionStepNode from '@automation/components/BuilderDecisionStepNode.vue';
import eventBus from '@shared/services/eventBus.js';

const capitalize = (s) => s.charAt(0).toUpperCase() + s.slice(1);

export default {
  name: 'StepNode',
  components: {
    AddStepButton,
    DecisionStepNode,
    ExitNode,
  },
  props: {
    // step: Object,
    steps: Array,
    parent: {
      type: Object,
      default: () => ({}),
    },
    config: {
      type: Object,
      default: () => ({}),
    },
    statistics: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      isHover: false,
    };
  },
  computed: {
    ...mapState('automations', [
      'sendEmailActionsEntities',
      'completedEmailIds',
      'stepBasedStatistics',
      'status',
    ]),
  },

  methods: {
    ...mapActions('automations', ['deleteAutomationStep']),
    ...mapMutations('automations', ['updateModal', 'resetModal']),

    getEmailStatistics(stepId, type) {
      if (
        !this.stepBasedStatistics[stepId] ||
        !this.stepBasedStatistics[stepId].extra
      )
        return 0;
      const { extra } = this.stepBasedStatistics[stepId];
      const totalSent = extra.total_sent;
      if (type === 'sent') return totalSent;
      if (totalSent === 0) return `${totalSent}%`;
      const rate = (extra[`total_${type}`] / totalSent) * 100;
      return `${rate.toFixed(2)}%`;
    },
    getBaseStatistics(stepId) {
      return this.stepBasedStatistics[stepId]?.completed ?? 0;
    },

    getStepNodeImageSrc,

    getStepName(type) {
      return capitalize(type);
    },

    isDelay(stepType) {
      return stepType === 'delay';
    },
    isAction(stepType) {
      return stepType === 'action';
    },

    showStepModal(step, index) {
      this.updateModal({
        modal: {
          type: 'step',
          data: {
            id: step.id,
            index,
            parent: this.parent,
            config: this.config,
          },
        },
      });

      const modalId = `automation-${step.type}-step-modal`;
      new Modal(document.getElementById(modalId), {
        backdrop: 'static',
      }).show();
    },
    updateSelectedAction(step, index) {
      this.resetModal();
      this.updateModal({
        modal: {
          type: 'step',
          data: {
            id: step.id,
            index,
            parent: this.parent,
            config: this.config,
          },
        },
      });
      eventBus.$emit('step-action-changed');
    },
    showReport(step) {
      return (
        step.kind === 'automationSendEmailAction' &&
        this.completedEmailIds.includes(step?.properties?.email_id)
      );
    },
    viewReport(stepId) {
      const email = this.sendEmailActionsEntities[stepId];
      window.open(`/emails/${email.email_reference_key}/report`);
    },
    hideStatistics() {
      const statisticsElem = document.querySelectorAll('.node_statistics');
      statisticsElem.forEach((e) => {
        e.style.display = 'none';
      });
      const stepNodeDiv = document.querySelectorAll('.step_node_div');
      stepNodeDiv.forEach((e) => {
        e.style.margin = '1.2rem';
      });
    },
    changeActionButtonPosition() {
      const actionButton = document.querySelectorAll('.step_node_action');
      actionButton.forEach((e) => {
        e.style.bottom = '-3.5rem';
      });
    },
    handleAutomationElem() {
      if (this.status === 'draft') {
        this.hideStatistics();
        return;
      }
      this.changeActionButtonPosition();
    },
  },

  mounted() {
    this.handleAutomationElem();

    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new Tooltip(tooltipTriggerEl, {
        trigger: 'hover',
      });
    });
  },

  updated() {
    this.handleAutomationElem();
  },
};
</script>

<style scoped lang="scss">
.step-description {
  overflow-wrap: anywhere;
  word-break: break-word;
  font-size: 13px;
}

.step_node_div {
  margin: 1.2rem;
  height: 68px;
  max-height: 72px;

  &:hover {
    .action_button {
      display: inline-block;
    }
  }
}

.node_button {
  position: relative;
  display: inline-flex;
  align-items: center;
  max-height: 68px;
  padding: 10px 10px;
  margin: 0;
  line-height: 20px;
  border: 1px solid #dbd9d2;
  border-left-color: transparent;
  background: #fff;
  cursor: pointer;
  // width: 100%;
  width: 340px;
  user-select: none;

  .btn_left_border {
    position: absolute;
    left: 0;
    width: 4px;
    height: 100%;
  }

  /* for image inside wrapper */
  .container_image {
    width: 68px;
    height: 68px;

    &--new {
      width: 30px;
      height: 40px;
    }
  }

  .divider {
    margin: 0 12px;
    height: 48px;
    border-left: 1px solid rgba(36, 28, 21, 0.15);
  }

  .text_wrapper {
    display: flex;
    flex-direction: column;
    font-size: 14px;

    span {
      text-align: left;

      &:first-child {
        font-weight: 700;
      }

      &:last-child {
        color: rgba(36, 28, 21, 0.65);
      }
    }
  }

  .action_button {
    position: absolute;
    right: -7rem;
    display: none;
    padding: 2.5rem;

    &:hover {
      display: 'inline-block';
    }
  }
}

.step_node_content:hover {
  border-width: 1px;
  border-left-color: transparent;
  box-shadow: 0 4px 12px rgba(36, 28, 21, 0.12);

  .action_button {
    display: inline-block;
  }
}
.node_statistics {
  border: 1px solid #dbd9d2;
  border-top: 0;
  background-color: #fff;
  display: flex;
  padding: 10px;

  div {
    text-align: center;
    margin: auto;
  }

  p {
    font-weight: 600;
    margin-bottom: 0.25rem;
  }

  h4 {
    font-weight: 400;
    margin-bottom: 0;
  }
}

.step_node_div.exit-div {
  margin: 1.2rem;
  min-height: 70px;
}

.exit-div {
  position: relative;
}

.delay_border,
.node_button {
  .btn_left_border {
    background: #e903df;
  }

  &:hover {
    border-color: lighten(#ced4da, 10%);
  }
}

.action_border {
  .btn_left_border {
    background: $h-secondary-pictonBlue;
  }

  &:hover {
    border-color: lighten(#ced4da, 10%);
  }
}

.email-action-icon {
  font-size: 18px;
  padding-left: 0.5rem;
}

.fa-file-lines:hover {
  color: #17a2b8;
}
.fa-trash-alt:hover {
  color: #dc3545;
}

.step_node_div {
  margin-bottom: 100px;
}

.image_container {
  width: 68px;
  height: 68px;

  &_delay {
    width: 30px;
    height: 40px;
    margin: 0 18px;
  }
}
</style>

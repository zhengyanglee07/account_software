<template>
  <div class="builder-root">
    <!-- <main class="ab-main-panel"> -->
    <div class="container-fluid">
      <div class="navbar navbar-light bg-white border-bottom navbar-fixed-top">
        <div class="col">
          <div class="d-flex align-items-center py-3 px-5">
            <div class="d-flex align-items-center w-400px">
              <div
                v-if="!editAutomationNameMode"
                class="title h-three"
                style="font-size: 18px"
              >
                {{ automationName }}
                <i
                  class="fas fa-pencil-alt fa-xs edit-icon"
                  @click="editAutomationName"
                />
              </div>
              <div v-if="editAutomationNameMode">
                <input
                  id="automationName"
                  v-model="automationName"
                  type="text"
                  class="automation-name-input"
                >
                <!-- <div @click="editAutomationName"> -->
                <i
                  class="fas fa-check fa-xs edit-icon"
                  @click="editAutomationName"
                />
                <!-- </div> -->
              </div>
              <div
                class="automation-status-field pt-0 ms-2"
                @click="
                  changeAutomationStatus(
                    obtainStatusPhrase({
                      draft: 'activate',
                      paused: 'resume',
                      activated: 'pause',
                    })
                  )
                "
              >
                <BaseBadge
                  :text="status.toUpperCase()"
                  :type="badgeType"
                  style="margin: 0 !important"
                />
              </div>
            </div>
            <div class="automation-statictics mx-auto text-center">
              <template v-if="status !== 'draft'">
                <div>
                  <p>Entered</p>
                  <h4>
                    {{ overallStatistics.total_entered }}
                  </h4>
                </div>
                <div>
                  <p>Currently In</p>
                  <h4>{{ overallStatistics.total_pending }}</h4>
                </div>
                <div>
                  <p>Completed</p>
                  <h4>{{ overallStatistics.total_completed }}</h4>
                </div>
              </template>
            </div>
            <div class="d-flex align-items-center">
              <div class="toolbar-section me-3">
                <BaseFormGroup no-margin>
                  <BaseFormSelect
                    v-model="repeat"
                    class="form-select-sm"
                    :options="[
                      { name: 'Triggered once per contact', value: 0 },
                      { name: 'Always trigger for a contact', value: 1 },
                    ]"
                    label-key="name"
                    value-key="value"
                    @update:modelValue="changeExecutionFreq"
                  />
                </BaseFormGroup>
              </div>

              <BaseButton
                size="sm"
                class="save-button"
                :disabled="savingAutomationSettings"
                @click="saveAutomationSettings"
              >
                <span v-show="!savingAutomationSettings"> SAVE </span>
                <span v-show="savingAutomationSettings">
                  <div class="spinner--white-small">
                    <i class="fas fa-spinner fa-pulse" />
                  </div>
                </span>
              </BaseButton>

              <BaseButton
                id="dropdown-menu"
                size="sm"
                class="dropdown-toggle-btn"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="fa-solid fa-caret-down" />
              </BaseButton>

              <BaseDropdown
                id="dropdown-menu"
                style="left: unset"
              >
                <BaseDropdownOption
                  :text="
                    obtainStatusPhrase({
                      draft: 'Activate',
                      paused: 'Resume',
                      activated: 'Pause',
                    })
                  "
                  @click="
                    changeAutomationStatus(
                      obtainStatusPhrase({
                        draft: 'activate',
                        paused: 'resume',
                        activated: 'pause',
                      })
                    )
                  "
                />
                <BaseDropdownOption
                  text="Exit"
                  @click="exitBuilder"
                />
              </BaseDropdown>

              <!-- <li>
                  <button
                    class="dropdown-item"
                    style="font-size: 14px"

                  >
                    <i
                      v-if="status == 'activated'"
                      class="fas fa-pause"
                      style="margin-right: 13px; margin-left: 1px"
                    />
                    <i
                      v-if="status == 'draft'"
                      class="fas fa-check-square"
                      style="
                        margin-right: 11px;
                        margin-left: 1px;
                        font-size: 17px;
                      "
                    />
                    <img
                      v-if="status == 'paused'"
                      src="@automation/assets/media/resume.png"
                      alt=""
                      style="
                        width: 24px;
                        position: relative;
                        left: -4px;
                        top: -1px;
                      "
                    >

                  </button>
                </li> -->

              <!-- <li>
                  <a
                    href="/automations"
                    class="dropdown-item"
                  >
                    <img
                      src="@builder/assets/media/exit.svg"
                      alt=""
                      style="
                        width: 26px;
                        transform: scale(1.8);
                        position: relative;
                        left: -5px;
                        top: -2px;
                      "
                    >
                    Exit
                  </a>
                </li> -->
            </div>
          </div>
        </div>
        <!-- <div id="titleNavShow" class="title h-three" style="font-size: 18px">
          Automation Builder
        </div> -->
      </div>
    </div>
    <div
      id="right_container_content"
      class="right_container_content"
    >
      <div
        id="builder-container"
        ref="builderDiv"
        class="builder-container d-flex flex-column align-items-center"
      >
        <TriggerNodes />

        <StepNode :steps="steps" />

        <ExitNode id="last-exit-node" />
      </div>
    </div>
    <!-- </main> -->

    <TriggerModal />
    <AddStepModal />
    <DelayStepModal />
    <ActionStepModal />
    <DecisionStepModal />
    <SendTestEmailModal
      modal-id="auto-test-email-modal"
      :email-ref-key="testEmailRefKey"
    />
  </div>
</template>

<script>
import { mapGetters, mapState, mapMutations } from 'vuex';
import TriggerNodes from '@automation/components/BuilderTriggerNodes.vue';
import ExitNode from '@automation/components/BuilderExitNode.vue';
import AddStepModal from '@automation/components/ModalsAddStepModal.vue';
import TriggerModal from '@automation/components/ModalsTriggerModal.vue';
import DelayStepModal from '@automation/components/ModalsStepsDelayStepModal.vue';
import DecisionStepModal from '@automation/components/ModalsStepsDecisionStepModal.vue';
import StepNode from '@automation/components/BuilderStepNode.vue';
import {
  transformTags,
  transformLandingPageForms,
  transformUsersProducts,
  transformSegments,
} from '@automation/lib/automations.js';
import nodesConnectionMixin from '@automation/mixins/nodesConnectionMixin.js';
import ActionStepModal from '@automation/components/ModalsStepsActionStepModal.vue';
import { validationFailedNotification } from '@shared/lib/validations.js';
import SendTestEmailModal from '@email/components/SendTestEmailModal.vue';

export default {
  name: 'AutomationBuilder',
  components: {
    SendTestEmailModal,
    DelayStepModal,
    TriggerNodes,
    AddStepModal,
    TriggerModal,
    ActionStepModal,
    DecisionStepModal,
    ExitNode,
    StepNode,
  },
  mixins: [nodesConnectionMixin],
  props: {
    dbAutomation: Object,
    dbLandingPageForms: Array,
    dbSegments: Array,
    dbTags: Array,
    dbTriggers: Array,
    dbUsersProducts: Array,
    dbSendEmailActionsEntities: [Array, Object], // array if empty, else object
    dbSenders: [Array],
    dbCustomFieldNames: {
      type: Array,
      required: true,
    },
    overallStatistics: Object,
    stepBasedStatistics: Object,
  },
  data() {
    return {
      leftPanelWidth: 250, // remember to edit this value if you change leftbar width down there
      changingStatus: false,
      savingAutomationSettings: false,
      editAutomationNameMode: false,

      screenMaximumWidth: 0,
      // draggablePositions: {
      //   clientX: null,
      //   clientY: null,
      //   movementX: 0,
      //   movementY: 0,
      // },
    };
  },

  computed: {
    ...mapGetters('automations', {
      orderedSteps: 'getOrderedSteps',
    }),
    ...mapState('automations', [
      'automationRefKey',
      'testEmailRefKey',
      'status',
      'name',
      'repeat',
      'automationTriggers',
      'steps',
    ]),

    leftPanelHidden() {
      return this.leftPanelWidth === 0;
    },
    automationName: {
      get() {
        return this.name;
      },
      set(val) {
        this.updateName(val);
      },
    },

    badgeType() {
      let type = '';
      // for all available statuses please refer to automation_status db table
      switch (this.status) {
        case 'paused':
          type = 'warning';
          break;
        case 'activated':
          type = 'success';
          break;
        default:
          type = 'secondary';
      }
      return type;
    },
  },
  methods: {
    ...mapMutations('automations', [
      'updateAutomationRefKey',
      'updateStatus',
      'updateName',
      'updateRepeat',
      'updateStepsOrder',
      'updateSteps',
      'updateAutomationTriggers',
      'updateTags',
      'updateTriggerOptions',
      'updateLandingPageForms',
      'updateUsersProducts',
      'updateSendEmailActionsEntities',
      'updateSenders',
      'updateSegments',
      'updateCustomFieldNames',
      'updateStepBasedStatistics',
    ]),

    obtainStatusPhrase({ draft, paused, activated }) {
      if (this.status === 'draft') {
        return draft;
      }
      if (this.status === 'paused') {
        return paused;
      }
      if (this.status === 'activated') {
        return activated;
      }
      return '';
    },
    async changeAutomationStatus(action) {
      const string = action.charAt(0).toUpperCase() + action.slice(1);
      this.$toast.success('Success', `Status has changed to ${string}`);

      if (
        (action === 'activate' || action === 'resume') &&
        this.automationTriggers?.length === 0
      ) {
        this.$toast.warning(
          'Warning',
          'Entry point must present before activating or resuming an automation'
        );
        return;
      }

      let status = '';
      if (action === 'activate') {
        status = 'activated';
      }
      if (action === 'pause') {
        status = 'paused';
      }
      if (action === 'resume') {
        status = 'activated';
      }
      this.changingStatus = true;

      try {
        await this.$axios.put(`/automations/status/${this.automationRefKey}`, {
          status,
        });

        this.updateStatus(status);
      } catch (err) {
        this.$toast.error('Error', 'Failed to update automation status');
      } finally {
        this.changingStatus = false;
      }
    },
    changeExecutionFreq(e) {
      this.updateRepeat(e.target.value);
    },

    async saveAutomationSettings() {
      const { name } = this;
      const { repeat } = this;

      this.savingAutomationSettings = true;

      try {
        await this.$axios.put(`/automations/${this.automationRefKey}`, {
          name,
          repeat,
        });
        this.editAutomationNameMode = false;
        this.$toast.success('Success', 'Successfully updated settings');
      } catch (err) {
        // generic unknown error
        if (err.response && err.response.status !== 422) {
          this.$toast.error('Error', 'Failed to save settings');
          return;
        }

        // validations error in backend
        validationFailedNotification(err);
      } finally {
        this.savingAutomationSettings = false;
      }
    },

    async editAutomationName() {
      const name = this.automationName;
      // const repeat = this.repeat;
      if (this.editAutomationNameMode) {
        try {
          await this.$axios.put(`/automations/${this.automationRefKey}`, {
            name,
            // repeat,
          });

          this.$toast.success(
            'Success',
            'Successfully updated automations name'
          );
        } catch (err) {
          if (err.response && err.response.status !== 422) {
            this.$toast.error('Error', 'Failed to save settings');
            return;
          }
        }
      }

      this.editAutomationNameMode = !this.editAutomationNameMode;
    },

    exitBuilder() {
      window.location.href = '/automations';
    },

    // ================================================
    // Builder panning (disabled since Dec 2020)
    //
    // Leave it here in case it's needed later
    // Add both the events below to top-level component
    // - mousedown.passive="handleDraggableDivMousedown"
    // - @mouseup="closeDragElement"
    // ================================================
    /**
     * Simple rAF that default to 60 fps,
     * If you want to tweak for extremely efficient perf, go ahead 👍
     * Currently this reposition performance quite smooth, IMO
     */
    // repositionLines() {
    //   // reposition each lines instead of clear and reconnect,
    //   // like the one in nodesConnectionMixin Vuex watcher.
    //   // This increases the performance of lines reposition
    //   this.lines.forEach((line) => {
    //     line.position();
    //   });
    // },
    // handleDraggableDivMousedown(e) {
    //   const acceptedTargetClasses = ['builder-root', 'builder-container'];
    //   const classList = e.target.classList;
    //
    //   if (!acceptedTargetClasses.some((cls) => classList.contains(cls))) {
    //     return;
    //   }
    //
    //   this.draggablePositions.clientX = e.clientX;
    //   this.draggablePositions.clientY = e.clientY;
    //
    //   // Note: touch are secondary support, added just for this pan/drag
    //   //       to be at least functional in mobile device only. Admittedly
    //   //       to say, touch support on this pan/drag isn't that good
    //   ['mousemove', 'touchmove'].forEach((e) => {
    //     window.addEventListener(
    //       e,
    //       this.elementDrag,
    //
    //       // fix err in touch
    //       e === 'touchmove' && {
    //         capture: true,
    //         passive: false,
    //       }
    //     );
    //   });
    //   ['mouseup', 'touchend'].forEach((e) => {
    //     window.addEventListener(e, this.closeDragElement);
    //   });
    // },
    // elementDrag(e) {
    //   e.preventDefault();
    //
    //   const isTouch = e.type === 'touchmove';
    //
    //   /**
    //    * @type {MouseEvent|Touch}
    //    */
    //   const realEvent = isTouch ? e.touches[0] : e;
    //   const clientX = realEvent.clientX;
    //   const clientY = realEvent.clientY;
    //
    //   const draggableContainer = this.$refs.draggableContainer;
    //
    //   this.draggablePositions.movementX =
    //     this.draggablePositions.clientX - clientX;
    //   this.draggablePositions.movementY =
    //     this.draggablePositions.clientY - clientY;
    //   this.draggablePositions.clientX = clientX;
    //   this.draggablePositions.clientY = clientY;
    //
    //   // set the element's new position:
    //   draggableContainer.style.top = `${
    //     draggableContainer.offsetTop - this.draggablePositions.movementY
    //   }px`;
    //   draggableContainer.style.left = `${
    //     draggableContainer.offsetLeft - this.draggablePositions.movementX
    //   }px`;
    //
    //   window.requestAnimationFrame(this.repositionLines);
    // },
    // closeDragElement() {
    //   ['mousemove', 'touchmove'].forEach((e) => {
    //     window.removeEventListener(e, this.elementDrag);
    //   });
    //   ['mouseup', 'touchend'].forEach((e) => {
    //     window.removeEventListener(e, this.closeDragElement);
    //   });
    // },
    // ================================================
    // Builder panning
    // ================================================
  },
  mounted() {
    const {
      reference_key: referenceKey,
      name,
      repeat,
      automation_status: { name: status },
      steps_order: stepsOrder,
      steps,
      automation_triggers: automationTriggers,
    } = this.dbAutomation;

    this.updateAutomationRefKey(referenceKey);
    this.updateStatus(status);
    this.updateName(name);
    this.updateRepeat(repeat);
    this.updateStepsOrder(stepsOrder);
    this.updateSteps({ steps });
    this.updateAutomationTriggers({ automationTriggers });
    this.updateTags({ tags: transformTags(this.dbTags) });
    this.updateTriggerOptions({
      triggers: this.dbTriggers,
    });
    this.updateLandingPageForms({
      forms: transformLandingPageForms(this.dbLandingPageForms),
    });
    this.updateUsersProducts({
      usersProducts: transformUsersProducts(this.dbUsersProducts),
    });
    this.updateSendEmailActionsEntities({
      entities: this.dbSendEmailActionsEntities,
    });
    this.updateSenders({ senders: this.dbSenders });
    this.updateSegments({ segments: transformSegments(this.dbSegments) });
    this.updateCustomFieldNames(this.dbCustomFieldNames);
    const divWidth = this.$refs.builderDiv.clientWidth;
    setTimeout(function () {
      // console.log(divWidth,'dassda');
      window.scrollTo((divWidth - document.body.clientWidth) / 2, 0);
    }, 100);
    this.updateStepBasedStatistics(this.stepBasedStatistics);
  },
};
</script>

<style scoped lang="scss">
:deep(.navbar) {
  padding-top: 0px !important;
  padding-bottom: 0px !important;
}

:deep(.menu-sub) {
  inset: 50px 10px auto auto;
}

.form-group.mb-6 {
  margin-bottom: 0 !important;
}

$builder-vertical-spacing: 1.5rem;

.mt-2 {
  margin-top: 6px !important;
}

.navbar-fixed-top {
  position: fixed;
  right: 0;
  left: 0;
  z-index: 5;
}
.automation-settings-description {
  font-size: $base-font-size;
  font-family: $base-font-family;
  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
  }
}

.automation-label {
  font-size: $base-font-size;
  font-family: $base-font-family;
  margin-bottom: 0;

  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
  }
}

.automation-input {
  color: #808080;
  appearance: auto;
}

.automation-input-repeat {
  color: #808080;
  appearance: auto;
  padding-right: 3.5rem !important;
}

.builder-root {
  width: 100%;
  height: 100%;
}

@mixin navbar-height() {
  --navbar-height: 70px;

  @media screen and (max-width: 1024px) {
    --navbar-height: 60px;
  }
}

.left-panel {
  width: 265px;
  height: 100%;
  /* position: fixed; */
  /* left: 0; */
  z-index: 3;
  /* transition: transform 450ms; */

  .left-panel-main {
    position: relative;
    width: inherit;
    overflow-y: auto;
    overflow-x: hidden;
    background-color: white;
    height: 100%;
  }

  .left-panel-btn {
    position: absolute;
    border: none;
    top: 50%;
    left: 100%;
    transform: translateY(-50%);
    width: 15px;
    height: 50px;
    background-color: #e6e9ec;
  }
}

.ab-main-panel {
  padding-top: $builder-vertical-spacing;
}

.builder-container {
  min-width: 100%;
  position: absolute;
  // padding: 0px 10rem $builder-vertical-spacing 10rem;
  left: 0;
  padding-top: 5rem;
  // right: 0;
  /* transform: translateX(-50%); */
  z-index: 1;
}

.exit-btn-group {
  --spacing: 1.7rem;

  position: absolute;
  z-index: 2;
  right: var(--spacing);
  display: flex;
  align-items: center;

  @media screen and (max-width: 768px) {
    --spacing: 1rem;
  }
}

.setting-accordion-style {
  flex-direction: column;
  align-items: flex-start !important;
  padding-top: 10px;
}

.edit-icon {
  padding: 0.25rem;
  cursor: pointer;
  background: transparent;
}

.edit-icon:hover {
  color: $h-primary;
}

.automation-name-edit-field {
  display: flex;
  flex-wrap: nowrap;
  padding-left: 1.25rem;
}

.automation-name-input {
  outline: 0;
  border-width: 0 0 2px;
}

.automation-status-field {
  padding-top: 3px;
  margin-left: 0.25rem;
  cursor: pointer;
}
</style>

<style lang="scss">
.toolbar-panel {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  overflow: hidden;
  background-color: #ffffff;
  color: $base-font-color;
  font-size: $page-title-font-size;
  align-items: center;
  display: flex;
  padding: 9px 32px;
  border-bottom: 1px solid #c2c9ce;
  border-left: 1px solid #c2c9ce;
  height: 60px;
}

.toolbar-section {
  display: table-cell;
  text-align: center;
  vertical-align: middle;
}

.save-button {
  width: 100px !important;
  height: 35px;
  margin-right: 1.5px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;

  &__text {
    color: white;
    font-size: 12px;
  }

  &--disabled {
    color: #a4afb7;
    opacity: 0.65;
  }
}

.dropdown-toggle-btn {
  height: 35px;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.automation-statictics {
  display: flex;

  div {
    padding: 3px 1rem;
    border-right: 1px solid #808080;
  }

  div:last-child {
    border-right: none;
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
</style>

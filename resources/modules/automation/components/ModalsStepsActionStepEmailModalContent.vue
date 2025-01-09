<template>
  <div class="modal-body row pb-10 px-lg-17" style="padding-top: 0 !important">
    <p v-if="!emailEntity.sender_name" class="alert alert-warning">
      Warning: Email sender name is required in email.
    </p>
    <p v-if="!emailEntity.subject" class="alert alert-warning">
      Warning: Email subject is required in email.
    </p>
    <p v-if="!emailHasFooter" class="alert alert-warning">
      Warning: Email footer is required in email.
    </p>
    <div
      v-if="
        emailHasFooter &&
        emailEntity.email_design_reference_key &&
        !emailEntity.hasRequiredMergeTags
      "
      class="alert alert-warning"
    >
      <p v-pre>
        Warning: Both {{ COMPANY_ADDRESS }} and {{ UNSUBSCRIBE }} tags are
        required in email footer.
      </p>
    </div>
    <h5>Email Settings</h5>
    <BaseFormGroup label="Name">
      <span class="mb-5">What is the name of this email?</span>
      <BaseFormGroup
        label-for="emailName"
        description="Email name is for your reference only"
      >
        <BaseFormInput
          id="emailName"
          v-model="emailEntity.name"
          type="text"
          placeholder="Enter name"
          maxlength="100"
        />
      </BaseFormGroup>
    </BaseFormGroup>
    <BaseFormGroup label="From">
      <span class="mb-5">Who is sending this email?</span>
      <div class="form-inline align-items-start row">
        <div class="col-md-6">
          <div class="form-group col-md">
            <div class="d-flex w-100">
              <BaseFormGroup
                required
                label="Name"
                label-for="senderName"
                description="Use something subscribers will instantly recognize, like
              your company name."
              >
                <BaseFormInput
                  id="senderName"
                  v-model="emailEntity.sender_name"
                  type="text"
                  name="senderName"
                  placeholder="Enter name"
                  maxlength="100"
                >
                  <template #floatEnd>
                    <EmailPlaceholderIndicator
                      popover-id="emailSetupNamePlaceholderPopover"
                      type="emailSetupName"
                      input-id="senderName"
                      :input-value="emailEntity.sender_name"
                      @insert="(text) => (emailEntity.sender_name = text)"
                    />
                  </template>
                </BaseFormInput>
                <template #label-row-end> 100 characters </template>
              </BaseFormGroup>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div
            class="form-group d-flex flex-column align-items-start form-spacing"
          >
            <div class="d-flex justify-content-center w-100">
              <EmailAddressInput
                v-model="sender.email_address"
                :senders="senders"
                :sender="sender"
                @updateSenders="updateSenderArray"
              />
            </div>
          </div>
        </div>
      </div>
    </BaseFormGroup>
    <BaseFormGroup label="Subject">
      <span class="mb-5">What's the subject line for this email?</span>
      <div class="form-inline align-items-start row">
        <div class="col-md-6">
          <div class="form-group col-md">
            <div class="d-flex w-100">
              <BaseFormGroup required label="Subject" label-for="subject">
                <BaseFormInput
                  id="subject"
                  v-model="emailEntity.subject"
                  type="text"
                  name="subject"
                  placeholder="Enter subject"
                  maxlength="100"
                  required
                >
                  <template #floatEnd>
                    <EmailPlaceholderIndicator
                      popover-id="emailSetupSubjectPlaceholderPopover"
                      type="emailSetupSubject"
                      input-id="subject"
                      :input-value="emailEntity.subject"
                      @insert="(text) => (emailEntity.subject = text)"
                    />
                  </template>
                </BaseFormInput>
                <template #label-row-end> 100 characters </template>
              </BaseFormGroup>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div
            class="form-group d-flex flex-column align-items-start form-spacing"
          >
            <div class="d-flex justify-content-center w-100">
              <BaseFormGroup
                label="Preview text"
                label-for="preview"
                description="This snippet will appear in the inbox after the subject
              line."
              >
                <BaseFormInput
                  id="preview"
                  v-model="emailEntity.preview"
                  placeholder="Enter content"
                  maxlength="150"
                  required
                />
                <template #label-row-end> 150 characters </template>
              </BaseFormGroup>
            </div>
          </div>
        </div>
      </div>
    </BaseFormGroup>
    <BaseFormGroup label="Content">
      <span class="mb-5">Design the content for your email</span>
      <template #label-row-end>
        <BaseButton
          :disabled="showEditOrDesignEmailSpinner"
          @click="editEmailDesign"
        >
          <!--
            Note: that shaped landing animation spinner isn't visible in primary-white-button,
                  so I put this spinner first. Feel free to replace back if appropriate
          -->
          <!-- <i
            v-if="showEditOrDesignEmailSpinner"
            class="fas fa-circle-notch fa-spin pr-0"
          /> -->
          {{ emailEntity.email_design_reference_key ? 'Edit' : 'Design' }}
          Email
        </BaseButton>
      </template>
    </BaseFormGroup>
  </div>
  <div class="modal-footer flex-center">
    <BaseButton
      v-if="isAddStepModal"
      type="light"
      @click="$emit('back', $event)"
    >
      Back
    </BaseButton>
    <BaseButton type="secondary" @click="openTestEmailModal">
      Test Email
    </BaseButton>
    <BaseButton @click="saveSendEmailActionStepSettings"> Save </BaseButton>
  </div>
</template>

<script>
import { deepInsert } from '@shared/lib/deep.js';
import { mapState, mapActions, mapMutations } from 'vuex';
import modalSaveBtnMixin from '@automation/mixins/modalSaveBtnMixin.js';
import EmailAddressInput from '@email/components/EmailAddressInput.vue';
import EmailPlaceholderIndicator from '@email/components/EmailPlaceholderIndicator.vue';
import { Modal } from 'bootstrap';
import eventBus from '@shared/services/eventBus.js';

export default {
  name: 'ActionStepEmailModalContent',
  components: {
    EmailAddressInput,
    EmailPlaceholderIndicator,
  },
  mixins: [modalSaveBtnMixin],
  props: {
    isAddStepModal: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      showEmailNameCollapse: false,
      showRecipientsCollapse: false,
      showSubjectCollapse: false,
      showEmailDesignCollapse: false,
      showEditOrDesignEmailSpinner: false,
      senders: [],

      showErrors: false,
      emailEntity: {
        email_reference_key: null,
        name: '',
        subject: '',
        preview: '',
        sender_id: '',
        email_design_reference_key: null,
        html: '',
        emailId: '',
        sender_name: '',
      },

      sender: {
        id: null,
        name: '',
        email_address: '',
      },
    };
  },
  computed: {
    ...mapState('automations', [
      'automationRefKey',
      'stepActions',
      'modal',
      'sendEmailActionsEntities',
    ]),
    ...mapState('automations', {
      vuexSenders: 'senders',
    }),

    senderEmailInSenders() {
      return this.senders.find(
        (sender) => sender.email_address === this.sender.email_address
      );
    },
    isSenderCompleted() {
      return !!this.emailEntity.sender_name && !!this.sender.email_address;
    },
    isEmailCompleted() {
      return (
        !!this.emailEntity.subject &&
        !!this.emailEntity.email_design_reference_key
      );
    },
    emailHasFooter() {
      const { emailDesign } = this.emailEntity;
      // user hasn't create any email design
      if (!emailDesign) return true;

      const previewStr = emailDesign.preview;

      // this happens if user created an email design but abandon
      // it directly without making any changes
      if (!previewStr) return false;

      const preview = JSON.parse(previewStr);

      const flatten = (lists) => {
        return lists.flatMap((item) => [item, ...flatten(item.children || [])]);
      };
      return flatten(preview).filter((widget) => widget.type === 'Footer')
        .length;
    },
    isEmailSetupCompleted() {
      return (
        this.isSenderCompleted &&
        this.isEmailCompleted &&
        this.emailHasFooter &&
        this.emailEntity.hasRequiredMergeTags
      );
    },
  },
  watch: {
    senders(newSenders) {
      this.updateSenders({ senders: [...newSenders] });
    },
  },
  methods: {
    ...mapActions('automations', [
      'fetchAutomationEmail',
      'fetchAutomationStep',
      'insertOrUpdateSendEmailActionStep',
    ]),
    ...mapMutations('automations', [
      'updateSenders',
      'updateTestEmailRefKey',
      'updateCompletedEmailIds',
      'removeIncompletedEmail',
    ]),

    getBuilderUrl(emailRefKey, emailDesignRefKey) {
      return `/email-builder/${emailRefKey}/design/${emailDesignRefKey}/edit?source=automation&key=${this.automationRefKey}`;
    },

    /**
     *
     * @returns {object|undefined}
     */
    getActionEntity() {
      const {
        data: { id: stepId },
      } = this.modal;

      // new step
      if (!stepId) return null;

      return this.sendEmailActionsEntities[stepId];
    },
    loadSavedEmailEntities() {
      const entity = this.getActionEntity();

      this.emailEntity = {
        ...this.emailEntity,
        ...entity,
      };
    },
    loadSavedSender() {
      const entity = this.getActionEntity();

      if (!entity) return;

      const sender = this.senders.find(
        (senderFind) => senderFind.id === entity.sender_id
      );

      if(!sender) return;

      this.sender = { ...sender };
    },
    clearErrors() {
      this.showErrors = false;
    },

    openTestEmailModal() {
      this.updateTestEmailRefKey(this.emailEntity.email_reference_key);
      new Modal(document.getElementById('auto-test-email-modal'), {
        backdrop: false,
      }).show();
    },
    updateCompletedEmail() {
      const { emailId } = this.emailEntity;
      if (this.isEmailSetupCompleted) {
        this.updateCompletedEmailIds(emailId);
        return;
      }
      this.removeIncompletedEmail(emailId);
    },

    async editEmailDesign() {
      this.showEditOrDesignEmailSpinner = true;
      try {
        // email hasn't created
        if (!this.emailEntity.email_reference_key) {
          const {
            data: { id, index, parent, config },
          } = this.modal;

          const { newEmail } = await this.insertOrUpdateSendEmailActionStep({
            id,
            index,
            emailEntity: this.emailEntity,
            sender: this.sender,
            parent,
            config,
          });

          this.emailEntity = {
            ...this.emailEntity,
            email_reference_key: newEmail.reference_key,
          };
        }

        // directly enter builder if email builder ref key already present
        // else create new design and get new key
        if (this.emailEntity.email_design_reference_key) {
          window.location.href = this.getBuilderUrl(
            this.emailEntity.email_reference_key,
            this.emailEntity.email_design_reference_key
          );
          return;
        }

        const res = await this.$axios.post(
          `/emails/${this.emailEntity.email_reference_key}/design/create`,
          {
            type: 'default',
            preview: null,
          }
        );
        const { email_design_reference_key: newEmailDesignRefKey } = res.data;

        window.location.href = this.getBuilderUrl(
          this.emailEntity.email_reference_key,
          newEmailDesignRefKey
        );
      } catch (error) {
        console.error(error);
        this.$toast.error('Error', 'Something wrong. Please try again');
      } finally {
        this.showEditOrDesignEmailSpinner = false;
      }
    },
    async saveSendEmailActionStepSettings() {
      const {
        data: { id, index, parent, config },
      } = this.modal;

      this.saving = true;

      await this.insertOrUpdateSendEmailActionStep({
        id,
        index,
        emailEntity: this.emailEntity,
        sender: this.sender,
        parent,
        config,
      });

      this.saving = false;
      this.updateCompletedEmail();
      this.$emit('close-modal');
      document.getElementById('automation-add-step-modal-close-button').click();
    },
    updateSenderArray(senders) {
      this.senders = senders;
    },
    initializeEmail() {
      this.senders = [...this.vuexSenders].filter(e => e.status === 'verified');

      this.sender.email_address = this.senders[0]?.email_address;

      this.loadSavedEmailEntities();
      this.loadSavedSender();
    },
  },
  mounted() {
    this.initializeEmail();
    eventBus.$on(`email-modal-updated`, () => {
      this.initializeEmail();
      this.updateCompletedEmail();
    });
  },
};
</script>

<style scoped lang="scss">
label {
  margin-bottom: 0;
}

.content-desc {
  font-weight: bold;
}

.modal-body {
  // @include scrollbar;
}

.email-setup-section {
  padding-left: 0;
}

.edit-email-btn {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: grey;
  color: white;

  i {
    font-size: 1.5rem;
    padding: 0;
  }

  &:hover {
    background-color: darken(grey, 5%);
  }
}

.pl-emailSetting {
  padding-left: 0.5rem;
}

.form-spacing {
  justify-content: space-between;
  display: flex;
  padding-inline: 10px;
}

input {
  height: 36px;
  border-radius: 2.5px;
}
</style>

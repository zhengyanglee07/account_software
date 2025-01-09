<template>
  <BasePageLayout
    :page-name="email.name + ' - ' + email.status"
    back-to="/emails"
  >
    <template #left>
      <p v-if="!emailHasFooter" class="alert alert-warning">
        Warning: Email footer is required in email.
      </p>
      <div v-if="requiredMergeTagsAbsent" class="alert alert-warning">
        <p v-pre>
          Warning: Both {{ COMPANY_ADDRESS }} and {{ UNSUBSCRIBE }} tags are
          required in email footer.
        </p>
      </div>
      <BaseCard has-header title="To">
        <span class="mb-5">Who are you sending this email to</span>
        <BaseFormGroup label="Recipients" required>
          <BaseFormSelect
            v-model="email.target"
            label-key="label"
            value-key="value"
            :options="recipientOption"
          />
        </BaseFormGroup>
        <BaseFormGroup
          v-if="email.target === 'specific-tag'"
          label="Which Tag"
          required
        >
          <BaseFormSelect
            v-model="email.tag_id"
            label-key="tagName"
            value-key="id"
            :options="dbTags"
          >
            <option value="" disabled selected aria-disabled="true">
              Please select tag
            </option>
          </BaseFormSelect>
          <div v-if="selectedTag" class="text-muted">
            View all the people in Tag <b>{{ selectedTag.tagName }}</b>
            <BaseButton
              type="link"
              is-open-in-new-tab
              href="/people/tags"
              class="ms-1"
            >
              here
              <i
                class="ms-1 fa-solid fa-up-right-from-square"
                style="color: inherit"
              />
            </BaseButton>
          </div>
        </BaseFormGroup>
        <BaseFormGroup
          v-if="email.target === 'specific-segment'"
          label="Which Segment"
          required
          label-for="segmentNameList"
        >
          <BaseFormSelect
            v-model="email.segment_id"
            label-key="segmentName"
            value-key="id"
            :options="dbSegments"
          >
            <option value="" disabled selected aria-disabled="true">
              Please select segment
            </option>
          </BaseFormSelect>
          <div v-if="selectedSegment" class="text-muted">
            View all the people in Segment
            <b>{{ selectedSegment.segmentName }}</b>
            <BaseButton
              type="link"
              is-open-in-new-tab
              :href="`/segments/${selectedSegment.reference_key}`"
              class="ms-1"
            >
              here
              <i
                class="ms-1 fa-solid fa-up-right-from-square"
                style="color: inherit"
              />
            </BaseButton>
          </div>
        </BaseFormGroup>
        <!-- <template #footer>
          <BaseButton
            :disabled="savingSegment"
            @click="saveSegment(email['segment_id'])"
          >
            Save
          </BaseButton>
        </template> -->
      </BaseCard>
      <BaseCard has-header title="From">
        <span class="mb-5">Who is sending this email?</span>
        <BaseFormGroup
          label="Name"
          label-for="senderName"
          required
          description="Use something subscribers will instantly recognize, like
                your company name."
          col="6"
        >
          <BaseFormInput
            id="senderName"
            v-model="email.sender_name"
            type="text"
            name="senderName"
            placeholder="Enter name"
            maxlength="100"
            required
            @input="resetSenderDomainInput"
          >
            <template #floatEnd>
              <EmailPlaceholderIndicator
                popover-id="emailSetupNamePlaceholderPopover"
                type="emailSetupName"
                input-id="senderName"
                :input-value="email.sender_name"
                @insert="(text) => (email.sender_name = text)"
              />
            </template>
          </BaseFormInput>
          <template #label-row-end> 100 characters </template>
        </BaseFormGroup>
        <EmailAddressInput
          v-model="sender.domain"
          :sender="sender"
          is-fetch-by-default
          col="6"
          @updateSenders="updateSenders"
          @change="resetSenderDomainInput"
        />
      </BaseCard>
      <BaseCard has-header title="Subject">
        <span class="mb-5">What's the subject line for this email?</span>
        <div
          class="form-inline row"
          style="align-items: flex-start; margin-top: 6px"
        >
          <div class="col-md-6">
            <div class="form-group col-left">
              <div
                class="d-flex justify-content-between"
                style="margin-bottom: 4px"
              >
                <BaseFormGroup label="Subject" label-for="subject" required>
                  <BaseFormInput
                    id="subject"
                    v-model="email.subject"
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
                        :input-value="email.subject"
                        @insert="(text) => (email.subject = text)"
                      />
                    </template>
                  </BaseFormInput>
                  <template #label-row-end> 100 characters </template>
                </BaseFormGroup>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group col-right">
              <div
                class="d-flex justify-content-between"
                style="margin-bottom: 4px"
              >
                <BaseFormGroup
                  label="Preview text"
                  label-for="preview"
                  description="This snippet will appear in the inbox after the subject line."
                >
                  <BaseFormInput
                    id="preview"
                    v-model="email['preview_text']"
                    placeholder="Enter content"
                    maxlength="150"
                  />
                  <template #label-row-end> 150 characters </template>
                </BaseFormGroup>
              </div>
            </div>
          </div>
        </div>

        <!-- <template #footer>
          <BaseButton
            :disabled="savingSubjectPreview"
            @click="saveSubjectPreview(email.subject, email['preview_text'])"
          >
            Save
          </BaseButton>
        </template> -->
      </BaseCard>
      <BaseCard has-header has-toolbar title="Content">
        <span class="mb-5">Design the content for your email</span>
        <template #toolbar>
          <BaseButton
            v-if="!email['email_design_id']"
            @click="createEmailDesign"
          >
            Design Email
          </BaseButton>
          <BaseButton v-else @click="navigateToEditEmail">
            Edit Email
          </BaseButton>
        </template>
        <EmailBuilderDisplayTemplatesModal
          :create="true"
          :email-ref-key="dbEmail.reference_key"
        />
      </BaseCard>
      <EmailSetupPageFooter
        ref="emailSetupPageFooter"
        :db-email="dbEmail"
        :email-setup-completed="emailSetupCompleted"
        :required-merge-tags-absent="requiredMergeTagsAbsent"
        :email-has-footer="emailHasFooter"
        :loading="loading"
        @save="saveSetup"
        @sendEmail="sendEmail"
      />
    </template>
  </BasePageLayout>
</template>

<script>
import EmailSetupPageFooter from '@email/components/EmailSetupPageFooter.vue';
import EmailBuilderDisplayTemplatesModal from '@email/components/EmailBuilderDisplayTemplatesModal.vue';
import EmailAddressInput from '@email/components/EmailAddressInput.vue';
import EmailPlaceholderIndicator from '@email/components/EmailPlaceholderIndicator.vue';
import { Modal } from 'bootstrap';
import eventBus from '@shared/services/eventBus.js';

export default {
  name: 'EmailSetupHome',
  components: {
    EmailPlaceholderIndicator,
    EmailBuilderDisplayTemplatesModal,
    EmailSetupPageFooter,
    EmailAddressInput,
  },
  props: {
    dbEmail: {
      type: Object,
      required: true,
    },
    dbSender: {
      type: Object,
      default: () => {},
    },
    dbSegments: {
      type: Array,
      default: () => [],
    },
    dbTags: {
      type: Array,
      default: () => [],
    },
    requiredMergeTagsAbsent: {
      type: Boolean,
    },
  },
  data() {
    return {
      isEmailSetupCompleted: false,
      showRecipientsCollapse: false,
      showSenderCollapse: false,
      showSubjectCollapse: false,

      // to be hydrated by db data
      oldSettings: {},
      email: {},
      sender: {
        name: '',
        domain: '',
        nameErr: false,
        domainErr: false,
      },
      senders: [],
      senderEmails: [],
      selectedSenderEmail: null,

      // spinners
      savingSegment: false,
      savingSender: false,
      savingSubjectPreview: false,

      loading: false,
      recipientOption: [
        { label: 'Everyone', value: 'all' },
        { label: 'People in a specific tag', value: 'specific-tag' },
        { label: 'People in a specific segment', value: 'specific-segment' },
      ],
      isEnterOrExitEmailBuilder: false,
      refreshState: 'initial',
    };
  },
  computed: {
    selectedTag() {
      return this.dbTags.find((e) => e.id === this.email.tag_id);
    },
    selectedSegment() {
      return this.dbSegments.find((e) => e.id === this.email.segment_id);
    },
    recipientsComplete() {
      const { target } = this.email;
      if (target === 'all') return true;
      return target === 'specific-tag'
        ? !!this.email.tag_id
        : !!this.email.segment_id;
    },
    senderComplete() {
      return (
        (!!this.email.sender_id || !!this.sender.domain) &&
        !!this.email.sender_name
      );
    },
    subjectComplete() {
      return !!this.email.subject;
    },
    senderDomainInSenders() {
      return this.senderEmails.find((sender) => sender === this.sender.domain);
    },
    emailSetupCompleted() {
      return (
        this.recipientsComplete &&
        this.senderComplete &&
        this.subjectComplete &&
        !!this.email.email_design_id
      );
    },
    emailHasFooter() {
      const emailDesign = this.email.email_design;

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
    emailRefKey() {
      return this.email.reference_key;
    },
    emailDesignRefKey() {
      return this.email.email_design_reference_key;
    },
    hasChanges() {
      const newSettings = {
        email: this.email,
        sender: this.sender,
      };
      if (typeof this.oldSettings.isCompleted === 'boolean')
        newSettings.isCompleted = this.oldSettings.isCompleted;
      return JSON.stringify(this.oldSettings) !== JSON.stringify(newSettings);
    },
    monitorSetting() {
      const { email, sender } = this;
      return {
        email,
        sender,
      };
    },
  },

  watch: {
    monitorSetting: {
      handler() {
        this.temporaryStoreEmailSettings();
      },
      deep: true,
    },
  },
  unmounted() {
    this.temporaryStoreEmailSettings();
  },
  beforeMount() {
    window.addEventListener('beforeunload', () => {
      this.temporaryStoreEmailSettings();
    });
    eventBus.$on('email-design-created', (designRefKey) => {
      this.isEnterOrExitEmailBuilder = true;
      window.location.href = this.getEmailDesignURL(designRefKey);
    });

    // hydrate sender data from db
    this.sender = {
      ...this.sender,
      ...this.dbSender,

      // domain is used here to prevent confusion
      // don't ask me why the button is named "Verify Domain",
      domain: this.dbSender.email_address,
    };
    this.selectedSenderEmail = this.dbSender.email_address;
    this.initializeEmailSetting();

    this.oldSettings = {
      email: {
        ...this.dbEmail,
        tag_id: parseInt(this.dbEmail.tag_id ?? this.dbTags[0]?.id),
        segment_id: parseInt(this.dbEmail.segment_id ?? this.dbSegments[0]?.id),
      },
      sender: { ...this.sender },
    };

    eventBus.$on('send-test-email', () => {
      this.sendEmail(true);
    });

    eventBus.$on('update-scheduled-email', () => {
      this.sendEmail(false, true);
    });

    this.isEmailSetupCompleted = this.emailSetupCompleted;
  },

  methods: {
    updateSenders(senders) {
      this.senders = senders;
      this.senderEmails = senders.map((sender) => sender.email_address);
      this.sender.domain =
        this.selectedSenderEmail || (this.senderEmails[0] ?? null);
      this.selectedSenderEmail = null;
    },
    temporaryStoreEmailSettings() {
      const emailSetting = JSON.parse(localStorage.emailSetting ?? '{}');
      emailSetting[this.email.reference_key] = {
        email: this.email,
        sender: this.sender,
        isCompleted: this.isEmailSetupCompleted && !this.hasChanges,
      };
      localStorage.setItem('emailSetting', JSON.stringify(emailSetting));
    },
    initializeEmailSetting() {
      // hydrate email from db
      this.email = {
        ...this.dbEmail,
        tag_id: parseInt(this.dbEmail.tag_id ?? this.dbTags[0]?.id),
        segment_id: parseInt(this.dbEmail.segment_id ?? this.dbSegments[0]?.id),
      };
      if (!localStorage.emailSetting) return;
      const emailSetting = JSON.parse(localStorage.emailSetting ?? '{}');

      const currentEmailSetting = emailSetting[this.dbEmail.reference_key];
      if (!currentEmailSetting) return;

      const propertyToReplace = [
        'target',
        'tag_id',
        'segment_id',
        'subject',
        'preview_text',
        'sender_name',
      ];
      propertyToReplace.forEach((key) => {
        const value = currentEmailSetting.email[key] ?? null;
        if (value || value === 0) this.email[key] = value;
      });
      this.sender = currentEmailSetting.sender;
    },
    toggleRecipientsCollapse() {
      this.showRecipientsCollapse = !this.showRecipientsCollapse;
      this.showSenderCollapse = false;
      this.showSubjectCollapse = false;
    },
    toggleSenderCollapse() {
      this.showSenderCollapse = !this.showSenderCollapse;
      this.showRecipientsCollapse = false;
      this.showSubjectCollapse = false;
    },
    toggleSubjectCollapse() {
      this.showSubjectCollapse = !this.showSubjectCollapse;
      this.showRecipientsCollapse = false;
      this.showSenderCollapse = false;
    },

    resetSenderDomainInput() {
      this.sender.nameErr = false;
      this.sender.domainErr = false;
    },
    sendEmail(isTest = false, isScheduled = false) {
      const emailSetting =
        JSON.parse(localStorage.emailSetting ?? '{}')[
          [this.email.reference_key]
        ] ?? {};
      const isCompleted =
        !Object.prototype.hasOwnProperty.call(emailSetting, 'isCompleted') ||
        emailSetting.isCompleted;
      if (!isCompleted || this.hasChanges) {
        this.saveSetup(true, isTest, isScheduled);
        return;
      }
      if (isTest) {
        this.$refs.emailSetupPageFooter.$refs.sendTestEmailModal.sendTestEmail();
        return;
      }
      if (isScheduled) {
        this.$refs.emailSetupPageFooter.$refs.scheduleEmailModal.updateEmailSchedule();
        return;
      }
      this.$refs.emailSetupPageFooter.sendEmail();
    },
    saveSetup(isSend = false, isTest = false, isScheduled = false) {
      if (!this.recipientsComplete) {
        this.$toast.error('Error', 'Please complete recipient settings');
        return;
      }
      this.loading = true;
      const {
        segment_id: segmentId,
        tag_id: tagId,
        target,
        subject,
        preview_text: preview,
        sender_name: senderName,
      } = this.email;

      const { domain: senderEmail } = this.sender;

      this.$axios
        .post(`/emails/${this.email.reference_key}/setup`, {
          target,
          tagId,
          segmentId,
          senderName,
          senderEmail,
          subject,
          preview,
        })
        .then(() => {
          this.email = {
            ...this.email,
            subject,
            target,
            tag_id: tagId,
            segment_id: segmentId,
            preview_text: preview,
          };
          this.sender = {
            ...this.sender,
            name: senderName,
            domain: senderEmail,
          };

          this.oldSettings = {
            email: { ...this.email },
            sender: { ...this.sender },
          };

          this.$toast.success('Success', 'Successfully saved.');

          localStorage.removeItem('emailSetting');

          if (!isSend) {
            this.$inertia.visit(window.location.pathname);
            return;
          }

          if (isTest) {
            this.$refs.emailSetupPageFooter.$refs.sendTestEmailModal.sendTestEmail();
            return;
          }

          if (isScheduled) {
            this.$refs.emailSetupPageFooter.$refs.scheduleEmailModal.updateEmailSchedule();
            return;
          }

          this.$refs.emailSetupPageFooter.sendEmail();
        })
        .catch((error) => {
          const errorMessage =
            error?.response?.data?.message ||
            'Something wrong when saving subject, please try again';
          this.$toast.error('Error', errorMessage);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    saveSegment(val) {
      this.savingSegment = true;
      this.$axios
        .post(`/emails/${this.email.reference_key}/segment`, {
          segmentId: val,
        })
        .then(({ data: { message } }) => {
          this.email.segment_id = val;

          this.toggleRecipientsCollapse();
          this.$toast.success('Success', message);
        })
        .catch((error) => {
          console.error(error.response.data.message);
          this.$toast.error(
            'Error',
            'Something wrong, please try again later.'
          );
        })
        .finally(() => {
          this.savingSegment = false;
        });
    },
    saveSubjectPreview(sub, pre) {
      const subject = sub;
      const preview = pre;

      this.savingSubjectPreview = true;
      this.$axios
        .post(`/emails/${this.email.reference_key}/subject-preview`, {
          subject,
          preview,
        })
        .then(({ data: { message } }) => {
          this.email.subject = subject;
          this.email.preview_text = preview;

          this.toggleSubjectCollapse();
          this.$toast.success('Success', message);
        })
        .catch((error) => {
          console.error(error);

          this.$toast.error(
            'Error',
            'Something wrong when saving subject, please try again'
          );
        })
        .finally(() => {
          this.savingSubjectPreview = false;
        });
    },
    getEmailDesignURL(designReferenceKey = null) {
      return `/email-builder/${this.dbEmail.reference_key}/design/${
        designReferenceKey ?? this.dbEmail.email_design_reference_key
      }/edit?source=standard&key=${this.emailRefKey}`;
    },
    navigateToEditEmail() {
      this.isEnterOrExitEmailBuilder = true;
      window.location.href = this.getEmailDesignURL();
    },
    createEmailDesign() {
      new Modal(document.getElementById('eb-display-templates-modal')).show();
      // this.$modal.show('eb-display-templates-modal', {
      //   create: true,
      //   emailRefKey: this.emailRefKey,
      // });
    },
  },
};
</script>

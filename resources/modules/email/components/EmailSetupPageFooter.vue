<template>
  <div class="justify-content-end d-flex">
    <BaseButton
      type="link"
      class="text-primary me-3 fs-5"
      :disabled="loading"
      @click="$emit('save')"
    >
      Save As Draft
    </BaseButton>
    <!-- send now button -->
    <BaseButton
      id="send-email-dropdown"
      type="primary"
      :has-dropdown-icon="!isSendingEmail"
      :disabled="disableSendAndSchedule || loading || isSendingEmail"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      class="fs-5"
    >
      <i
        :class="{
          'fa fa-paper-plane me-2': !isSendingEmail,
          'header-container__icons--disabled': disableSendAndSchedule,
        }"
        aria-hidden="true"
      />
      <template v-if="isSendingEmail">
        <i class="me-2 fas fa-spinner fa-pulse" /> Sending
      </template>
      <template v-else>
        Send
      </template>
    </BaseButton>
    <BaseDropdown id="send-email-dropdown">
      <BaseDropdownOption
        :disabled="isSendingEmail"
        text="Send Now"
        @click="$emit('sendEmail')"
      />
      <BaseDropdownOption
        text="Schedule"
        data-bs-toggle="modal"
        data-bs-target="#email-setup-schedule-modal"
      />
      <BaseDropdownOption
        text="Test Email"
        data-bs-toggle="modal"
        data-bs-target="#send-test-email-modal"
      />
    </BaseDropdown>

    <!-- Edit button -->
    <!-- <BaseButton
          data-bs-toggle="modal"
          data-bs-target="#email-name-modal"
          type="light-primary"
          class="me-5"
        >
          <i
            class="fa fa-pencil-alt header-container__icons"
            aria-hidden="true"
          />
          Edit Name
        </BaseButton> -->

    <!-- Save button -->
    <!-- <BaseButton
          type="light-primary"
          class="me-5"
          @click="saveAsDraft"
        >
          <i
            class="fa fa-bookmark header-container__icons"
            aria-hidden="true"
          />
          Save as draft
        </BaseButton> -->

    <!-- Schedule button -->
    <!-- <BaseButton
          type="light-primary"
          data-bs-toggle="modal"
          data-bs-target="#email-setup-schedule-modal"
          class="me-5"
        >
          <i
            class="fa fa-calendar header-container__icons"
            :class="{
              'header-container__icons--disabled': disableSendAndSchedule,
            }"
            aria-hidden="true"
          />
          Schedule
        </BaseButton> -->

    <!-- Send Now Dropdown -->

    <!-- <BaseButton
          id="send-email-dropdown"
          type="light-primary"
          :disabled="disableSendAndSchedule"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <i
            class="fa fa-paper-plane header-container__icons"
            :class="{
              'header-container__icons--disabled': disableSendAndSchedule,
            }"
            aria-hidden="true"
          />
          Send
          <i
            class="fa fa-caret-down"
            :class="{
              'header-container__icons--disabled': disableSendAndSchedule,
            }"
            aria-hidden="true"
          />
        </BaseButton>
        <BaseDropdown id="send-email-dropdown">
          <BaseDropdownOption
            text="Send Now"
            @click="sendEmail"
          />
          <BaseDropdownOption
            text="Test Email"
            data-bs-toggle="modal"
            data-bs-target="#send-test-email-modal"
          />
        </BaseDropdown> -->

    <!--  -->

    <EmailSetupNameModal
      modal-id="email-name-modal"
      :email="email"
      @update-email-name="handleUpdateEmailName"
    />
    <EmailSetupScheduleModal
      ref="scheduleEmailModal"
      modal-id="email-setup-schedule-modal"
      :db-email="dbEmail"
      @update-email-status="handleUpdateEmailStatus"
    />
    <SendTestEmailModal
      ref="sendTestEmailModal"
      modal-id="send-test-email-modal"
      :email-ref-key="dbEmail.reference_key"
      is-email-setup
    />
  </div>
</template>

<script>
import EmailSetupNameModal from '@email/components/EmailSetupNameModal.vue';
import EmailSetupScheduleModal from '@email/components/EmailSetupScheduleModal.vue';
import SendTestEmailModal from '@email/components/SendTestEmailModal.vue';

export default {
  name: 'EmailSetupPageFooter',
  components: {
    SendTestEmailModal,
    EmailSetupNameModal,
    EmailSetupScheduleModal,
  },
  props: {
    dbEmail: {
      type: Object,
      required: true,
    },
    emailSetupCompleted: Boolean,
    requiredMergeTagsAbsent: Boolean,
    emailHasFooter: Boolean,
    loading: Boolean,
  },
  emits: ['save', 'sendEmail'],
  data() {
    return {
      email: {},
      showLimitModal: false,
      schedule: '',
      isSendingEmail: false,
    };
  },
  computed: {
    emailRefKey() {
      return this.dbEmail.reference_key;
    },
    disableSendAndSchedule() {
      return (
        !this.emailSetupCompleted ||
        !this.emailHasFooter ||
        this.requiredMergeTagsAbsent
      );
    },
  },
  mounted() {
    this.email = { ...this.dbEmail };
    this.schedule = this.email.schedule;
  },
  methods: {
    handleUpdateEmailName(name) {
      this.email.name = name;
    },
    handleUpdateEmailStatus({ status, schedule }) {
      this.email.status = status;
      this.schedule = schedule;
    },
    saveAsDraft() {
      this.$axios
        .put(`/emails/${this.emailRefKey}`, {
          email_status_id: 1, // status id 1 = Draft
          schedule: null, // reset schedule time when status = Draft
        })
        .then(() => {
          this.$inertia.visit('/emails');
        })
        .catch((error) => {
          this.$toast.error(
            'Error',
            'Something wrong when saving email. Please retry later or contact support.'
          );
        });
    },
    sendEmail() {
      this.isSendingEmail = true;
      this.$toast.info('Info', 'Sending email, please wait...');

      this.$axios
        .put(`/emails/standard/${this.emailRefKey}/send`)
        .then(() => {
          this.$toast.success('Success', 'The email has successfully sent!');
          this.$inertia.visit('/emails');
        })
        .catch((err) => {
          // if(error.response.data.exceed_limit){
          // console.log(err.response)
          this.showLimitModal = err.response.data.exceed_limit;

          // }else if(error.response.data.noPermission){
          //   this.showLimitModal = error.response.data.noPermission;
          // }

          console.error(err);

          this.$toast.error('Error', 'Failed to send email.');
        })
        .finally(() => {
          this.isSendingEmail = false;
        });
    },
  },
};
</script>

<style scoped lang="scss">
:deep(.btn i) {
  line-height: 0;
}
// New Design
* {
  // font-family: $base-font-family;
  font-size: $base-font-size;
  color: $base-font-color;
  margin: 0;
  //padding: 0;
}

.header-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  width: 100%;

  &__sub-container {
    display: flex;
    flex-direction: row;
    width: 100%;
    justify-content: flex-end;
    align-items: center;
    margin: 12px 0;
  }

  &__title {
    margin-right: 24px !important;
    font-size: $page-title-font-size;
    margin: 10px 0;
    font-weight: bold;

    @media (max-width: $md-display) {
      margin-left: calc(#{$mobile-align-left-padding} - 2px);
    }
  }

  &__status {
    font-style: italic;
    color: #ced4da;
    font-size: 18px;
    margin-top: 9px;
    margin-left: 24px;
  }

  &__buttons {
    margin-right: 24px;
    font-weight: bold;
    border: none;
    background-color: #f6f8f9;
    font-size: $base-font-size;

    @media (max-width: $sm-display) {
      margin-right: 24px;
      font-size: $responsive-base-font-size;
    }

    &--disabled {
      color: #ced4da;
    }

    &:focus {
      outline: none;
    }

    &:first-child {
      @media (max-width: $md-display) {
        margin-left: $mobile-align-left-padding;
      }
    }
  }

  &__icons {
    padding-right: 10px;
    font-size: 16px;
    color: $base-font-color;

    &--disabled {
      color: #ced4da;
    }

    @media (max-width: $sm-display) {
      padding-right: 0px;
      font-size: 14px;
    }
  }
}
//
</style>

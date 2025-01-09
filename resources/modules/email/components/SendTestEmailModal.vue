<template>
  <BaseModal
    title="Send Test Email"
    :modal-id="modalId"
  >
    <p>
      Test email will share the exact same sender, subject and content as the
      real email, with recipients (test email address) of your choice.
    </p>

    <p class="mt-3">
      *Please note that there are some limitations in test email, such as unable
      to process merge tags.
    </p>
    <BaseFormGroup
      label-for="email-name"
      label="Recipient email address"
      description="You may enter multiple email addresses separated by ,"
    >
      <BaseFormInput
        id="email-name"
        v-model="recipientEmailAddresses"
        name="name"
        type="text"
      />
      <span
        v-if="showError"
        class="error"
      >Field is required</span>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="sendingEmail"
        @click="triggerSendTestEmail()"
      >
        <span v-if="sendingEmail">
          <i class="me-2 fas fa-spinner fa-pulse" /> Sending
        </span>
        <span v-else>Send</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';
import BaseModal from '@shared/components/BaseModal.vue';
import eventBus from '@shared/services/eventBus.js';

export default {
  name: 'SendTestEmailModal',
  components: {
    BaseModal,
  },
  props: {
    modalId: String,
    emailRefKey: {
      type: [String, Number],
      required: false,
    },
    isReferral: {
      type: Boolean,
      required: false,
      default: false,
    },
    referralEmailData: {
      type: Object,
      required: false,
    },
    isEmailSetup: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      recipientEmailAddresses: '',
      sendingEmail: false,
      showError: false,
      modalEl: null,
    };
  },
  mounted() {
    setTimeout(() => {
      this.modalEl = document.getElementById(this.modalId);
      this.modalEl.addEventListener('hidden.bs.modal', this.cleanUp);
    }, 1000);
  },
  beforeUnmount() {
    this.modalEl?.removeEventListener('hidden.bs.modal', this.cleanUp);
  },
  methods: {
    cleanUp(e) {
      this.recipientEmailAddresses = '';
      this.showError = false;
    },
    triggerSendTestEmail() {
      if (this.isEmailSetup) {
        eventBus.$emit('send-test-email');
        return;
      }
      this.sendTestEmail();
    },

    async sendTestEmail() {
      if (this.isReferral) {
        this.sendTestReferralEmail();
        return;
      }
      this.showError = false;

      if (!this.recipientEmailAddresses) {
        this.showError = true;
        return;
      }

      const emailAddresses = this.recipientEmailAddresses
        .split(',')
        .map((s) => s.trim());

      if (!this.validateEmailAddresses(emailAddresses)) {
        return;
      }

      // check whether an email is created after validation
      // specifically for automation email
      if (!this.emailRefKey) {
        this.$toast.error(
          'Error',
          'No email found. Email is probably not created yet'
        );
        return;
      }

      this.sendingEmail = true;

      try {
        await this.$axios.put(
          `/emails/standard/${this.emailRefKey}/send-test`,
          {
            emailAddresses,
          }
        );

        this.$toast.success(
          'Success',
          'Successfully sent test email. Please check your mailbox.'
        );
        $(`#${this.modalId}`).modal('hide');
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to send test email: ${err.response.data.message}`
        );
      } finally {
        this.sendingEmail = false;
        const modalInstance = Modal.getInstance(
          document.getElementById(this.modalId)
        );
        if (modalInstance) {
          modalInstance.hide();
        }
      }
    },

    async sendTestReferralEmail() {
      this.showError = false;

      if (!this.recipientEmailAddresses) {
        this.showError = true;
        return;
      }

      const emailAddresses = this.recipientEmailAddresses
        .split(',')
        .map((s) => s.trim());

      if (!this.validateEmailAddresses(emailAddresses)) {
        return;
      }

      this.sendingEmail = true;

      try {
        await this.$axios.put(`/referral/campaign/emails/send-test`, {
          emailAddresses,
          emailData: this.referralEmailData,
        });

        this.$toast.success(
          'Success',
          'Successfully sent test email. Please check your mailbox.'
        );
        $(`#${this.modalId}`).modal('hide');
      } catch (err) {
        this.$toast.error(
          'Error',
          `Failed to send test email: ${err.response.data.message}`
        );
      } finally {
        this.sendingEmail = false;
        const modalInstance = Modal.getInstance(
          document.getElementById(this.modalId)
        );
        if (modalInstance) {
          modalInstance.hide();
        }
      }
    },

    validateEmailAddresses(emailAddresses) {
      // reference: https://stackoverflow.com/a/1373724
      const regex =
        /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
      let isValid = true;
      emailAddresses?.map((emailAddress) => {
        if (!regex.test(emailAddress)) {
          this.$toast.error('Error', `${emailAddress} is not a valid address`);
          isValid = false;
        }
        return isValid;
      });

      return isValid;
    },
  },
};
</script>

<style scoped lang="scss"></style>

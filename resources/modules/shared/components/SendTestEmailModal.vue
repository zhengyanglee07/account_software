<template>
  <VueJqModal
    :modal-id="modalId"
    small
  >
    <template #title>
      Send Test Email
    </template>

    <template #body>
      <p>
        Test email will share the exact same sender, subject and content as the
        real email, with recipients (test email address) of your choice.
      </p>

      <p class="mt-3">
        *Please note that there are some limitations in test email, such as
        unable to process merge tags.
      </p>

      <div class="mt-3">
        <label
          for="email-name"
          class="m-container__text"
        >
          Recipient email address

          <i
            class="fa fa-info-circle ms-1"
            data-bs-toggle="tooltip"
            title="you may enter multiple email addresses separated by ,"
          />
        </label>
        <input
          id="email-name"
          v-model="recipientEmailAddresses"
          type="text"
          class="m-container__input form-control"
          :class="{
            'error-border': showError,
          }"
          @input="showError = false"
        >
        <span
          v-if="showError"
          class="error"
        >Field is required</span>
      </div>
    </template>

    <template #footer>
      <button
        type="button"
        class="cancel-button"
        data-bs-dismiss="modal"
      >
        Cancel
      </button>
      <button
        class="primary-small-square-button"
        :disabled="sendingEmail"
        @click="sendTestEmail"
      >
        <span v-if="sendingEmail">
          <div class="spinner--white-small">
            <div class="loading-animation loading-container my-0">
              <div class="shape shape1" />
              <div class="shape shape2" />
              <div class="shape shape3" />
              <div class="shape shape4" />
            </div>
          </div>
        </span>
        <span
          v-else
          style="font-size: 12px"
        >Send</span>
      </button>
    </template>
  </VueJqModal>
</template>

<script>
export default {
  name: 'SendTestEmailModal',
  props: {
    modalId: String,
    emailRefKey: {
      type: [String, Number],
      required: false,
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
    this.modalEl = document.getElementById(this.modalId);
    this.modalEl.addEventListener('hidden.bs.modal', this.cleanUp);
  },
  beforeUnmount() {
    this.modalEl?.removeEventListener('hidden.bs.modal', this.cleanUp);
  },
  methods: {
    cleanUp(e) {
      this.recipientEmailAddresses = '';
      this.showError = false;
    },

    async sendTestEmail() {
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
      }
    },

    validateEmailAddresses(emailAddresses) {
      // reference: https://stackoverflow.com/a/1373724
      const regex =
        /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;

      emailAddresses.forEach((emailAddress) => {
        if (!regex.test(emailAddress)) {
          this.$toast.error('Error', `${emailAddress} is not a valid address`);
          return false;
        }
        return null;
      });

      return true;
    },
  },
};
</script>

<style scoped lang="scss">
</style>

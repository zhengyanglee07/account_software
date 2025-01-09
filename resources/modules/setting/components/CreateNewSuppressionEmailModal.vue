<template>
  <BaseModal
    title="Add Suppression Emails"
    modal-id="new-suppression-email-modal"
  >
    <BaseFormGroup
      label="Email Address"
      description="Add one email address per line"
    >
      <BaseFormTextarea
        id="suppression-email-addresses"
        v-model="suppressionEmails"
        rows="5"
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="adding"
        @click="createNewSuppressionEmail"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'CreateNewSuppressionEmailModal',
  props: {
    suppressionEmailAddresses: { type: Array, default: () => [] },
  },
  data() {
    return {
      adding: false,
      suppressionEmails: '',
    };
  },

  methods: {
    validateEmailAddresses(emailAddresses) {
      // reference: https://stackoverflow.com/a/1373724
      const regex =
        /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;

      const supressionEmail = this.suppressionEmailAddresses.map(
        (m) => m.emailAddress
      );

      let result = true;
      emailAddresses.forEach((emailAddress) => {
        if (!regex.test(emailAddress)) {
          this.$toast.error('Error', `${emailAddress} is not a valid address`);
          result = false;
        }

        if (supressionEmail.includes(emailAddress)) {
          this.$toast.error(
            'Error',
            `Emails of ${emailAddress} already existed. Please remove them and save again`
          );
          result = false;
        }

        return null;
      });

      return result;
    },

    async createNewSuppressionEmail() {
      const emailAddresses = this.suppressionEmails
        .split('\n')
        .map((s) => s.trim());

      if (!this.validateEmailAddresses(emailAddresses)) {
        return;
      }

      this.adding = true;

      try {
        const res = await this.$axios.post('/emails/add-suppression', {
          emailAddresses,
        });

        this.$emit('updateSuppressionList', res.data.bouncedEmail);

        this.$toast.success(
          'Success',
          'Successfully added suppression email address.'
        );
        this.suppressionEmails = '';
        const modalInstance = Modal.getInstance(
          document.getElementById('new-suppression-email-modal')
        );
        if (modalInstance) {
          modalInstance.hide();
        } else {
          new Modal(
            document.getElementById('new-suppression-email-modal')
          ).hide();
        }
      } catch (err) {
        this.$toast.error('Error', 'Failed to add suppression email.');
      } finally {
        this.adding = false;
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

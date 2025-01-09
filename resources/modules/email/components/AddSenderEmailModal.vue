<template>
  <form @submit.prevent="saveSenderEmail">
    <BaseModal
      :title="modalTitle"
      modal-id="add-sender-email-modal"
      no-dismiss
    >
      <BaseFormGroup
        v-if="isNewSender"
        label="Email Address"
      >
        <BaseFormInput
          id="new-sender-email"
          v-model="senderEmail"
          name="email"
          type="email"
          required
        />
      </BaseFormGroup>
      <div v-else>
        <p>
          A verification email has been sent to the inbox of {{ senderEmail }}.
        </p>
        <p>
          Please click on the link in the email to verify your ownership of this
          email address
        </p>
        <p>
          After you have verified this email address, click on the
          <b>Refresh</b> button on the top right of the
          <b>Email Address</b> setting or refresh this page to use
          {{ senderEmail }} as sender email in this marketing email
        </p>
      </div>
      <template #footer>
        <BaseButton
          type="secondary"
          @click="hideModal"
        >
          Dismiss
        </BaseButton>
        <BaseButton
          v-if="isNewSender"
          is-submit
          :disabled="isVerifying"
        >
          <i
            v-if="isVerifying"
            class="fas fa-spinner fa-pulse"
          />
          <span v-else>Save</span>
        </BaseButton>
      </template>
    </BaseModal>
  </form>
</template>

<script setup>
import { computed, ref, inject, onBeforeMount } from 'vue';
import verifySenderDomainMixin from '@setting/mixins/verifySenderDomainMixin.js';
import eventBus from '@services/eventBus.js';
import BaseModal from '@shared/components/BaseModal.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const $toast = inject('$toast');
const props = defineProps({
  senders: { type: Array, default: () => [] },
});
verifySenderDomainMixin.methods.setToast();

const isNewSender = ref(true);
const modalTitle = computed(() =>
  isNewSender.value ? 'Add Sender Email Address' : 'Email Verification'
);
const senderEmail = ref(null);
const areCurrentSendersVerified = computed(
  () => props.senders.filter((e) => e.status !== 'verified').length === 0
);
const isVerifying = ref(false);

const modal = ref(null);
onBeforeMount(() => {
  eventBus.$on('base-modal-mounted', () => {
    // Clear all data after close modal
    const modalElem = document.getElementById('add-sender-email-modal');
    modalElem.addEventListener('hide.bs.modal', () => {
      senderEmail.value = null;
      isNewSender.value = true;
      isVerifying.value = false;
    });
  });

  eventBus.$on('show-add-sender-email-modal', () => {
    bootstrap?.then(({ Modal }) => {
      modal.value = new Modal(
        document.getElementById('add-sender-email-modal')
      );
      modal.value.show();
    });
  });
});

const hideModal = () => {
  modal.value.hide();
};

const saveSenderEmail = async () => {
  if (!areCurrentSendersVerified.value) {
    $toast.warning(
      'Warning',
      'Seems like you have one or more emails pending for verification. Please verify them before verifying new email.'
    );
    return;
  }
  isVerifying.value = true;
  await verifySenderDomainMixin.methods.verifySenderDomain(senderEmail.value);
  isVerifying.value = false;
  isNewSender.value = false;
};
</script>

<style></style>

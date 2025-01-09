<template>
  <BaseModal
    title="Cancel Schedule"
    :modal-id="modalId"
  >
    <p>
      Are you sure you want to stop sending the scheduled email for
      {{ emailData.name ?? '' }} campaign? You may edit and re-schedule the
      email after the confirmation of this cancellation.
    </p>
    <template #footer>
      <BaseButton @click="cancelSchedule">
        Confirm
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'CancelScheduleModal',
  props: {
    modalId: String,
    emailData: Object,
  },
  emits: ['canceled'],
  methods: {
    cancelSchedule() {
      this.$axios
        .put(`emails/${this.emailData.refKey}/cancel-schedule`)
        .then(() => {
          this.$toast.success(
            'Success',
            'Successfully canceled email schedule.'
          );
          const modalInstance = Modal.getInstance(
            document.getElementById(this.modalId)
          );
          if (modalInstance) {
            modalInstance.hide();
          } else {
            new Modal(document.getElementById(this.modalId)).hide();
          }
          this.$emit('canceled', this.emailData);
        })
        .catch((err) => {
          this.$toast.error('Error', 'Something went wrong.');
          console.error(err);
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

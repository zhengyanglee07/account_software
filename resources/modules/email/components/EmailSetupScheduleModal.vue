<template>
  <BaseModal
    title="Schedule Email"
    :modal-id="modalId"
  >
    <h5>Set up your schedule</h5>
    <BaseFormGroup
      label="Delivery date and time"
      description="*The minimum schedule time for same-day delivery is 30 minutes from now"
    >
      <BaseDatePicker
        v-model="schedule"
        class="mt-2"
        :class="{
          'error-border': showError && !v$.schedule.required,
        }"
        type="datetime"
        :format="datetimeFormat"
        :editable="false"
        :show-second="false"
        :use12h="true"
        :minute-step="15"
        :default-value="defaultDate"
        :disabled-date="disabledDates"
        :disabled-time="disabledTime"
      />

      <template
        v-if="showError && !v$.schedule.required"
        #error-message
        class="error"
      >
        Field is required
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="updatingSchedule"
        @click="triggerScheduleEvent"
      >
        Submit
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import dayjs from 'dayjs';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import { Modal } from 'bootstrap';
import { validationFailedNotification } from '@shared/lib/validations.js';
import axios from 'axios';
import eventBus from '@shared/services/eventBus.js';

dayjs.extend(localizedFormat);

export default {
  name: 'EmailSetupScheduleModal',

  props: {
    modalId: String,
    dbEmail: {
      type: Object,
      required: true,
    },
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      schedule: '',
      timezone: '',
      datetimeFormat: 'YYYY-MM-DD HH:mm:ss',

      showError: false,
      updatingSchedule: false,
    };
  },
  validations: {
    schedule: {
      required,
    },
  },
  computed: {
    emailRefKey() {
      return this.dbEmail.reference_key;
    },

    defaultDate() {
      const now = new Date();

      return new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        now.getHours(),
        now.getMinutes()
      );
    },
  },
  mounted() {
    this.schedule = this.dbEmail.schedule;

    axios
      .get(`/account/${this.dbEmail.account_id}/timezone`)
      .then(({ data }) => {
        this.timezone = data.timezone;
      })
      .catch((err) => {
        console.error(err);

        this.$toast.error(
          'Error',
          'Failed to get timezone. Please refresh your page or contact support.'
        );
      });
  },
  methods: {
    disabledDates(date) {
      return date < new Date(new Date().setHours(0, 0, 0, 0));
    },
    disabledTime(date) {
      const now = new Date();

      // enable time after 30 minutes from now for same-day delivery
      return (
        date <
        new Date(
          now.getFullYear(),
          now.getMonth(),
          now.getDate(),
          now.getHours(),
          now.getMinutes() + 30
        )
      );
    },
    triggerScheduleEvent() {
      eventBus.$emit('update-scheduled-email');
    },
    updateEmailSchedule() {
      this.showError = false;

      if (this.v$.$invalid) {
        this.showError = true;
        return;
      }

      // guess timezone if account doesn't set a timezone, just in case
      const timezone = this.timezone || dayjs.tz.guess(true);

      const schedule = dayjs.tz(this.schedule, timezone);
      const scheduleInKLTimezone = schedule
        .clone()
        .tz('Asia/Kuala_Lumpur') // default timezone in laravel server (this project)
        .format(this.datetimeFormat);

      this.updatingSchedule = true;
      axios
        .put(`/emails/${this.emailRefKey}/schedule`, {
          schedule: scheduleInKLTimezone,
        })
        .then(({ data: { message } }) => {
          this.$toast.success('Success', message);

          this.$emit('update-email-status', {
            status: 'Scheduled',
            schedule: this.schedule,
          });

          const modalInstance = Modal.getInstance(
            document.getElementById(this.modalId)
          );
          if (modalInstance) {
            modalInstance.hide();
          }
          this.$inertia.visit('/emails');
        })
        .catch((error) => {
          if (error.response.status === 422) {
            validationFailedNotification(error);
            return;
          }

          // generic
          // show if email setup is incomplete
          this.$toast.error('Error', error.response.data.message);
        })
        .finally(() => {
          this.updatingSchedule = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

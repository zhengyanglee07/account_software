<template>
  <BaseFormGroup
    :label="`Select the days you offer ${
      type === 'pickup' ? 'pickup' : 'scheduled delivery'
    }`"
  >
    <BaseFormRadio
      v-for="(isDaily, desc, index) in {
        'Every day of the week': true,
        'Specific days of the week': false,
      }"
      :id="`${type}-is-daily-${isDaily}`"
      :key="index"
      :value="isDaily"
      :model-value="settings.isDaily"
      @input="instantUpdateSetting(type, 'isDaily', isDaily)"
    >
      {{ desc }}
    </BaseFormRadio>
  </BaseFormGroup>

  <BaseFormGroup v-if="!settings.isDaily && settings.deliveryHours?.length > 0">
    <BaseFormCheckBox
      v-for="(deliveryHour, index) in settings.deliveryHours"
      :id="`${type}-days-${deliveryHour.name}`"
      :key="deliveryHour.name"
      :value="true"
      :model-value="deliveryHour.isOpen"
      :disabled="isLastEnabledDay && deliveryHour.isOpen"
      @input="
        instantUpdateSetting(
          type,
          'deliveryHours',
          !deliveryHour.isOpen,
          'isOpen',
          index
        )
      "
    >
      {{ deliveryHour.name }}
    </BaseFormCheckBox>
  </BaseFormGroup>

  <BaseFormGroup
    :label="`${type === 'pickup' ? 'Pickup' : 'Scheduled Delivery'} Time Slots`"
  >
    <BaseFormRadio
      v-for="(isSameTime, desc) in {
        'Same time slots every day of the week': true,
        'Different time slots each day of the week': false,
      }"
      :id="`${type}-is-same-time-${isSameTime}`"
      :key="`is-same-time-${isSameTime}`"
      :value="isSameTime"
      :model-value="settings.isSameTime"
      @input="instantUpdateSetting(type, 'isSameTime', isSameTime)"
    >
      {{ desc }}
    </BaseFormRadio>
  </BaseFormGroup>

  <BaseFormGroup>
    <BaseFormCheckBox
      :id="`${type}-is-limit-order`"
      :value="true"
      :model-value="!!settings.isLimitOrder"
      @input="
        instantUpdateSetting(type, 'isLimitOrder', !settings.isLimitOrder)
      "
    >
      Is there an order limit for each time slot?
    </BaseFormCheckBox>
  </BaseFormGroup>

  <BaseDatatable
    class="time-slot-datatable"
    title="customer"
    no-action
    no-header
    no-sorting
    :table-headers="timeslotTableHeader"
    :table-datas="timeslotTableDatas.filter((e) => e.isOpen)"
  >
    <template #cell-day="{ row: { day, isFirstRow } }">
      <p>{{ isFirstRow ? day : '' }}</p>
    </template>
    <template #cell-start="{ row: { dayIndex, timeIndex, isOpen, start } }">
      <BaseFormGroup class="time-slot_form-group">
        <BaseFormSelect
          :id="`${type}-start-time-${timeIndex}`"
          :options="rearrangedArray('start', dayIndex, timeIndex)"
          placeholder="Select start time"
          :disabled="!checkDeliveryIsOpen(isOpen)"
          :model-value="start.toUpperCase()"
          @input="
            instantNestedSetting(
              type,
              'deliveryHours',
              $event.target.value,
              'availableSlots',
              'start',
              dayIndex,
              timeIndex
            )
          "
        />
      </BaseFormGroup>
    </template>
    <template #cell-until>
      <p class="text-center">
        to
      </p>
    </template>
    <template #cell-end="{ row: { dayIndex, timeIndex, isOpen, end } }">
      <BaseFormGroup class="time-slot_form-group">
        <BaseFormSelect
          :id="`${type}-end-time-${timeIndex}`"
          :options="rearrangedArray('end', dayIndex, timeIndex)"
          placeholder="Select end time"
          :disabled="!checkDeliveryIsOpen(isOpen)"
          :model-value="end.toUpperCase()"
          @input="
            instantNestedSetting(
              type,
              'deliveryHours',
              $event.target.value,
              'availableSlots',
              'end',
              dayIndex,
              timeIndex
            )
          "
        />
      </BaseFormGroup>
    </template>
    <template #cell-limit="{ row: { dayIndex, timeIndex, limitValue } }">
      <BaseFormGroup class="time-slot-limit_form-group">
        <BaseFormInput
          :id="`${type}-limit-value-${timeIndex}`"
          type="number"
          :model-value="limitValue"
          @keydown="validateInteger($event)"
          @input="
            instantNestedSetting(
              type,
              'deliveryHours',
              $event.target.value,
              'availableSlots',
              'limitValue',
              dayIndex,
              timeIndex
            )
          "
        />
      </BaseFormGroup>
    </template>
    <template
      #cell-action="{ row: { dayIndex, timeIndex, start, end, totalSlots } }"
    >
      <BaseButton
        class="me-3 mb-6"
        type="link"
        @click="addAvailableSlot(dayIndex, timeIndex, start, end)"
      >
        <i class="fas fa-plus" />
      </BaseButton>
      <BaseButton
        v-if="totalSlots > 1"
        class="mb-6 ms-3"
        type="link"
        color="danger"
        @click="
          instantDeleteNestedSetting(
            type,
            'deliveryHours',
            dayIndex,
            'availableSlots',
            timeIndex
          )
        "
      >
        <i class="text-danger fa-solid fa-xmark" />
      </BaseButton>
    </template>
  </BaseDatatable>

  <BaseFormGroup
    v-if="!onboarding"
    label="Off Days"
    :description="`The dates not available for ${
      type === 'delivery' ? 'scheduled delivery' : 'store pickup'
    }`"
  >
    <BaseDatePicker
      v-model="disabledDates"
      type="date"
      format="DD/MM/YYYY"
      value-type="format"
      :editable="false"
      :value="settings.disableDate"
      :disabled-date="notBeforeToday"
      :multiple="true"
      :clearable="false"
      is-value-in-badge
      @delete="(value) => instantDeleteSetting(type, 'disableDate', value)"
    />
  </BaseFormGroup>

  <BaseFormGroup
    v-if="!onboarding"
    class="datetime_form-group"
    col="6"
    label="Number Of Day Slots Shown To Customers"
    description="Exp: If 7 days, order slots will be visible until a week. Set 1 day to
        show the slots of customer's checkout date only."
  >
    <BaseFormInput
      :id="`${type}-pre-order-day`"
      type="number"
      :model-value="settings.preOrderDay"
      @keydown="validateInteger($event)"
      @input="instantUpdateSetting(type, 'preOrderDay', $event.target.value)"
      @blur="onBlurValidatePreOrderDay"
    >
      <template #append>
        day(s)
      </template>
    </BaseFormInput>
  </BaseFormGroup>

  <div />

  <BaseFormGroup
    class="datetime_form-group"
    label="Order Preparation Time"
    :description="
      settings.isPreperationTime
        ? 'The average time you need to prepare an order after receiving a customer order'
        : ''
    "
    col="6"
  >
    <BaseFormCheckBox
      :id="`${type}-order-preparation-time`"
      :value="true"
      :model-value="!!settings.isPreperationTime"
      @input="
        instantUpdateSetting(
          type,
          'isPreperationTime',
          !settings.isPreperationTime
        )
      "
    >
      Do you need some time to prepare an order?
    </BaseFormCheckBox>

    <BaseFormInput
      v-if="settings.isPreperationTime"
      :id="`${type}-preparation-value`"
      type="number"
      :model-value="settings.preperationValue"
      @keydown="validateInteger($event)"
      @input="
        instantUpdateSetting(type, 'preperationValue', $event.target.value)
      "
    >
      <template #append>
        hour(s)
      </template>
    </BaseFormInput>
  </BaseFormGroup>
</template>

<script>
import PickupAndDeliveryMixin from '@setting/mixins/PickupAndDeliveryMixin.js';
import { mapState } from 'vuex';

import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);
export default {
  name: 'TimeslotPicker',
  mixins: [PickupAndDeliveryMixin],
  props: {
    settings: Object,
    type: String,
    onboarding: Boolean,
  },
  data() {
    return {
      timeArray: [],
      scheduleSettings: [],
      disabledDates: [],
    };
  },
  computed: {
    ...mapState('settings', ['error']),
    isLastEnabledDay() {
      return this.settings.deliveryHours.filter((e) => e.isOpen).length === 1;
    },
    timeslotTableHeader() {
      const headers = [
        { name: 'Start', key: 'start', custom: true },
        { name: '', key: 'until', custom: true },
        { name: 'End', key: 'end', custom: true },
        { name: 'Action', key: 'action', custom: true },
      ];
      if (!this.settings.isSameTime)
        headers.splice(0, 0, { name: 'Days', key: 'day', custom: true });

      if (this.settings.isLimitOrder) {
        const startIndex = 3 + (this.settings.isSameTime ? 0 : 1);
        headers.splice(startIndex, 0, {
          name: 'Limit',
          key: 'limit',
          custom: true,
        });
      }
      return headers;
    },
    timeslotTableDatas() {
      const { isDaily, isSameTime, singleDeliveryHour, deliveryHours } =
        this.settings;
      const datas = isSameTime ? singleDeliveryHour : deliveryHours;

      return (
        datas
          ?.map((m, dayIndex) => {
            return m.availableSlots
              .map((mm, timeIndex) => ({
                ...mm,
                dayIndex,
                timeIndex,
                day: m.name,
                totalSlots: m.availableSlots.length,
                isFirstRow: timeIndex === 0,
                isOpen: m.isOpen,
                isDaily,
              }))
              .flat();
          })
          .flat() ?? []
      );
    },
  },
  watch: {
    disabledDates(date) {
      this.instantUpdateSetting(this.type, 'disableDate', date);
    },
    settings: {
      deep: true,
      handler(newValue) {
        if (Object.keys(this.scheduleSettings).length === 0) {
          this.scheduleSettings = newValue;
        }
      },
    },
    'settings.isDaily': function (newVal) {
      if (newVal) {
        this.updateDeliveryHour(this.type);
      } else {
        if (!this.scheduleSettings.deliveryHours) return;
        this.instantUpdateSetting(
          this.type,
          'deliveryHours',
          this.scheduleSettings.deliveryHours
        );
      }
    },
    'settings.isSameTime': function (newVal) {
      this.updateDeliveryHour(this.type);
      if (!newVal)
        this.updateScheduleTimeslot({ key: this.type, isTemp: true });
    },
    error(newVal) {
      if (newVal) {
        this.$toast.error('Error', newVal);
        this.initializeSetting({
          key: 'error',
          value: null,
        });
      }
    },
  },
  mounted() {
    this.loadTime();
    this.disabledDates = this.settings.disableDate;
  },
  methods: {
    onBlurValidatePreOrderDay() {
      if (this.settings.preOrderDay && this.settings.preOrderDay < 1)
        this.instantUpdateSetting(this.type, 'preOrderDay', 1);
    },
    rearrangedArray(type, index, index2) {
      if (this.timeArray.length === 0) return [];
      const { timeArray } = this;
      const slot = this.settings.deliveryHours?.[index]?.availableSlots[index2];
      const { length } = timeArray;
      const indexOf = timeArray.indexOf(slot[type]) % length;
      const firstNElement = timeArray.slice(0, indexOf);
      const remainedElements = timeArray.slice(indexOf, length);
      return [...remainedElements, ...firstNElement];
    },
    checkDeliveryIsOpen(isOpen) {
      return this.settings.isDaily ? true : isOpen;
    },
    addAvailableSlot(dayIndex, timeIndex, start, end) {
      const startSlot = dayjs(start.toUpperCase(), 'h:mm A');
      const endSlot = dayjs(end.toUpperCase(), 'h:mm A');
      const diffInMinutes = endSlot.diff(startSlot, 'minute');
      const convertTime = dayjs(end.toUpperCase(), 'h:mm A')
        .add(diffInMinutes, 'minute')
        .format('h:mm A');

      const slotObj = {
        start: end.toUpperCase(),
        end: convertTime,
        limitValue: 0,
      };

      const isValid = this.checkTimeslot(
        this.type,
        'deliveryHours',
        slotObj,
        'availableSlots',
        null,
        dayIndex,
        timeIndex
      );

      if (isValid) {
        this.alterAvailableSlot({
          name: this.type,
          value: slotObj,
          index1: dayIndex,
          index2: timeIndex,
        });
      }
    },
    loadTime() {
      const x = 15; // minutes interval
      const times = []; // time array
      let tt = 0; // start time
      const ap = ['AM', 'PM']; // AM-PM

      // loop to increment the time and push results in array
      for (let i = 0; tt < 24 * 60; i++) {
        const hh = Math.floor(tt / 60); // getting hours of day in 0-24 format
        const mm = tt % 60; // getting minutes of the hour in 0-55 format
        times[i] = `${hh === 0 || hh === 12 ? 12 : hh % 12}:${`0${mm}`.slice(
          -2
        )} ${ap[Math.floor(hh / 12)]}`; // pushing data in array in [00:00 - 12:00 AM/PM format]
        tt += x;
      }
      this.timeArray = times;
    },
  },
};
</script>

<style lang="scss">
@media screen and (max-width: 415px) {
  .time-slot-datatable {
    td,
    .card,
    .table-responsive {
      padding-left: 0 !important;
    }
  }
  .time-slot_form-group {
    width: 120px;
  }
  .time-slot-limit_form-group {
    width: 50px;
  }
  .datetime_form-group {
    width: 100%;
  }
  .fas {
    font-size: 1.5rem !important;
  }
}
</style>

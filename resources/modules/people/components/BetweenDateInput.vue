<template>
  <div
    class="input-group col-md-2 d-flex flex-column pfilter__elem--max-content"
  >
    <label>
      <DatePicker
        class="people-dropdown-col"
        input-class="dropdown-input "
        :class="{
          'error-border': showError,
        }"
        format="YYYY-MM-DD"
        value-type="format"
        :value="value"
        :placeholder="placeholder"
        :editable="false"
        :disabled-date="disabledDates"
        @input="handleDateInputChange"
      />
    </label>

    <!-- for showing custom validation errors -->
    <slot name="errors" />
  </div>
</template>

<script>
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { parseDate } from '@shared/lib/date.js';

export default {
  name: 'BetweenDateInput',
  components: { DatePicker },
  props: {
    value: {
      type: String,
      required: true,
      default: '',
    },
    showError: Boolean,
    placeholder: {
      type: String,
      required: false,
      default: 'Pick a date',
    },
    disableBefore: {
      type: String,
      required: false,
      default: '',
    },
  },
  computed: {
    parsedDisableBefore() {
      return parseDate(this.disableBefore);
    },
  },
  methods: {
    handleDateInputChange(val) {
      this.$emit('input', val);

      // custom @input event
      this.$emit('handle-input-change');
    },
    disabledDates(date) {
      if (!this.disableBefore) return false;
      return date.getTime() <= this.parsedDisableBefore.getTime();
    },
  },
};
</script>

<style scoped lang="scss">
:deep input {
  //   width: 160px;
  margin: 0;
  border-radius: 0;
  height: 30px;

  @media (max-width: $md-display) {
    margin: 0;
    width: 100%;
  }

  &::placeholder {
    padding: 0px !important;
    font-size: 12px !important;
  }
}

:deep .mx-icon-calendar,
.mx-icon-clear {
  right: 15px;
  @media (max-width: $md-display) {
    right: 20px;
  }
}

.error-border {
  border-radius: 2.5px;
  // margin: -1px;
  height: 30px;
  :deep {
    input {
      border: 0;
    }
    .mx-input-wrapper,
    input {
      height: 100%;
    }
  }
}
</style>

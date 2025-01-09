<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="ordersSubKey"
        @change="updateOrdersConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(o, i) in ordersSub"
          :key="i"
          :value="o"
        >
          {{ o }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('ordersSubKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="ordersSubKey !== 'between'"
      col="md-3"
    >
      <BaseFormInput
        v-model="ordersSubValue"
        type="number"
        min="0"
        step="1"
        @input="updateOrdersConditionFilter"
      />
      <template
        v-if="showError('ordersSubValue')"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <div
      v-else
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseFormInput
          v-model="ordersBetweenFrom"
          type="number"
          @input="updateOrdersConditionFilter"
        />
        <template
          v-if="showError('ordersBetweenFrom')"
          #error-message
        >
          Field is required
        </template>
      </BaseFormGroup>

      <BaseFormGroup col="md-0">
        <BaseButton
          type="secondary"
          disabled
        >
          and
        </BaseButton>
      </BaseFormGroup>

      <BaseFormGroup col="md-4">
        <BaseFormInput
          v-model="ordersBetweenTo"
          type="number"
          @input="updateOrdersConditionFilter"
        />
        <template
          v-if="
            showError('ordersBetweenTo') && v$.ordersBetweenTo.required.$invalid
          "
          #error-message
        >
          {{
            showError('ordersBetweenTo') && v$.ordersBetweenTo.required.$invalid
              ? 'Field is required'
              : showError('ordersBetweenTo') &&
                ordersBetweenFrom &&
                v$.ordersBetweenTo.minValue.$invalid
                ? `Value must be greater than ${ordersBetweenFrom}`
                : ''
          }}
        </template>
      </BaseFormGroup>
    </div>

    <BaseFormGroup col="md-3">
      <!-- Note: timeFrameKey v-model here exists only for triggering validation -->
      <BaseFormSelect
        v-model="timeFrameKey"
        @change="handleTimeFrameChange(updateOrdersConditionFilter)"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(time, index) in timeFrame"
          :key="index"
          :value="time"
        >
          {{ time }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('timeFrameKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showInTheLastDurationInput"
      col="md-3"
    >
      <BaseFormInput
        v-model="durationValue"
        type="number"
        :min="1"
        @input="updateOrdersConditionFilter"
      />
      <template #error-message>
        {{
          conditionFiltersShowErrors && v$.durationValue.required.$invalid
            ? 'Field is required'
            : conditionFiltersShowErrors && v$.durationValue.minValue.$invalid
              ? 'This field should have a value of at least 1'
              : ''
        }}
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showInTheLastDurationInput"
      col="md-3"
    >
      <BaseFormSelect
        v-model="durationKey"
        @change="updateOrdersConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(duration, index) in durationArr"
          :key="index"
          :value="duration"
        >
          {{ duration }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('durationKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <div
      v-if="showBetweenDurationInput"
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenFrom"
          :show-error="showError('durationBetweenFrom')"
          placeholder="From"
          @change="updateOrdersConditionFilter"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationFromRequiredErr" />
        </template>
      </BaseFormGroup>

      <BaseFormGroup col="md-0">
        <BaseButton
          type="secondary"
          disabled
        >
          and
        </BaseButton>
      </BaseFormGroup>

      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenTo"
          :show-error="showError('durationBetweenTo')"
          placeholder="To"
          :disable-before="durationBetweenFrom"
          @change="updateOrdersConditionFilter"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationToRequiredErr" />
        </template>
      </BaseFormGroup>
    </div>
  </ConditionFilterContainer>
</template>

<script>
/* eslint-disable indent */

import { mapGetters, mapState, mapMutations } from 'vuex';
import { required, minValue, requiredIf, helpers } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import timeframeMixin from '@people/mixins/timeframeMixin.js';
import useVuelidate from '@vuelidate/core';

// Why make custom func? Because the minValue validator is useless
function customMinVal(val) {
  return (
    !helpers.req(val) || parseFloat(val) > parseFloat(this.ordersBetweenFrom)
  );
}

export default {
  name: 'OrdersConditionFilter',
  components: { ConditionFilterContainer },
  mixins: [conditionFilterMixin, timeframeMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      ordersSubKey: '',
      ordersSubValue: '',

      ordersBetweenFrom: '',
      ordersBetweenTo: '',
    };
  },
  validations() {
    return {
      ordersSubKey: {
        required,
      },
      ordersSubValue: {
        required: requiredIf(function () {
          return !this.subValueIsBetween;
        }),
        minValue: minValue(0),
      },
      ordersBetweenFrom: {
        required: requiredIf(function () {
          return this.subValueIsBetween;
        }),
      },
      ordersBetweenTo: {
        required: requiredIf(function () {
          return this.subValueIsBetween;
        }),
        minValue: customMinVal,
      },
      ...this.timeFrameValidation,
    };
  },
  computed: {
    ...mapState('people', ['condition']),
    ...mapGetters('people', ['getConditionFilterSubConditions']),
    subConditions() {
      return this.getConditionFilterSubConditions(
        this.conditionId,
        this.orIndex
      );
    },
    emptySubConditions() {
      return this.subConditions.length === 0;
    },
    ordersSub() {
      return this.condition.ordersSub;
    },

    subValueIsBetween() {
      return this.ordersSubKey === 'between';
    },
  },

  // Note: ops below are used to show saved state in "View segment" feature,
  //       and it isn't used in "People" filter. Despite that, you still
  //       need to check for object property existence since this component
  //       is also used in "People" filter
  mounted() {
    this.ordersSubKey = this.subConditions[0].key;
    this.ordersSubValue = this.subConditions[0].value;

    if (this.subValueIsBetween) {
      this.ordersBetweenFrom = this.ordersSubValue.from;
      this.ordersBetweenTo = this.ordersSubValue.to;
    }

    if (this.subConditions[1]) {
      this.timeFrameKey = this.subConditions[1].key;
      this.toggleTimeFrameInput();
    }

    if (this.subConditions[2]) {
      this.durationKey = this.subConditions[2].key;
      this.durationValue = this.subConditions[2].value;
    }

    if (this.durationKey === 'custom') {
      this.durationBetweenFrom = this.durationValue.from;
      this.durationBetweenTo = this.durationValue.to;
    }
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    updateOrdersConditionFilter() {
      // don't let - sign and '.' show up, on input display
      this.durationValue = Math.abs(Math.floor(this.durationValue)) || '';
      this.ordersSubValue = Math.abs(Math.floor(this.ordersSubValue)) || 0;

      if (!this.subValueIsBetween) {
        this.ordersBetweenFrom = '';
        this.ordersBetweenTo = '';
      }

      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Orders',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: this.ordersSubKey,
              value: !this.subValueIsBetween
                ? this.ordersSubValue
                : {
                    from: this.ordersBetweenFrom,
                    to: this.ordersBetweenTo,
                  },
            },
            this.timeframeSubCondition,
            this.durationSubCondition,
          ],
        },
      });
    },
  },
};
</script>

<style scoped lang="scss"></style>

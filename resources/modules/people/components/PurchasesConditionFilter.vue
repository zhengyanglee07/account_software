<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <!-- purchases sub dropdown (has/has not been made) -->
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="purchasesSubKey"
        @change="updatePurchasesConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(p, i) in purchasesSub"
          :key="i"
          :value="p"
        >
          {{ p }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('purchasesSubKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <!-- product select dropdown (any/include any) -->
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="productSelectKey"
        @change="handleProductSelectKeyChange"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(ps, k) in productSelect"
          :key="k"
          :value="k"
        >
          {{ ps }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('productSelectKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <!-- product options dropdown (name, category, etc) -->
    <BaseFormGroup
      v-if="showProductOptionsDropdown"
      col="md-3"
    >
      <BaseFormSelect
        v-model="productOptionKey"
        @change="handleProductOptionKeyChange"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(po, k) in productOptions"
          :key="k"
          :value="k"
        >
          {{ po }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('productOptionKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <!-- product options values, aka users products dropdown -->
    <BaseFormGroup
      v-if="showProductOptionsValuesDropdown"
      col="md-3"
    >
      <BaseFormSelect
        v-model="productOptionVal"
        @change="updatePurchasesConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(p, k) in productOptionsValues"
          :key="k"
          :value="p"
        >
          {{ p }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('productOptionVal')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <!-- accuracy dropdown & its respective input -->
    <BaseFormGroup
      v-if="showAccuracyDropdown"
      col="md-3"
    >
      <!-- accuracy key dropdown -->
      <BaseFormSelect
        v-model="accuracyKey"
        @change="updatePurchasesConditionFilter"
      >
        <option
          value=""
          selected
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(a, i) in accuracy"
          :key="i"
          :value="a"
        >
          {{ a }}
        </option>
      </BaseFormSelect>

      <template
        v-if="showError('accuracyKey')"
        #error-message
      >
        Please select an option
      </template>
      <!-- accuracy val input -->
    </BaseFormGroup>

    <BaseFormGroup
      v-if="showAccuracyDropdown"
      col="md-3"
    >
      <div class="d-flex">
        <BaseFormInput
          v-model="accuracyVal"
          type="number"
          :min="1"
          @input="updatePurchasesConditionFilter"
        />

        <BaseButton
          type="secondary"
          disabled
        >
          times
        </BaseButton>
      </div>
      <template
        v-if="conditionFiltersShowErrors && v$.accuracyVal.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <!-- timeframe dropdown (in the last/over all time) -->
    <BaseFormGroup
      v-if="showTimeFrameKeyDropdown"
      col="md-3"
    >
      <!-- Note: timeFrameKey v-model here exists only for triggering validation -->
      <BaseFormSelect
        v-model="timeFrameKey"
        @change="handleTimeFrameChange(updatePurchasesConditionFilter)"
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

    <!-- duration input -->
    <BaseFormGroup
      v-if="showInTheLastDurationInput"
      col="md-3"
    >
      <BaseFormInput
        v-model="durationValue"
        type="number"
        :min="1"
        @input="updatePurchasesConditionFilter"
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
      <!-- duration dropdown (days/months/years) -->
      <BaseFormSelect
        v-model="durationKey"
        @change="updatePurchasesConditionFilter"
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
          @change="updatePurchasesConditionFilter"
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
          @change="updatePurchasesConditionFilter"
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
import { required, requiredIf } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import timeframeMixin from '@people/mixins/timeframeMixin.js';
import useVuelidate from '@vuelidate/core';

// indexes for subConditions
// Note: order of these indexes is important
const PURCHASES_SUB = 0;
const PRODUCT_SELECT = 1;
const ACCURACY = 2;
const TIMEFRAME = 3;
const DURATION = 4;

export default {
  name: 'PurchasesConditionFilter',
  components: { ConditionFilterContainer },
  mixins: [conditionFilterMixin, timeframeMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      // show/hide dropdown/input
      showProductOptionsDropdown: false,
      showProductOptionsValuesDropdown: false,
      showAccuracyDropdown: false,
      showTimeFrameKeyDropdown: false,

      // v-models
      purchasesSubKey: '',
      productSelectKey: '',
      productOptionKey: '',
      productOptionVal: '',
      accuracyKey: '',
      accuracyVal: '',
    };
  },
  validations() {
    return {
      purchasesSubKey: {
        required,
      },

      productSelectKey: {
        required,
      },
      productOptionKey: {
        required: requiredIf(function () {
          return this.productSelectKey === 'some';
        }),
      },
      productOptionVal: {
        required: requiredIf(function () {
          return this.productSelectKey === 'some';
        }),
      },

      accuracyKey: {
        required,
      },
      accuracyVal: {
        required,
      },
      ...this.timeFrameValidation,
    };
  },
  computed: {
    ...mapState('people', ['condition', 'usersProducts']),
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

    // has been made/has not been made
    purchasesSub() {
      return this.condition.purchasesSub;
    },

    // any products/include any (keys: any/some)
    productSelect() {
      return this.condition.productSelect;
    },

    // Product Name, etc... (keys: productTitle, etc...)
    productOptions() {
      return this.condition.productOptions;
    },

    // values set depends on selected product option, aka productOptionKey
    // default to product title/name
    productOptionsValues() {
      return this.usersProducts.map(
        (product) => product[this.productOptionKey || 'productTitle']
      );
    },

    // at least/at most/exactly
    accuracy() {
      return this.condition.accuracy;
    },
  },

  // Note: ops below are used to show saved state in "View segment" feature,
  //       and it isn't used in "People" filter. Despite that, you still
  //       need to check for object property existence since this component
  //       is also used in "People" filter
  mounted() {
    this.purchasesSubKey = this.subConditions[PURCHASES_SUB].key;

    // any products/include any
    if (this.subConditions[PRODUCT_SELECT]) {
      this.productSelectKey = this.subConditions[PRODUCT_SELECT].key;

      this.productOptionKey = this.subConditions[PRODUCT_SELECT].value.key;
      this.productOptionVal = this.subConditions[PRODUCT_SELECT].value.value;

      if (this.productSelectKey === 'some') {
        this.showProductOptionsDropdown = true;
        this.showProductOptionsValuesDropdown = true;
      }
    }

    // accuracy
    if (this.subConditions[ACCURACY]) {
      this.accuracyKey = this.subConditions[ACCURACY].key;
      this.accuracyVal = this.subConditions[ACCURACY].value;

      this.showAccuracyDropdown = true;
    }

    // timeframe
    if (this.subConditions[TIMEFRAME]) {
      this.timeFrameKey = this.subConditions[TIMEFRAME].key;
      this.showTimeFrameKeyDropdown = true;
      this.toggleTimeFrameInput();
    }

    if (this.subConditions[DURATION]) {
      this.durationKey = this.subConditions[DURATION].key;
      this.durationValue = this.subConditions[DURATION].value;
    }

    if (this.durationKey === 'custom') {
      this.durationBetweenFrom = this.durationValue.from;
      this.durationBetweenTo = this.durationValue.to;
    }
  },
  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    handleProductSelectKeyChange(e) {
      const val = e.target.value;

      // show/hide corresponding dropdown/input
      this.showProductOptionsDropdown = val === 'some';
      this.showProductOptionsValuesDropdown = val === 'some';
      this.showAccuracyDropdown = !!val;
      this.showTimeFrameKeyDropdown = !!val;
      this.showInTheLastDurationInput = false;
      this.showBetweenDurationInput = false;

      // reset product options key & val
      this.productOptionKey = '';
      this.productOptionVal = '';

      // reset accuracy
      this.accuracyKey = '';
      this.accuracyVal = '';

      // reset timeframe & durations
      this.timeFrameKey = '';
      this.durationKey = '';
      this.durationValue = '';
      this.durationBetweenFrom = '';
      this.durationBetweenTo = '';

      this.updatePurchasesConditionFilter();
    },
    handleProductOptionKeyChange() {
      this.productOptionVal = '';

      this.updatePurchasesConditionFilter();
    },
    updatePurchasesConditionFilter() {
      // just don't let - sign and '.' show up, on input display, though there's
      // already have validation. Your boss want
      this.durationValue = Math.abs(Math.floor(this.durationValue)) || '';

      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Purchases',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: this.purchasesSubKey, // has placed/has not placed
              value: null,
            },
            {
              key: this.productSelectKey, // any (Any Products) / some (Includes Any)

              // this value is important for "some" only
              // the processing logic is deferred to backend
              value: {
                key: this.productOptionKey,
                value: this.productOptionVal,
              },
            },
            {
              key: this.accuracyKey, // at least/at most/exactly
              value: this.accuracyVal,
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

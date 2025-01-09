<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="visitCondition"
        @change="updateTagSubCondition"
      >
        <option
          value=""
          selected
          disabled
        >
          -- Visit pattern --
        </option>
        <option
          v-for="(siteActivitySub, index) in getConditionSiteActivitySub"
          :key="index"
          :value="siteActivitySub"
        >
          {{ siteActivitySub }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('visitCondition')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="visitSalesChannel"
        @change="updateTagSubCondition"
      >
        <option
          value=""
          disabled
        >
          -- Sales channel --
        </option>
        <option
          v-for="(channel, index) in salesChannels"
          :key="index"
          :value="channel.value"
        >
          {{ channel.label }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('visitSalesChannel')"
        #error-message
      >
        Please select a channel
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-if="visitSalesChannel === 'funnel'"
      col="md-3"
    >
      <BaseFormSelect
        v-model="funnelId"
        @change="updateTagSubCondition"
      >
        <option
          value=""
          selected
          disabled
        >
          -- Which funnel --
        </option>
        <option
          v-for="(funnel, index) in funnels"
          :key="index"
          :value="funnel.id"
        >
          {{ funnel.funnel_name }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <template v-if="isShowPageFilter">
      <BaseFormGroup col="md-3">
        <BaseFormSelect
          v-model="visitPagePattern"
          @change="updateTagSubCondition"
        >
          <option
            value=""
            disabled
          >
            -- Choose a page pattern --
          </option>
          <option
            v-for="(selectOption, index) in condition.pageSelect"
            :key="index"
            :value="selectOption"
          >
            {{ selectOption }}
          </option>
        </BaseFormSelect>
      </BaseFormGroup>

      <BaseFormGroup
        v-if="visitPagePattern !== 'Any Pages'"
        col="md-3"
      >
        <BaseFormSelect
          v-model="pageId"
          @change="updateTagSubCondition"
        >
          <option
            value=""
            disabled
          >
            -- Which page --
          </option>
          <option
            v-for="(page, index) in pageOptions"
            :key="index"
            :value="page.id"
          >
            {{ page.name }}
          </option>
        </BaseFormSelect>
      </BaseFormGroup>
    </template>

    <!-- accuracy dropdown & its respective input -->
    <template v-if="isShowAccuracyFilter">
      <BaseFormGroup col="md-3">
        <BaseFormSelect
          v-model="visitFrequencyPattern"
          @change="updateTagSubCondition"
        >
          <option
            value=""
            selected
            disabled
          >
            -- Frequency pattern --
          </option>
          <option
            v-for="(accuracy, i) in condition.accuracy"
            :key="i"
            :value="accuracy"
          >
            {{ accuracy }}
          </option>
        </BaseFormSelect>
        <template
          v-if="showError('visitFrequencyPattern')"
          #error-message
        >
          Please select an option
        </template>
      </BaseFormGroup>

      <BaseFormGroup col="md-3">
        <div class="d-flex">
          <BaseFormInput
            v-model="visitFrequencyInTimes"
            type="number"
            :min="1"
            @input="updateTagSubCondition"
          >
            <template #append>
              times
            </template>
          </BaseFormInput>
        </div>
        <template
          v-if="
            conditionFiltersShowErrors &&
              v$.visitFrequencyInTimes.required.$invalid
          "
          #error-message
        >
          Field is required
        </template>
      </BaseFormGroup>

      <!-- timeframe dropdown (in the last/over all time) -->
      <BaseFormGroup
        v-if="visitSalesChannel"
        col="md-3"
      >
        <BaseFormSelect
          v-model="timeFrameKey"
          @change="handleTimeFrameChange(updateTagSubCondition)"
        >
          <option
            value=""
            selected
            disabled
          >
            -- Timeframe pattern --
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
    </template>

    <!-- duration input -->
    <template v-if="timeFrameKey === 'in the last'">
      <BaseFormGroup col="md-3">
        <BaseFormInput
          v-model="durationValue"
          type="number"
          :min="1"
          @input="updateTagSubCondition"
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

      <BaseFormGroup col="md-3">
        <!-- duration dropdown (days/months/years) -->
        <BaseFormSelect
          v-model="durationKey"
          @change="updateTagSubCondition"
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
    </template>

    <div
      v-if="timeFrameKey === 'between'"
      class="d-md-flex col-md-9"
    >
      <BaseFormGroup col="md-4">
        <BaseDatePicker
          v-model="durationBetweenFrom"
          placeholder="From"
          no-short-cuts
          :show-error="showError('durationBetweenFrom')"
          @change="updateTagSubCondition"
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
          placeholder="To"
          no-short-cuts
          :disable-before="durationBetweenFrom"
          :show-error="showError('durationBetweenTo')"
          @change="updateTagSubCondition"
        />
        <template #error-message>
          <RequiredErrMsg :show-error="showDurationToRequiredErr" />
        </template>
      </BaseFormGroup>
    </div>
  </ConditionFilterContainer>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex';
import { required } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import timeframeMixin from '@people/mixins/timeframeMixin.js';
import useVuelidate from '@vuelidate/core';
import { startCase } from 'lodash';

export default {
  name: 'VisitActivityConditionFilter',

  components: {
    ConditionFilterContainer,
  },

  mixins: [conditionFilterMixin, timeframeMixin],

  setup() {
    return {
      v$: useVuelidate(),
    };
  },

  data() {
    return {
      visitCondition: '',
      visitSalesChannel: '',
      funnelId: null,
      visitPagePattern: 'Any Pages',
      pageId: null,
      visitFrequencyPattern: 'at least',
      visitFrequencyInTimes: 1,
    };
  },

  validations: {
    visitCondition: {
      required,
    },
    visitSalesChannel: {
      required,
    },
    visitFrequencyPattern: {
      required,
    },
    visitFrequencyInTimes: {
      required,
    },
    timeFrameKey: {
      required,
    },
  },

  computed: {
    ...mapState('people', ['condition', 'funnels', 'ecommercePages']),

    ...mapGetters('people', [
      'getConditionSiteActivitySub',
      'getConditionFilterSubConditions',
    ]),

    subConditions() {
      return this.getConditionFilterSubConditions(
        this.conditionId,
        this.orIndex
      );
    },

    emptySubConditions() {
      return this.subConditions.length === 0;
    },

    salesChannels() {
      return this.$page.props.enabledSalesChannels.map((channel) => {
        return {
          label: startCase(channel),
          value: channel,
        };
      });
    },

    pageOptions() {
      if (this.visitSalesChannel !== 'funnel') return this.ecommercePages;
      return this.funnels
        .find((funnel) => funnel.id === this.funnelId)
        ?.landingpages.map((page) => {
          return {
            id: page.id,
            name: page.name,
          };
        });
    },

    isShowPageFilter() {
      return this.visitSalesChannel === 'funnel'
        ? this.funnelId
        : this.visitSalesChannel;
    },

    isShowAccuracyFilter() {
      if (!this.isShowPageFilter) return false;
      return this.visitPagePattern === 'Any Pages'
        ? true
        : this.pageId !== null;
    },
  },

  mounted() {
    // has visited a page/has not visited a page
    this.visitCondition = this.subConditions[0].key;

    if (this.subConditions[1]) {
      // funnel/online-store/mini-store
      this.visitSalesChannel = this.subConditions[1].key;

      if (this.visitSalesChannel === 'funnel') {
        // if (visitSalesChannel === funnel) => funnelId, else => null
        this.funnelId = this.subConditions[1].value;
      }
    }

    if (this.subConditions[2]) {
      const pagePattern = this.subConditions[2].value;
      this.visitPagePattern =
        pagePattern === 'any' ? 'Any Pages' : 'Include Any';
      this.pageId = this.visitPagePattern === 'any' ? null : pagePattern;
    }

    // frequency
    if (this.subConditions[3]) {
      this.visitFrequencyPattern = this.subConditions[3].key;
      this.visitFrequencyInTimes = this.subConditions[3].value;
    }

    // timeframe
    if (this.subConditions[4]) {
      this.timeFrameKey = this.subConditions[4].key;
      this.showTimeFrameKeyDropdown = true;
      this.toggleTimeFrameInput();
    }

    if (this.subConditions[5]) {
      this.durationKey = this.subConditions[5].key;
      this.durationValue = this.subConditions[5].value;
    }

    if (this.durationKey === 'custom') {
      this.durationBetweenFrom = this.durationValue.from;
      this.durationBetweenTo = this.durationValue.to;
    }
  },

  methods: {
    ...mapMutations('people', ['updateANDConditionFilter']),

    /**
     * Example subConditions:
     * Find contacts who visited a page with id 6 in Funnel id 1 at least 1 time in the last 6 days
     * ```
     * { "key": "has visited a page" },
     * { "key": "funnel", "value": 1 },
     * { "key": "page", "value": 2 },
     * { "key": "at least", "value": 1 },
     * { "key": "in the last", "value": null },
     * { "key": "days", "value": 6 }
     * ```
     */
    updateTagSubCondition() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Site Activity',
          error: this.v$.$invalid,

          subConditions: [
            {
              // has visited a page/has not visited a page
              key: this.visitCondition,
            },
            {
              // funnel/online-store/mini-store
              key: this.visitSalesChannel,
              // if (visitSalesChannel === funnel) => funnelId, else => null
              value: this.funnelId,
            },
            {
              key:
                this.visitSalesChannel === 'funnel'
                  ? 'builder-page'
                  : 'store-page',
              value:
                this.visitPagePattern !== 'Any Pages' ? this.pageId : 'any',
            },
            {
              key: this.visitFrequencyPattern,
              value: this.visitFrequencyInTimes,
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

<template>
  <BasePageLayout
    :page-name="`${isNewCashback ? 'Add' : 'Edit'} Cashback`"
    back-to="/marketing/cashback"
  >
    <template #left>
      <BaseCard has-header title="General">
        <BaseFormGroup label="Cashback Title">
          <BaseFormInput
            id="cashback-title"
            v-model="title"
            type="text"
            placeholder="Give this cashback a name"
          />
        </BaseFormGroup>
      </BaseCard>

      <BaseCard has-header title="Conditions">
        <BaseFormGroup
          label="Minimum Spending Requirement"
          description=" A minimum spend to receive this cashback."
        >
          <BaseFormInput
            id="cashback-minimum-spent"
            v-model="minSpent"
            type="number"
            step="1"
            @blur="onBlurValidateMinAmount"
          >
            <template #prepend>
              {{ currency === 'MYR' ? 'RM' : currency }}
            </template>
          </BaseFormInput>
        </BaseFormGroup>
        <BaseFormGroup label="Cashback Rate">
          <div class="d-flex overflow-auto pb-3">
            <BaseButton
              v-for="(amount, index) in cashbackAmountLists"
              :key="index"
              type="light-primary"
              class="me-3"
              :style="`min-width: 75px; width: ${
                amount === 'Custom' ? '100%' : '120px'
              }`"
              :active="amount === selectedCashbackAmount"
              @click="updateSelectedAmount(amount)"
            >
              {{ amount !== 'Custom' ? `${amount} %` : 'Set Custom Rate' }}
            </BaseButton>
          </div>
        </BaseFormGroup>

        <BaseFormGroup v-if="selectedCashbackAmount === 'Custom'">
          <BaseFormInput
            id="cashback-amount"
            v-model="cashbackAmount"
            type="number"
            step="1"
            @blur="onBlurValidateAmount"
          >
            <template #append> % </template>
          </BaseFormInput>
        </BaseFormGroup>

        <BaseFormGroup
          label="Capped At"
          description="Maximum value of store credit the customer can get from
                    cashback. Leave it empty for no limit"
        >
          <BaseFormInput
            id="cashback-capped-amount"
            v-model="cappedAmount"
            type="number"
            step="1"
            @blur="onBlurValidateCappedAmount"
          >
            <template #prepend>
              {{ currency === 'MYR' ? 'RM' : currency }}
            </template>
          </BaseFormInput>
        </BaseFormGroup>

        <BaseFormGroup
          label="Cashback Expiry Date"
          description="Once the cashback has expired, it may no longer be used to
                    make a transaction."
        >
          <BaseFormInput
            id="cashback-expiry-date"
            v-model="expireMonths"
            type="text"
            append="Months"
            @blur="onBlurValidateExpireDate"
          >
            <template #append> Months </template>
          </BaseFormInput>
        </BaseFormGroup>
      </BaseCard>

      <BaseCard has-header title="Extra Conditions">
        <BaseFormGroup
          label="Target Segments"
          description="Apply this cashback to selected Segments."
        >
          <BaseMultiSelect
            id="cashback-segment"
            :model-value="selectedSegments"
            multiple
            placeholder="Add Segments"
            label="segmentName"
            :options="availableSegments"
            @input="addSegment"
          />
        </BaseFormGroup>

        <BaseFormGroup label="Sales Channel">
          <BaseFormCheckBox
            id="cashback-sales-channel-online-store"
            v-model="selectedSalesChannel.onlineStore"
            :value="true"
            :model-value="selectedSalesChannel.onlineStore"
          >
            Online Store
          </BaseFormCheckBox>
          <!-- <BaseFormCheckBox
            id="cashback-sales-channel-mini-store"
            v-model="selectedSalesChannel.miniStore"
            :value="true"
            :model-value="selectedSalesChannel.miniStore"
          >
            Mini Store
          </BaseFormCheckBox> -->
        </BaseFormGroup>
      </BaseCard>
    </template>
    <template #footer>
      <BaseButton type="link" class="me-6" href="/marketing/cashback">
        Cancel
      </BaseButton>

      <BaseButton id="add-cashback-button" @click="validateInputs">
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import cashbackAPI from '@cashback/api/cashbackAPI.js';
import eventBus from '@services/eventBus.js';

export default {
  mixins: [specialCurrencyCalculationMixin],
  props: {
    cashback: { type: Object, default: () => {} },
    targetSegments: { type: Array, default: () => [] },
    segments: { type: Array, default: () => [] },
    currency: { type: String, default: '' },
    cashbackTitles: { type: Array, default: () => [] },
  },

  data() {
    return {
      title: '',
      minSpent: 0,
      cappedAmount: null,
      cashbackAmountLists: [5, 10, 15, 20, 'Custom'],
      selectedCashbackAmount: 5,
      cashbackAmount: 5,
      expireMonths: 3,
      defaultSegment: '',
      selectedSegments: ['All'],
      existingCashbackTitles: [],
      selectedSalesChannel: {
        onlineStore: true,
        miniStore: true,
      },
    };
  },

  computed: {
    isNewCashback() {
      return Object.entries(this.cashback).length === 0;
    },

    availableSegments() {
      const selected = this.selectedSegments.map((e) => e.segmentName);
      let available = [];
      if (this.selectedSegments[0] === 'All')
        available = this.segments.filter(
          (seg) => !selected.includes(seg.segmentName)
        );
      else
        available = [
          'All',
          ...this.segments.filter((seg) => !selected.includes(seg.segmentName)),
        ];
      return available;
    },

    isTitleDuplicated() {
      return this.existingCashbackTitles.includes(
        this.title?.replaceAll(' ', '').toLowerCase()
      );
    },
  },

  mounted() {
    this.initializeCashback();
  },

  methods: {
    updateSelectedAmount(amount) {
      this.selectedCashbackAmount = amount;
      this.cashbackAmount = amount === 'Custom' ? 25.0 : amount;
    },

    onBlurValidateMinAmount() {
      if (!this.minSpent || this.minSpent < 0) {
        this.minSpent = 0.0;
      }
      this.minSpent = parseFloat(this.minSpent).toFixed(2);
    },

    onBlurValidateCappedAmount() {
      if (!this.cappedAmount && !Number.isFinite(this.cappedAmount)) return;
      if (+this.cappedAmount < 1) {
        this.cappedAmount = 1;
      }
      this.cappedAmount = parseFloat(this.cappedAmount).toFixed(2);
    },

    onBlurValidateAmount() {
      if (this.cashbackAmount > 100) {
        this.cashbackAmount = 100;
      }
      if (!this.cashbackAmount || this.cashbackAmount < 0) {
        this.cashbackAmount = 0;
      }
      this.cashbackAmount = parseFloat(this.cashbackAmount).toFixed(2);
    },

    onBlurValidateExpireDate() {
      if (!this.expireMonths || this.expireMonths < 0) {
        this.expireMonths = 1;
      }
    },

    addSegment(segment) {
      if (segment[0] === 'All' && segment.length > 1) segment.splice(0, 1);
      if (segment.includes('All')) this.selectedSegments = ['All'];
      else this.selectedSegments = segment;
    },

    removeSegment(id) {
      this.selectedSegments = this.selectedSegments.filter(
        (seg) => seg.id !== id
      );
      if (this.selectedSegments.length === 0) this.selectedSegments = ['All'];
    },

    /**
     * Validate every input, then add/update cashback
     */
    validateInputs() {
      if (this.title === '') {
        this.$toast.error('Error', 'Cashback title cannot be empty.');
        return;
      }
      if (this.selectedSegments.length === 0) {
        this.$toast.error('Error', 'Please select at least one segment.');
        return;
      }
      if (this.isTitleDuplicated) {
        this.$toast.error('Error', 'Cashback title already been used!');
        return;
      }

      /**
       * After validating every input, run addCashback or updateCashback
       */
      if (this.isNewCashback) {
        this.addCashback();
        return;
      }
      this.updateCashback();
    },

    addCashback() {
      cashbackAPI
        .create({
          title: this.title,
          minAmount: this.minSpent * 100,
          amount: this.cashbackAmount * 100,
          segments: this.selectedSegments,
          cappedAmount:
            !this.cappedAmount || this.cappedAmount === ''
              ? null
              : this.cappedAmount * 100,
          expMonth: this.expireMonths,
          salesChannel: this.selectedSalesChannel,
        })
        .then(({ data }) => {
          this.$toast.success('Success', 'Cashback added.');
          this.$inertia.visit('/marketing/cashback');
        })
        .catch((e) => this.$toast.error('Error', 'Something went wrong!'));
    },

    updateCashback() {
      cashbackAPI
        .update(this.cashback.id, {
          title: this.title,
          minAmount: this.minSpent * 100,
          amount: this.cashbackAmount * 100,
          segments: this.selectedSegments,
          cappedAmount:
            !this.cappedAmount || this.cappedAmount === ''
              ? null
              : this.cappedAmount * 100,
          expMonth: this.expireMonths,
          salesChannel: this.selectedSalesChannel,
        })
        .then(({ data }) => {
          this.$toast.success('Success', 'Cashback updated.');
          this.$inertia.visit('/marketing/cashback');
        })
        .catch((e) => this.$toast.error('Error', 'Something went wrong!'));
    },

    initializeCashback() {
      this.existingCashbackTitles = this.cashbackTitles.filter(
        (item) =>
          item !==
          this.cashback?.cashback_title?.replaceAll(' ', '').toLowerCase()
      );
      if (Object.entries(this.cashback).length === 0) return;
      const parsedAmount = parseFloat(
        this.cashback.cashback_amount / 100
      ).toFixed(2);
      let isCustomAmount = true;
      if (['5.00', '10.00', '15.00', '20.00'].includes(parsedAmount))
        isCustomAmount = false;

      this.title = this.cashback.cashback_title;
      this.minSpent = parseFloat(this.cashback.min_amount / 100).toFixed(2);
      this.cashbackAmount = parsedAmount;
      this.cappedAmount = this.cashback.capped_amount
        ? parseFloat(this.cashback.capped_amount / 100).toFixed(2)
        : this.cashback.capped_amount;
      this.selectedCashbackAmount = isCustomAmount
        ? 'Custom'
        : parseInt(parsedAmount);
      if (this.targetSegments)
        this.selectedSegments = cloneDeep(this.targetSegments);
      this.expireMonths = this.cashback.expire_date;
      this.selectedSalesChannel.onlineStore = this.cashback.salesChannel.some(
        (e) => e.type === 'online-store'
      );
      this.selectedSalesChannel.miniStore = this.cashback.salesChannel.some(
        (e) => e.type === 'mini-store'
      );
    },
  },
};
</script>

<style scoped lang="scss">
@media (max-width: 426px) {
  .responsive-container {
    padding-left: 20px !important;
    padding-right: 20px !important;
  }

  .custom-responsive {
    display: flex;
    flex-direction: row;
  }
}

.cashback-card-column {
  display: flex;
  flex-direction: column;
}

.input-group-prepend {
  width: 22%;
  margin-left: -2px !important;
  border-left-style: none;
}

.input-group-text {
  padding: 10px !important;
  padding-left: 14px !important;
  font-size: $base-font-size;
  font-weight: 400 !important;
  font-family: 'Roboto', sans-serif !important;
}

.right_container_content_inner-page {
  padding-top: 12px !important;

  @media (max-width: $md-display) {
    padding-top: 0px !important;
  }
}

.mb-4 {
  margin-bottom: 20px !important;
}

.headerText {
  padding: 8px 0 !important;
  margin-bottom: 20px;
}

.back-to-previous {
  padding-bottom: 4px !important;

  @media (max-width: $md-display) {
    padding-left: 0 !important;
  }
}

.sub-header {
  color: $h-secondary-color !important;
  padding-top: 8px;
}

.cashback-container {
  width: 720px;
  // margin-left: auto;
  // margin-right: auto;
}

.general-card {
  &__body {
    padding: 20px;
    @media (max-width: $md-display) {
      padding: 20px $mobile-align-left-padding !important;
    }
  }
}

.general-card-section {
  margin-bottom: 0rem !important;
}

.form-control {
  margin-top: 8px;
  padding: 10px;

  &::placeholder {
    font-size: 12px;
  }
  &:hover,
  &:active,
  &:focus {
    outline: none !important;
  }
}

.section-title {
  font-size: 16px;
  margin: 0px 0px 4px;
  color: #202930;
  font-weight: 700;
}

.section-description {
  font-size: 14px;
  color: #202930;
  margin: 0px 0px 5px;
}

.amount-input {
  display: flex;
  flex-direction: row;
  padding: 0 !important;
  width: 100%;
  height: 40px;

  &__currency,
  &__percentage {
    display: flex;
    justify-content: center;
    align-items: center;
    border-right: 1px solid #ced4da;
    width: 13%;
    height: 100%;
  }

  &__input {
    font-size: 1rem;
    width: 100%;
    padding: 0 12px;
    border: none;
  }
}

.cashback-card-container {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  width: 100%;
  padding-top: 3px;
}

.cashback-card {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 120px;
  height: 50px;
  margin-right: 10px;
  background-color: #fff;
  border-radius: 6px;
  border: 1px solid #ced4da;
  cursor: pointer;

  &:hover {
    border: 1px solid #202930;
  }

  &:last-child {
    width: 100%;
    margin-right: 0;
  }
}

.amount-selected {
  border: 1px solid #202930;
}

.segment-tag {
  display: inline-block;
  cursor: pointer;

  font-weight: normal;
  background-color: #e0e0e0;
  //font-size: 15px;
  border-radius: 4px;
  padding: 5px 10px;
  color: black;
  height: fit-content;
  word-break: break-word;
}

.remove-tag {
  padding-left: 4px;
}

.footer-container {
  padding: 20px 0;
  margin: 0 auto;
  border-top: 0.1rem solid var(--p-border-subdued, #dfe3e8);
  display: flex;
  justify-content: flex-end;

  @media (max-width: $sm-display) {
    display: flex;
    justify-content: center;
    flex-direction: column-reverse;

    .primary-small-square-button,
    .whiteBaseButton {
      width: 100% !important;
      margin-bottom: 10px;
    }
  }

  &__cancel-btn {
    color: #6c757d;
    text-decoration: underline;
    display: flex;
    justify-content: center;
    align-items: center;

    &:hover {
      cursor: pointer;
    }
  }

  /*&__button {
    margin-left: 12px;
  }*/
}

.cancel-button {
  text-align: center;
  padding-top: 0px !important;
  height: 36px;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;

  @media (max-width: $sm-display) {
    padding: 10px 0px 5px 0px;
    color: #808285;
  }
}

.responsive-container {
  @media (max-width: $md-display) {
    padding-left: $mobile-align-left-padding !important;
  }
}

input[type='checkbox'].purple-checkbox {
  position: absolute;
  opacity: 0;
  z-index: -1;
}

input[type='checkbox'].purple-checkbox + span:before {
  content: '';
  border: 1px solid grey;
  border-radius: 3px;
  display: inline-block;
  width: 16px;
  height: 16px;
  margin-right: 0.5em;
  margin-top: 0.5em;
  vertical-align: -2px;
  cursor: pointer;
}

input[type='checkbox'].purple-checkbox:checked + span:before {
  background-image: url('/FontAwesomeSVG/check-white.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: 12px;
  border-radius: 2px;
  background-color: #8514eb;
  color: white;
  cursor: pointer;
}
</style>

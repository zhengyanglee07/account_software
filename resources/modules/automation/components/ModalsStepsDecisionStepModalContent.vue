<template>
  <div class="modal-body row py-10 px-lg-17">
    <BaseFormGroup label="Condition Filters">
      <div
        v-for="(orCondition, orIdx) in decision.filters"
        :key="orIdx"
        class="mt-3 filter-wrapper"
      >
        <BaseFormGroup
          :label="
            (orIdx === 0 ? 'Include' : 'OR') + ' people matching these filters'
          "
        >
          <div
            v-for="(andCondition, andIdx) in orCondition"
            :key="andCondition.id"
          >
            <BaseFormGroup class="row">
              <BaseFormGroup col="md-3">
                <BaseFormSelect
                  v-model="andCondition.name"
                  :options="types"
                  @update:modelValue="updateSubFilters(orIdx, andIdx)"
                />
              </BaseFormGroup>
              <template v-if="andCondition.subFilters.length !== 0">
                <template
                  v-for="(subFilter, fIdx) in andCondition.subFilters"
                  :key="fIdx"
                >
                  <BaseFormGroup
                    v-if="fIdx === 0"
                    col="md-3"
                  >
                    <BaseFormSelect
                      v-model="subFilter.value"
                      :options="getSecondFilterOptions(andCondition.name)"
                    />
                  </BaseFormGroup>
                  <BaseFormGroup
                    v-if="fIdx === 1"
                    col="md-3"
                  >
                    <BaseFormSelect
                      v-model="subFilter.value"
                      :options="getThirdFilterOptions(andCondition.name)"
                    />
                  </BaseFormGroup>

                  <BaseFormGroup
                    v-if="fIdx === 2 && showFourthFilterInput(orIdx, andIdx)"
                    col="md-3"
                  >
                    <BaseFormInput
                      v-model="subFilter.value"
                      :type="getFourthFilterInputType(orIdx, andIdx)"
                    />
                  </BaseFormGroup>
                </template>
              </template>

              <template #label-row-end>
                <button
                  v-show="andIdx !== 0"
                  class="condition-filter-close-btn close"
                  aria-label="close"
                  @click="deleteANDFilter(orIdx, andIdx)"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
              </template>
            </BaseFormGroup>

            <BaseButton
              v-if="andIdx === orCondition.length - 1"
              has-add-icon
              class="mt-2"
              @click="addNewCondition(orIdx)"
            >
              And
            </BaseButton>

            <h3
              v-else
              class="my-2 h-five"
              style="font-weight: bold"
            >
              AND
            </h3>
          </div>
        </BaseFormGroup>

        <button
          v-show="orIdx !== 0"
          class="close mt-2 or-close-btn"
          aria-label="close"
          @click="deleteORFilter(orIdx)"
        >
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    </BaseFormGroup>
    <div class="form-group text-start m-0">
      <BaseButton
        has-add-icon
        type="light-primary"
        class="mt-2"
        @click="addNewORCondition"
      >
        Add another set of Filter
      </BaseButton>
    </div>
  </div>
  <div class="modal-footer flex-center">
    <BaseButton
      v-if="isAddStepModal"
      type="light"
      @click="$emit('back', $event)"
    >
      Back
    </BaseButton>
    <BaseButton
      :disabled="saving"
      @click="saveDecisionStep"
    >
      Save
    </BaseButton>
  </div>
</template>

<script>
import { nanoid } from 'nanoid';
import { mapState, mapActions } from 'vuex';
import modalSaveBtnMixin from '@automation/mixins/modalSaveBtnMixin.js';

const getDefaultANDFilter = () => ({
  id: nanoid(),
  name: '',
  subFilters: [],
});

const assignmentFilters = ['is set', 'is not set'];
const similarityFilters = ['is', 'is not'];
const inequalityFilters = [
  'is less than or equal to',
  'is greater than or equal to',
];

export default {
  name: 'DecisionStepModalContent',
  mixins: [modalSaveBtnMixin],
  props: {
    value: {
      type: Object,
      default: () => ({}),
    },
    modelValue: {
      type: Object,
      default: () => ({}),
    },
    isAddStepModal: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      types: ['Tags', 'Custom Field', 'People Profile'],

      containFilters: ['contain', 'do not contain'],

      cfComparisonFilters: [
        ...assignmentFilters,
        ...similarityFilters,
        ...inequalityFilters,
      ],
      ppOptions: [
        'First name',
        'Last name',
        'Email',
        'Mobile number',
        'Gender',
        'Birth date',
        'Address 1',
        'Address 2',
        'Postcode',
        'City',
        'State',
        'Country',
      ],
      ppComparisonFilters: [...assignmentFilters, ...similarityFilters],
    };
  },
  computed: {
    ...mapState('automations', ['modal', 'tags', 'customFieldNames']),
    decision: {
      get() {
        // if (this.value) return this.value;
        if (Object.keys(this.modelValue).length !== 0) return this.modelValue;
        return this.value;
      },
      set(value) {
        this.$emit('input', value);
      },
    },
  },

  // mounted(){
  //   this.value = this.modelValue,
  // },

  methods: {
    ...mapActions('automations', ['insertOrUpdateStep']),

    getSecondFilterOptions(type) {
      if (type === 'Tags') {
        return this.containFilters;
      }

      if (type === 'Custom Field') {
        return this.customFieldNames.map((c) => c.custom_field_name);
      }

      if (type === 'People Profile') {
        return this.ppOptions;
      }

      throw new Error('First dropdown option seems wrong, please check it');
    },

    getThirdFilterOptions(type) {
      if (type === 'Tags') {
        return this.tags.map((t) => t.tagName);
      }

      if (type === 'Custom Field') {
        return this.cfComparisonFilters;
      }

      if (type === 'People Profile') {
        return this.ppComparisonFilters;
      }

      throw new Error('First dropdown option seems wrong, please check it');
    },

    showFourthFilterInput(orIdx, andIdx) {
      const filter = this.decision.filters[orIdx][andIdx];
      const filterName = filter.name;

      if (!filterName) {
        throw new Error('Unable to find filter type');
      }

      if (filterName === 'Custom Field' || filterName === 'People Profile') {
        const selectedComparison = filter.subFilters[1]?.value;

        return (
          selectedComparison && !assignmentFilters.includes(selectedComparison)
        );
      }

      return false;
    },

    getFourthFilterInputType(orIdx, andIdx) {
      const selectedComparisonFilter =
        this.decision.filters[orIdx][andIdx]?.subFilters[1]?.value;

      if (!selectedComparisonFilter) {
        console.error('No comparison selected, take a look on your code');
        return '';
      }

      if (similarityFilters.includes(selectedComparisonFilter)) {
        return 'text';
      }

      if (inequalityFilters.includes(selectedComparisonFilter)) {
        return 'number';
      }

      throw new Error(
        'Selected filter value does not match any of the available options'
      );
    },

    updateSubFilters(orIdx, andIdx) {
      this.decision.filters[orIdx][andIdx].subFilters = [
        {
          value: '',
        },
        {
          value: '',
        },

        // Note that this third entry in subFilters only useful in certain filter type
        // You can safely ignore this and let it as empty string on irrelevant filter types
        {
          value: '',
        },
      ];
    },

    addNewCondition(orIdx) {
      if (this.decision.filters[orIdx].length >= 5) {
        this.$toast.warning(
          'Warning',
          'You have reached a maximum of 5 AND conditions'
        );
        return;
      }

      this.decision.filters[orIdx].push(getDefaultANDFilter());
    },

    deleteANDFilter(orIdx, andIdx) {
      this.decision.filters[orIdx].splice(andIdx, 1);
    },

    addNewORCondition() {
      if (this.decision.filters.length >= 5) {
        this.$toast.warning(
          'Warning',
          'You have reached a maximum of 5 OR conditions'
        );
        return;
      }

      this.decision.filters.push([getDefaultANDFilter()]);
    },

    deleteORFilter(orIdx) {
      this.decision.filters.splice(orIdx, 1);
    },

    checkInputEmptyOrError() {
      // empty is considered error here
      let hasError = false;

      this.decision.filters.forEach((or) => {
        if (hasError) {
          return;
        }

        or.forEach((and) => {
          const { name } = and;
          const { subFilters } = and;

          if (!name) {
            hasError = true;
            return;
          }

          if (name === 'Tags' && !hasError) {
            hasError = !subFilters[0].value || !subFilters[1].value;
            return;
          }

          if (name === 'People Profile' && !hasError) {
            const comparison = subFilters[1].value;

            if (!comparison) {
              hasError = true;
              return;
            }

            const emptyComparisonValue =
              ['is', 'is not'].includes(comparison) &&
              !subFilters[2].value &&
              subFilters[2].value !== 0;

            hasError =
              !hasError &&
              (!subFilters[0].value ||
                !subFilters[1].value ||
                emptyComparisonValue);

            return;
          }

          if (name === 'Custom Field' && !hasError) {
            const comparison = subFilters[1].value;

            if (!comparison) {
              hasError = true;
              return;
            }

            const emptyComparisonValue =
              [
                'is',
                'is not',
                'is less than or equal to',
                'is greater than or equal to',
              ].includes(comparison) &&
              !subFilters[2].value &&
              subFilters[2].value !== 0;

            hasError =
              !hasError &&
              (!subFilters[0].value ||
                !subFilters[1].value ||
                emptyComparisonValue);
          }
        });
      });

      return hasError;
    },

    async saveDecisionStep() {
      const {
        data: { id, index, parent, config },
      } = this.modal;

      if (this.checkInputEmptyOrError()) {
        this.$toast.warning(
          'Warning',
          'Please select or fill in all the values before saving'
        );
        return;
      }

      this.saving = true;

      await this.insertOrUpdateStep({
        id,
        index,
        data: {
          type: 'decision',
          kind: 'decision',
          desc: 'Decision', // TODO:
          properties: this.decision,

          ...(!id ? { yes: [], no: [] } : {}),
        },
        parent,
        config,
      });

      this.saving = false;
      this.$emit('close-modal');
    },
  },
};
</script>

<style scoped lang="scss">
.filter-wrapper {
  position: relative;
  background: #fff;
  border-radius: 5px;
  border: 1px solid #c2c9ce;
  color: black;
  padding: 20px;
  box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);

  &--first {
    border-top: none;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
  }
}

.condition-filter-close-btn {
  padding-bottom: 0.5rem;
  height: 30px;
  text-align: right;
  background-color: transparent;
  border: none;
  margin-left: auto;
  width: 2%;
  // position: absolute;
  // right: 0;

  @media (max-width: $md-display) {
    // position: absolute;
    // left: 85%;
    // width: 5%;
    margin-top: -38px;
    margin-left: 10px;
  }

  // @media screen and (max-width: $sm-display) {
  //   right: 24px;
  // }
}

.or-close-btn {
  position: absolute;
  top: 0%;
  left: 95.5%;
  border: none;
  background: #0000;

  @media (max-width: $sm-display) {
    left: 91.5%;
  }
}
</style>

<template>
  <ConditionFilterContainer v-show="!emptySubConditions">
    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="subConditionsKey"
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
          v-for="(tagSub, index) in getConditionTagSub"
          :key="index"
          :value="tagSub"
        >
          {{ tagSub }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('subConditionsKey')"
        #error-message
      >
        Please select an option
      </template>
    </BaseFormGroup>

    <BaseFormGroup col="md-3">
      <BaseFormSelect
        v-model="subConditionsValue"
        @change="updateTagSubCondition"
      >
        <option
          value=""
          disabled
        >
          Choose...
        </option>
        <option
          v-for="(tagName, index) in getTagNames"
          :key="index"
          :value="tagName"
        >
          {{ tagName }}
        </option>
      </BaseFormSelect>
      <template
        v-if="showError('subConditionsValue')"
        #error-message
      >
        Please select a tag
      </template>
    </BaseFormGroup>
  </ConditionFilterContainer>
</template>

<script>
import { mapGetters, mapMutations } from 'vuex';
import { required } from '@vuelidate/validators';
import ConditionFilterContainer from '@people/components/ConditionFilterContainer.vue';
import conditionFilterMixin from '@people/mixins/conditionFilterMixin.js';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'TagsConditionFilter',
  components: {
    ConditionFilterContainer,
  },
  mixins: [conditionFilterMixin],
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      subConditionsKey: '',
      subConditionsValue: '',
    };
  },
  validations: {
    subConditionsKey: {
      required,
    },
    subConditionsValue: {
      required,
    },
  },
  computed: {
    ...mapGetters('people', [
      'getConditionTagSub',
      'getTagNames',
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
  },
  async mounted() {
    // load from db if present (e.g. segment condition)
    this.subConditionsKey = this.subConditions[0].key;
    this.subConditionsValue = this.subConditions[0].value[0] ?? '';
  },
  methods: {
    ...mapMutations('people', [
      'updateANDConditionFilter',
      'updateConditionFilterSubCondition',
    ]),

    updateTagSubCondition() {
      this.updateANDConditionFilter({
        id: this.conditionId,
        orIdx: this.orIndex,
        newANDCondition: {
          id: this.conditionId,
          name: 'Tags',
          error: this.v$.$invalid,

          subConditions: [
            {
              key: this.subConditionsKey,
              value: [this.subConditionsValue], // backward-compatibility with multiple tags
            },
          ],
        },
      });
    },
  },
};
</script>

<style scoped lang="scss"></style>

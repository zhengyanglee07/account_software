<template>
  <div>
    <BaseFormGroup label="What action should we perform?">
      <BaseMultiSelect
        v-model="selectedOption"
        label="name"
        :options="stepActions"
      >
        <template #option="{ option }">
          <strong>{{ option.name }}</strong>
          <p>{{ option.description }}</p>
        </template>
      </BaseMultiSelect>
    </BaseFormGroup>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'ActionSelector',
  props: {
    modelValue: { type: Object, default: () => {} },
  },
  emits: ['update:modelValue'],

  computed: {
    ...mapState('automations', ['stepActions']),
    selectedOption: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      },
    },
  },
};
</script>

<style scoped lang="scss"></style>

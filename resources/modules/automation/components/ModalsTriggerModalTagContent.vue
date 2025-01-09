<template>
  <div>
    <BaseFormGroup label="Which tag?">
      <BaseMultiSelect
        v-model="properties"
        :options="options"
        label="tagName"
        :reduce="reducer"
        placeholder="Select tag"
      />
    </BaseFormGroup>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'TriggerModalTagContent',
  props: {
    modelValue: { type: Object, default: () => {} },
  },
  data() {
    return {
      properties: null,
    };
  },
  computed: {
    ...mapState('automations', {
      options: (state) => state.tags,
    }),
  },
  watch: {
    properties(val) {
      this.$emit('update:modelValue', val);
    },
    modelValue(val) {
      // this.properties = this.reducer(val);
    },
  },
  mounted() {
    this.properties = this.reducer(this.modelValue);
  },
  methods: {
    reducer(props) {
      return { processed_tag_id: props?.processed_tag_id };
    },
  },
};
</script>

<style scoped></style>

<template>
  <div>
    <BaseFormGroup label="Which form?">
      <BaseMultiSelect
        v-model="properties"
        :options="options"
        label="title"
        :reduce="reducer"
      >
        <template #option="{ option }">
          {{ option.title }}
        </template>
      </BaseMultiSelect>
    </BaseFormGroup>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { defineEmits, ref, watch, onMounted } from 'vue';

// const emit = defineEmits(['input']);

// const emitProperties = (value) => {
//   console.log(value);
//   emit('input', value);
// };

export default {
  name: 'TriggerModalSubmitFormContent',
  props: ['modelValue'],
  emits: ['update:modelValue'],
  data() {
    return {
      properties: null,
    };
  },
  computed: {
    ...mapState('automations', {
      options: (state) => state.landingPageForms,
    }),
  },
  watch: {
    properties(val) {
      this.$emit('update:modelValue', val);
    },
  },
  mounted() {
    this.properties = this.reducer(this.modelValue);
  },
  methods: {
    reducer(props) {
      return { landing_page_form_id: props?.landing_page_form_id };
    },
  },
};
</script>

<style lang="scss" scoped></style>

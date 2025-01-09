<template>
  <BaseFormGroup>
    <BaseDatePicker
      v-if="type === 'date' || type === 'datetime'"
      id="birthday"
      v-model="localValue"
      :type="type"
      :format="dateFormat"
      value-type="format"
      @change="handleInput"
    />
    <BaseFormInput
      v-else
      v-model="localValue"
      :type="type"
      @input="handleInput"
    />

    <template #error-message>
      {{
        showValidationError &&
          typeof v$.localValue.email !== 'string' &&
          v$.localValue.email?.$invalid
          ? `Email ${localValue} is invalid`
          : showValidationError &&
            typeof v$.localValue.numeric !== 'number' &&
            v$.localValue.numeric?.$invalid
            ? `Input should be a number`
            : ''
      }}
    </template>
  </BaseFormGroup>
</template>

<script>
import { email, numeric } from '@vuelidate/validators';
import 'vue-datepicker-next/index.css';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'CustomFieldInput',
  props: {
    type: String,

    showValidationError: {
      type: Boolean,
      required: false,
      default: false,
    },

    modelValue: [String, Date, Number],
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      localValue: '',
    };
  },
  validations() {
    if (this.type === 'email') {
      return {
        localValue: {
          email,
        },
      };
    }

    if (this.type === 'number') {
      return {
        localValue: {
          numeric,
        },
      };
    }

    return {
      localValue: {},
    };
  },
  computed: {
    dateFormat() {
      return this?.type === 'date' ? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm:ss';
    },
  },
  watch: {
    modelValue: {
      handler(val) {
        this.localValue = val;
      },
      immediate: true,
    },
  },
  methods: {
    handleInput() {
      this.$emit('validation-error', this.v$.$invalid);
      this.$emit('input', this.localValue);
      this.$emit('update:modelValue', this.localValue);
    },
  },
};
</script>

<style scoped lang="scss"></style>

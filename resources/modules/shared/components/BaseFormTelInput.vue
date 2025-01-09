<template>
  <div>
    <VueTelInput
      v-model="proxyValue"
      mode="international"
      auto-format
      auto-default-country
      valid-characters-only
      :default-country="defaultCountry"
      :input-options="inputOptions"
      :disabled="disabled"
      @on-input="$emit('on-input', $event)"
      @validate="$emit('validate', $event)"
      @country-changed="$emit('country-changed', $event)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/dist/vue-tel-input.css';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  /**
   * Default country code to be showed in country dropdown. By default,
   * we will show the country fetched from IP address of the user
   */
  defaultCountry: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Enter your mobile number',
  },
  name: {
    type: String,
    default: 'telephone',
  },
  /**
   * Mark the input as required. Useful in `<form>` only
   */
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  /**
   * Fires when the input changes (for v-model)
   */
  'update:modelValue',
  /**
   * Fires when the input changes
   */
  'on-input',
  /**
   * Fires when the correctness of the phone number changes (from true to false or vice-versa) and
   * when the component is mounted
   */
  'validate',
  /**
   * Fires when country changed (even for the first time)
   */
  'country-changed',
]);

const inputOptions = computed(() => ({
  autocomplete: 'on',
  autofocus: false,
  maxlength: 25,
  name: props.name,
  placeholder: props.placeholder,
  required: props.required,
  tabindex: 0,
  type: 'tel',
  styleClasses: 'form-control',
}));

const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});
</script>

<style lang="scss" scoped>
.vue-tel-input {
  box-shadow: none;
  border: 1px solid #e4e6ef;
  border-radius: 0.475rem;
}
</style>

<style>
.vti__input {
  border-radius: 0.475rem;
}
</style>

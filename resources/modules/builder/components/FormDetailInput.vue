<template>
  <BaseFormGroup
    :label="form.label"
    :required="form.required"
    :col="form.col ?? 12"
  >
    <template #error-message>
      <p
        v-for="(error, index) in errorMessage"
        :key="`checkout-error-${index}`"
        :class="`mt-${index ? 0 : 3} mb-0`"
      >
        {{ error }}
      </p>
    </template>
    <BaseFormInput
      v-if="['text', 'email', 'password'].includes(form.inputType)"
      :id="`${formType}-${form.field}`"
      v-model="value"
      :name="`${formType}[${form.field}]`"
      :type="form.inputType"
      :placeholder="form.placeholder"
      :max="form.max ?? null"
      :required="form.isShow && form.required"
      :disabled="form.isDisabled"
    />
    <BaseFormTelInput
      v-else-if="form.inputType === 'phoneNumber'"
      v-model="value"
      :name="`${formType}[${form.field}]`"
      :required="form.isShow && form.required"
      @country-changed="isCountryChanged = true"
    />
    <BaseFormCountrySelect
      v-else-if="form.inputType === 'country'"
      v-model="value"
      :name="`${formType}[${form.field}]`"
      :required="form.isShow && form.required"
      :country="country"
    />
    <BaseFormRegionSelect
      v-else-if="form.inputType === 'state'"
      v-model="value"
      :name="`${formType}[${form.field}]`"
      :required="form.isShow && form.required"
      :region="state"
      :country="country"
    />
    <BaseFormCheckBox
      v-else-if="form.inputType === 'checkbox'"
      :id="`${formType}-${form.field}`"
      v-model="value"
      :name="`${formType}[${form.field}]`"
      :value="true"
    >
      <span> {{ form.fieldlabel }} </span>
    </BaseFormCheckBox>
  </BaseFormGroup>
</template>

<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue', 'input']);

const props = defineProps({
  formType: { type: String, default: null },
  form: { type: Object, default: () => {} },
  country: { type: String, default: '' },
  state: { type: String, default: '' },
  modelValue: { type: [String, Number], default: '' },
  errorMessage: { type: String, default: '' },
});

const value = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
    emit('input', val);
  },
});
</script>

<style></style>

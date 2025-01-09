<template>
  <RegionSelect
    v-model="proxyValue"
    :name="name"
    class="form-select"
    :country="country"
    :region="region"
    :country-name="countryName"
    region-name
    placeholder="Select State"
    :required="required"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  /**
   * To specify what country to grab the list of regions from.
   * Bind with the same variable as the `v-model` of the corresponding `BaseFormCountrySelect` here
   */
  country: {
    type: String,
    required: true,
  },
  /**
   * Set the default value of the region select
   */
  region: {
    type: String,
    required: true,
  },
  /**
   * Set this to `false` if you are setting it `false` in the corresponding `BaseFormCountrySelect`
   */
  countryName: {
    type: Boolean,
    default: true,
  },
  required: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: 'region',
  },
});

const emit = defineEmits([
  /**
   * For v-model
   */
  'update:modelValue',
]);

const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit('update:modelValue', val);
  },
});
</script>

<template>
  <CountrySelect
    v-model="proxyValue"
    :name="name"
    class="form-select"
    :top-country="topCountry"
    :country="country"
    :country-name="countryName"
    :black-list="blackList"
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
   * Set the default value of the country select
   */
  country: {
    type: String,
    required: true,
  },
  /**
   * Set this to `false` if you want to get the abbreaviation of country as value
   */
  countryName: {
    type: Boolean,
    default: true,
  },
  /**
   * Set array with capitalized short codes of the countries you want to remove from dropdown list
   */
  blackList: {
    type: Array,
    default: () => [],
  },
  required: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: 'country',
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

const topCountry = computed(() => {
  if (props.blackList.includes('MY')) return '';
  return props.countryName ? 'Malaysia' : 'MY';
});
</script>

<template>
  <BaseMultiSelect
    :model-value="modelValue"
    placeholder="Search currency"
    :options="options ?? Object.keys(currencyOptions)"
    :style="{ 'border-radius': '0px' }"
    @input="updateValue"
  >
    <template #selected-option="{ option: { null: label } }">
      <span
        v-if="props.showFlag"
        :class="`currency-flag currency-flag-${label.toLowerCase()} m-0 p-0 me-3`"
      />
      <span>{{ label }}</span>
      <span v-if="props.showName"> - {{ currencyOptions[label].name }}</span>
      <span v-if="props.showSymbol">
        ({{ currencyOptions[label].symbol }})
      </span>
    </template>
    <template #option="{ option: { null: label } }">
      <div class="my-2">
        <span
          v-if="props.showFlag"
          :class="`currency-flag currency-flag-${label.toLowerCase()} m-0 p-0 me-3`"
        />
        <span>{{ label }}</span>
        <span v-if="props.showName"> - {{ currencyOptions[label].name }}</span>
        <span v-if="props.showSymbol">
          ({{ currencyOptions[label].symbol }})
        </span>
      </div>
    </template>
  </BaseMultiSelect>
</template>

<script setup>
import currencyOptions from '@setting/lib/currencies.js';
import { defineEmits } from 'vue';

const props = defineProps({
  options: { type: Array, default: null },
  showFlag: { type: Boolean, default: true },
  showName: { type: Boolean, default: false },
  showSymbol: { type: Boolean, default: false },
  modelValue: { type: String, default: 'MYR' },
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (value) => {
  emit('update:modelValue', value);
};
</script>

<style lang="scss" scoped>
@import 'currency-flags/dist/currency-flags.min.css';

ul {
  list-style: none;
}
.dropdown {
  text-align: center;
  padding: 20px 30px;
}
.dropdown > li {
  float: left;
}
.dropdown button {
  border: 0;
  cursor: pointer;
  background: transparent;
}
.dropdown button:hover,
.dropdown button:focus {
}

.dropdown-list {
  height: 200px;
  overflow-y: scroll;
}
.dropdown button:focus + .dropdown-list,
.dropdown-list:hover {
  display: block;
}
.dropdown-list-item {
  background-color: white;
  padding: 0.25rem 1rem !important;
}
.dropdown-list-item:hover {
  color: #1e2125;
  background-color: #e9ecef;
}
</style>

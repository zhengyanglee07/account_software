<template>
  <BaseSettingLayout title="Checkout Form Options">
    <template #description>
      Choose whether your checkout form requires extra information from your
      customer.
    </template>
    <template #content>
      <BaseFormGroup
        label="Full Name"
        col="6"
      >
        <BaseFormRadio
          v-for="option in ['required', 'hidden']"
          :id="`checkout-full-name-${option}`"
          :key="option"
          v-model="settings.is_fullname"
          :value="option"
        >
          {{ checkboxLabel(option) }}
        </BaseFormRadio>
      </BaseFormGroup>
      <BaseFormGroup
        label="Billing Address"
        col="6"
      >
        <BaseFormRadio
          v-for="option in ['required', 'hidden']"
          :id="`checkout-is-billing-address-${option}`"
          :key="option"
          v-model="settings.is_billingaddress"
          :value="option"
        >
          {{ checkboxLabel(option) }}
        </BaseFormRadio>
      </BaseFormGroup>
      <BaseFormGroup
        label="Company Name"
        col="6"
      >
        <BaseFormRadio
          v-for="option in ['required', 'hidden']"
          :id="`checkout-company-name-${option}`"
          :key="option"
          v-model="settings.is_companyname"
          :value="option"
        >
          {{ checkboxLabel(option) }}
        </BaseFormRadio>
      </BaseFormGroup>
    </template>
  </BaseSettingLayout>
</template>

<script setup>
import { defineEmits, ref, watch, onMounted } from 'vue';

const props = defineProps({
  storePreferences: { type: Object, default: () => {} },
});

const emit = defineEmits(['updated']);

const settings = ref({
  is_fullname: 'required',
  is_billingaddress: 'required',
  is_companyname: 'required',
});

const checkboxLabel = (option) =>
  option === 'required' ? 'Required' : 'Hidden';

watch(settings.value, (newValue) => {
  emit('updated', { ...newValue });
});

onMounted(() => {
  settings.value.is_fullname = props.storePreferences.is_fullname;
  settings.value.is_billingaddress = props.storePreferences.is_billingaddress;
  settings.value.is_companyname = props.storePreferences.is_companyname;
});
</script>

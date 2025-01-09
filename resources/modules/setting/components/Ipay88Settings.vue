<template>
  <BaseFormGroup
    label="Merchant Code"
    required
    :error-message="
      isShowError && !merchantCodeValue ? 'Merchant code is required' : ''
    "
  >
    <BaseFormInput
      id="payment-ipay88-merchant-code"
      v-model="merchantCodeValue"
      type="text"
    />
  </BaseFormGroup>

  <BaseFormGroup
    label="Merchant Key"
    required
    :error-message="
      isShowError && !merchantKeyValue ? 'Merchant key is required' : ''
    "
  >
    <BaseFormInput
      id="payment-ipay88-merchant-key"
      v-model="merchantKeyValue"
      type="text"
    />
  </BaseFormGroup>

  <BaseFormGroup
    v-for="saleChannel in ['onlineStore', 'miniStore'].filter(
      (type) => !!props.domainUrl[type]
    )"
    :key="saleChannel"
    :label="`Request URL/ Staging URL/ Test URL (${
      saleChannel === 'onlineStore' ? 'online-store' : 'mini-store'
    })`"
    description="Kindly enter this as Request URL in e-form during apply for ipay88 account"
  >
    <BaseFormInput
      :id="`payment-ipay88-request-url-${saleChannel}`"
      :model-value="getRequestUrl(saleChannel)"
      type="text"
      disabled
    />
  </BaseFormGroup>

  <BaseFormGroup
    v-for="saleChannel in ['onlineStore', 'miniStore'].filter(
      (type) => !!props.domainUrl[type]
    )"
    :key="saleChannel"
    :label="`Response URL (${
      saleChannel === 'onlineStore' ? 'online-store' : 'mini-store'
    })`"
    description="Kindly enter this as Response URL in e-form during apply for ipay88 account"
  >
    <BaseFormInput
      :id="`payment-ipay88-response-url-${saleChannel}`"
      :model-value="`${domainUrl[saleChannel]}/ipay88/response`"
      type="text"
      disabled
    />
  </BaseFormGroup>
  <BaseFormGroup
    label="Backend Url"
    description="Kindly enter this as Backend URL in e-form during apply for ipay88 account"
  >
    <BaseFormInput
      id="payment-ipay88-backend-url"
      type="text"
      disabled
      :model-value="`${appUrl}/ipay88/callback`"
    />
  </BaseFormGroup>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  merchantCode: { type: String, default: '' },
  merchantKey: { type: String, default: '' },
  isShowError: { type: Boolean, default: false },
  domainUrl: { type: Object, default: () => {} },
  appUrl: { type: String, default: '' },
});

const account = computed(() => usePage().props.account);

const emit = defineEmits(['update:merchantCode', 'update:merchantKey']);

const getRequestUrl = (saleChannel) =>
  `${props.domainUrl[saleChannel]}/checkout/${
    saleChannel === 'onlineStore' ? 'payment' : 'mini-store'
  }`;

const merchantCodeValue = computed({
  get() {
    return props.merchantCode;
  },
  set(val) {
    emit('update:merchantCode', val);
  },
});

const merchantKeyValue = computed({
  get() {
    return props.merchantKey;
  },
  set(val) {
    emit('update:merchantKey', val);
  },
});
</script>

<style></style>

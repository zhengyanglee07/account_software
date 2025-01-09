<template>
  <BasePageLayout
    page-name="Checkout Settings"
    back-to="/settings/all"
    is-setting
  >
    <CheckoutFormSettings
      v-if="Object.keys(storePreferences).length > 0"
      :store-preferences="storePreferences"
      @updated="updateFormOptionSettings"
    />

    <BaseSettingLayout
      v-if="Object.keys(storePreferences).length > 0"
      title="Customer Accounts"
    >
      <template #description>
        <p>
          Choose if you want to prompt your customer to create an account when
          they checkout.
        </p>
      </template>
      <template #content>
        <BaseFormGroup label="Customer Accounts">
          <BaseFormRadio
            v-for="option in customerAccountRadioOptions"
            :id="`checkout-customer-account-${option.value}`"
            :key="option.value"
            v-model="storePreferences.require_account"
            :value="option.value"
          >
            {{ option.label }}
            <div class="text-muted fs-7">
              {{ option.description }}
            </div>
          </BaseFormRadio>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout
      v-if="Object.keys(storePreferences).length > 0"
      title="Customer Contact"
    >
      <template #description>
        <!-- <p>
          Choose if you want to prompt your customer to create an account when
          they checkout
        </p> -->
      </template>
      <template #content>
        <BaseFormGroup label="Customers can checkout with">
          <BaseFormRadio
            v-for="option in customerContactRadioOptions"
            :id="`checkout-customer-contact-${option.value}`"
            :key="option.value"
            v-model="storePreferences.checkout_method"
            :value="option.value"
            :disabled="option.disabled"
          >
            {{ option.label }}
            <p
              v-if="option.disabled"
              class="text-danger"
            >
              Email address is required for customer account
            </p>
          </BaseFormRadio>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>
    <template #footer>
      <BaseButton
        :disabled="loading"
        @click="saveCheckoutSettings"
      >
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script setup>
import CheckoutFormSettings from '@setting/components/CheckoutFormSettings.vue';
import { onMounted, ref, watch, inject, computed } from 'vue';
import checkoutAPI from '@setting/api/checkoutAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  preference: { type: Object, default: () => {} },
});

const storePreferences = ref({});
const loading = ref(false);

const customerContactRadioOptions = computed(() => {
  return [
    { label: 'Email address only', value: 'email address', disabled: false },
    {
      label: 'Mobile number only',
      value: 'mobile number',
      disabled: storePreferences.value.require_account === 'required',
    },
    {
      label: 'Either email address or mobile number',
      value: 'email address||mobile number',
      disabled: storePreferences.value.require_account === 'required',
    },
    {
      label: 'Both email address and mobile number',
      value: 'email address&&mobile number',
      disabled: false,
    },
  ];
});

const customerAccountRadioOptions = [
  {
    label: 'Accounts are required',
    value: 'required',
    description: 'Customers will only be able to checkout as customer',
  },
  {
    label: 'Accounts are disabled',
    value: 'disabled',
    description: 'Customers will only be able to checkout as guests',
  },
  {
    label: 'Accounts are optional',
    value: 'optional',
    description:
      'Customers will have the option to create an account at the beginning of the checkout process',
  },
];

const updateFormOptionSettings = (newValues) => {
  storePreferences.value = {
    ...storePreferences.value,
    is_fullname: newValues.is_fullname,
    is_billingaddress: newValues.is_billingaddress,
    is_companyname: newValues.is_companyname,
  };
};

const saveCheckoutSettings = async () => {
  loading.value = true;
  try {
    const response = await checkoutAPI.update({
      ...storePreferences.value,
    });
    const data = await response.data;
    storePreferences.value = data;
    $toast.success('Success', 'Successfully to save checkout settings');
    loading.value = false;
  } catch (error) {
    $toast.error('Error', 'Fail to save checkout settings');
    loading.value = false;
  }
};

watch(
  () => storePreferences.value.require_account,
  (newValue) => {
    const { checkout_method: checkoutMethod } = storePreferences.value;
    if (
      newValue === 'required' &&
      (checkoutMethod === 'mobile number' ||
        checkoutMethod === 'email address||mobile number')
    )
      storePreferences.value.checkout_method = 'email address';
  }
);

onMounted(() => {
  storePreferences.value = props.preference;
});
</script>

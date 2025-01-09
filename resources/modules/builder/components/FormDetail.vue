<template>
  <div :class="`my-4 ${isMiniStore ? 'mini' : 'online'}-store-form`">
    <template
      v-for="([fieldType, value], index) in Object.entries(formSetting ?? {})"
      :key="index"
    >
      <p
        v-if="showForm(fieldType) && getFormTitle(fieldType)"
        class="form-label"
      >
        {{ getFormTitle(fieldType) }}
      </p>
      <p
        v-if="
          fieldType === 'customerInfo' &&
            !isFunnel &&
            hasCustomerAccount &&
            !isCustomerAuthenticated
        "
        class="text-muted"
      >
        Please fill up the express registration form if you dont have an account
      </p>

      <div class="row">
        <FormDetailInput
          v-for="(form, i) in value"
          v-show="showForm(fieldType) && form.isShow"
          :key="`${fieldType}-${i}`"
          :model-value="formDetail[fieldType][form.field]"
          :form="form"
          :form-type="fieldType"
          :country="formDetail[fieldType]?.country"
          :state="formDetail[fieldType]?.state"
          :error-message="getErrorMessage(`${fieldType}.${form.field}`)"
          @input="(val) => updateFormDetail(val, fieldType, form.field)"
        />
      </div>
    </template>
  </div>
  <input
    type="hidden"
    name="hasPhysicalProduct"
    :value="hasPhysicalProduct"
  >
  <input
    type="hidden"
    name="_token"
    :value="csrfToken"
  >
</template>

<script setup>
import { onBeforeMount, ref, computed, watch } from 'vue';
import FormDetailInput from '@builder/components/FormDetailInput.vue';
import { getErrorMessage } from '@onlineStore/hooks/useCheckoutProcess.js';
import {
  useGetFormDetailData,
  useSetFormDetail,
} from '@onlineStore/hooks/useFormDetail.js';
import { useGetCustomerAccountData } from '@onlineStore/hooks/useCustomerAccount.js';
import { useGetCartData } from '@onlineStore/hooks/useCart.js';
import { useGetShippingData } from '@onlineStore/hooks/useShipping.js';
import { useGetCheckoutData } from '@onlineStore/hooks/useCheckout';
import { useInitializeTwoStepForm } from '@builder/hooks/useTwoStepForm.js';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  settings: { type: Object, default: null },
});

if (Object.keys(props.settings ?? {}).length) {
  const data = usePage().props?.twoStepFormData ?? {};
  data.preferences = {
    is_fullname: props.settings.hasFullName ? 'required' : 'hidden',
    is_billingaddress: props.settings.hasBillingAddress ? 'required' : 'hidden',
    is_companyname: props.settings.hasCompanyName ? 'required' : 'hidden',
    checkout_method: props.settings.hasPhoneNumber
      ? 'email address&&mobile number'
      : 'email address',
    require_account: 'disabled',
  };
  useInitializeTwoStepForm(data);
}

const { isMiniStore, isFunnel, preferences } = useGetCheckoutData();
const {
  isCustomerAccountRequired,
  isCustomerAuthenticated,
  hasCustomerAccount,
} = useGetCustomerAccountData();

const hasPhysicalProduct = computed(() => {
  if (isFunnel.value) {
    const product = props.settings?.selectedProduct;
    return !product || product.physicalProducts === 'on';
  }
  return useGetCartData().hasPhysicalProduct.value;
});

const isShippingRequired = computed(() =>
  isFunnel.value
    ? hasPhysicalProduct.value
    : useGetShippingData().isShippingRequired.value
);

const { formDetail } = useGetFormDetailData();

const csrfToken = computed(() =>
  document.head.querySelector('meta[name="csrf-token"]')
    ? document.head.querySelector('meta[name="csrf-token"]').content
    : ''
);

const hasAddBillingAddress = computed(
  () => formDetail.value?.shipping?.hasBillingAddress
);

const showInputField = (type, key = '') => {
  // check requirement in builder mode
  if (isFunnel.value) return type === 'email address' || props.settings[key];

  // check requirement in online store / mini-store
  if (type === 'email address' || type === 'mobile number')
    return (
      !preferences.value || preferences.value.checkout_method.includes(type)
    );
  return !preferences.value || preferences.value[type] === 'required';
};

const showForm = (addressType) => {
  if (addressType === 'customerInfo') return true;

  const hasShipping = isFunnel.value
    ? hasPhysicalProduct.value
    : isShippingRequired.value;
  if (addressType === 'shipping') {
    return hasShipping;
  }

  return (
    showInputField('is_billingaddress', 'hasBillingAddress') &&
    (hasAddBillingAddress.value || !hasShipping)
  );
};

const updateFormDetail = (val, type, field) => {
  formDetail.value[type][field] = val;
  useSetFormDetail(formDetail.value, isShippingRequired.value, null, true);
};

const { deliveryMethod } = useGetShippingData();
watch(deliveryMethod, () => {
  useSetFormDetail(formDetail.value, isShippingRequired.value);
});

const formSetting = computed(() => {
  const isEmailAndPhoneNumberOptional =
    preferences.value.checkout_method.includes('||');
  const isEmailRequired = isEmailAndPhoneNumberOptional
    ? !formDetail.value.customerInfo.phoneNumber
    : true;
  const isPhoneNumberRequired = isEmailAndPhoneNumberOptional
    ? !formDetail.value.customerInfo.email
    : true;
  const formConfig = {
    customerInfo: [
      {
        inputType: 'text',
        field: 'fullName',
        label: 'Full Name',
        placeholder: 'Full Name',
        required: true,
        isShow: showInputField('is_fullname', 'hasFullName'),
        isDisabled: !isFunnel.value && isCustomerAuthenticated.value,
        max: '255',
      },
      {
        inputType: 'email',
        field: 'email',
        label: 'Email Address',
        placeholder: 'Email Address',
        required: isEmailRequired,
        isShow: showInputField('email address'),
        isDisabled: !isFunnel.value && isCustomerAuthenticated.value,
        max: '255',
      },
      {
        inputType: 'password',
        field: 'password',
        label: 'Password',
        placeholder:
          'must have at least 8 characters, 1 number or special character',
        required: isCustomerAccountRequired.value,
        isShow:
          !isFunnel.value &&
          hasCustomerAccount.value &&
          !isCustomerAuthenticated.value,
      },
      {
        inputType: 'phoneNumber',
        field: 'phoneNumber',
        label: 'Phone Number',
        required: isPhoneNumberRequired,
        isShow: showInputField('mobile number', 'hasPhoneNumber'),
      },
    ],
  };

  ['shipping', 'billing'].forEach((type) => {
    const isRequired = showForm(type);
    formConfig[type] = [
      {
        inputType: 'text',
        field: 'fullName',
        label: 'Full Name',
        placeholder: 'Full Name',
        required: isRequired,
        isShow: true,
        max: '255',
      },
      {
        inputType: 'text',
        field: 'companyName',
        label: 'Company Name',
        placeholder: 'Company Name',
        required: isRequired,
        isShow: showInputField('is_companyname', 'hasCompanyName'),
        max: '255',
      },
      {
        inputType: 'text',
        field: 'address',
        label: 'Full Address',
        placeholder: 'House Number, Building, Street name, etc',
        required: isRequired,
        isShow: true,
        max: '255',
      },
      {
        inputType: 'text',
        field: 'city',
        label: 'City',
        placeholder: 'City',
        required: isRequired,
        isShow: true,
        max: '45',
        col: 6,
      },
      {
        inputType: 'country',
        field: 'country',
        label: 'Country',
        required: isRequired,
        isShow: true,
        col: 6,
      },
      {
        inputType: 'state',
        field: 'state',
        label: 'State',
        placeholder: 'City',
        required: isRequired,
        isShow: true,
        col: 6,
      },
      {
        inputType: 'text',
        field: 'zipCode',
        label: 'Zip/Postal code',
        placeholder: 'Zip/Postal code',
        required: isRequired,
        isShow: true,
        col: 6,
      },
      {
        inputType: 'phoneNumber',
        field: 'phoneNumber',
        label: 'Phone Number',
        required: isRequired,
        isShow: type === 'shipping',
      },
      {
        inputType: 'checkbox',
        field: 'hasBillingAddress',
        fieldlabel: 'Use a different billing address',
        isShow:
          type === 'shipping' &&
          showInputField('is_billingaddress', 'hasBillingAddress'),
      },
    ];
  });
  return formConfig;
});

const getFormTitle = (type) => {
  if (type === 'customerInfo' && !isMiniStore.value)
    return 'Customer Information';
  if (type === 'shipping') return 'Shipping Address';
  if (type === 'billing') return 'Billing Address';
  return null;
};
</script>

<style scoped>
.online-store-form .form-label {
  font-size: 1.25rem;
  font-weight: 700;
  padding-top: 20px;
}
.mini-store-form .form-label {
  font-size: 14px;
  font-weight: 600;
  padding-top: 10px;
}
</style>

<template>
  <BasePageLayout
    :page-name="`${
      props.paymentMethod === null ? 'Add' : 'Edit'
    } Payment Method`"
    back-to="/payment/settings"
    is-setting
  >
    <BaseSettingLayout
      :is-onboarding="props.onboarding"
      :title="props.type === 'ipay88' ? 'iPay88' : props.type"
    >
      <template #description>
        <PaymentSettingDescription :payment-method="type" />
      </template>
      <template #content>
        <BaseFormGroup
          v-if="type === 'stripe'"
          :label="
            props?.accountName && props.accountName !== ''
              ? `Your account is connected (${props.accountName.trim()})`
              : ''
          "
        >
          <BaseButton :href="stripeConnectUrl" :is-open-in-new-tab="true">
            {{ stripeConnectButtonText }}
          </BaseButton>
        </BaseFormGroup>

        <BaseFormGroup
          label="Display Name"
          description="Customers will see this name when they choose the payment method
              to pay for their order at checkout"
          :error-message="
            state.showError && hasError('display_name')
              ? 'Display name is required'
              : ''
          "
        >
          <BaseFormInput
            id="payment-display-name"
            v-model="state.data.display_name"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup v-if="props.type === 'stripe'">
          <BaseFormCheckBox
            id="payment-is-enable-fpx"
            v-model="state.data.enable_fpx"
            :value="1"
          >
            Enable FPX
          </BaseFormCheckBox>

          <BaseFormGroup
            v-if="props.type === 'stripe' && state.data.enable_fpx"
            label="Display Name of FPX"
            :error-message="
              state.showError && hasError('display_name2')
                ? 'Display name of FPX is required.'
                : ''
            "
          >
            <BaseFormInput
              id="payment-fpx-display-name"
              v-model="state.data.display_name2"
              type="text"
            />
            <p class="text-muted">
              To accept payment via FPX, you must first enable it in
              <BaseButton
                type="link"
                href="https://dashboard.stripe.com/settings/payment_methods"
                is-open-in-new-tab
                @click="handleClickEvent"
              >
                Stripe Payment Settings
              </BaseButton>
            </p>
          </BaseFormGroup>
        </BaseFormGroup>

        <BaseFormGroup
          v-if="props.type === 'manual payment'"
          label="For Instruction To Your Customer"
          description="The instructions here will be shown to customers when they select
              this payment method during checkout"
        >
          <ProductDescriptionEditor
            ref="editor"
            :data="state.data"
            :property="'description'"
            class="general-card-section w-100"
            @input="state.data.description = value"
          />
        </BaseFormGroup>

        <BaseFormGroup
          v-if="props.type === 'senangPay'"
          :label="settingText.publishableKey"
          :error-message="
            state.showError && hasError('publishable_key')
              ? `${settingText.publishableKey} is required`
              : ''
          "
        >
          <BaseFormInput
            id="payment-senangpay-publishable-key"
            v-model="state.data.publishable_key"
            type="text"
          />
        </BaseFormGroup>

        <BaseFormGroup
          v-if="props.type === 'senangPay'"
          :label="settingText.secretKey"
          :error-message="
            state.showError && hasError('secret_key')
              ? `${settingText.secretKey} is required`
              : ''
          "
        >
          <BaseFormInput
            id="payment-senangpay-secret-key"
            v-model="state.data.secret_key"
            type="text"
          />
        </BaseFormGroup>

        <Ipay88Settings
          v-if="props.type === 'ipay88'"
          v-model:merchantCode="state.data.publishable_key"
          v-model:merchantKey="state.data.secret_key"
          :is-show-error="state.showError"
          :domain-url="domainUrl"
          :app-url="appUrl"
        />

        <BaseFormGroup
          v-for="saleChannel in ['onlineStore', 'miniStore'].filter(
            (type) => props.type === 'senangPay' && props.returnUrl[type] !== ''
          )"
          :key="saleChannel"
          :label="`Return URL(${
            salesChannel === 'onlineStore' ? 'online-store' : 'mini-store'
          })`"
          description="Kindly enter this as Return URL in your SenangPay Dashboard >
              Settings > Profile > SHOPPING CART INTEGRATION LINK"
        >
          <BaseFormInput
            :id="`payment-senangpay-return-url-${saleChannel}`"
            :model-value="props.returnUrl[saleChannel]"
            type="text"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup v-if="!props.onboarding">
          <BaseFormRadio
            id="payment-enable-os-ms-store"
            v-model="state.data.enabled_at"
            value="1"
          >
            {{ settingText.enableRadio }}
          </BaseFormRadio>
          <BaseFormRadio
            id="payment-disable-os-ms-store"
            v-model="state.data.enabled_at"
            value="0"
          >
            {{ settingText.disableRadio }}
          </BaseFormRadio>
        </BaseFormGroup>
      </template>
      <template v-if="props.onboarding" #footer>
        <div class="w-100 d-flex justify-content-between">
          <BaseButton type="link" href="/onboarding/payments">
            <i class="me-2 fa-solid fa-arrow-left" />
            Back
          </BaseButton>
          <div>
            <BaseButton type="link" href="/onboarding/shippings" class="me-3">
              Skip
              <i class="ms-2 fa-solid fa-arrow-right" />
            </BaseButton>
            <BaseButton @click="savePaymentMethod"> Submit </BaseButton>
          </div>
        </div>
      </template>
      <template v-else #footer>
        <BaseButton @click="savePaymentMethod"> Save </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
  <ImageUploader type="description" @update-value="chooseImage" />
  <Ipay88EmailTemplate />
</template>

<script setup>
import Cookies from 'js-cookie';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import ProductDescriptionEditor from '@product/components/ProductDescriptionEditor.vue';
import Ipay88Settings from '@setting/components/Ipay88Settings.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';
import Ipay88EmailTemplate from '@setting/components/IPay88EmailTemplate.vue';
import PaymentSettingDescription from '@setting/components/PaymentSettingDescription.vue';
import { computed, onMounted, reactive, inject, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import paymentAPI from '@setting/api/paymentAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  paymentMethod: { type: Object, default: () => {} },
  type: { type: String, default: '' },
  returnUrl: { type: Object, default: () => {} },
  stripeClientId: { type: String, default: '' },
  accountName: { type: String, default: '' },
  onboarding: { type: Boolean, default: false },
  domainUrl: { type: Object, default: () => {} },
  appUrl: { type: String, default: '' },
});

const defaultObject = {
  id: null,
  display_name: '',
  display_name2: '',
  description: '',
  enabled_at: 1,
  enable_fpx: 0,
  publishable_key: '',
  secret_key: '',
  payment_methods: '',
};

const state = reactive({
  data: defaultObject,
  showError: false,
  displayName: {
    stripe: 'Credit Card / Debit Card (Stripe)',
    'Stripe Fpx': ' Online Banking (Stripe)',
    senangPay: ' Credit Card / Debit Card and Online Banking (SenangPay)',
    'Manual Payment': 'Exp: Cash On Delivery, Bank Transfer, E-wallet',
    ipay88: 'iPay88',
  },
  paymentDescription: {
    stripe: 'Connect your Stripe account to Hypershapes',
    'manual payment':
      'Give instruction to your customer on how to pay for his order',
  },
});

const rules = reactive({
  data: {
    publishable_key: { required },
    secret_key: { required },
    display_name: { required },
    display_name2: { required },
  },
});
const v$ = useVuelidate(rules, state);

const stripeConnectUrl = computed(
  () =>
    `https://connect.stripe.com/oauth/authorize?response_type=code&client_id=${props.stripeClientId}&scope=read_write`
);
const stripeConnectButtonText = computed(() => {
  if (props.paymentMethod === null) return 'Connect With Stripe';
  const oldUser = props.paymentMethod.payment_account_id === null;
  if (oldUser) return 'Connect With Stripe';
  return 'Change Account';
});

const editor = ref(null);

const settingText = computed(() => {
  if (props.type === 'ipay88')
    return {
      publishableKey: 'Publishable Key',
      secretKey: 'Secret Key',
      enableRadio: 'Enable on Online Store',
      disableRadio: 'Disable on Online Store',
    };
  if (props.type === 'stripe')
    return {
      publishableKey: 'Publishable Key',
      secretKey: 'Secret Key',
      enableRadio: 'Enable on Online Store, and Funnel',
      disableRadio: 'Disable on Online Store, and Funnel',
    };
  return {
    publishableKey: 'Merchant ID',
    secretKey: 'Verify Key',
    enableRadio: 'Enable on Online Store',
    disableRadio: 'Disable on Online Store',
  };
});

const checkConnectedStripe = (displayName, displayName2) => {
  if (!displayName && !displayName2) paymentAPI.update(state.data);
};

const initializePaymentMethod = () => {
  // console.log(props.paymentMethod.payment_account_id);
  let { paymentMethod } = props;
  if (props.paymentMethod === null) {
    defaultObject.display_name = state.displayName[props.type];
    defaultObject.display_name2 = state.displayName['Stripe Fpx'];
    // const type =
    //   props.type === 'cash on delivery' ? 'cash on delivery' : props.type;
    defaultObject.payment_methods = props.type;
    paymentMethod = JSON.parse(JSON.stringify(defaultObject));
    state.data = paymentMethod;
  } else {
    state.data = paymentMethod;
    const { display_name: displayName, display_name2: displayName2 } =
      props.paymentMethod;
    if (displayName === null)
      state.data.display_name = state.displayName[props.type];
    if (displayName2 === null)
      state.data.display_name2 = state.displayName['Stripe Fpx'];
    if (props.paymentMethod.payment_methods.toLowerCase() === 'stripe')
      checkConnectedStripe(displayName, displayName2);
  }
};

const initializeNewPaymentMethod = () => {
  defaultObject.display_name = state.displayName[props.type];
  if (props.type === 'stripe')
    defaultObject.display_name2 = state.displayName['Stripe Fpx'];
  defaultObject.payment_methods = props.type;
  state.data = JSON.parse(JSON.stringify(defaultObject));
};

const checkStripeConnection = () => {
  const isConnectStripe =
    (v$.value.data.publishable_key.required.$invalid ||
      v$.value.data.secret_key.required.$invalid) &&
    props.type === 'stripe';
  if (isConnectStripe) $toast.error('Error', 'Please connect with stripe');
};

const hasError = (model) => v$.value.data[model].required.$invalid;
const isValid = computed(() => {
  if (props.type === 'stripe')
    return hasError('display_name') || hasError('display_name2');
  if (props.type === 'senangPay')
    return (
      hasError('display_name') ||
      hasError('publishable_key') ||
      hasError('secret_key')
    );
  if (props.type === 'ipay88') {
    return (
      hasError('display_name') ||
      hasError('publishable_key') ||
      hasError('secret_key')
    );
  }
  return hasError('display_name');
});

const savePaymentMethod = () => {
  state.showError = isValid.value;
  if (state.showError) return;
  // if (this.type === 'stripe') this.checkStripeConnection();

  // if (!state.data.display_name || state.data.display_name === '') {
  //   $toast.error('Error', 'Display name cannot be blank');
  //   return;
  // }

  if (
    props.type === 'stripe' &&
    hasError('publishable_key') &&
    hasError('secret_key')
  ) {
    $toast.error(
      'Error',
      'This error is shown probably due to you have not connected Stripe. Please connect with Stripe and save again'
    );
    return;
  }

  if (props.onboarding) {
    localStorage.setItem('paymentSettings', JSON.stringify(state.data));
    router.visit('/onboarding/shippings', { replace: true });
  } else {
    paymentAPI.update(state.data).then(() => {
      $toast.success('Success', 'Successfully saved payment method');
      router.visit('/payment/settings');
    });
  }
};

const showAlert = () => {
  const error = JSON.parse(Cookies.get('error'));
  $toast.error('Error', error.title.replace('_', ' '));
  Cookies.remove('error', { path: '/payment/settings/stripe' });
};

const chooseImage = (e) => {
  console.log(editor.value, 'editor');
  editor.value?.chooseImage(e);
};

onMounted(() => {
  const isError = Cookies.get('error') !== undefined;
  if (isError) showAlert();
  initializePaymentMethod();

  if (props.onboarding) {
    const paymentSettings = JSON.parse(localStorage.paymentSettings ?? '{}');
    if (paymentSettings.payment_methods === state.data.payment_methods) {
      state.data = paymentSettings;
    }
  }
});
</script>
<style scoped></style>

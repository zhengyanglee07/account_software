<template>
  <BasePageLayout
    page-name="Lalamove Settings"
    back-to="/apps/all"
    is-setting
  >
    <BaseSettingLayout
      title="Lalamove"
      :is-onboarding="onboarding"
      :class="onboarding ? 'onboarding' : ''"
    >
      <template #description>
        <p>
          You can get Api Key and Api Secret from Developers tab of the
          <BaseButton
            type="link"
            is-open-in-new-tab
            href="https://partnerportal.lalamove.com"
          >
            Partner Portal
          </BaseButton>.
        </p>

        <p>
          You need to top up your Lalamove Wallet in order to get your
          Production API key and secret
        </p>

        <p>
          Kindly ensure you have filled valid pickup location at
          <BaseButton
            type="link"
            href="/location/settings"
            is-open-in-new-tab
          >
            Location Setting
          </BaseButton>.
        </p>

        <p>
          Once connected to your account, you will be able to manage your
          shipments with Lalamove.
        </p>
      </template>
      <template #content>
        <BaseFormGroup
          label="API Key"
          :error-message="hasError('apiKey') ? 'Field is required' : ''"
        >
          <BaseFormInput
            id="lalamove-api-key"
            v-model="state.apiKey"
            type="text"
            placeholder="Enter API Key"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="API Secret"
          :error-message="hasError('apiSecret') ? 'Field is required' : ''"
        >
          <BaseFormInput
            id="lalamove-api-key"
            v-model="state.apiSecret"
            type="text"
            placeholder="Enter API Secret"
          />
        </BaseFormGroup>

        <BaseFormGroup label="Malaysia (Klang Valley, Penang and Johor)" />

        <BaseFormGroup>
          <BaseFormCheckBox
            id="lalamove-enable-car"
            v-model="state.enableCar"
            :value="true"
          >
            Enable Car
          </BaseFormCheckBox>

          <BaseFormGroup
            label="Short description for car delivery"
            :error-message="hasError('carDesc') ? 'Field is required' : ''"
          >
            <BaseFormInput
              id="lalamove-car-description"
              v-model="state.carDesc"
              type="text"
            />
          </BaseFormGroup>
        </BaseFormGroup>

        <BaseFormGroup>
          <BaseFormCheckBox
            id="lalamove-enable-bike"
            v-model="state.enableBike"
            :value="true"
          >
            Enable Motorcycle
          </BaseFormCheckBox>

          <BaseFormGroup
            label="Short description for motorcycle delivery"
            :error-message="hasError('bikeDesc') ? 'Field is required' : ''"
          >
            <BaseFormInput
              id="lalamove-bike-description"
              v-model="state.bikeDesc"
              type="text"
            />
          </BaseFormGroup>
        </BaseFormGroup>

        <BaseFormGroup label="Available Services">
          <BaseCard>
            <BaseFormGroup
              col="6"
              label="Motorcycle"
            >
              <p>Maximum 36cm x 36cm x 36cm, 10kg COD not supported</p>
            </BaseFormGroup>
            <BaseFormGroup
              col="6"
              label="Car"
            >
              <p>Maximum 50cm x 50cm x 50cm, 40kg COD not supported</p>
            </BaseFormGroup>
          </BaseCard>
        </BaseFormGroup>
      </template>
      <template
        v-if="!props.onboarding"
        #footer
      >
        <BaseButton @click="save">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { onMounted, reactive, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import lalamoveAPI from '@app/api/lalamoveAPI.js';
import eventBus from '@services/eventBus.js';

const $toast = inject('$toast');

const props = defineProps({
  onboarding: { type: Boolean, default: false },
  submitUrl: { type: String, default: '' },
});

const state = reactive({
  apiKey: '',
  apiSecret: '',
  enableCar: true,
  carDesc: 'Same-Day Delivery',
  enableBike: true,
  bikeDesc: '2-hour Express Delivery',
  showValidationErrors: false,
});

const rules = {
  apiKey: { required },
  apiSecret: { required },

  carDesc: { required },
  bikeDesc: { required },
};
const v$ = useVuelidate(rules, state);

const hasError = (field) =>
  state.showValidationErrors && v$.value[field].required.$invalid;

const save = async () => {
  state.showValidationErrors = v$.value.$invalid;

  if (state.showValidationErrors) return;

  const data = {
    link: '/lalamove/save',
    apiKey: state.apiKey,
    apiSecret: state.apiSecret,
    enableCar: state.enableCar,
    carDeliveryDesc: state.carDesc,
    enableBike: state.enableBike,
    bikeDeliveryDesc: state.bikeDesc,
  };

  if (props.onboarding) {
    localStorage.setItem('shippingSettings', JSON.stringify(data));
    router.visit(props.submitUrl, { replace: true });
    return;
  }

  try {
    await lalamoveAPI.update(data);

    $toast.success('Success', 'Successfully saved Lalamove settings');
    router.visit('/apps/all');
  } catch (err) {
    $toast.error('Error', 'Failed to save Lalamove settings');
  }
};

onMounted(async () => {
  eventBus.$on('lalamove-submit', () => {
    save();
  });
  if (props.onboarding) {
    const shippingSettings = JSON.parse(localStorage.shippingSettings ?? '{}');

    if (shippingSettings.link === '/lalamove/save') {
      state.apiKey = shippingSettings.apiKey;
      state.apiSecret = shippingSettings.apiSecret;
      state.enableCar = !!shippingSettings.enableCar;
      state.carDesc = shippingSettings.carDeliveryDesc;
      state.enableBike = !!shippingSettings.enableBike;
      state.bikeDesc = shippingSettings.bikeDeliveryDesc;
    }
    return;
  }
  try {
    const res = await lalamoveAPI.index();
    const { lalamove } = res.data;

    if (!lalamove) return;

    state.apiKey = lalamove.api_key;
    state.apiSecret = lalamove.api_secret;
    state.enableCar = !!lalamove.enable_car;
    state.carDesc = lalamove.car_delivery_desc;
    state.enableBike = !!lalamove.enable_bike;
    state.bikeDesc = lalamove.bike_delivery_desc;
  } catch (err) {
    $toast.error('Error', 'Failed to load EasyParcel settings');
  }
});
</script>

<style scoped>
.onboarding :deep(.card-body) {
  margin: 0px !important;
}
</style>

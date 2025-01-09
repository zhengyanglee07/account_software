<template>
  <BasePageLayout
    page-name="Easy Parcel Settings"
    back-to="/apps/all"
    is-setting
  >
    <BaseSettingLayout
      title="Easy Parcel"
      :is-onboarding="onboarding"
    >
      <template #description>
        <p>
          You can get Api key from
          <BaseButton
            type="link"
            href="https://easyparcel.com/my/en/integrations/api/"
            is-open-in-new-tab
          >
            Integration Setting
          </BaseButton>
          in your EasyParcel account. Copy api key under Individual API section.
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
          shipments with EasyParcel.
        </p>
      </template>
      <template #content>
        <BaseFormGroup
          label="EasyParcel API Key"
          :error-message="
            apiKeyValidationError
              ? 'Please fill in your EasyParcel API key'
              : ''
          "
        >
          <BaseFormInput
            id="easyparcel-api-key"
            v-model="apiKey"
            type="text"
            placeholder="Enter API Key"
          />
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
import { onMounted, ref, computed, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import easyparcelAPI from '@app/api/easyparcelAPI.js';
import eventBus from '@services/eventBus.js';

const $toast = inject('$toast');

const props = defineProps({
  onboarding: { type: Boolean, default: false },
  submitUrl: { type: String, default: '' },
});

const apiKey = ref('');
const isSubmit = ref(false);
const rules = {
  apiKey: { required },
};
const v$ = useVuelidate(rules, { apiKey });

const apiKeyValidationError = computed(
  () => isSubmit.value && v$.value.apiKey.required.$invalid
);

const save = async () => {
  if (v$.value.$invalid) {
    isSubmit.value = true;
    return;
  }

  isSubmit.value = false;

  if (props.onboarding) {
    localStorage.setItem(
      'shippingSettings',
      JSON.stringify({
        link: '/easyparcel/save',
        apiKey: apiKey.value,
      })
    );
    router.visit(props.submitUrl, { replace: true });
    return;
  }

  try {
    await easyparcelAPI.update(apiKey.value);

    $toast.success('Success', 'Successfully saved EasyParcel API key');
    router.visit('/apps/all');
  } catch (err) {
    $toast.error('Error', 'Failed to save EasyParcel API key');
  }
};

onMounted(async () => {
  eventBus.$on('easyparcel-submit', () => {
    save();
  });
  if (props.onboarding) {
    const shippingSettings = JSON.parse(localStorage.shippingSettings ?? '{}');
    apiKey.value =
      shippingSettings.link === '/easyparcel/save'
        ? shippingSettings.apiKey
        : null;
    return;
  }
  try {
    const res = await easyparcelAPI.index();
    apiKey.value = res.data.apiKey;
  } catch (err) {
    console.error('Error', 'Failed to load EasyParcel settings');
  }
});
</script>

<style scoped>
:deep(.card-body) {
  margin: 0px !important;
}
</style>

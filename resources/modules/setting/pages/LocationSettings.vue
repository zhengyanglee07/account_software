<template>
  <BasePageLayout
    page-name="Location Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Pickup Locations">
      <template #description>
        <p>
          Your physical store/outlet location, or location for parcel pickup.
        </p>
      </template>
      <template #content>
        <BaseFormGroup
          label="Location Name"
          required
          :error-message="
            showValidationErrors && v$.locationName.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormInput
            id="location-name"
            v-model="state.locationName"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Address 1"
          required
          :error-message="
            showValidationErrors && v$.address1.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormInput
            id="location-address1"
            v-model="state.address1"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup label="Address 2">
          <BaseFormInput
            id="location-address2"
            v-model="state.address2"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="City"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.city.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormInput
            id="location-city"
            v-model="state.city"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Zip Code"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.zip.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormInput
            id="location-zip"
            v-model="state.zip"
            type="number"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Country"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.country.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormCountrySelect
            v-model="state.country"
            :country="state.country"
            disabled
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="State"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.state.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormRegionSelect
            v-model="state.state"
            :region="state.state"
            :country="state.country"
            placeholder="Select State"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Phone Number"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.phoneNumber.required.$invalid
              ? ' Field is required'
              : ''
          "
        >
          <BaseFormTelInput v-model="state.phoneNumber" />
        </BaseFormGroup>
        <BaseFormGroup
          label="Email"
          required
          col="6"
          :error-message="
            showValidationErrors && v$.email.required.$invalid
              ? ' Field is required'
              : showValidationErrors && v$.email.email.$invalid
                ? 'Email format is invalid'
                : ''
          "
        >
          <BaseFormInput
            id="location-email"
            v-model="state.email"
            type="email"
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton
          :disabled="saving"
          @click="saveLocation"
        >
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import useVuelidate from '@vuelidate/core';
import { required, email, numeric } from '@vuelidate/validators';
import { validationFailedNotification } from '@shared/lib/validations.js';
import { onMounted, reactive, inject, watch, ref } from 'vue';
import locationAPI from '@setting/api/locationAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  location: { type: [Object], default: () => {} },
});

const state = reactive({
  locationName: null,
  address1: '',
  address2: '',
  city: '',
  zip: '',
  country: 'Malaysia',
  state: '',
  phoneNumber: '',
  email: '',
  saving: false,
});

const showValidationErrors = ref(false);

const rules = reactive({
  locationName: { required },
  address1: { required },
  city: { required },
  zip: { required, numeric },
  state: { required },
  country: { required },
  phoneNumber: { required },
  email: { required, email },
});
const v$ = useVuelidate(rules, state);

watch(state, () => {
  showValidationErrors.value = false;
});

const saveLocation = () => {
  showValidationErrors.value = true;
  if (v$.value.$invalid) return;

  state.saving = true;

  try {
    locationAPI
      .update({
        locationName: state.locationName,
        address1: state.address1,
        address2: state.address2,
        city: state.city,
        zip: state.zip,
        country: state.country,
        state: state.state,
        phoneNumber: state.phoneNumber,
        email: state.email,
      })
      .then(() => {
        $toast.success('Success', 'Successfully saved location');
      });
  } catch (err) {
    if (err.response.status !== 422) {
      $toast.error(
        'Error',
        'Unexpected error occurs, failed to update location.'
      );
      return;
    }

    validationFailedNotification(err);
  } finally {
    state.saving = false;
  }
};

onMounted(() => {
  if (!props.location) return;

  state.locationName = props.location.name;
  state.address1 = props.location.address1;
  state.address2 = props.location.address2;
  state.city = props.location.city;
  state.zip = props.location.zip;
  state.country = props.location.country;
  state.state = props.location.state;
  state.phoneNumber = props.location.phone_number;
  state.email = props.location.email;
});
</script>

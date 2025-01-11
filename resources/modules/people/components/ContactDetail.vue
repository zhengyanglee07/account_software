<template>
  <BaseCard title="Basic Information" has-header no-x-spacing>
    <BaseFormGroup col="12" label="Entity Type" required>
      <BaseButtonGroup v-model="form.entity_type" :list="ENTITY_TYPE" />
    </BaseFormGroup>

    <BaseFormGroup col="6" for="f_legalName" label="Legal Name" required>
      <BaseFormInput v-model="form.name" id="f_legalName" type="text" />
      <!-- <template #error-message> Field is required </template> -->
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_tin" label="TIN" required>
      <BaseFormInput v-model="form.tax_id_no" id="f_tin" type="text" />
    </BaseFormGroup>

    <BaseFormGroup col="6" label="Registration No. Type" required>
      <BaseButtonGroup v-model="form.reg_no_type" :list="REG_TYPE" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_regNo" label="Registration No." required>
      <BaseFormInput v-model="form.reg_no" id="f_regNo" type="text" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_oldRegNo" label="Old Registration No." required>
      <BaseFormInput v-model="form.old_reg_no" id="f_oldRegNo" type="text" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_sstRegNo" label="SST Registration No." required>
      <BaseFormInput v-model="form.sst_reg_no" id="f_sstRegNo" type="text" />
    </BaseFormGroup>
  </BaseCard>

  <BaseCard title="Contact Information" has-header no-x-spacing>
    <BaseFormGroup col="6" for="f_fName" label="First Name" required>
      <BaseFormInput v-model="form.fname" id="f_fName" type="text" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_lName" label="Last Name" required>
      <BaseFormInput v-model="form.lname" id="f_lName" type="text" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_phone" label="Phone Number" required>
      <BaseFormTelInput v-model="form.phone_number" id="f_phone" />
    </BaseFormGroup>
    <BaseFormGroup col="6" for="f_email" label="Email" required>
      <BaseFormInput v-model="form.email" id="f_email" type="email" />
    </BaseFormGroup>
  </BaseCard>

  <BaseCard title="Contact Addresses" has-header has-footer no-x-spacing>
    <template v-for="(address, index) in form.addresses" :key="index">
      <BaseFormGroup col="11" :for="`f_address_name_${index}`" label="Address" required>
        <BaseFormInput v-model="address.name" :id="`f_address_name_${index}`" type="text" />
      </BaseFormGroup>
      <BaseFormGroup col="1" label="&nbsp;">
        <BaseButton type="none" has-delete-icon @click="removeAddress(index)" p-2></BaseButton>
      </BaseFormGroup>

      <BaseFormGroup col="12" :for="`f_address_${index}`" label="Address" required>
        <BaseFormInput v-model="address.address1" :id="`f_address_${index}`" type="text" />
      </BaseFormGroup>
      <BaseFormGroup col="6" :for="`f_city_${index}`" label="City" required>
        <BaseFormInput v-model="address.city" :id="`f_city_${index}`" type="text" />
      </BaseFormGroup>

      <BaseFormGroup col="6" :for="`f_zip_${index}`" label="Zip" required>
        <BaseFormInput v-model="address.zip" :id="`f_zip_${index}`" type="text" />
      </BaseFormGroup>
      <BaseFormGroup col="6" :for="`f_country_${index}`" label="Country" required>
        <BaseFormSelect v-model="address.country_code" :id="`f_country_#${index}`" :options="props.countries"
          label-key="name" value-key="a3_code" />
      </BaseFormGroup>
      <BaseFormGroup col="6" :for="`f_state_${index}`" label="State" required>
        <BaseFormSelect v-if="address.country_code === 'MYS'" v-model="form.addresses[0].state" :id="`f_state_${index}`"
          :options="props.states" label-key="name" value-key="code" />
        <BaseFormInput v-else v-model="address.state" id="f_state" type="text" />
      </BaseFormGroup>
      <BaseFormGroup col="6" :for="`f_defaultBilling_${index}`" label="Default Billing" required>
        <BaseFormCheckBox v-model="address.is_default_billing" @change="handleDefaultChange('billing', index)"
          :id="`f_defaultBilling_${index}`" :value="true" />
      </BaseFormGroup>
      <BaseFormGroup col="6" :for="`f_defaultShipping_${index}`" label="Default Shipping" required>
        <BaseFormCheckBox v-model="address.is_default_shipping" @change="handleDefaultChange('shipping', index)"
          :id="`f_defaultShipping_${index}`" :value="true" />
      </BaseFormGroup>
    </template>
    <template #footer>
      <BaseButton has-add-icon type="link" @click="addAddress">Add Address</BaseButton>
    </template>
  </BaseCard>
</template>

<script setup>
import { reactive } from 'vue';
import useVuelidate from '@vuelidate/core';
import { required, sameAs } from '@vuelidate/validators';

const props = defineProps({
  countries: {
    type: Array,
    required: true,
  },
  states: {
    type: Array,
    required: true,
  },
});

const formRules = {
  name: { required },
  tax_id_no: { required },
  reg_no: { required },
  old_reg_no: { required },
  sst_reg_no: { required },
  fname: { required },
  lname: { required },
  phone_number: { required },
  email: { required },
  addresses: {
    $each: {
      address1: { required },
      city: { required },
      zip: { required },
      country_code: { required },
      state: { required },
      is_default_billing: { required },
      is_default_shipping: { required },
    },
  },
};

const ENTITY_TYPE = [
  { label: 'Company', value: 'company' },
  { label: 'Individual', value: 'individual' },
  { label: 'General Public', value: 'general_public' },
  { label: 'Foreign Company', value: 'foreign_company' },
  { label: 'Foreign Individual', value: 'foreign_individual' },
  { label: 'Exempted Person', value: 'exempted_person' },
];

const REG_TYPE = [
  { label: 'None', value: 'none' },
  { label: 'NRIC', value: 'NRIC' },
  { label: 'BRN', value: 'BRN' },
  { label: 'PASSPORT', value: 'PASSPORT' },
  { label: 'ARMY', value: 'ARMY' },
];

const DEFAULT_ADDRESS = {
  name: '',
  address1: '',
  city: '',
  zip: '',
  country_code: '',
  state: '',
  is_default_billing: false,
  is_default_shipping: false,
};
const form = reactive({
  // basic information
  name: '',
  entity_type: 'company',
  tax_id_no: '',
  reg_no_type: 'NRIC',
  reg_no: '',
  old_reg_no: '',
  sst_reg_no: '',
  // contact Information
  fname: '',
  lname: '',
  phone_number: '',
  email: '',
  // contact Addresses
  addresses: [{ ...DEFAULT_ADDRESS }],
});
const v$ = useVuelidate(formRules, form);

const addAddress = () => {
  form.addresses.push({ ...DEFAULT_ADDRESS });
};
const removeAddress = (index) => {
  form.addresses.splice(index, 1);
};
const handleDefaultChange = (type, index) => {
  const key = type === 'billing' ? 'is_default_billing' : 'is_default_shipping';
  form.addresses.forEach((address, i) => {
    if (i !== index) {
      address[key] = false;
    }
  });
};
</script>

<template>
  <BasePageLayout page-name="Account Settings" back-to="/settings/all" is-setting>
    <BaseSettingLayout title="Company Information">
      <template #description> Information related to your company. </template>

      <template #content>
        <BaseFormGroup col="6" for="f_company" label="Company" required>
          <BaseFormInput v-model="form.company" id="f_company" type="text" />
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

        <BaseFormGroup col="6" for="f_msicCode" label="MSIC Code" @click="openModal(MSIC_CODE_MODAL_ID)" required>
          <BaseFormInput v-model="form.msic_code_description" id="f_msicCode" type="text" />
        </BaseFormGroup>
        <BaseFormGroup col="6" for="f_" label="Tourism Tax Registration No." required>
          <BaseFormInput v-model="form.tourism_tax_reg_no" id="f_sstRegNo" type="text" />
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Company Contact">
      <template #description>The contact information for your company.</template>

      <template #content>
        <BaseFormGroup col="6" for="f_contact_no" label="Contact No." required>
          <BaseFormTelInput v-model="form.contact_no" :name="'phone_number'" :input-id="'f_contact_no'" />
        </BaseFormGroup>
        <BaseFormGroup col="6" for="f_contact_email" label="Email Address" required>
          <BaseFormInput id="f_contact_email" v-model="form.contact_email" type="email" />
        </BaseFormGroup>

        <BaseFormGroup col="12" label="Website URL" for="f_website_url" required>
          <BaseButtonGroup v-model="form.website_url" />
          <BaseFormInput v-model="form.website_url" id="f_website_url" type="text" />
        </BaseFormGroup>
      </template>

      <template #footer>
        <!-- <BaseButton @click="saveCompanyProfile"> -->
        <!--   Save -->
        <!-- </BaseButton> -->
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Company Addresses">
      <template #description>The contact information for your company.</template>

      <template #content>
        <template v-for="(address, index) in form.addresses" :key="index">
          <BaseFormGroup col="11" :for="`f_address_name_${index}`" label="Address Name" required>
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
            <BaseFormSelect v-if="address.country_code === 'MYS'" v-model="form.addresses[0].state"
              :id="`f_state_${index}`" :options="props.states" label-key="name" value-key="code" />
            <BaseFormInput v-else v-model="address.state" id="f_state" type="text" />
          </BaseFormGroup>
          <BaseFormGroup col="6" :for="`f_defaultBilling_${index}`" label="Default Billing" required>
            <BaseFormCheckBox v-model="address.is_default_billing" @change="handleDefaultChange('billing', index)"
              :id="`f_defaultBilling_${index}`" :value="true" />
          </BaseFormGroup>
          <BaseFormGroup col="6" :for="`f_defaultShipping_${index}`" label="Default Shipping">
            <BaseFormCheckBox v-model="address.is_default_shipping" @change="handleDefaultChange('shipping', index)"
              :id="`f_defaultShipping_${index}`" :value="true" />
          </BaseFormGroup>
        </template>
      </template>

      <template #footer>
        <BaseButton has-add-icon type="link" @click="addAddress">Add Address</BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Company Logo">
      <template #description>The official logo of your business.</template>

      <template #content>
        <BaseFormGroup col="6" for="f_company_logo" required>
          <BaseImagePreview style="height: 100%" size="lg" :image-u-r-l="form.company_logo ||
            'https://media.hypershapes.com/images/hypershapes-favicon.png'
            " @delete="form.company_logo = ''" @click="sectionType = 'companyLogo'" />
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <template #footer>
      <BaseButton @click="saveCompanyProfile"> Save </BaseButton>
    </template>
  </BasePageLayout>

  <MSICCodeModal :modal-id="MSIC_CODE_MODAL_ID" :msic-codes="msicCodes" @change="handleMSCICodeSelect" />

  <ImageUploader @update-value="chooseImage" />
</template>

<script setup>
import { reactive, computed, ref, watch } from 'vue';
import MSICCodeModal from '@setting/components/MSICCodeModal.vue';
import { Modal } from 'bootstrap';
import ImageUploader from '@shared/components/ImageUploader.vue';
import eventBus from '@services/eventBus.js';
import axios from 'axios';

const REG_TYPE = [
  { label: 'None', value: 'none' },
  { label: 'NRIC', value: 'NRIC' },
  { label: 'BRN', value: 'BRN' },
  { label: 'PASSPORT', value: 'PASSPORT' },
  { label: 'ARMY', value: 'ARMY' },
];

const MSIC_CODE_MODAL_ID = 'MSICCodeModal';

const props = defineProps({
  countries: {
    type: Array,
    required: true,
  },
  states: {
    type: Array,
    required: true,
  },
  account: {
    type: Object,
    required: true,
  },
  msicCodes: {
    type: Array,
    required: true,
  },
});

const DEFAULT_ADDRESS = {
  id: '',
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
  company: props.account.company,
  tax_id_no: props.account.tax_id_no,
  reg_no_type: props.account.reg_no_type ?? 'NRIC',
  reg_no: props.account.reg_no,
  old_reg_no: props.account.old_reg_no,
  msic_code: props.account.msic_code,
  msic_code_description: props.account.msic_code_description,
  tourism_tax_reg_no: props.account.tourism_tax_reg_no,
  contact_no: props.account.contact_no,
  contact_email: props.account.contact_email,
  website_url: props.account.website_url,
  addresses: props.account.account_address.length
    ? props.account.account_address
    : [{ ...DEFAULT_ADDRESS }],
  company_logo: props.account.company_logo,
});
const sectionType = ref('companyLogo');

const addAddress = () => {
  form.addresses.push({ ...DEFAULT_ADDRESS });
};

const removeAddress = (index) => {
  form.addresses.splice(index, 1);
};

const openModal = (modalId) => {
  const modalInstance = Modal.getInstance(document.getElementById(modalId));
  modalInstance.show();
};

const handleMSCICodeSelect = (selectedMsicCode) => {
  form.msic_code = selectedMsicCode.code;
  form.msic_code_description = `${selectedMsicCode.code}-${selectedMsicCode.description}`;
};

const chooseImage = (e) => {
  if (sectionType.value === 'companyLogo')
    eventBus.$emit('addCompanyLogoEvent', e);
  else eventBus.$emit('addFaviconEvent', e);
};

const saveCompanyProfile = () => {
  axios.put('/account/setting/company/update', { data: form });
};
</script>

<style scoped lang="scss">
.setting-page {
  &__section-subtitle {
    margin-bottom: 10px;
  }
}

.image-uploader {
  width: 100%;
  height: 150px;

  &__img {
    width: 100%;
    height: 100%;

    &::hover {
      cursor: pointer;
    }
  }

  &::hover {
    cursor: pointer;
  }
}
</style>

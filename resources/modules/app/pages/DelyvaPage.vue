<template>
  <BasePageLayout
    page-name="Delyva Settings"
    back-to="/apps/all"
    is-setting
  >
    <BaseSettingLayout
      title="Delyva"
      :is-onboarding="onboarding"
    >
      <template #description>
        You can get the Company code, Company ID, User ID and Customer ID from
        <BaseButton
          type="link"
          is-open-in-new-tab
          href="https://my.delyva.app/customer/settings/integrations"
        >
          Integration Setting
        </BaseButton>
        in your Delyva account. For API key, you can add a new one by clicking
        on the Add new key button.
        <div v-if="!onboarding">
          <br><br>
          Make sure you have filled correct pickup location at
          <BaseButton
            type="link"
            href="/location/settings"
            is-open-in-new-tab
          >
            Location Setting
          </BaseButton>. Wrong address will fail the process of create shipment.
          <br><br>
          Once your account is connected, you will be able to fulfil the
          customer's orders with Delyva delivery services.
        </div>
      </template>
      <template #content>
        <BaseFormGroup
          label="Company Code"
          :error-message="companyCodeErr ? 'Company code is required' : ''"
          col="md-6"
        >
          <BaseFormInput
            id="delyva-company-code"
            v-model="companyCode"
            type="text"
            @input="companyCodeErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Company ID"
          :error-message="companyIDErr ? 'Company ID is required' : ''"
          col="md-6"
        >
          <BaseFormInput
            id="delyva-company-id"
            v-model="companyID"
            type="text"
            @input="companyIDErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="User ID"
          :error-message="userIDErr ? 'User ID is required' : ''"
          col="md-6"
        >
          <BaseFormInput
            id="delyva-user-id"
            v-model="userID"
            type="text"
            @input="userIDErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Customer ID"
          :error-message="customerIDErr ? 'Customer ID is required' : ''"
          col="md-6"
        >
          <BaseFormInput
            id="delyva-customer-id"
            v-model="customerID"
            type="text"
            @input="customerIDErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="API Key"
          :error-message="apiKeyErr ? 'API key is required' : ''"
          col="md-6"
        >
          <BaseFormInput
            id="delyva-api-key"
            v-model="apiKey"
            type="text"
            @input="apiKeyErr = false"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Item Type"
          :error-message="selectedItemTypeErr ? 'Item type is required' : ''"
          col="md-6"
        >
          <BaseFormSelect
            v-model="selectedItemType"
            :options="itemsType"
          />
        </BaseFormGroup>
      </template>
      <template
        v-if="!onboarding"
        #footer
      >
        <BaseButton @click="save">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import eventBus from '@services/eventBus.js';
import delyvaAPI from '@app/api/delyvaAPI.js';

export default {
  props: {
    delyvaInfo: Object,
    account: Object,
    onboarding: {
      type: Boolean,
      default: false,
    },
    submitUrl: { type: String, default: '' },
    domain: { type: String, default: '' },
  },
  data() {
    return {
      companyCode: '',
      companyID: '',
      userID: '',
      customerID: '',
      apiKey: '',
      selectedItemType: 'PARCEL',
      itemsType: ['PARCEL', 'FOOD'],

      companyCodeErr: false,
      customerIDErr: false,
      companyIDErr: false,
      userIDErr: false,
      apiKeyErr: false,
      selectedItemTypeErr: false,
    };
  },

  mounted() {
    eventBus.$on('delyva-submit', () => {
      this.save();
    });
    if (this.delyvaInfo) {
      this.companyCode = this.delyvaInfo.delyva_company_code ?? '';
      this.companyID = this.delyvaInfo.delyva_company_id ?? '';
      this.userID = this.delyvaInfo.delyva_user_id ?? '';
      this.customerID = this.delyvaInfo.delyva_customer_id ?? '';
      this.apiKey = this.delyvaInfo.delyva_api ?? '';
      this.selectedItemType = this.delyvaInfo.item_type;
    }
  },

  methods: {
    validation() {
      if (this.companyCode.trim() === '') this.companyCodeErr = true;
      if (this.customerID.trim() === '') this.customerIDErr = true;
      if (this.companyID.trim() === '') this.companyIDErr = true;
      if (this.userID.trim() === '') this.userIDErr = true;
      if (this.apiKey.trim() === '') this.apiKeyErr = true;
      if (this.selectedItemType === '') this.selectedItemTypeErr = true;

      return (
        this.companyCodeErr ||
        this.customerIDErr ||
        this.companyIDErr ||
        this.userIDIDErr ||
        this.apiKeyErr ||
        this.selectedItemTypeErr
      );
    },

    save() {
      const result = '';
      if (this.validation()) {
        this.$toast.error('Error', 'Check the required fields!');
        return;
      }

      if (this.onboarding) {
        localStorage.setItem(
          'shippingSettings',
          JSON.stringify({
            link: 'delyva',
            companyCode: this.companyCode.trim(),
            companyID: this.companyID.trim(),
            userID: this.userID.trim(),
            customerID: this.customerID.trim(),
            apiKey: this.apiKey.trim(),
            selected: true,
            itemType: this.selectedItemType.toUpperCase(),
          })
        );
        this.$inertia.visit(this.submitUrl, { replace: true });
        return;
      }
      delyvaAPI
        .orderUpdate(
          {
            event: 'order.updated',
            url: `${this.domain}/api/v1/check/delyva/update`,
          },
          {
            headers: {
              'X-Delyvax-Access-Token': this.apiKey.trim(),
            },
          }
        )
        .then((response) => {
          delyvaAPI
            .saveDetail({
              companyCode: this.companyCode.trim(),
              companyID: this.companyID.trim(),
              userID: this.userID.trim(),
              customerID: this.customerID.trim(),
              apiKey: this.apiKey.trim(),
              itemType: this.selectedItemType.toUpperCase(),
            })
            .then((response1) => {
              this.$toast.success('Success', 'Successfully Saved');
              window.location.href = `/apps/all`;
            })
            .catch((error) => {
              console.log(error);
              this.$toast.error('Error', 'Unexpected Error Occured');
            });
        })
        .catch((error) => {
          this.$toast.error(
            'Error',
            'Failed to setup Delyva: Please check the input details'
          );
        });
    },
  },
};
</script>

<style scoped>
:deep(.card-footer) {
  margin-right: 1.5rem !important;
}

:deep(.card-flush) {
  padding-top: 1rem !important;
}

:deep(.col-md-8) {
  padding-left: 0px !important;
}

:deep(.form-control) {
  margin-right: 5px;
}
</style>

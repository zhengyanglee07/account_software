<template>
  <!-- Billing Address Modal -->
  <BaseModal
    :title="modalTitle"
    :modal-id="modalId"
  >
    <template #footer>
      <BaseButton
        :disabled="isDisabled"
        @click="save"
      >
        Save
      </BaseButton>
    </template>
    <form
      class="row"
      @keydown="errors.clear($event.target.name)"
    >
      <BaseFormGroup
        label="Full Name"
        :error-message="errors.has('fullname') ? errors.get('fullname') : ''"
      >
        <BaseFormInput
          id="order-address-full-name"
          v-model="addressDetail.fullname"
          type="text"
          name="fullname"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Company Name"
        :error-message="errors.has('company') ? errors.get('company') : ''"
      >
        <BaseFormInput
          id="order-address-company"
          v-model="addressDetail.company"
          type="text"
          name="company"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Address"
        :error-message="errors.has('address') ? errors.get('address') : ''"
        col="6"
      >
        <BaseFormInput
          id="order-address"
          v-model="addressDetail.address"
          type="text"
          name="address"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Mobile Number"
        :error-message="errors.has('phoneNo') ? errors.get('phoneNo') : ''"
        col="6"
      >
        <BaseFormTelInput
          v-model="addressDetail.phoneNo"
          @validate="validate"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="City"
        :error-message="errors.has('city') ? errors.get('city') : ''"
        col="6"
      >
        <BaseFormInput
          id="order-address-city"
          v-model="addressDetail.city"
          type="text"
          name="city"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Country"
        :error-message="errors.has('country') ? errors.get('country') : ''"
        col="6"
      >
        <BaseFormCountrySelect
          v-model="addressDetail.country"
          :country="addressDetail.country"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="State"
        :error-message="errors.has('region') ? errors.get('region') : ''"
        col="6"
      >
        <BaseFormRegionSelect
          v-model="addressDetail.state"
          :country="addressDetail.country"
          :region="addressDetail.state"
        />
      </BaseFormGroup>

      <BaseFormGroup
        label="Zip/Postal code"
        :error-message="errors.has('zip') ? errors.get('zip') : ''"
        col="6"
      >
        <BaseFormInput
          id="order-address-city"
          v-model="addressDetail.zip"
          type="text"
          name="zip"
        />
      </BaseFormGroup>
    </form>
  </BaseModal>
</template>

<script>
import { Errors } from '@shared/lib/classes.js';
import OrderCentraliseMixin from '@order/mixins/OrderCentraliseMixins.js';
import eventBus from '@services/eventBus.js';

export default {
  name: 'AddressModal',

  mixins: [OrderCentraliseMixin],

  props: [
    'modalId',
    'propTitle',
    'propDatas',
    'prop_errors',
    'addressType',
    'loadDatas',
  ],

  emits: ['save-button', 'close-button'],

  data() {
    return {
      addressDetail: {
        company: '',
        fullname: '',
        address: '',
        phoneNo: '',
        city: '',
        country: '',
        state: '',
        zip: '',
      },
      isPhoneNoValid: false,
      errors: new Errors(),
      defaultAddress: {},
      modal: null,
    };
  },

  computed: {
    modalTitle() {
      if (!this.propTitle) return 'Sample Title';
      return `Edit  ${this.propTitle}`;
    },

    isDisabled() {
      return (
        JSON.stringify(this.addressDetail) ===
        JSON.stringify(this.defaultAddress)
      );
    },
  },
  watch: {
    propDatas: {
      deep: true,
      handler() {
        this.loadData();
      },
    },
    prop_errors: {
      handler(value, oldVal) {
        this.errors.record(this.prop_errors);
      },
    },
    loadDatas: {
      deep: true,
      immediate: true,
      handler(newVal) {
        this.initializeData(newVal);
      },
    },
  },
  mounted() {
    this.initializeData(this.loadDatas);

    eventBus.$on('order-address-modal', (data) => {
      const addressModal = document.getElementById(data.target);
      addressModal.addEventListener('hide.bs.modal', () => {
        this.setState({ key: 'errors', value: {} });
        this.addressDetail = { ...this.defaultAddress };
      });
    });
    if (this.propData) {
      this.loadData();
    }
  },

  methods: {
    close() {
      this.errors.clear();
      this.$emit('close-button');
    },

    save() {
      this.$emit('save-button', {
        address: this.addressDetail,
        type: this.addressType,
        isPhoneNoValid: this.isPhoneNoValid,
      });
    },

    validate(phoneObj) {
      this.isPhoneNoValid = phoneObj.valid;
    },
    clearError(event) {
      this.deleteErrorMessages({ type: event });
    },

    loadData() {
      this.addressDetail.company =
        this.propDatas[`${this.addressType}_company_name`];
      this.addressDetail.fullname = this.propDatas[`${this.addressType}_name`];
      this.addressDetail.address =
        this.propDatas[`${this.addressType}_address`];
      this.addressDetail.phoneNo =
        this.propDatas[`${this.addressType}_phoneNumber`] ?? '';
      this.addressDetail.city = this.propDatas[`${this.addressType}_city`];
      this.addressDetail.state = this.propDatas[`${this.addressType}_state`];
      this.addressDetail.country =
        this.propDatas[`${this.addressType}_country`];
      this.addressDetail.zip = this.propDatas[`${this.addressType}_zipcode`];
      this.addressDetail.isPhoneNoValid = this.addressDetail.phoneNo !== '';
      this.defaultAddress = { ...this.addressDetail };
    },
    initializeData(data) {
      if (data) {
        Object.keys(data).forEach((key) => {
          this.addressDetail[key] = data[key] ?? '';
        });
        this.defaultAddress = { ...this.addressDetail };
      }
    },
  },
};
</script>

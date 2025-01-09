<template>
  <BaseModal
    title="Edit Affiliate Member Profile"
    :modal-id="modalId"
  >
    <BaseFormGroup
      label="Fisrt Name"
      :col="6"
      required
    >
      <BaseFormInput
        id="fname"
        v-model="firstName"
        type="text"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Last Name"
      :col="6"
      required
      :error-message="showLastNameRequiredErr ? 'This field is required' : ''"
    >
      <BaseFormInput
        id="lname"
        v-model="lastName"
        type="text"
        @input="clearValidationErrors"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Email"
      :col="6"
      required
    >
      <BaseFormInput
        id="email"
        :model-value="email"
        type="text"
        disabled
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Full Address"
      :col="6"
    >
      <BaseFormInput
        id="address"
        v-model="fullAddress"
        type="text"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Postcode"
      :col="6"
    >
      <BaseFormInput
        id="zip"
        v-model="zipcode"
        v-mask="'#####'"
        type="tel"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="City"
      :col="6"
    >
      <BaseFormInput
        id="city"
        v-model="city"
        type="text"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Country"
      :col="6"
    >
      <BaseFormCountrySelect
        id="country1"
        v-model="country"
        :country="country"
        top-country="Malaysia"
        country-name
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="State"
      :col="6"
    >
      <BaseFormRegionSelect
        id="state"
        v-model="state"
        type="text"
        class="form-control m-container__input"
        :region="state"
        :country="country"
        placeholder="Select State"
        country-name
        region-name
      />
    </BaseFormGroup>
    <BaseFormGroup label="Promo Code">
      <BaseFormSelect v-model="selectedPromoCode">
        <option value="">
          None
        </option>
        <option
          v-for="promo in availablePromotionCodes"
          :key="promo"
          :value="promo"
        >
          {{ promo }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="saving"
        class="primary-small-square-button"
        @click="updateAffiliateProfile"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { email as emailValidator, requiredIf } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { mask } from 'vue-the-mask';
import { countries } from '@lib/countries.js';
// import PhoneNumberInput from '@shared/components/PhoneNumberInput.vue';

export default {
  directives: {
    mask,
  },
  components: {
    // PhoneNumberInput,
  },
  props: {
    modalId: {
      type: String,
      default: () => '',
    },
    affiliateProfile: {
      type: Object,
      default: () => ({}),
    },
    address: {
      type: Object,
      default: () => ({}),
    },
    availablePromotions: {
      type: Array,
      default: () => [],
    },
    saving: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      countries: countries.map((country) => country.name),
      //   selectedPromoCode: affiliateProfile.promo_code,

      // localContact: {},
      // localAddress: {},
      firstName: '',
      lastName: '',
      email: '',
      mobileNum: '',
      fullAddress: '',
      zipcode: '',
      city: '',
      country: '',
      state: '',

      showValidationErrors: false,
      showLastNameRequiredErr: false,

      selectedPromoCode: 'none',
    };
  },

  validations: {
    email: {
      email: emailValidator,
      required: requiredIf(function () {
        return !this.mobileNum;
      }),
    },
    phone_number: {
      required: requiredIf(function () {
        return !this.email;
      }),
    },
  },
  computed: {
    availablePromotionCodes() {
      return this.availablePromotions.map((promo) => {
        return promo.discount_code;
      });
    },

    emailFormatErr() {
      return this.showValidationErrors && !this.v$.email.email;
    },
    emailRequiredErr() {
      return this.showValidationErrors && !this.v$.email.required;
    },
    phoneNumberRequiredErr() {
      return this.showValidationErrors && !this.v$.phone_number.required;
    },
  },

  mounted() {
    this.$nextTick(() => {
      this.firstName = this.affiliateProfile.first_name;
      this.lastName = this.affiliateProfile.last_name ?? '';
      this.email = this.affiliateProfile.email;
      this.mobileNum = this.affiliateProfile.phone_number;
      this.fullAddress = this.address.address;
      this.zipcode = this.address.zipcode;
      this.city = this.address.city;
      this.state = this.address.state ?? '';
      this.country = this.address.country ?? 'Malaysia';
      this.selectedPromoCode = this.affiliateProfile.promo_code;
    });
  },
  methods: {
    clearValidationErrors() {
      this.showValidationErrors = false;
      if (this.lastName !== '') {
        this.showLastNameRequiredErr = false;
      }
    },
    updateAffiliateProfile() {
      this.showValidationErrors = this.v$.$invalid;
      if (this.showValidationErrors) return;

      if (this.lastName === '') {
        this.showLastNameRequiredErr = true;
        return;
      }

      this.$emit('update-affiliate-profile', {
        newProfile: {
          ...this.affiliateProfile,
          first_name: this.firstName,
          last_name: this.lastName,
          email: this.email,
          phone_number: this.mobileNum,
          address: this.fullAddress,
          zipcode: this.zipcode,
          city: this.city,
          state: this.state === '' ? null : this.state,
          country: this.country === '' ? null : this.country,
          promo_code: this.selectedPromoCode,
        },
      });
    },
    cancelUpdateAffiliateProfile() {
      this.firstName = this.affiliateProfile.first_name;
      this.lastName = this.affiliateProfile.last_name;
      this.email = this.affiliateProfile.email;
      this.mobileNum = this.affiliateProfile.phone_number;
      this.fullAddress = this.address.address;
      this.zipcode = this.address.zipcode;
      this.city = this.address.city;
      this.state = this.address.state ?? '';
      this.country = this.address.country ?? 'Malaysia';
      this.selectedPromoCode = this.affiliateProfile.promo_code;
    },
  },
};
</script>

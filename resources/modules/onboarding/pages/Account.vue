<template>
  <!-- Desktop view -->
  <div class="sub-title-container__box px-0 ms-3">
    <h4
      class="sub-title-container__title h-two"
      style="font-size: 19.5px !important; text-align: left"
    >
      Tell Us About Your Business
    </h4>
    <p
      class="sub-title-container__sub-title p-two p-0"
      style="
        font-size: 13.975px;
        color: #a1a5b7;
        text-align: left;
        font-weight: 500;
      "
    >
      So we can better craft your experience throughout Hypershapes.
    </p>
  </div>

  <!-- <BaseCard class="w-100"> -->
  <div
    class="row w-100"
    style="margin: auto"
  >
    <!-- Company Logo -->
    <BaseFormGroup label="Company Logo">
      <BaseImagePreview
        is-rounded
        :default-image="
          companyLogo ||
            'https://cdn.hypershapes.com/assets/product-default-image.png'
        "
        @delete="deleteNotificationImage"
      />
    </BaseFormGroup>

    <!-- Store Name -->
    <BaseFormGroup
      col="md-6"
      label="Store Name"
    >
      <BaseFormInput
        id="storeName"
        v-model="v$.storeName.$model"
        type="text"
        name="storeName"
      />
      <template
        v-if="
          (showAllErrors || v$.storeName.$error) &&
            v$.storeName.required.$invalid
        "
        #error-message
      >
        Store name is required
      </template>
    </BaseFormGroup>

    <!-- Subdomain -->
    <BaseFormGroup
      col="md-6"
      label="Domain"
      description="You will use this domain to serve your funnel or store"
    >
      <BaseFormInput
        id="storeSubdomain"
        v-model="v$.storeSubdomain.$model"
        type="text"
        name="storeSubdomain"
        placeholder="Only a-z 0-9 and - accepted"
        @keyup="isSubdomainTaken = false"
      >
        <template #append>
          {{ domain }}
        </template>
      </BaseFormInput>
      <template
        v-if="(showAllErrors && v$.storeSubdomain.$invalid) || isSubdomainTaken"
        #error-message
      >
        {{ subdomainError }}
      </template>
    </BaseFormGroup>

    <!-- Full Address -->
    <BaseFormGroup
      col="md-6"
      label="Full Address"
    >
      <BaseFormInput
        id="address"
        v-model="v$.address.$model"
        type="text"
        name="address"
      />
      <template
        v-if="
          (showAllErrors || v$.address.$error) && v$.address.required.$invalid
        "
        #error-message
      >
        Address is required
      </template>
    </BaseFormGroup>

    <!-- Country -->
    <BaseFormGroup
      col="md-6"
      label="Country"
    >
      <BaseFormCountrySelect
        v-model="select_country"
        :country="select_country"
      />
      <template
        v-if="
          (showAllErrors || v$.select_country.$error) &&
            v$.select_country.required.$invalid
        "
        #error-message
      >
        Country is required
      </template>
    </BaseFormGroup>

    <!-- State -->

    <BaseFormGroup
      col="md-6"
      label="State"
    >
      <BaseFormRegionSelect
        v-model="v$.select_state.$model"
        :country="select_country"
        :region="select_state"
      />
      <template
        v-if="
          (showAllErrors || v$.select_state.$error) &&
            v$.select_state.required.$invalid
        "
        #error-message
      >
        State is required
      </template>
    </BaseFormGroup>

    <!-- City -->
    <BaseFormGroup
      col="md-6"
      label="City"
    >
      <BaseFormInput
        id="city"
        v-model="v$.city.$model"
        type="text"
        name="city"
      />
      <template
        v-if="(showAllErrors || v$.city.$error) && v$.city.required.$invalid"
        #error-message
      >
        City is required
      </template>
    </BaseFormGroup>

    <!-- ZIP Code -->
    <BaseFormGroup
      col="md-6"
      label="Zip Code"
    >
      <BaseFormInput
        id="zip"
        v-model="v$.zip.$model"
        type="text"
        name="zip"
      />
      <template
        v-if="(showAllErrors || v$.zip.$error) && v$.zip.required.$invalid"
        #error-message
      >
        Zip code is required
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      col="md-6"
      label="Mobile Number"
    >
      <BaseFormTelInput
        v-model="mobileNumber"
        :phone-number="mobileNumber"
        @input="inputMobileNumber"
      />
      <template
        v-if="
          (showAllErrors || v$.mobileNumber.$error) &&
            v$.mobileNumber.required.$invalid
        "
        #error-message
      >
        Mobile number is required
      </template>
    </BaseFormGroup>
    <div
      class="w-100 px-3 text-end"
      style="margin-bottom: 15px; padding: 0px"
    >
      <BaseButton @click="validateFormData">
        Submit
      </BaseButton>
    </div>
    <!-- </BaseCard> -->
    <ImageUploader @update-value="chooseImage" />
  </div>
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { countries } from '@lib/countries';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import currencyOptions from '@setting/lib/currencies.js';
import ImageUploader from '@shared/components/ImageUploader.vue';

const isValidSubdomain = (value) => /^[a-z0-9-]*$/.test(value.trim());

export default {
  components: {
    ImageUploader,
  },
  layout: OnboardingLayout,

  props: {
    env: {
      type: String,
      default: 'production',
    },
  },

  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      countries,
      showAllErrors: false,
      isSubdomainTaken: false,
      // v-models
      storeName: '',
      storeSubdomain: '',
      address: '',
      city: '',
      select_state: '',
      zip: '',
      timezone: '',
      select_country: 'Malaysia',
      currency: 'MYR',
      mobileNumber: null,
      currencyOptions,
      companyLogo:
        'https://media.hypershapes.com/images/hypershapes-favicon.png',
    };
  },
  validations() {
    return {
      storeName: {
        required,
      },
      storeSubdomain: {
        required,
        isValidSubdomain,
      },
      address: {
        required,
      },
      city: {
        required,
      },
      select_state: {
        required,
      },
      select_country: {
        required,
      },
      zip: {
        required,
      },
      mobileNumber: {
        required,
      },
    };
  },

  computed: {
    subdomainError() {
      let errorMessage = null;
      switch (true) {
        case this.v$.storeSubdomain.required.$invalid:
          errorMessage = 'Store subdomain is required';
          break;
        case this.v$.storeSubdomain.isValidSubdomain.$invalid:
          errorMessage = 'Only a-z 0-9 and - is accepted';
          break;
        case this.isSubdomainTaken:
          errorMessage = 'Subdomain has been taken. Please use a new subdomain';
          break;
        default:
          errorMessage = 'Error';
      }
      return errorMessage;
    },

    domain() {
      return this.env === 'production'
        ? '.myhypershapes.com'
        : '.salesmultiplier.asia';
    },
  },

  mounted() {
    const { timezone, location, currency } = this.$page.props.ipInfo;
    this.timezone = timezone;
    this.country = {
      name: location.country,
      code: location.country_code,
    };
    this.currency = currency.code;
    this.select_country = location.country;
  },

  methods: {
    chooseImage(image) {
      this.companyLogo = image;
    },
    deleteNotificationImage() {
      this.companyLogo =
        'https://media.hypershapes.com/images/hypershapes-favicon.png';
    },
    inputMobileNumber(e) {
      if (e.target?.value) {
        this.v$.mobileNumber.$model = e.target.value;
      }
    },
    validateFormData() {
      this.showAllErrors = false;
      console.log('rynb', this.v$);
      if (this.v$.$invalid) {
        this.showAllErrors = true;
        return;
      }
      console.log('er');
      //* check domain availability
      this.$axios
        .post('/domain/check', {
          domainName: `${this.storeSubdomain}${this.domain}`,
          isSkipPermissionChecker: true,
        })
        .then((res) => {
          if (res.data.isExisted) {
            this.isSubdomainTaken = true;
          } else {
            this.submitOnboardingAccountData();
          }
        })
        .catch((err) => {
          throw new Error(err);
        });
    },

    submitOnboardingAccountData() {
      this.$axios
        .post('/account/details', {
          storeName: this.storeName,
          subdomainName: this.storeSubdomain,
          address: this.address,
          city: this.city,
          state: this.select_state,
          zip: this.zip,
          timezone: this.timezone,
          countryName: this.select_country,
          currency: this.currency,
          mobileNumber: this.mobileNumber,
          companyLogo: this.companyLogo,
        })
        .then(() => {
          this.$inertia.visit('/onboarding/salechannels', { replace: true });
        })
        .catch((err) => {
          throw new Error(err);
        });
    },
  },
};
</script>

<style>
.currency-select .vs__dropdown-toggle {
  width: 100%;
  padding: 0.75rem 1rem;
  -moz-padding-start: calc(1rem - 3px);
  font-size: 1.1rem;
  font-weight: 500;
  line-height: 1.5;
  color: #5e6278;
  background-color: #ffffff;
  border: 1px solid #e4e6ef;
  border-radius: 0.475rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  appearance: none;
}

@media (max-width: 450px) {
  .px-0 {
    width: 100% !important;
  }

  .row {
    margin: 0px !important;
  }
}
</style>

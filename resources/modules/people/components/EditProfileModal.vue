<template>
  <BaseModal
    :modal-id="modalId"
    no-dismiss
    title="Edit People Profile"
  >
    <div
      v-if="emailTakenError"
      class="alert alert-warning d-flex align-items-center p-5 mb-10"
    >
      {{ emailTakenError }}
    </div>
    <BaseFormGroup
      for="fname"
      label="First Name"
      required
      col="6"
    >
      <BaseFormInput
        id="fname"
        v-model="localContact.fname"
        type="text"
      />
      <template
        v-if="showValidationErrors && v$.localContact.fname.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="lname"
      label="Last Name"
    >
      <BaseFormInput
        id="lname"
        v-model="localContact.lname"
        type="text"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="phone_number"
      label="Mobile Number"
    >
      <BaseFormInput
        type="text"
        hidden
      />
      <BaseFormTelInput
        v-model="localContact.phone_number"
        :name="'phone_number'"
        :input-id="'phone_number'"
        :phone-number="localContact.phone_number"
        @input="clearValidationErrors"
      />
      <template
        v-if="phoneNumberRequiredErr"
        #error-message
      >
        Either email or phone number is required
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="email"
      label="Email"
    >
      <BaseFormInput
        id="email"
        v-model="localContact.email"
        type="email"
        required
        @input="clearValidationErrors"
      />
      <template
        v-if="emailFormatErr"
        #error-message
      >
        Email {{ localContact.email }} format is invalid
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="gender"
      label="Gender"
    >
      <BaseFormSelect
        id="gender"
        v-model="localContact.gender"
      >
        <option
          value=""
          disabled
          selected
          aria-disabled="true"
        >
          Choose...
        </option>
        <option value="Male">
          Male
        </option>
        <option value="Female">
          Female
        </option>
        <option value="Other">
          Other
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="birthday"
      label="Birthday"
    >
      <BaseDatePicker
        id="birthday"
        v-model="localContact.birthday"
        format="YYYY-MM-DD"
        value-type="format"
      />
    </BaseFormGroup>

    <BaseFormGroup
      for="address1"
      label="Address 1"
    >
      <BaseFormInput
        id="address1"
        v-model="localAddress.address1"
        type="text"
      />
    </BaseFormGroup>

    <BaseFormGroup
      for="address2"
      label="Address 2"
    >
      <BaseFormInput
        id="address2"
        v-model="localAddress.address2"
        type="text"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="zip"
      label="Postcode"
    >
      <BaseFormInput
        id="zip"
        v-model="localAddress.zip"
        v-mask="'#####'"
        type="tel"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="city"
      label="City"
    >
      <BaseFormInput
        id="city"
        v-model="localAddress.city"
        type="text"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="country"
      label="Country"
    >
      <BaseFormCountrySelect
        id="country1"
        v-model="localAddress.country"
        type="text"
        :country="localAddress.country"
        top-country="Malaysia"
        country-name
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="state"
      label="State"
    >
      <BaseFormRegionSelect
        id="state"
        v-model="localAddress.state"
        type="text"
        :region="localAddress.state"
        :country="localAddress.country"
        placeholder="Select State"
        country-name
        region-name
      />
    </BaseFormGroup>

    <div class="modal__dividor" />

    <BaseFormGroup label="Custom Fields" />
    <div v-if="customFields.length !== 0">
      <div
        v-for="(customField, index) in localCustomFields"
        :key="index"
      >
        <BaseFormGroup>
          {{ customField.customFieldName }}
          <CustomFieldInput
            v-model="customField.customFieldContent"
            :type="customField.type"
            :show-validation-error="showValidationErrors"
            @validation-error="handleCustomFieldValidationError"
            @input="clearValidationErrors"
          />
        </BaseFormGroup>
      </div>
    </div>
    <span v-else>No custom field found on this contact</span>
    <template #footer>
      <BaseButton
        type="light"
        data-bs-dismiss="modal"
        @click="cancelUpdatePeopleProfile"
      >
        Dismiss
      </BaseButton>
      <BaseButton
        :disabled="saving"
        @click="updatePeopleProfile"
      >
        <div v-if="saving">
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
        <span v-else>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import {
  email as emailValidator,
  requiredIf,
  required,
} from '@vuelidate/validators';
import 'vue-datepicker-next/index.css';
import { mask } from 'vue-the-mask';
import { countries } from '@lib/countries';
import CustomFieldInput from '@people/components/CustomFieldInput.vue';
import useVuelidate from '@vuelidate/core';
import eventBus from '@services/eventBus.js';

export default {
  name: 'EditProfileModal',
  directives: {
    mask,
  },
  components: {
    CustomFieldInput,
  },
  props: {
    modalId: String,
    contact: Object,
    address: Object,
    customFields: Array,
    saving: Boolean,
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      countries: countries.map((country) => country.name),
      // telInputProps: {
      //   name: 'phone_number',
      //   inputId: 'phone_number', // note: this id is important

      // },

      localContact: {},
      localAddress: {},
      localCustomFields: [],

      customFieldValidationError: false,
      showValidationErrors: false,
      emailTakenError: null,
    };
  },
  validations: {
    localContact: {
      fname: {
        required,
      },
      email: {
        email: emailValidator,
        required: requiredIf(function () {
          return !this.localContact.phone_number;
        }),
      },
      phone_number: {
        required: requiredIf(function () {
          return !this.localContact.email;
        }),
      },
    },
  },
  computed: {
    emailFormatErr() {
      return this.v$.localContact.email.email.$invalid;
    },
    emailRequiredErr() {
      return this.v$.localContact.email.required.$invalid;
    },
    phoneNumberRequiredErr() {
      return this.v$.localContact.phone_number.required.$invalid;
    },
  },
  watch: {
    contact(value) {
      this.localContact = { ...value };
    },
    address(value) {
      this.localAddress = { ...value };

      // workaround for the hailat state problem
      // ref:https://github.com/gehrj/vue-country-region-select/issues/26
      this.$nextTick(() => {
        this.localAddress.state = value.state;
      });
    },
    customFields(cfs) {
      this.localCustomFields = cfs.map((cf) => ({
        ...cf,
        customFieldContent:
          cf.customFieldContent === '-' ? null : cf.customFieldContent,
      }));
    },
  },
  mounted() {
    eventBus.$on('get-taken-email', (err) => {
      this.emailTakenError = err;
    });
  },
  methods: {
    clearValidationErrors() {
      this.showValidationErrors = false;
    },
    handleCustomFieldValidationError(hasError) {
      this.customFieldValidationError = hasError;
    },
    updatePeopleProfile() {
      const { fname, lname } = this.localContact;

      this.showValidationErrors =
        this.v$.$invalid || this.customFieldValidationError;
      if (this.showValidationErrors) return;

      this.$emit('update-people-profile', {
        newContact: {
          ...this.localContact,
          displayName: !fname && !lname ? '-' : `${fname || ''} ${lname || ''}`,
        },
        newAddress: this.localAddress,
        newCustomFields: this.localCustomFields,
      });
    },
    cancelUpdatePeopleProfile() {
      // reset all local states
      this.localContact = { ...this.contact };
      this.localAddress = { ...this.address };
      this.localCustomFields = cloneDeep(this.customFields);
    },
  },
};
</script>

<style scoped lang="scss">
.modal__dividor {
  width: 100%;
  height: 0.4px;
  background-color: #ced4da;
  margin: 20px 0;
}
</style>

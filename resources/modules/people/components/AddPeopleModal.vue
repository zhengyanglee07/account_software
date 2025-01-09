<template>
  <BaseModal
    :modal-id="modalId"
    no-dismiss
    title="Add People Profile"
  >
    <div
      v-if="emailTakenError"
      class="alert alert-warning d-flex align-items-center p-5 mb-10"
    >
      {{ emailTakenError }}
    </div>
    <BaseFormGroup
      col="6"
      for="fname"
      label="First Name"
      required
    >
      <BaseFormInput
        id="fname"
        v-model="localContact.fname"
        type="text"
        @input="clearValidationErrors"
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
        @input="clearValidationErrors"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="phone_number"
      label="Mobile Number"
    >
      <BaseFormTelInput
        v-model="localContact.phone_number"
        :name="'phone_number'"
        :input-id="'phone_number'"
        @input="clearValidationErrors"
      />

      <template
        v-if="
          (showValidationErrors &&
            v$.localContact.phone_number.required.$invalid) ||
            (showValidationErrors && v$.localContact.email.required.$invalid)
        "
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
        @input="clearValidationErrors"
      />

      <template
        v-if="showValidationErrors && v$.localContact.email.email.$invalid"
        #error-message
      >
        Invalid email {{ localContact.email }}
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
        class="form-select"
        @change="clearValidationErrors"
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
        class="w-100 birthday-date-picker"
        :disabled-date="disableAfterToday"
        format="YYYY-MM-DD"
        value-type="format"
        @input="clearValidationErrors"
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
        @input="clearValidationErrors"
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
        @input="clearValidationErrors"
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
        @input="clearValidationErrors"
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
        @input="clearValidationErrors"
      />
    </BaseFormGroup>

    <BaseFormGroup
      col="6"
      for="country"
      label="Country"
    >
      <BaseFormCountrySelect
        id="country"
        v-model="localAddress.country"
        type="text"
        :country="localAddress.country"
        country-name
        @input="clearValidationErrors"
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
        :country="localAddress.country"
        :region="localAddress.state"
        placeholder="Select State"
        country-name
        region-name
        @input="clearValidationErrors"
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
          {{ customField.custom_field_name }}
          <CustomFieldInput
            v-model="customField.value"
            :type="customField.type || 'text'"
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
        @click="cancelAddPeopleProfile"
      >
        Dismiss
      </BaseButton>
      <BaseButton
        id="add-people-button"
        :disabled="saving"
        @click="addPeopleProfile"
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
  required,
  requiredIf,
} from '@vuelidate/validators';
import 'vue-datepicker-next/index.css';
import { mask } from 'vue-the-mask';
import shortId from 'shortid';
import { countries } from '@lib/countries';
import CustomFieldInput from '@people/components/CustomFieldInput.vue';
import useVuelidate from '@vuelidate/core';
import eventBus from '@services/eventBus.js';

export default {
  name: 'AddProfileModal',
  directives: {
    mask,
  },
  components: {
    CustomFieldInput,
  },
  props: {
    modalId: {
      type: String,
      required: true,
    },
    customFields: {
      type: Array,
      default: () => [],
    },
    saving: Boolean,
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
      localCustomFields: {},

      customFieldValidationError: false,
      showValidationErrors: false,
      emailTakenError: null,
    };
  },
  watch: {
    contact(value) {
      this.localContact = { ...value };
    },
    address(value) {
      this.localAddress = { ...value };
    },
    customFields: {
      handler(value) {
        this.localCustomFields = cloneDeep(
          value.map((v) => ({ ...v, value: '' }))
        );
      },
      immediate: true,
    },
  },
  mounted() {
    eventBus.$on('get-taken-email', (err) => {
      this.emailTakenError = err;
    });
  },
  methods: {
    disableAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0));
    },
    clearValidationErrors() {
      this.showValidationErrors = false;
    },
    handleCustomFieldValidationError(hasError) {
      this.customFieldValidationError = hasError;
    },
    addPeopleProfile() {
      const { fname, lname } = this.localContact;

      this.showValidationErrors =
        this.v$.$invalid || this.customFieldValidationError;
      if (this.showValidationErrors) {
        this.$toast.warning(
          'Warning',
          'Please clear all the errors before proceeding'
        );
        return;
      }

      this.$emit('add-people-profile', {
        newContact: {
          ...this.localContact,
          displayName: !fname && !lname ? '-' : `${fname || ''} ${lname || ''}`,
        },
        newAddress: { ...this.localAddress, email: this.localContact.email },
        newCustomFields: this.localCustomFields,
      });
    },
    cancelAddPeopleProfile() {
      // reset all local states
      this.localContact = { ...this.contact };
      this.localAddress = { ...this.address };
      this.localCustomFields = cloneDeep(
        this.customFields.map((cf) => ({ ...cf, value: '' }))
      );
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

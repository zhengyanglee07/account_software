<template>
  <div class="w-100">
    <div class="w-100">
      <BaseFormGroup
        label="Full Name"
        label-for="firstName"
      >
        <BaseFormInput
          id="fullName"
          v-model="fullName"
          type="text"
          placeholder="Enter your full name"
          required
        />
        <template
          v-if="showError"
          #error-message
        >
          This field cannot be empty
        </template>
      </BaseFormGroup>
      <BaseFormGroup
        label="Email Address"
        label-for="emailAddress"
      >
        <BaseFormInput
          id="emailAddress"
          v-model="emailAddress"
          type="text"
          disabled
        />
      </BaseFormGroup>
      <BaseFormGroup label="Phone Number">
        <BaseFormTelInput v-model="phoneNumber1" />
      </BaseFormGroup>
    </div>
    <div class="col-md-8 w-100">
      <div class="d-flex justify-content-end pt-0">
        <BaseButton
          type="theme-primary"
          :disabled="isSaving"
          @click="save"
        >
          Save
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AccountSettingsEditProfile',

  props: {
    name: {
      type: String,
      default: null,
    },
    email: {
      type: String,
      default: null,
    },
    phoneNumber: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      fullName: '',
      emailAddress: this.email,
      phoneNumber1: '',
      dialCode: '',
      showError: false,
      isSaving: false,
    };
  },
  watch: {
    fullName(newValue) {
      this.showError = false;
      if (newValue === '') this.showError = true;
    },
  },
  mounted() {
    this.fullName = this.name;
    this.phoneNumber1 = this.phoneNumber;
  },
  methods: {
    save() {
      if (this.showError) return;
      this.isSaving = true;
      axios
        .post('/save/user/name', {
          name: this.fullName,
          phoneNumber: this.phoneNumber1,
        })
        .then(() => {
          this.$toast.success('Success', 'Updated successfully');
        })
        .catch((error) => console.log(error))
        .finally(() => {
          this.isSaving = false;
        });
    },
    inputPhoneNumber(phoneNumber) {
      if (!phoneNumber.includes('+'))
        this.phoneNumber1 = `+${this.dialCode}${phoneNumber}`;
    },
  },
};
</script>

<style lang="scss" scoped>
:deep(.form-group) {
  padding-right: 0 !important;
  padding-left: 0 !important;
}
:deep(.card .card-footer) {
  padding: 0.5rem 1rem !important;
}
</style>

<template>
  <!-- Customer Information Modal -->
  <BaseModal
    title="Edit Customer Information"
    modal-id="contactInfoModal"
  >
    <template #footer>
      <BaseButton @click="updateContact">
        Save
      </BaseButton>
    </template>
    <form
      class="row"
      @submit.prevent="updateContact"
      @keyup.enter="updateContact"
      @keydown="clearError($event.target.name)"
    >
      <BaseFormGroup
        label="First name"
        col="6"
        :error-message="hasError('fname') ? errors.fname[0] : ''"
      >
        <BaseFormInput
          id="order-customer-fname"
          ref="fname"
          v-model="customer.fname"
          type="text"
          name="fname"
        />
      </BaseFormGroup>
      <BaseFormGroup
        label="Last name"
        col="6"
        :error-message="hasError('lname') ? errors.lname[0] : ''"
      >
        <BaseFormInput
          id="order-customer-lname"
          ref="lname"
          v-model="customer.lname"
          type="text"
          name="lname"
        />
      </BaseFormGroup>
      <BaseFormGroup
        label="Email Address"
        :error-message="hasError('email') ? errors.email[0] : ''"
        required
      >
        <BaseFormInput
          id="order-customer-email"
          ref="email"
          v-model="customer.email"
          type="email"
          name="email"
        />
      </BaseFormGroup>
      <BaseFormGroup
        label="Mobile Number"
        :error-message="hasError('phoneNo') ? errors.phoneNo[0] : ''"
        required
      >
        <BaseFormTelInput
          ref="phoneNo"
          v-model="customer.phoneNo"
          @validate="validate"
        />
      </BaseFormGroup>
    </form>
  </BaseModal>
</template>

<script>
import { mapState } from 'vuex';
import OrderCentraliseMixin from '@order/mixins/OrderCentraliseMixins.js';

export default {
  name: 'ContactInfoModal',
  mixins: [OrderCentraliseMixin],
  props: ['loadDatas'],
  emits: ['save-contact'],
  data() {
    return {
      customer: {
        fname: '',
        lname: '',
        email: '',
        phoneNo: '',
      },
      isPhoneNoValid: false,
      defaultData: {},
    };
  },
  computed: {
    ...mapState('orders', ['errors']),
  },
  watch: {
    loadDatas: {
      deep: true,
      immediate: true,
      handler(newVal) {
        if (newVal !== undefined) {
          Object.keys(newVal).forEach((key) => {
            this.customer[key] = newVal[key] ?? '';
          });
          this.defaultData = { ...this.customer };
        }
      },
    },
  },
  mounted() {
    const contactInfoModal = document.getElementById(`contactInfoModal`);
    contactInfoModal?.addEventListener('hide.bs.modal', () => {
      this.setState({ key: 'errors', value: {} });
      this.customer = { ...this.defaultData };
    });
  },
  methods: {
    updateContact() {
      const filter =
        /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      this.setState({ key: 'errors', value: {} });
      if (!this.isPhoneNoValid) {
        this.setErrors({
          key: 'phoneNo',
          value: 'Please enter correct phone format',
        });
        this.$refs.phoneNo.focus();
      }

      if (!filter.test(this.customer.email)) {
        this.setErrors({
          key: 'email',
          value: 'Please enter correct email format',
        });
        this.$refs.email.focus();
      }
      if (this.customer.fname === '') {
        this.setErrors({ key: 'fname', value: 'First name is required' });
        this.$refs.fname.focus();
      }

      if (Object.keys(this.errors).length === 0) {
        this.$emit('save-contact', this.customer);
      }
    },
    hasError(name) {
      Object.prototype.hasOwnProperty.call(this.errors, name);
    },
    validate(phoneObj) {
      console.log('phone number validate');
      this.isPhoneNoValid = phoneObj.valid;
    },
    clearError(event) {
      this.deleteErrorMessages({ type: event });
    },
  },
};
</script>

<style></style>

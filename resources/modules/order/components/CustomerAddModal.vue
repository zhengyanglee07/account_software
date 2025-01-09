<template>
  <form @submit.prevent="emits('saveCustomer', formDetail)">
    <BaseModal
      title="Create a new customer"
      modal-id="createCustomerModal"
    >
      <template
        v-for="([fieldType, value], index) in Object.entries(
          formSettings ?? {}
        )"
        :key="index"
      >
        <div class="row">
          <FormDetailInput
            v-for="(form, i) in value"
            :key="`${fieldType}-${i}`"
            v-model="formDetail[fieldType][form.field]"
            :form="form"
            :form-type="fieldType"
            :country="formDetail[fieldType]?.country"
            :state="formDetail[fieldType]?.state"
            :error-message="getErrors(fieldType, form)"
          />
        </div>
      </template>

      <template #footer>
        <BaseButton is-submit>
          <span>Save</span>
        </BaseButton>
      </template>
    </BaseModal>
  </form>
</template>

<script setup>
import FormDetailInput from '@builder/components/FormDetailInput.vue';
import { computed, ref } from 'vue';
import { useStore } from 'vuex';

const emits = defineEmits(['saveCustomer']);

const store = useStore();

const errors = computed(() => store.state.orders.errors ?? {});

const getErrors = (type, form) => {
  if (!errors.value[type]) return '';
  return errors.value[type][form.error_field ?? form.field] ?? '';
};

const formDetail = ref({
  customerInfo: {
    fname: '',
    lname: '',
    email: '',
    phoneNo: '',
  },
});

const formSettings = {
  customerInfo: [
    {
      inputType: 'text',
      field: 'fname',
      label: 'First name',
      placeholder: 'First Name',
      required: true,
      isShow: true,
      max: '255',
      col: 6,
    },
    {
      inputType: 'text',
      field: 'lname',
      label: 'Last name',
      placeholder: 'Last Name',
      required: false,
      isShow: true,
      max: '255',
      col: 6,
    },
    {
      inputType: 'email',
      field: 'email',
      label: 'Email Address',
      placeholder: 'Email Address',
      required: true,
      isShow: true,
      max: '255',
    },
    {
      inputType: 'phoneNumber',
      field: 'phoneNo',
      error_field: 'phone_number',
      label: 'Phone Number',
      required: true,
      isShow: true,
    },
  ],
};
</script>

<style scoped></style>

<template>
  <BaseModal :modal-id="modalId" no-dismiss title="Add People Profile" no-padding size="lg">
    <!-- <div class="alert alert-warning d-flex align-items-center p-5 mb-10"> -->
    <!--   Sample Error -->
    <!-- </div> -->

    <ContactDetail :countries="countries" :states="states" v-model="form" />
    <template #footer>
      <BaseButton type="light" data-bs-dismiss="modal">
        Dismiss
      </BaseButton>
      <BaseButton id="add-people-button" :disabled="saving" @click="saveContact">
        <div v-if="saving">
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
        <span v-else>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import {  ref,reactive } from 'vue';
import ContactDetail from '@people/components/ContactDetail.vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const props = defineProps({
  modalId: {
    type: String,
    required: true,
  },
  countries: {
    type: Array,
    required: true,
  },
  states: {
    type: Array,
    required: true,
  },
});

const emits = defineEmits(['update-contact-list']);

const DEFAULT_ADDRESS = {
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
  // basic information
  name: '',
  entity_type: 'company',
  tax_id_no: '',
  reg_no_type: 'NRIC',
  reg_no: '',
  old_reg_no: '',
  sst_reg_no: '',
  // contact Information
  fname: '',
  lname: '',
  phone_number: '',
  email: '',
  // contact Addresses
  addresses: [{ ...DEFAULT_ADDRESS }],
});

const hideModal = () => {
  const modal = document.getElementById(props.modalId);
  const modalInstance = Modal.getInstance(modal);
  modalInstance.hide();
};
const saveContact = () => {
  axios.post('/sales/contact/save', form)
    .then((response) => {
      console.log(response.data);
      emits('update-contact-list');
      hideModal();
    })
    .catch((error) => {
      console.log(error);
    });
}
</script>

<style scoped lang="scss">
.modal__dividor {
  width: 100%;
  height: 0.4px;
  background-color: #ced4da;
  margin: 20px 0;
}
</style>

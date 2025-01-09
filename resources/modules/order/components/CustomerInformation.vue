<template>
  <BaseCard
    has-header
    has-toolbar
    title="Customer Information"
  >
    <template #toolbar>
      <BaseButton
        v-if="page == 'view'"
        type="link"
        data-bs-toggle="modal"
        data-bs-target="#contactInfoModal"
      >
        Edit
      </BaseButton>
      <BaseButton
        v-if="page == 'create'"
        type="button"
        @click="clearDetail"
      >
        <i class="fa-solid fa-xmark fs-2" />
      </BaseButton>
    </template>

    <p
      v-if="Object.keys(customer).length == 0"
      class="text-muted"
    >
      No customer information
    </p>
    <div v-else>
      <p
        v-if="customer.name == null || customer.name == ''"
        class="text-muted"
      >
        No customer
      </p>
      <p v-else>
        {{ customer.name }}
      </p>

      <p
        v-if="customer.email == null || customer.email == ''"
        class="text-muted"
      >
        No email address
      </p>
      <p v-else>
        <BaseButton
          type="link"
          is-open-in-new-tab
          :href="`/people/profile/${customer.processedRandomId}`"
        >
          {{ customer.email }}
        </BaseButton>
      </p>

      <p
        v-if="customer.phoneNo == null || customer.phoneNo == ''"
        class="text-muted"
      >
        No mobile number
      </p>
      <p v-else>
        {{ customer.phoneNo }}
      </p>
    </div>
  </BaseCard>

  <AddressDetail
    address-title="Shipping Address"
    address-type="shipping"
    modal-target="shipAddressModal"
    :is-same-address="false"
    :address-data="shipping"
  />

  <AddressDetail
    address-title="Billing Address"
    address-type="billing"
    :is-same-address="checkSameAsShipping"
    modal-target="billingAddressModal"
    :address-data="billing"
  />

  <!-- End of card body -->
  <AddressModal
    modal-id="shipAddressModal"
    prop-title="Shipping Address"
    address-type="shipping"
    :load-datas="shipping"
    :prop_errors="errors"
    @close-button="setState({ key: 'errors', value: {} })"
    @save-button="saveAddress"
  />
  <AddressModal
    modal-id="billingAddressModal"
    prop-title="Billing Address"
    address-type="billing"
    :load-datas="billing"
    :prop_errors="errors"
    @close-button="setState({ key: 'errors', value: {} })"
    @save-button="saveAddress"
  />
  <ContactInfoModal
    :load-datas="customer"
    @save-contact="saveContact"
  />
</template>

<script>
import AddressDetail from '@order/components/OrderAddressDetail.vue';
import AddressModal from '@order/components/OrderAddressModal.vue';
import ContactInfoModal from '@order/components/OrderContactInfoModal.vue';
import { mapMutations, mapState } from 'vuex';
import eventBus from '@services/eventBus.js';

export default {
  name: 'CustomerInformation',
  components: {
    AddressDetail,
    AddressModal,
    ContactInfoModal,
  },
  props: {
    page: { type: String, default: '' },
  },
  emits: ['save-contact', 'save-address'],
  data() {
    return {};
  },
  computed: {
    ...mapState('orders', ['customer', 'shipping', 'billing', 'errors']),
    checkSameAsShipping() {
      return JSON.stringify(this.shipping) === JSON.stringify(this.billing);
    },
  },
  methods: {
    ...mapMutations('orders', ['setErrors', 'setState', 'updateState']),
    clearDetail() {
      this.setState({ key: 'customer', value: {} });
      this.setState({ key: 'shipping', value: {} });
      this.setState({ key: 'billing', value: {} });
    },
    saveAddress(value) {
      const modalId =
        value.type === 'billing' ? 'billingAddressModal' : 'shipAddressModal';
      if (this.page === 'create') {
        Object.keys(value.address).forEach((key, index) => {
          this.updateState({
            key: value.type,
            name: key,
            value: value.address[key],
          });
        });
        this.hideModal(modalId);
      } else if (!value.isPhoneNoValid && value.address.phoneNo !== '') {
        this.setErrors({
          key: 'phoneNo',
          value: 'Please enter correct phone format',
        });
      } else {
        this.hideModal(modalId);
        this.$emit('save-address', value);
      }
    },
    saveContact(value) {
      this.$emit('save-contact', value);
      this.hideModal('contactInfoModal');
    },
    hideModal(modalId) {
      eventBus.$emit(`hide-modal-${modalId}`);
    },
  },
};
</script>

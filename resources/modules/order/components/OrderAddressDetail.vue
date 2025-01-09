<template>
  <BaseCard
    has-header
    has-toolbar
    :title="addressTitle"
  >
    <template #toolbar>
      <BaseButton
        type="link"
        data-bs-toggle="modal"
        :data-bs-target="`#${modalTarget}`"
        @click="triggerModal()"
      >
        Edit
      </BaseButton>
    </template>

    <p
      v-if="isEmptyAddressData"
      class="text-muted"
    >
      No {{ addressType }} address
    </p>
    <p
      v-else-if="isSameAddress && addressType == 'billing'"
      class="text-muted"
    >
      Same as shipping address
    </p>
    <template
      v-for="field in [
        'company',
        'fullname',
        'address',
        'zip',
        'state',
        'country',
        'phoneNo',
      ].filter((e) => addressType !== 'billing' || e !== 'phoneNo')"
      v-else
      :key="field"
    >
      <p v-if="address[field]">
        {{
          field === 'zip' ? `${address.zip} ${address.city}` : address[field]
        }}
      </p>
    </template>
  </BaseCard>
</template>

<script>
import eventBus from '@services/eventBus.js';

export default {
  name: 'AddressDetail',
  props: [
    'isSameAddress',
    'addressData',
    'addressTitle',
    'modalTarget',
    'addressType',
  ],
  data() {
    return {
      address: this.addressData,
    };
  },
  computed: {
    isEmptyAddressData() {
      return (
        Object.keys(this.addressData).length === 0 ||
        !Object.values(this.addressData).some((x) => x !== null && x !== '')
      );
    },
  },
  watch: {
    addressData: {
      immediate: true,
      deep: true,
      handler(newVal) {
        if (newVal !== undefined) {
          this.updateLocalData();
        }
      },
    },
  },
  mounted() {
    // this.updateLocalData();
  },
  methods: {
    triggerModal() {
      eventBus.$emit('order-address-modal', {
        action: 'show',
        target: this.modalTarget,
      });
    },
    updateLocalData() {
      Object.keys(this.addressData).forEach((key) => {
        this.address[key] = this.addressData[key];
      });
    },
  },
};
</script>

<style lang="scss" scoped></style>

<template>
  <div class="py-5 fs-6">
    <!-- Contact -->
    <div class="fw-bolder mt-5">
      Contact
    </div>
    <div class="text-gray-600">
      {{ `Email: ${contact.email ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Mobile Number: ${contact.phone_number ?? ''}` }}
    </div>

    <!-- Details -->
    <div class="fw-bolder mt-5">
      Details
    </div>
    <div class="text-gray-600">
      {{ `First Name: ${contact.fname ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Last Name: ${contact.lname ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Gender: ${contact.gender ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Birth date: ${contact.birthday ?? ''}` }}
    </div>
    <!-- Location -->
    <div class="fw-bolder mt-5">
      Location
    </div>
    <div class="text-gray-600">
      {{ `Address 1: ${address?.address1 ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Address 2: ${address?.address2 ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Postcode: ${address?.zip ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `City: ${address?.city ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `State: ${address?.state ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Country: ${address?.country ?? ''}` }}
    </div>
    <!-- Acquisition -->
    <div class="fw-bolder mt-5">
      Acquisition
    </div>
    <div class="text-gray-600">
      {{ `Channel: ${contact.acquisition_channel ?? ''}` }}
    </div>
    <div class="text-gray-600">
      {{ `Date: ${acquisitionDate ?? ''}` }}
    </div>
    <!-- Marketing -->
    <div class="fw-bolder mt-5">
      Marketing Email
    </div>
    <div class="text-gray-600">
      {{ `Status: ${marketingEmailStatus ?? ''}` }}
    </div>
    <!-- Custom Fields -->
    <div class="fw-bolder mt-5">
      Custom Fields
    </div>
    <div
      v-for="customField in customFields"
      :key="customField.id"
    >
      <template v-if="customField.customFieldName">
        <div class="text-gray-600">
          {{
            `${customField.customFieldName}: ${
              customField.customFieldContent ?? ''
            }`
          }}
        </div>
      </template>
    </div>
  </div>
</template>

<script>
/* eslint-disable indent */
import dayjs from 'dayjs';
import localizedFormat from 'dayjs/plugin/localizedFormat';

dayjs.extend(localizedFormat);

export default {
  name: 'PeopleProfileProfile',
  components: {},
  props: {
    contact: Object,
    address: Object,
    customFields: Array,
    marketingEmailStatus: String,
  },
  computed: {
    acquisitionDate() {
      return dayjs(this.contact.created_at).format('llll');
    },
  },
};
</script>

<style scoped lang="scss"></style>

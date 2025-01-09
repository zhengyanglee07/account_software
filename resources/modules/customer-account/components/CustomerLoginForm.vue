<template>
  <div>
    <form
      class="w-100"
      @submit.prevent="accountLogin()"
    >
      <div
        class="checkout-container_content pt-0 px-0"
        style="padding: 0"
        :class="{ 'mini-store-customer-login': isMiniStore }"
      >
        <div :class="isMiniStore ? '' : 'checkout-container-content'">
          <BaseFormGroup
            label="Email Address"
            required
          >
            <BaseFormInput
              id="customer-account-email"
              v-model="email"
              type="email"
              required
              max="255"
              name="email"
              placeholder="Email Address"
            />
          </BaseFormGroup>
          <BaseFormGroup
            label="Password"
            required
          >
            <BaseFormInput
              id="customer-account-password"
              v-model="password"
              type="password"
              name="password"
              required
              min="0"
              placeholder="Password"
            />
          </BaseFormGroup>

          <div class="w-100 text-end">
            <BaseButton
              type="theme-primary"
              is-submit
            >
              Login
            </BaseButton>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import axios from 'axios';
import eventBus from '@services/eventBus.js';
import { useSetCustomerAccount } from '@onlineStore/hooks/useCustomerAccount.js';
import { useSetCustomerInfo } from '@onlineStore/hooks/useFormDetail.js';

const emits = defineEmits(['submit']);
const email = ref('');
const password = ref('');

const $toast = inject('$toast');

const { isMiniStore } = inject('checkoutData');

const accountLogin = () => {
  axios
    .post('/login', {
      email: email.value,
      password: password.value,
      isManuallyLogin: true,
    })
    .then(({ data }) => {
      if (data.status !== 'success') {
        $toast.error('Error', 'These credentials do not match our records');
        return;
      }
      useSetCustomerAccount({ processedContact: data.user?.processed_contact });
      useSetCustomerInfo(data.customerInfo);
      eventBus.$emit('initializeForm', data.user);
      emits('submit');
      $toast.success('Success', 'Successful login');
      window.location.reload();
    });
};
</script>

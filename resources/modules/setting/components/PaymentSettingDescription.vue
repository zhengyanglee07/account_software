<template>
  <div v-if="paymentMethod === 'ipay88'">
    You have to request an account from iPay88 by submitting their contact form
    <BaseButton
      href="https://www.ipay88.com/contact-sales/"
      type="link"
      is-open-in-new-tab
    >
      here
    </BaseButton>. You will get <b>Merchant Code</b> and <b>Merchant Key</b> after
    successfully creating an iPay88 account. Your account will be in 'Testing'
    status by default. To activate your iPay88 account, you need to let the
    iPay88 team conduct testing on your store to make sure payment can be
    performed successfully without any errors.

    <div class="pt-3">
      To activate your account:
      <ol>
        <li>
          Fill in the correct <b>Merchant Code</b> and <b>Merchant Key</b>
        </li>
        <li>Create a virtual product with a price of RM 1.00</li>
        <li>Ensure the order total is RM 1.00 during checkout</li>
        <li>
          Drop an email to
          <a
            href="mailto:support@ipay88.com.my?subject=Account Activation&body=Dear ipay88,%0d%0aKindly activate my account. MYR 1.00 test product is as below for ipay88 checking and verification%0d%0aMerchant code: M0XXXX%0d%0aMerchant Name: Your Co Sdn Bhd%0d%0aMYR 1.00 product link: https://your-store-domain.com/products/test-product"
            class="h_link"
          >iPay88 support</a>. View
          <BaseButton
            type="link"
            data-bs-toggle="modal"
            data-bs-target="#ipay88-email-template-sample"
          >
            here
          </BaseButton>
          for sample of email template for account activatation.
        </li>
      </ol>
    </div>

    <div class="pt-3">
      One iPay88 account only supported one domain at a time. If you have
      updated the domain for the store that you accept iPay88, kindly send an
      email to iPay88 support to update the <b>Request URL</b> and
      <b>Response URL</b>
      of your new store.
    </div>

    <div class="pt-3">
      Supported Currency: <br>
      <BaseBadge
        v-for="currency in supportedCurrency"
        :key="currency"
        :text="currency"
      />
    </div>
  </div>

  <div v-else>
    Set up your payment method to have a secure payment system.'
  </div>
</template>

<script setup>
const supportedCurrency = [
  'MYR',
  'AUD',
  'CAD',
  'EUR',
  'GBP',
  'HKD',
  'MYR',
  'SGD',
  'THB',
  'USD',
  'MYR',
  'USD',
  'AUD',
  'CAD',
  'EUR',
  'GBP',
  'SGD',
  'HKD',
];
defineProps({
  paymentMethod: { type: String, default: '' },
});
</script>

<style></style>

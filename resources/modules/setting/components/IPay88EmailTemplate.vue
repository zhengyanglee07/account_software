<template>
  <BaseModal
    modal-id="ipay88-email-template-sample"
    :small="true"
    title="Sample of email template for account activation"
  >
    <div
      class="d-flex flex-column align-items-center justify-content-start bg-light-info rounded p-5"
    >
      <p>Dear ipay88,</p>
      <p>
        Kindly activate my account. MYR 1.00 test product is as below for ipay88
        checking and verification.
      </p>
      <p>Merchant code: <strong>M0XXXX</strong></p>
      <p>Merchant Name: <strong>Your Co Sdn Bhd</strong></p>
      <p>
        MYR 1.00 product link:
        <a href="#">https://your-store-domain.com/products/test-product</a>
      </p>
    </div>
    <template #footer>
      <BaseButton @click="copyText">
        Copy
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
const copyText = () => {
  navigator.clipboard.writeText(`
  Dear ipay88,
  Kindly activate my account. MYR 1.00 test product is as below for ipay88 checking and verification
  Merchant code: M0XXXX
  Merchant Name: Your Co Sdn Bhd
  MYR 1.00 product link: https://your-store-domain.com/products/test-product
`);
};
</script>

<style scoped>
p {
  margin-bottom: 5px;
  width: 100%;
  text-align: left;
}
</style>

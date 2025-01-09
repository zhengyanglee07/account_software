<template>
  <div
    class="container col-md-12"
    style="text-align: center"
  >
    <div class="row">
      <div class="col-md-12 d-none d-sm-block">
        <img
          src="@email/assets/media/success-tick.png"
          style="margin: 15px 0 25px 0; width: 13%"
        >
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 d-block d-sm-none">
        <img
          src="@email/assets/media/success-tick.png"
          style="margin: 15px 0 25px 0; width: 35%"
        >
      </div>
    </div>

    <br>
    <h1
      class="kt-login__title"
      style="color: #4a4a4a"
    >
      Success
    </h1>
    <p
      class="grey-font mt-4"
      style="
        font-weight: 400;
        margin-top: 5px;
        font-size: 16px;
        padding: 8px 5px 12px 5px;
      "
    >
      You have successfully verified an email address{{
        email ? ' '.email : ''
      }}. You can now start sending email from this address.
      <br>

      <BaseButton
        href="/emails"
        class="mt-5"
      >
        Back to Emails
      </BaseButton>
    </p>
  </div>
</template>

<script>
export default {
  name: 'VerificationSuccess',
  props: {
    email: String,
  },
};
</script>

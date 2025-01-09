<template>
  <div class="container">
    <div class="d-flex justify-content-center">
      <img
        src="@shared/assets/media/hypershapes-logo.png"
        class="onboarding-logo"
        width="500"
      >
    </div>
    <div class="d-flex justify-content-center">
      <form
        method="POST"
        action="/master-admin/login"
        class="w-50"
      >
        <BaseFormGroup required>
          <BaseFormInput
            id="user-name"
            name="username"
            placeholder="Username"
            type="text"
          />
        </BaseFormGroup>
        <BaseFormGroup required>
          <BaseFormInput
            id="password"
            name="password"
            placeholder="Password"
            type="password"
          />
        </BaseFormGroup>

        <input
          type="hidden"
          name="_token"
          :value="csrfToken"
        >

        <BaseButton is-submit>
          Log in
        </BaseButton>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  layout: '',
  setup() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    return {
      csrfToken,
    };
  },
};
</script>

<style>
body,
#app,
.container {
  height: 100%;
}
</style>

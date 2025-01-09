<template>
  <Head title="Register" />
  <!-- <div class="row">
          <div
            class=""
            style="text-align: center; width: 100%"
          >
            <img
              src="@shared/assets/media/hypershapes-logo.png"
              class="onboarding-logo"
            >
          </div>
        </div> -->

  <h4
    class="loginOrSignUp__main-title h-two"
    style="text-align: center; font-size: 22.75px"
  >
    Create An Affiliate Account
  </h4>
  <div
    class="mt-3"
    style="text-align: center; margin-bottom: 22.75px; font-weight: 500"
  >
    <span
      class="p-two"
      style="
        cursor: default;
        padding-right: 5px;
        font-size: 16.25px;
        color: #b5b5c3;
      "
    >Already have an account ?</span>
    <Link
      id="kt_login_signup"
      href="/login"
      class="inputContainer__redirect p-two"
      style="font-size: 16.25px"
    >
      Sign in here
    </Link>
  </div>

  <div class="container w-100">
    <form @submit.prevent="submit">
      <div class="form-group">
        <div class="row">
          <div class="col-md-6">
            <div class="inputContainer__row inputContainer__row--col-left">
              <BaseFormGroup label="First Name">
                <BaseFormInput
                  id="firstName"
                  v-model="form.fName"
                  type="text"
                  name="firstName"
                />
              </BaseFormGroup>
              <!-- <BaseFormGroup
                        description=" "
                        label="First Name"
                      >
                        <BaseFormInput
                          id="firstName"
                          placeholder=" "
                          name="firstName"
                          type="text"
                          v-model:model-value="form.fName"
                        />
                      </BaseFormGroup> -->
              <!-- <label
                        class="inputContainer__label h-five"
                      >First Name</label>
                      <input
                        id="firstName"
                        v-model="form.fName"
                        name="firstName"
                        type="text"
                        class="inputContainer__input"
                      > -->
            </div>
          </div>
          <div class="col-md-6">
            <div
              class="inputContainer__row inputContainer__row--col-right text-start"
            >
              <BaseFormGroup label="Last Name">
                <BaseFormInput
                  id="lastName"
                  v-model="form.lName"
                  type="text"
                  name="lastName"
                />
                <template
                  v-if="errors.lName"
                  #error-message
                >
                  Last Name is required
                </template>
              </BaseFormGroup>
              <!-- <BaseFormGroup
                        description=" "
                        label="Last Name"
                      >
                        <BaseFormInput
                          id="lastName"
                          placeholder=" "
                          name="lastName"
                          type="text"
                          v-model:model-value="form.lName"
                        />
                      </BaseFormGroup> -->
              <!-- <label
                        class="inputContainer__label h-five"
                      >Last Name</label>
                      <input
                        id="lastName"
                        v-model="form.lName"
                        name="lastName"
                        type="text"
                        class="inputContainer__input"
                        required
                      > -->
              <!-- <span
                        v-if="errors.has('lastName')"
                        class="error-message error-font-size"
                        v-text="errors.get('lastName')"
                      /> -->
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="inputContainer__row text-start">
              <BaseFormGroup label="Email">
                <BaseFormInput
                  id="email"
                  v-model="form.email"
                  type="email"
                  name="email"
                />
                <template
                  v-if="errors.email"
                  #error-message
                >
                  {{ errors.email }}
                </template>
              </BaseFormGroup>
              <!-- <BaseFormGroup
                        description=" "
                        label="Email"
                      >
                        <BaseFormInput
                          id="email"
                          placeholder=" "
                          name="email"
                          v-model:model-value="form.email"
                          type="email"
                        />
                      </BaseFormGroup> -->
              <!-- <label class="inputContainer__label h-five">Email</label>
                      <input
                        id="email"
                        v-model="form.email"
                        name="email"
                        class="inputContainer__input"
                        autocomplete="off"
                        autofocus
                        required
                      > -->

              <!-- <span
                        v-if="errors.email"
                        class="error-message error-font-size failed"
                      >
                        {{ errors.email }}
                      </span> -->
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="inputContainer__row text-start">
              <BaseFormGroup label="Password">
                <BaseFormInput
                  id="password"
                  v-model="form.password"
                  type="password"
                  name="password"
                />
                <template
                  v-if="errors.password"
                  #error-message
                >
                  {{ errors.password }}
                </template>
              </BaseFormGroup>
              <!-- <BaseFormGroup
                        description=" "
                        label="Password"
                      >
                        <BaseFormInput
                          id="password"
                          placeholder=""
                          name="password"
                          type="password"
                          v-model:model-value="form.password"
                        />
                      </BaseFormGroup> -->
              <!-- <label
                        class="inputContainer__label h-five"
                      >Password</label>
                      <input
                        id="password"
                        v-model="form.password"
                        style="margin-bottom: 0"
                        type="password"
                        class="inputContainer__input"
                        name="password"
                        autocomplete="new-password"
                        required
                      > -->
              <!-- <span
                        v-if="errors.password"
                        class="error-message error-font-size failed"
                        v-text="errors.password"
                      /> -->
            </div>
          </div>
        </div>
      </div>
      <div class="inputContainer__row mb-0 d-flex justify-content-center">
        <BaseButton
          type="primary"
          class="w-100"
          :disabled="loading"
          is-submit
          @click="triggerSomeEventOnClick"
        >
          <span v-if="!loading">Submit</span>
          <div v-else>
            <div
              class="d-flex"
              style="align-items: center"
            >
              <div class="spinner--white-small">
                <div class="loading-animation loading-container m-0">
                  <div class="shape shape1" />
                  <div class="shape shape2" />
                  <div class="shape shape3" />
                  <div class="shape shape4" />
                </div>
              </div>
              <span
                class="ms-2"
                style="font-size: 12px"
              >Please wait ...</span>
            </div>
          </div>
        </BaseButton>
        <!-- <button
                  id="submit-button"
                  class="primary-square-button"
                  :disabled="loading"
                >
                  <span v-if="!loading">Submit</span>
                  <div v-else>
                    <div
                      class="d-flex"
                      style="align-items: center"
                    >
                      <div class="spinner--white-small">
                        <div class="loading-animation loading-container m-0">
                          <div class="shape shape1" />
                          <div class="shape shape2" />
                          <div class="shape shape3" />
                          <div class="shape shape4" />
                        </div>
                      </div>
                      <span
                        class="ms-2"
                        style="font-size: 12px"
                      >Please wait ...</span>
                    </div>
                  </div>
                </button> -->
      </div>
    </form>

    <!-- <div class="mt-3">
              <span
                class="p-two"
                style="cursor: default; padding-right: 5px"
              >Already have an account ?</span>
              <Link
                id="kt_login_signup"
                href="/login"
                class="inputContainer__redirect p-two"
              >
                Log In Now !
              </Link>
            </div> -->
  </div>
</template>

<script>
import AuthenticateLayout from '@hypershapesAffiliate/layouts/AuthLayout.vue';

export default {
  layout: AuthenticateLayout,
};
</script>

<script setup>
/* eslint-disable */
import { router, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import BaseFormInput from '@shared/components/BaseFormInput.vue';
import BaseFormGroup from '@shared/components/BaseFormGroup.vue';
import BaseButton from '@shared/components/BaseButton.vue';
/* eslint-enable */

const form = useForm({
  fName: '',
  lName: '',
  email: '',
  password: '',
  terms: false,
});

const loading = ref(false);

const errors = computed(() => usePage().props.errors);

const submit = () => {
  loading.value = true;
  form.post('/register', {
    onFinish: () => {
      form.reset('password');
      loading.value = false;
    },
  });
};
</script>

<style lang="scss" scoped>
*:not(i) {
  font-size: $base-font-size;
}

.container {
  padding: 0 !important;
}
// label{
//   margin: 1rem 0;
// }

.input-label {
  font-size: 14px;
  margin-bottom: 6px;
}

.grey-font {
  color: #808080;
}
.input-style {
  // width: 95%;
  // border: none;
  // color: #4a4a4a;
  // font-size: 1rem;
  // font-weight: 400;
  // line-height: 1.5;
  // border-radius: 4px;
  // margin-bottom: 8px;
  // padding: 0.65rem 1rem;
  // height: calc(1.5em + 1.3rem + 2px);
  // background-color: rgba(235, 237, 242, 0.4);

  height: 36px;

  padding: 0 12px;
  font-size: 12px;
  width: 100%;
}

.failed {
  color: red;
}

.success {
  color: #009933;
}

.input-success {
  border: 1px solid #009933;
}

.input-danger {
  border: 1px solid #ff0000;
}
</style>

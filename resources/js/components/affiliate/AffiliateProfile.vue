<template>
  <div class="setting-page right_container_content_inner-page">
    <setting-page-header
      title="Profile Settings"
      :previous-button-u-r-l="'/dashboard'"
      previous-button-link-title="Back to Dashboard"
    ></setting-page-header>

    <setting-layout title="Personal Profile" has-action-button>
      <template #description>
        Hypershapes will use this information to contact you
      </template>

      <template #content>
        <div class="w-100" @keydown="errors.clear($event.target.name)">
          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <div class="setting-page__content-col-lg col-lg-6">
                <!-- First Name -->
                <div class="col-left-lg">
                  <label for="firstName" class="setting-page__label">
                    First Name
                  </label>
                  <input
                    type="text"
                    class="setting-page__form-input form-control"
                    id="firstName"
                    name="profileSettings.first_name"
                    v-model="profileSettings.first_name"
                    placeholder="Enter First Name"
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.first_name')"
                  v-text="'This field is required'"
                ></span>
              </div>

              <div class="col-lg-6">
                <!-- Last Name -->
                <div class="setting-page__content-col-lg col-right-lg">
                  <label for="lastName" class="setting-page__label">
                    Last Name
                  </label>
                  <input
                    type="text"
                    class="setting-page__form-input form-control"
                    id="lastName"
                    name="profileSettings.last_name"
                    placeholder="Enter Last Name"
                    v-model="profileSettings.last_name"
                    required
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.last_name')"
                  v-text="'This field is required'"
                ></span>
              </div>
            </div>
          </div>

          <!-- Email -->
          <div class="w-100">
            <div class="setting-page__content-oneline">
              <label for="address" class="setting-page__label"> Email </label>
              <input
                type="email"
                class="setting-page__form-input form-control"
                name="email"
                v-model="profileSettings.email"
                disabled="true"
              />
            </div>
          </div>

          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <div class="setting-page__content-col-lg col-lg-6">
                <!-- Address-->
                <div class="col-left-lg">
                  <label for="address" class="setting-page__label">
                    Address
                  </label>
                  <input
                    type="text"
                    class="setting-page__form-input form-control"
                    name="profileSettings.address"
                    v-model="profileSettings.address"
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.address')"
                  v-text="'This field is required'"
                ></span>
              </div>

              <div class="col-lg-6">
                <!-- Country -->
                <div class="setting-page__content-col-lg col-right-lg">
                  <label for="country" class="setting-page__label"
                    >Country</label
                  >
                  <country-select
                    class="setting-page__form-input form-control"
                    v-model="profileSettings.country"
                    :country="profileSettings.country"
                    top-country="MY"
                    :country-name="true"
                  />
                  <span
                    class="help-block error-message error-font-size"
                    role="alert"
                    v-if="errors.has('profileSettings.country')"
                    v-text="'This field is required'"
                  ></span>
                </div>
              </div>
            </div>
          </div>

          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <div class="setting-page__content-col-lg col-lg-6">
                <!-- City -->
                <div class="col-left-lg">
                  <label for="city" class="setting-page__label"> City </label>
                  <input
                    type="text"
                    class="setting-page__form-input form-control"
                    id="city"
                    name="profileSettings.city"
                    v-model="profileSettings.city"
                    placeholder="Enter city"
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.city')"
                  v-text="'This field is required'"
                ></span>
              </div>

              <div class="col-lg-6">
                <!-- State -->
                <div class="setting-page__content-col-lg col-right-lg">
                  <label for="state" class="setting-page__label">State</label>
                  <region-select
                    class="setting-page__form-input form-control"
                    v-model="profileSettings.state"
                    :country="profileSettings.country"
                    :region="profileSettings.state"
                    :region-name="true"
                    :country-name="true"
                    required
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.state')"
                  v-text="'This field is required'"
                ></span>
              </div>
            </div>
          </div>
          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <div class="setting-page__content-col-lg col-lg-6">
                <!-- Zipcode -->
                <div class="col-left-lg">
                  <label for="zipcode" class="setting-page__label">
                    Zipcode
                  </label>
                  <input
                    type="text"
                    class="setting-page__form-input form-control"
                    id="zipcode"
                    name="profileSettings.zipcode"
                    v-model="profileSettings.zipcode"
                    placeholder="Enter zipcode"
                    required
                  />
                </div>
                <span
                  class="help-block error-message error-font-size"
                  role="alert"
                  v-if="errors.has('profileSettings.zipcode')"
                  v-text="'This field is required'"
                ></span>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #button>
        <button
          :disabled="updatingProfileSetting"
          type="submit"
          class="primary-small-square-button"
          @click="updateProfileSetting"
        >
          <div class="spinner--white-small" v-if="updatingProfileSetting">
            <div class="loading-animation loading-container my-0">
              <div class="shape shape1"></div>
              <div class="shape shape2"></div>
              <div class="shape shape3"></div>
              <div class="shape shape4"></div>
            </div>
          </div>
          <span v-else class="text-light" style="font-size: 12px"> Save </span>
        </button>
      </template>
    </setting-layout>

    <div class="setting-page__dividor"></div>

    <setting-layout title="Payout" has-action-button>
      <template #description>
        Hypershapes will credit commission through this email.
      </template>
      <template #content>
        <div
          class="setting-page__content-row-lg"
          @keydown="errors.clear($event.target.name)"
        >
          <!-- Email Address -->
          <div class="w-100">
            <div class="setting-page__content-oneline">
              <label for="email" class="setting-page__label">
                Paypal Email Address
              </label>
              <input
                type="email"
                class="setting-page__form-input form-control"
                name="paypal_email"
                @input="delete errors.paypal_email"
                v-model="paypal_email"
              />
              <span
                class="help-block error-message error-font-size"
                role="alert"
                v-if="errors.has('paypal_email')"
                v-text="errors.get('paypal_email')"
              ></span>
            </div>
          </div>
        </div>
      </template>
      <template #button>
        <button
          :disabled="updatingPayoutSetting"
          type="submit"
          class="primary-small-square-button"
          @click="updatePayoutSetting"
        >
          <div class="spinner--white-small" v-if="updatingPayoutSetting">
            <div class="loading-animation loading-container my-0">
              <div class="shape shape1"></div>
              <div class="shape shape2"></div>
              <div class="shape shape3"></div>
              <div class="shape shape4"></div>
            </div>
          </div>
          <span v-else class="text-light" style="font-size: 12px"> Save </span>
        </button>
      </template>
    </setting-layout>
    <!-- Change Password  -->
    <div class="setting-page__dividor"></div>
    <setting-layout title="Change Password" has-action-button>
      <template #content>
        <div class="w-100">
          <div class="d-none">
            <div class="alert alert-success" role="alert">
              <div class="alert-text">{{ alertMessage }}</div>
              <div class="alert-close">
                <button
                  class="close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                >
                  <span aria-hidden="true">
                    <i class="la la-close"></i>
                  </span>
                </button>
              </div>
            </div>
          </div>

          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <div class="col-lg-6">
                <!-- Change Password -->
                <div class="setting-page__content-col col-left-lg">
                  <label for="newPwd" class="setting-page__label">
                    New Password
                  </label>
                  <input
                    type="password"
                    v-model="newPassword"
                    @input="delete errors.password"
                    class="setting-page__form-input form-control"
                    placeholder="Enter New Password"
                    required
                  />

                  <span
                    class="help-block error-message error-font-size"
                    role="alert"
                    v-if="errors.has('password')"
                    v-text="errors.get('password')"
                  ></span>
                </div>
              </div>

              <div class="col-lg-6">
                <!-- Confirm New Password -->
                <div
                  class="setting-page__content-col-lg col-right-lg setting-page__last-row"
                >
                  <label for="confirmPwd" class="setting-page__label">
                    Confirm New Password
                  </label>
                  <input
                    type="password"
                    v-model="newPasswordConfirmation"
                    @input="delete errors.password_confirmation"
                    class="setting-page__form-input form-control"
                    placeholder="Enter New Password"
                    required
                  />
                  <span
                    class="help-block error-message error-font-size"
                    role="alert"
                    v-if="errors.has('password_confirmation')"
                    v-text="errors.password_confirmation[0]"
                  ></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #button>
        <button
          :disabled="updatingPassword"
          type="submit"
          class="primary-small-square-button"
          @click="updatePassword"
        >
          <div class="spinner--white-small" v-if="updatingPassword">
            <div class="loading-animation loading-container my-0">
              <div class="shape shape1"></div>
              <div class="shape shape2"></div>
              <div class="shape shape3"></div>
              <div class="shape shape4"></div>
            </div>
          </div>
          <span v-else class="text-light" style="font-size: 12px"> Save </span>
        </button>
      </template>
    </setting-layout>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';
import { Errors } from '@lib/classes';

export default {
  name: 'AffiliateProfile',
  props: ['affiliateUser'],
  data() {
    return {
      profileSettings: {
        first_name: this.affiliateUser.first_name,
        last_name: this.affiliateUser.last_name,
        email: this.affiliateUser.email,
        address: this.affiliateUser.address,
        city: this.affiliateUser.city,
        zipcode: this.affiliateUser.zipcode,
        state: this.affiliateUser.state,
        country: this.affiliateUser.country,
      },
      paypal_email: this.affiliateUser.paypal_email,
      updatingProfileSetting: false,
      updatingPayoutSetting: false,
      updatingPassword: false,
      errors: new Errors(),
      oldPassword: '',
      newPassword: '',
      newPasswordConfirmation: '',
      alertMessage: '',
    };
  },

  methods: {
    updateProfileSetting() {
      this.updatingProfileSetting = true;
      axios
        .post('/profile/update', {
          profileSettings: this.profileSettings,
        })
        .then((response) => {
          this.$toast.success('Success', 'Profile update successfully');
          window.location.reload();
        })
        .catch((error) => {
          this.$toast.error('Error', 'Opps Something went wrong');
          this.errors.record(error.response.data.errors);
        })
        .finally(() => {
          this.updatingProfileSetting = false;
        });
    },
    updatePayoutSetting() {
      this.updatingPayoutSetting = true;
      axios
        .post('/payout/update', {
          paypal_email: this.paypal_email,
        })
        .then((response) => {
          this.$toast.success('Success', 'Paypal email update successfully');
        })
        .catch((error) => {
          this.$toast.error('Error', 'Opps Something went wrong');
          this.errors.record(error.response.data.errors);
        })
        .finally(() => {
          this.updatingPayoutSetting = false;
        });
    },
    updatePassword() {
      this.updatingPassword = true;
      axios
        .post('/password/update', {
          password: this.newPassword,
          password_confirmation: this.newPasswordConfirmation,
        })
        .then((response) => {
          this.$toast.success('Success', 'Password update successfully');
        })
        .catch((error) => {
          this.$toast.error('Error', 'Opps Something went wrong');
          this.errors.record(error.response.data.errors);
        })
        .finally(() => {
          this.updatingPassword = false;
        });
    },
    saveDetail() {
      console.log('detail save');
      this.edit_profile_modal = new Modal(
        document.getElementById('edit-profile-modal')
      );
      this.edit_profile_modal.hide();
      axios
        .post('/update-profile', {
          formValues: this.formValues,
        })
        .then((response) => {
          // console.log(response);

          this.$toast.success('Success', 'Profile update successfully');
          setTimeout(function () {
            window.location.reload();
          }, 600);
        })
        .catch((error) => {
          // console.log(error);
          this.$toast.error('Error', 'Opps Something went wrong');
        });
    },

    checkDetail() {
      if (
        this.affiliate_user.paypal_email == null ||
        this.affiliate_user.paypal_email == ''
      ) {
        // console.log("abc")
        Swal.fire({
          title: 'Info Required',
          text: 'Please update your profile detail',
          type: 'warning',
        }).then((response) => {
          this.edit_profile_modal.show();
        });
      }
    },
  },
  mounted() {
    // this.edit_profile_modal = new Modal(
    //   document.getElementById('edit-profile-modal')
    // );
  },
};
</script>

<style lang="scss" scoped>

input[type='email']:disabled {
  background: #ccc;
}

.paypal-email {
  text-align: center;
  border: 1px solid lightgrey;
  border-radius: 1rem;
  padding: 0.5rem;
  pointer-events: none;
}

.affiliate-name {
  font-weight: bold;
  font-size: 1.5rem;
}

.paypal-logo {
  max-height: 45px;
  max-width: 125px;
  width: 100%;
}

.profile-img {
  max-width: 150px;
  max-height: 150px;
  width: 100%;
  padding-right: 10px;
}
</style>

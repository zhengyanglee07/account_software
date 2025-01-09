<template>
  <BasePageLayout
    page-name="Account Settings"
    back-to="/settings/all"
    is-setting
  >
    <!-- general info  -->
    <BaseSettingLayout title="Timezone">
      <template #description>
        View and update your account timezone.
      </template>

      <template #content>
        <AccountGeneralInfo
          :account-name="account.accountName"
          :currency="account.currency"
          :timezone="account.timeZone"
        />
      </template>

      <template #footer />
    </BaseSettingLayout>
    <!-- end general info  -->

    <!-- company profile  -->

    <BaseSettingLayout title="Company Profile">
      <template #description>
        View and update your company details.
      </template>

      <template #content>
        <div class="w-100">
          <div
            class="row w-100"
            style="height: 300px"
          >
            <div class="col-lg-6">
              <!-- Company Logo -->
              <BaseFormGroup
                style="height: 100%"
                label="Company Logo"
              >
                <BaseImagePreview
                  style="height: 100%"
                  size="lg"
                  :image-u-r-l="
                    accountDetails.company_logo ||
                      'https://media.hypershapes.com/images/hypershapes-favicon.png'
                  "
                  @delete="removeCompanyLogo"
                  @click="sectionType = 'companyLogo'"
                />
              </BaseFormGroup>
            </div>
            <div class="col-lg-6">
              <!-- Favicon -->
              <BaseFormGroup
                label="Favicon"
                style="height: 100%"
              >
                <BaseImagePreview
                  style="height: 100%"
                  size="lg"
                  :image-u-r-l="
                    accountDetails.favicon ||
                      'https://media.hypershapes.com/images/hypershapes-favicon.png'
                  "
                  @delete="removeFavicon"
                  @click="sectionType = 'favicon'"
                />
                <span
                  id="favicon"
                  class="error-message error-font-size"
                />
              </BaseFormGroup>
            </div>

            <!-- Empty Div for styling -->
            <!-- <div class="setting-page__content-row col-sm"></div> -->
          </div>

          <!-- Store Name -->
          <BaseFormGroup
            label="Store Name"
            label-for="storeName"
          >
            <BaseFormInput
              id="storeName"
              v-model="accountDetails.store_name"
              type="text"
              placeholder="Enter Store Name"
            />
            <span class="error-message error-font-size">{{
              errorsMsg.store_name
            }}</span>
          </BaseFormGroup>

          <!-- Company Name -->
          <BaseFormGroup
            label="Company Name"
            label-for="companyName"
          >
            <BaseFormInput
              id="companyName"
              v-model="accountDetails.company"
              type="text"
              placeholder="Enter Company Name"
            />
            <span class="error-message error-font-size">{{
              errorsMsg.company
            }}</span>
          </BaseFormGroup>

          <!-- Full Address -->
          <BaseFormGroup
            label="Full Address"
            label-for="fullAddress"
          >
            <BaseFormInput
              id="fullAddress"
              v-model="accountDetails.address"
              type="text"
              placeholder="Enter Address"
            />
            <span class="error-message error-font-size">{{
              errorsMsg.address
            }}</span>
          </BaseFormGroup>

          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <!-- Country -->
              <div class="col-lg-6">
                <BaseFormGroup label="Country">
                  <BaseFormCountrySelect
                    v-model="accountDetails.country"
                    :country="accountDetails.country"
                  />
                </BaseFormGroup>
              </div>

              <!-- State -->
              <div class="col-lg-6">
                <BaseFormGroup label="State">
                  <BaseFormRegionSelect
                    v-model="accountDetails.state"
                    :country="accountDetails.country"
                    :region="accountDetails.state"
                  />
                  <span
                    id="errState"
                    class="error-message error-font-size"
                  >{{
                    errorsMsg.state
                  }}</span>
                </BaseFormGroup>
              </div>
            </div>
          </div>

          <div class="setting-page__content-row-lg">
            <div class="row w-100">
              <!-- City -->
              <div class="col-lg-6">
                <BaseFormGroup
                  label="City"
                  label-for="city"
                >
                  <BaseFormInput
                    id="city"
                    v-model="accountDetails.city"
                    type="text"
                    placeholder="Enter City"
                  />
                  <span
                    id="errCity"
                    class="error-message error-font-size"
                  >{{
                    errorsMsg.city
                  }}</span>
                </BaseFormGroup>
              </div>

              <!-- ZIP Code -->
              <div class="col-lg-6">
                <BaseFormGroup
                  label="Zip Code"
                  label-for="zipCode"
                >
                  <BaseFormInput
                    id="zipCode"
                    v-model="accountDetails.zip"
                    type="number"
                    placeholder="Enter ZIP Code"
                  />
                  <span class="error-message error-font-size">{{
                    errorsMsg.zip
                  }}</span>
                </BaseFormGroup>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <BaseButton @click="saveCompanyProfile">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
    <!-- end company profile  -->
    <ImageUploader @update-value="chooseImage" />
  </BasePageLayout>
</template>

<script>
import AccountGeneralInfo from '@setting/components/AccountGeneralInfo.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';
import cloneDeep from 'lodash/cloneDeep';
import eventBus from '@services/eventBus.js';

export default {
  name: 'AccountSetting',

  components: {
    AccountGeneralInfo,
    ImageUploader,
  },

  props: {
    account: {
      type: Object,
      default: () => {},
    },
  },

  data() {
    return {
      sectionType: 'companyLogo',
      accountDetails: {},
      errorsMsg: {
        address: '',
        city: '',
        store_name: '',
        company: '',
        state: '',
        zip: '',
      },
    };
  },

  mounted() {
    this.accountDetails = cloneDeep(this.account);
    eventBus.$on('addCompanyLogoEvent', (image) => {
      this.accountDetails.company_logo = image;
    });
    eventBus.$on('addFaviconEvent', (image) => {
      this.accountDetails.favicon = image;
    });
  },

  methods: {
    chooseImage(e) {
      if (this.sectionType === 'companyLogo')
        eventBus.$emit('addCompanyLogoEvent', e);
      else eventBus.$emit('addFaviconEvent', e);
    },
    saveCompanyProfile() {
      let isError = false;
      Object.keys(this.accountDetails).forEach((key) => {
        const isEmpty = this.accountDetails[key] === '';
        if (isError === false) isError = isEmpty;
        this.errorsMsg[key] = isEmpty ? 'Please fill in the blanks' : '';
      });
      if (isError || document.getElementById('favicon').innerHTML !== '')
        return;
      this.$axios
        .put('/account/setting/company/update', {
          companyLogo: this.accountDetails.company_logo,
          favicon: this.accountDetails.favicon,
          storeName: this.accountDetails.store_name,
          companyName: this.accountDetails.company,
          companyAddress: this.accountDetails.address,
          country: this.accountDetails.country,
          city: this.accountDetails.city,
          state: this.accountDetails.state,
          zip: this.accountDetails.zip,
        })
        .then((response) => {
          this.$toast.success(
            'Success',
            'Successfully updated company profiles'
          );
        })
        .catch((error) => {
          throw new Error(error);
        });
    },

    removeCompanyLogo() {
      this.accountDetails.company_logo =
        'https://media.hypershapes.com/images/hypershapes-favicon.png';
    },

    removeFavicon() {
      this.accountDetails.favicon =
        'https://media.hypershapes.com/images/hypershapes-favicon.png';
    },
    changeRegion(value) {
      if (value !== '') {
        this.accountDetails.state = value;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.setting-page {
  &__section-subtitle {
    margin-bottom: 10px;
  }
}

.image-uploader {
  width: 100%;
  height: 150px;

  &__img {
    width: 100%;
    height: 100%;

    &::hover {
      cursor: pointer;
    }
  }

  &::hover {
    cursor: pointer;
  }
}
</style>

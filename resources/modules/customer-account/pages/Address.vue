<template>
  <EcommerceAccountLayout>
    <template #content>
      <p class="customer-account-onlineStore-p-two mb-10">
        The following addresses will be used on the checkout page by default.
      </p>
      <div class="row w-100">
        <div
          v-for="(type, index) in addressType"
          :key="index"
          class="col-md-6"
        >
          <BaseCard
            has-header
            has-toolbar
            no-body-margin
            :title="type + ' address'"
          >
            <div v-if="savedForm?.[`${type}_address`]">
              <div
                v-for="(field, index) in addressFields"
                :key="index"
              >
                <p
                  v-if="savedForm?.[`${type}_${field}`]"
                  class="mb-0 customer-account-onlineStore-p-two"
                >
                  {{ savedForm?.[`${type}_${field}`] }}
                </p>
              </div>
            </div>

            <p
              v-if="savedForm?.[`${type}_address`] == null"
              class="null-msg customer-account-onlineStore-p-two"
              style="margin-top: 20px"
            >
              You have not set up this type of address yet
            </p>
            <template #toolbar>
              <BaseButton
                type="theme-primary"
                :has-add-icon="savedForm?.[`${type}_address`] == null"
                :has-edit-icon="savedForm?.[`${type}_address`]"
                @click="showAddress(type)"
              >
                {{ savedForm?.[`${type}_address`] == null ? 'Add' : 'Edit' }}
              </BaseButton>
            </template>
          </BaseCard>
          <!-- <span
            class="
              setting-page__section-title
              text-capitalize
              customer-account-onlineStore-h-five
            "
            >{{ `${type} address` }}
            <a class="cleanButton h_link p-0" @click="showAddress(type)">
              <i
                class="fas fa-edit"
                style="margin-left: 5px; font-size: 18px"
              ></i>
            </a>
            <p
              class="customer-account-onlineStore-p-two mb-0"
              style="color: #808285; padding-top: 10px"
            >
              Default
            </p>
          </span> -->
        </div>
      </div>
      <br>
      <div
        v-for="(type, index) in addressType"
        :key="index"
        class="setting-page"
      >
        <div v-if="selectedType === type">
          <BaseCard
            has-header
            no-body-margin
            has-footer
            :title="`${
              savedForm?.[type + '_address'] ? 'Edit' : 'Add'
            } ${type} Address`"
          >
            <BaseFormGroup
              v-if="false"
              label="Name"
              required
            >
              <BaseFormInput
                :id="`${type}-full-name`"
                v-model="form[`${type}_name`]"
                type="text"
                placeholder="Enter Name"
                required
              />
            </BaseFormGroup>
            <BaseFormGroup
              v-if="type === 'shipping'"
              label="Phone Number"
              required
            >
              <BaseFormTelInput
                v-model="form[type + '_phoneNumber']"
                placeholder="Enter Your Phone Number"
              />
            </BaseFormGroup>
            <BaseFormGroup
              label="Full Address"
              required
            >
              <BaseFormInput
                :id="`${type}-full-address`"
                v-model="form[type + '_address']"
                max="255"
                :name="`${type}-address`"
                type="text"
                placeholder="Enter Full Address"
                required
              />
            </BaseFormGroup>
            <BaseFormGroup
              label="City"
              col="lg-6"
              required
            >
              <BaseFormInput
                :id="`${type}-city`"
                v-model="form[type + '_city']"
                placeholder="Enter City"
                :name="`${type}-city`"
                type="text"
                required
                maxlength="45"
              />
            </BaseFormGroup>

            <BaseFormGroup
              label="Country"
              col="lg-6"
              required
            >
              <BaseFormCountrySelect
                v-model="form[type + '_country']"
                :country="form[type + '_country']"
              />
            </BaseFormGroup>

            <BaseFormGroup
              label="State"
              col="lg-6"
            >
              <BaseFormRegionSelect
                v-model="form[type + '_state']"
                :region="form[type + '_state']"
                :country="form[type + '_country']"
              />
            </BaseFormGroup>

            <BaseFormGroup
              label="Zip/Postal code"
              required
              col="lg-6"
            >
              <BaseFormInput
                :id="`${type}-zip`"
                v-model="form[type + '_zipcode']"
                placeholder="Enter Zip/Postal code"
                :name="`${type}-zip-code`"
                type="text"
                required
                maxlength="45"
              />
            </BaseFormGroup>
            <div class="w-100 d-flex justify-content-end mt-3">
              <BaseButton
                type="theme-secondary"
                class="me-2"
                @click="selectedType = ''"
              >
                Cancel
              </BaseButton>
              <BaseButton
                type="theme-primary"
                :disabled="isSaving"
                @click="saveAddress"
              >
                Save
              </BaseButton>
            </div>
          </BaseCard>
        </div>
      </div>
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import EcommerceAccountLayout from '@customerAccount/layout/EcommerceAccountLayout.vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';
import axios from 'axios';
import countryRegionOptions from '@onlineStore/assets/js/country-region-options.js';
import { required } from '@vuelidate/validators';
import { useVuelidate } from '@vuelidate/core';

export default {
  name: 'Address',
  components: {
    EcommerceAccountLayout,
  },
  layout: PublishLayout,
  props: {
    addressBook: Object,
    pageName: String,
  },

  setup() {
    return { v$: useVuelidate() };
  },

  validations() {
    if (this.selectedType === 'shipping') {
      return {
        form: {
          shipping_address: { required },
          shipping_city: { required },
          shipping_country: { required },
          shipping_name: { required },
          shipping_state: { required },
          shipping_zipcode: { required },
          shipping_phoneNumber: { required },
        },
      };
    }
    return {
      form: {
        billing_address: { required },
        billing_city: { required },
        billing_country: { required },
        billing_name: { required },
        billing_state: { required },
        billing_zipcode: { required },
      },
    };
  },

  data() {
    return {
      showError: false,
      addressType: ['shipping', 'billing'],
      selectedType: '',
      form: {},
      savedForm: {},
      dialCode: '',
      isSaving: false,
      addressFields: [
        'name',
        'phoneNumber',
        'address',
        'city',
        'country',
        'state',
        'zipcode',
      ],
    };
  },

  computed: {
    countryRegionOption() {
      return countryRegionOptions;
    },
  },

  watch: {
    selectedType(val, prev) {
      if (prev) {
        this.addressFields.forEach((field) => {
          if (field !== 'name')
            this.form[`${prev}_${field}`] =
              this.addressBook[`${prev}_${field}`];
        });
      }
    },
  },

  mounted() {
    this.savedForm = { ...this.addressBook };
    this.form = { ...this.addressBook };
  },

  methods: {
    showAddress(addressType) {
      this.selectedType = addressType;
      this.showError = false;
    },
    getRegion(addressType) {
      if (!this.form[`${addressType}_country`]) return [];
      return this.countryRegionOption.find(
        (c) => c.c === this.form[`${addressType}_country`]
      )?.r;
    },
    saveAddress() {
      this.showError = false;
      // if (this.v$.$invalid) {
      //   this.showError = true;
      //   return;
      // }
      this.isSaving = true;
      axios
        .post('/save/address', {
          form: this.form,
          id: this.addressBook.id,
        })
        .then((response) => {
          if (response.data === 'success') {
            this.savedForm = { ...this.addressBook, ...this.form };
            this.selectedType = '';
            this.$toast.success('Success', 'Successfully saved address');
          } else {
            this.$toast.error('Error', 'Fail to saved address');
          }
        })
        .finally(() => {
          this.isSaving = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
:deep(h2) {
  text-transform: capitalize;
}

:deep(.card-header) {
  margin-left: 0 !important;
}
</style>

<template>
  <EcommerceAccountLayout>
    <template #content>
      <div v-if="false">
        <i class="fas fa-spinner fa-spin fa-2x mt-5 me-2" />
        Loading ...
      </div>
      <div class="card-container">
        <div class="customer-account-onlineStore-h-five">
          Account Details
        </div>

        <div class="dashboard-content">
          <div class="row w-100">
            <div class="col-md-7 customer">
              <div
                class="card-item"
                style="padding: 0"
              >
                <div class="customer-info-container">
                  <div class="customer-info">
                    <div class="col-10">
                      <p
                        class="customer-account-onlineStore-p-two"
                        style="margin-bottom: 5px"
                      >
                        {{ customerInfo?.fname || 'Full Name' }}
                      </p>
                      <p
                        class="customer-account-onlineStore-p-two"
                        style="margin-bottom: 5px"
                      >
                        {{ customerInfo?.email || 'Email' }}
                      </p>
                      <p
                        class="customer-account-onlineStore-p-two"
                        style="margin-bottom: 5px"
                      >
                        {{ customerInfo?.phone_number || 'Phone Number' }}
                      </p>
                    </div>
                    <div
                      class="col-2"
                      style="text-align: end"
                    >
                      <Link
                        v-if="false"
                        href="/account/settings"
                        class="edit-button customer-account-onlineStore-p-two"
                      >
                        Edit
                      </Link>
                    </div>
                  </div>
                </div>

                <div
                  v-if="false"
                  class="row"
                >
                  <div class="customer-additional-info">
                    <div
                      class="card-item"
                      style="margin-right: 20px; justify-content: flex-end"
                    >
                      <span
                        class="customer-account-onlineStore-p-two widget-content"
                      >Total Spend ({{ prefix }})</span>
                      <p
                        class="mb-0 text-center customer-account-onlineStore-h-three"
                        style="font-weight: 900 !important"
                      >
                        <!-- {{ specialCurrencyCalculation(totalOrder) }} -->
                      </p>
                    </div>
                    <div
                      class="card-item"
                      style="margin-right: 20px; justify-content: flex-end"
                    >
                      <span
                        class="customer-account-onlineStore-p-two widget-content"
                      >Total Purchased</span>
                      <p
                        class="mb-0 text-center customer-account-onlineStore-h-three"
                        style="font-weight: 900 !important"
                      >
                        {{ customerInfo?.orders.length }}
                      </p>
                    </div>
                    <div
                      class="card-item"
                      style="justify-content: flex-end"
                    >
                      <span
                        class="customer-account-onlineStore-p-two widget-content"
                      >Store Credit Balance ({{ prefix }})</span>
                      <p
                        class="mb-0 text-center customer-account-onlineStore-h-three"
                        style="font-weight: 900 !important"
                      >
                        <!-- {{
                          specialCurrencyCalculation(
                            customerInfo?.credit_balance
                          )
                        }} -->
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="false"
              class="col-md-5"
            >
              <div class="card-item">
                <div
                  v-for="(type, index) in addressType"
                  :key="index"
                  class="address-container"
                >
                  <div class="address-title mb-3">
                    <div
                      class="col-10 customer-account-onlineStore-h-five text-capitalize"
                    >
                      {{ `${type} address` }}
                    </div>
                    <div
                      class="col-2"
                      style="text-align: end"
                    >
                      <Link
                        href="/address/book"
                        class="edit-button customer-account-onlineStore-p-two"
                      >
                        Edit
                      </Link>
                    </div>
                  </div>
                  <div v-if="addressBook?.[`${type}_address`] !== null">
                    <p class="mb-0 customer-account-onlineStore-p-two">
                      {{ addressBook?.[`${type}_name`] }}
                    </p>
                    <p class="mb-0 customer-account-onlineStore-p-two">
                      {{ addressBook?.[`${type}_phoneNumber`] }}
                    </p>
                    <p class="mb-0 customer-account-onlineStore-p-two">
                      {{ addressBook?.[`${type}_address`] }}
                    </p>
                    <p
                      v-if="
                        addressBook?.[`${type}_city`] !== null ||
                          addressBook?.[`${type}_zipcode`] !== null
                      "
                      class="mb-0 customer-account-onlineStore-p-two"
                    >
                      {{ addressBook?.[`${type}_city`] }} ,{{
                        addressBook?.[`${type}_zipcode`]
                      }}
                    </p>
                    <p
                      v-if="
                        addressBook?.[`${type}_state`] !== null ||
                          addressBook?.[`${type}_country`] !== null
                      "
                      class="mb-0 customer-account-onlineStore-p-two"
                    >
                      {{ addressBook?.[`${type}_state`] }} ,{{
                        addressBook?.[`${type}_country`]
                      }}
                    </p>
                  </div>
                  <div v-else>
                    <p class="customer-account-onlineStore-p-two">
                      You have not set up this type of address yet
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import axios from 'axios';
// import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import EcommerceAccountLayout from '@customerAccount/layout/EcommerceAccountLayout.vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';

export default {
  name: 'OnlineStoreDashboard',
  components: {
    EcommerceAccountLayout,
  },
  // mixins: [specialCurrencyCalculationMixin],
  layout: PublishLayout,
  props: {
    customerInfo: Object,
    totalOrder: Array,
    prefix: String,
    pageName: String,
  },
  data() {
    return {
      addressType: ['shipping', 'billing'],
      // customerInfo: null,
      // totalOrder: null,
      // prefix: null,
      // isLoading: true,
    };
  },
  computed: {
    addressBook() {
      return this.customerInfo?.addressBook;
    },
  },
  // async mounted() {
  //   await axios
  //     .get(`/dashboard/${window.location.host}`)
  //     .then(({ data }) => {
  //       console.log(data);
  //       this.customerInfo = data.customerInfo;
  //       this.totalOrder = data.totalOrder;
  //       this.prefix = data.prefix;
  //       this.pageName = data.pageName;
  //       this.isLoading = false;
  //     })
  //     .catch((error) => {
  //       console.log(error);
  //     });
  // },
};
</script>

<style lang="scss" scoped>
.dashboard-content {
  padding-top: 20px;
  display: flex;
}

.card-container {
  padding: 20px 40px 20px 40px;
}

.card-item {
  display: flex;
  flex-direction: column;
  padding: 20px;
  width: 100%;
  height: auto;
  border: 1px solid #ced4da;
  border-radius: 5px;
  background-color: #fff;

  @media (max-width: $md-display) {
    width: 100%;
  }
}

.customer-additional-info {
  display: flex;
  flex-direction: row;
  padding: 20px;
  overflow-x: auto;

  @media (max-width: $md-display) {
    flex-direction: column;
  }

  .card-item {
    @media (max-width: $md-display) {
      margin-bottom: 20px;

      &:last-child {
        margin-bottom: 0;
      }
    }
  }
}

.customer-info-container {
  // border-bottom: 1px solid #ced4da;
}

.customer-info {
  padding: 20px;
  display: flex;
}

.widget-content {
  margin-bottom: 10px;
  text-align: center;
}

.customer {
  padding-right: 20px !important;

  @media (max-width: $md-display) {
    padding-right: 0 !important;
    margin-bottom: 20px;
  }
}

.edit-button {
  text-decoration: none;
  color: $h-secondary-color;
}

.address-container {
  border-bottom: 1px solid #ced4da;
  padding-bottom: 20px;

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
    padding-top: 20px;
  }
}

.address-title {
  display: flex;
}
</style>

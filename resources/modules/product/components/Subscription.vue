<template>
  <div class="product-section-border pb-0 mb-0">
    <div class="product-configure-container__section row">
      <div class="col-md-6 pe-1">
        <div class="row px-0">
          <p class="section-title">
            Display Name
            <span class="error-message">*</span>
            <button
              v-if="subscription.id === null"
              class="h_link delete-customization"
              @click="deleteSubscriptionOption(index)"
            >
              Delete Subscription
            </button>
          </p>
        </div>
        <input
          class="input-content form-control"
          type="text"
          :value="subscription.display_name"
          @input="
            inputSubscriptionOption({
              index,
              type: 'display_name',
              value: $event.target.value,
            })
          "
        >
        <p
          class="p-0 m-0 p-three"
          style="color: #b8b8b8"
        >
          Visible to customers on storefront
        </p>
        <p
          v-if="
            !$v.subscription.display_name.required && subscription.errorMessage
          "
          class="error-message"
        >
          Display name is required
        </p>
      </div>
      <div class="col-md-6 ps-1">
        <div class="row px-0">
          <p class="section-title">
            Description
          </p>
        </div>
        <input
          class="input-content"
          type="text"
          :value="subscription.description"
          @input="
            inputSubscriptionOption({
              index,
              type: 'description',
              value: $event.target.value,
            })
          "
        >
        <p
          class="p-0 m-0 p-three"
          style="color: #b8b8b8"
        >
          Visible to customers on storefront
        </p>
      </div>
    </div>

    <div class="product-configure-container__section row">
      <div class="col-md-6 pe-1">
        <div class="row px-0">
          <p class="section-title">
            Discount Type
          </p>
        </div>
        <select
          class="form-control"
          aria-label="Default select example"
          :value="subscription.type"
          @input="
            inputSubscriptionOption({
              index,
              type: 'type',
              value: $event.target.value,
            })
          "
        >
          <option value="none">
            None
          </option>
          <option value="percentage discount">
            Percentage discount
          </option>
          <option value="fixed product price">
            Fixed product price
          </option>
        </select>
      </div>

      <div
        v-if="subscription.type === 'fixed product price'"
        class="col-md-6 ps-1"
      >
        <div class="row px-0">
          <p class="section-title">
            Amount
            <span class="error-message">*</span>
          </p>
        </div>
        <div class="form-group-preppend">
          <div class="input-group">
            <div class="input-group-preppend">
              <span
                id="inputGroup-sizing-default"
                class="input-group-text h-100"
              >{{ filteredCurrency }}</span>
            </div>
            <input
              class="input-content price"
              type="number"
              :value="subscription.amount"
              @input="
                inputSubscriptionOption({
                  index,
                  type: 'amount',
                  value: $event.target.value,
                })
              "
            >
          </div>
        </div>
        <p
          v-if="!$v.subscription.amount.required && subscription.errorMessage"
          class="error-message"
        >
          Amount is required
        </p>
      </div>
    </div>

    <div class="product-configure-container__section row">
      <div
        v-if="subscription.type === 'percentage discount'"
        class="col-md-6 pe-1"
      >
        <div class="row px-0">
          <p class="section-title">
            Rate
          </p>
        </div>
        <div class="form-group-append">
          <div class="input-group">
            <input
              class="input-content"
              min="0"
              max="100"
              oninput="validity.valid || (value='');"
              type="number"
              :value="subscription.discount_rate"
              @input="
                inputSubscriptionOption({
                  index,
                  type: 'discount_rate',
                  value: $event.target.value,
                })
              "
            >
            <div class="input-group-append">
              <span
                id="inputGroup-sizing-default"
                class="input-group-text h-100"
              >%</span>
            </div>
          </div>
        </div>
        <p
          v-if="
            !$v.subscription.discount_rate.required && subscription.errorMessage
          "
          class="error-message"
        >
          Rate is required
        </p>
      </div>
      <div
        v-if="subscription.type === 'percentage discount'"
        class="col-md-6 ps-1"
      >
        <div class="row px-0">
          <p class="section-title">
            Capped at
          </p>
        </div>
        <div class="form-group-prepend">
          <div class="input-group">
            <div class="input-group-preppend">
              <span
                id="inputGroup-sizing-default"
                class="input-group-text h-100"
              >{{ filteredCurrency }}</span>
            </div>
            <input
              class="input-content price"
              type="number"
              :value="subscription.capped_at"
              @input="
                inputSubscriptionOption({
                  index,
                  type: 'capped_at',
                  value: specialCurrencyCalculation(
                    currency,
                    $event.target.value
                  ),
                })
              "
            >
          </div>
        </div>
        <!-- <p
                class="error-message"
                v-if="!$v.subscription.capped_at.required && subscription.errorMessage"
            >
                Capped at is required
            </p> -->
      </div>
    </div>

    <div class="product-configure-container__section row">
      <div class="col-md-6 pe-1">
        <div class="row px-0">
          <p class="section-title">
            Charge every
            <span class="error-message">*</span>
          </p>
        </div>
        <div class="row">
          <div class="col-md-9">
            <input
              class="input-content form-control input-left"
              type="number"
              oninput="validity.valid || (value='');"
              :value="subscription.interval_count"
              @input="
                inputSubscriptionOption({
                  index,
                  type: 'interval_count',
                  value: $event.target.value,
                })
              "
            >
          </div>
          <div class="col-md-3">
            <select
              id="inputGroupSelect01"
              class="form-control input-right h-100"
              :value="subscription.interval"
              @change="
                inputSubscriptionOption({
                  index,
                  type: 'interval',
                  value: $event.target.value,
                })
              "
            >
              <option value="day">
                Day
              </option>
              <option value="week">
                Week
              </option>
              <option value="month">
                Month
              </option>
            </select>
          </div>
        </div>
        <p
          v-if="
            !$v.subscription.interval.required ||
              (!$v.subscription.interval_count.required &&
                subscription.errorMessage)
          "
          class="error-message"
        >
          Charge every is required
        </p>
      </div>
    </div>

    <div class="product-configure-container__section row">
      <div class="col-md-6 pe-1">
        <p class="section-title">
          Expiration
        </p>
        <select
          class="input-content w-100"
          style="height: 36px"
          :value="subscription.expiration"
          @change="
            inputSubscriptionOption({
              index,
              type: 'expiration',
              value: $event.target.value,
            })
          "
        >
          <option value="never_expire">
            Never Expire
          </option>
          <option value="expire_after">
            Expire After
          </option>
        </select>
      </div>
      <div
        v-if="subscription.expiration === 'expire_after'"
        class="col-md-6 ps-1"
      >
        <div class="row px-0">
          <p class="section-title">
            Expire After
            <span class="error-message">*</span>
          </p>
        </div>
        <div class="form-group-append">
          <div class="input-group">
            <input
              class="input-content"
              type="number"
              :value="subscription.expiration_cycle"
              @input="
                inputSubscriptionOption({
                  index,
                  type: 'expiration_cycle',
                  value: $event.target.value,
                })
              "
            >
            <div class="input-group-append">
              <span
                id="inputGroup-sizing-default"
                class="input-group-text h-100 ps-2"
                style="border-left: 1px solid #ced4da"
              >Cycle</span>
            </div>
          </div>
        </div>
        <p
          v-if="
            !$v.subscription.expiration_cycle.required &&
              subscription.errorMessage
          "
          class="error-message"
        >
          Expire After is required
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from '@vuelidate/validators';
import { mapState, mapMutations } from 'vuex';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  name: 'Subscription',
  mixins: [specialCurrencyCalculationMixin],
  props: {
    subscription: {
      type: Object,
    },
    index: Number,
    currency: String,
  },
  computed: {
    filteredCurrency() {
      return this.currency === 'MYR' ? 'RM' : this.currency;
    },
  },
  validations: {
    subscription: {
      display_name: {
        required,
      },
      discount_rate: {
        required,
      },
      amount: {
        required,
      },
      interval_count: {
        required,
      },
      interval: {
        required,
      },
      expiration_cycle: {
        required,
      },
    },
  },
  methods: {
    ...mapMutations('product', [
      'inputSubscriptionOption',
      'deleteSubscriptionOption',
    ]),
  },
  mounted() {
    eventBus.$on('checkError', () => {
      this.$store.commit('product/checkSubscriptionError', this.$v.$invalid);
    });
  },
};
</script>

<style scoped lang="scss">

input {
  color: $base-font-color;
  font-family: $base-font-family;
  border-color: #ced4da;
  padding: 0 12px;
  height: 36px;
  &::placeholder {
    color: #ced4da;
  }
}

textarea {
  color: $base-font-color;
  font-family: $base-font-family;
  border-color: #ced4da;
  padding: 6px 12px;
  &::placeholder {
    color: #ced4da;
  }
}

.input-group {
  flex-wrap: unset;
}

.input-group-text {
  background: white !important;
  border: 1.5px solid #ced4da;
  border-right: 0px solid white;
  border-radius: 3px;
  padding: 0 12px;
}

#weight.input-group-text {
  background: white !important;
  border: 1.5px solid #ced4da;
  border-left: 0px solid white;
  border-radius: 3px;
  padding: 0 12px;
}

.input-content {
  width: 100%; //color: black;
  border-radius: 3px; //padding: 6px 10px;
  //border: 1.5px solid lightgrey;
  //new design
  border: 1.5px solid #ced4da;
  font-size: $base-font-size;
  padding-left: 8px;
  &::placeholder {
    color: #ced4da;
  }
}

.section-title {
  text-transform: capitalize;
}

input:focus {
  outline: none;
}

textarea:focus {
  outline: none;
}

::placeholder {
  font-size: 1rem;
  color: lightslategray;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.table {
  cursor: default;
}

tr {
  border-bottom: 1px solid #e0e0e0;
}

th {
  &:first-child {
    width: 10%;
  }
  width: 18%;
}

tr:last-child {
  border-bottom: none;
}

td,
th {
  padding: 1rem;
}

.table th:nth-child(6),
.table td:nth-child(6) {
  text-align: right;
  padding-right: 1.4rem;
}

/* Firefox */

// input[type='number'] {
//   -moz-appearance: textfield;
// }

.checkbox-oneline {
  margin-top: 2rem;
  @media (max-width: $md-display) {
    margin-top: 10px;
  }
}

.delete-customization {
  color: #ff0000;
  pointer-events: all;
  opacity: 1;
  float: right;
}

.default-title {
  text-align: center;
}

// .price-title {
//   @media (max-width: 426px) {
//     position: absolute !important;
//     left: -165px;
//     top: 70px;
//   }
//   @media (max-width: 378px) {
//     position: absolute !important;
//     top: 70px;
//     left: -145px;
//   }
//   @media (max-width: 321px) {
//     position: absolute !important;
//     top: 70px;
//     left: -118px;
//   }
// }

.product-configure-container__section {
  margin-bottom: 20px;
}

.input-left {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.input-right {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  margin-left: -1px;
}
</style>

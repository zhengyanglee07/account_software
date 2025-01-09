<template>
  <VueJqModal :modal-id="modalId">
    <template #title>
      Conversion Price Table
    </template>
    <template #body>
      <div class="d-flex w-100">
        <div
          v-for="(price, index) in prices"
          :key="index"
          class="d-flex mb-2 w-50"
          style="flex-direction: column; margin-right: 20px"
        >
          <p class="p-0 font-text align-center me-3 p-two">
            {{ inputLabel[index] }}
          </p>
          <input
            type="number"
            class="currency-input font-text mb-0 w-100"
            step="0.01"
            min="0.00"
            :value="price"
            @input="inputPrice(inputLabel[index], $event.target.value)"
          >
        </div>
      </div>
      <div
        v-if="currencies.length > 0"
        class="mb-3"
      >
        <div class="card card-conatiner">
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th
                    v-for="(tableHeader, index) in tableHeaders"
                    :key="index"
                    class="table-header h-five"
                    scope="col"
                  >
                    {{ tableHeader }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(currency, index) in currencies"
                  :key="index"
                  class="text-center"
                >
                  <td
                    class="currency-padding p-two"
                    scope="row"
                  >
                    {{ currency.currency }}
                  </td>
                  <td
                    class="currency-padding p-two"
                    scope="row"
                  >
                    {{ currency.exchangeRate }}
                  </td>
                  <td
                    class="currency-padding p-two"
                    scope="row"
                  >
                    {{
                      (
                        parseFloat(prices[0]) * currency.exchangeRate || 0
                      ).toFixed(2)
                    }}
                  </td>
                  <td
                    class="currency-padding p-two"
                    scope="row"
                  >
                    {{
                      (
                        parseFloat(prices[1]) * currency.exchangeRate || 0
                      ).toFixed(2)
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <button
        type="button"
        class="cancel-button p-two"
        data-bs-dismiss="modal"
      >
        Close
      </button>
    </template>
  </VueJqModal>
</template>

<script>
import VueJqModal from '@shared/components/VueJqModal.vue';

export default {
  name: 'ProductPricingModal',
  components: { VueJqModal },
  props: ['productPrice', 'modalId', 'objKey', 'accountId'],
  data() {
    return {
      currencies: [],
    };
  },
  computed: {
    tableHeaders() {
      return [
        'Currency',
        'Convert Rate',
        'Converted Selling Price',
        'Converted Original Price',
      ];
    },
    inputLabel() {
      return ['Selling Price', 'Original Price'];
    },
    prices() {
      const prices = [0, 0];
      this.productPrice.forEach((productPrice, index) => {
        prices[index] = productPrice;
      });
      return prices;
    },
  },
  mounted() {
    this.initializeCurrency();
  },
  methods: {
    initializeCurrency() {
      axios
        .post('/get/all/currency', {
          accountId: this.accountId,
        })
        .then((response) => {
          this.currencies = response.data.filter(
            (currency) => currency.isDefault === '0'
          );
        });
    },
    inputPrice(type, value = 0) {
      eventBus.$emit('inputProductPrice', {
        type,
        value,
        objKey: this.objKey,
      });
    },
  },
};
</script>

<style scoped>
/* style of currency */

.modal-body {
  overflow-x: auto !important;
  min-height: 350px !important;
}

.text-center {
  text-align: left !important;
}

.currency-padding {
  padding: 20px 25px 20px 0;
}

.mb-2 {
  margin-bottom: 20px !important;
}

tr {
  border-bottom: 1px solid #ced4da;
}

.card-body {
  padding: 0 !important;
  overflow: auto;
}

.table th:first-child,
.table td:first-child {
  padding-left: 10px;
}

.header-container {
  display: flex;
  justify-content: flex-end;
}
.action {
  color: #4a78df;
}
.subtilte {
  font-size: 1rem;
  color: grey;
}
.setting-title {
  font-size: 14px;
  margin-bottom: 6.5px;
}
.font-text {
  font-size: 14px;
  color: #808285;
  margin-bottom: 8px;
}
.footer-container {
  margin-right: auto !important;
}

.card-conatiner {
  /*box-shadow: 0 15px 45px 0 rgba(0,0,0,.1);*/
  border-radius: 2.5px;
  background: white;
}
.footer {
  background: white;
  border-top: none;
  border-radius: 10px;
}
td {
  color: #333333;
  font-size: 14px;
}
.table-header {
  font-size: 17px;
  font-weight: 400;
  /*color: #202930;*/
  padding: 20px 25px 20px 0;
}
.action-text {
  color: blue;
  cursor: pointer;
}
.currency-input {
  border: 1px solid rgba(50, 57, 144, 0.25);
  border-radius: 2.5px;
  padding: 0 0 4px 0;
  font-size: 15px;
  padding: 0 1rem;
  height: 36px;
}

.currency-input:focus {
  outline: rgba(50, 57, 144, 0.25);
}
</style>

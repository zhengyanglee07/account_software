<template>
  <div>
    <h1 class="header h-three">
      <slot name="name" />
    </h1>

    <slot name="headerButton" />

    <div class="datatable-container mb-5 order-table">
      <template v-if="!isDataEmpty">
        <div class="order-function-wrapper">
          <div class="d-flex filter-container">
            <div class="order-settings row">
              <div class="dropdown">
                <button
                  id="dropdownMenuButton"
                  class="filter_button filter_button_first dropdown-toggle h-100"
                  type="button"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  Status
                </button>

                <!-- status dropdown  -->
                <div
                  class="dropdown-menu statusDropdown"
                  aria-labelledby="dropdownMenuButton"
                >
                  <ul class="p-0 m-0">
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('filterRadio', 'Open')"
                    >
                      <div class="form-check p-0 position-relative">
                        <input
                          v-model="filterRadio"
                          value="Open"
                          class="form-check-input purple-radio"
                          type="radio"
                        >
                        <label
                          class="form-check-label p-two"
                          for="exampleRadios1"
                        >Open</label>
                      </div>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('filterRadio', 'Closed')"
                    >
                      <div class="form-check p-0 position-relative">
                        <input
                          v-model="filterRadio"
                          value="Closed"
                          class="form-check-input purple-radio"
                          type="radio"
                        >
                        <label
                          class="form-check-label p-two"
                          for="exampleRadios1"
                        >Closed</label>
                      </div>
                    </li>
                    <li
                      class="dropdown-item px-3 pb-2"
                      @click="addFilter('filterRadio', 'Archived')"
                    >
                      <div class="form-check p-0 position-relative">
                        <input
                          v-model="filterRadio"
                          value="Archived"
                          class="form-check-input purple-radio"
                          type="radio"
                        >
                        <label
                          class="form-check-label p-two"
                          for="exampleRadios1"
                        >Archived</label>
                      </div>
                    </li>
                    <button
                      class="btn clear_filter_link px-3 p-two"
                      :disabled="filterRadio == null"
                      @click="clearFilter('filterRadio')"
                    >
                      Clear
                    </button>
                  </ul>
                </div>
                <!-- end status dropdown  -->
              </div>

              <div class="dropdown">
                <button
                  id="dropdownMenuButton"
                  class="filter_button filter_button_last dropdown-toggle h-100"
                  type="button"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  Fulfillment Status
                </button>

                <div
                  class="dropdown-menu fulfillmentStatusDropdown"
                  aria-labelledby="dropdownMenuButton"
                >
                  <ul class="p-0 m-0">
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('fulfillmentFilter', 'Fulfilled')"
                    >
                      <input
                        v-model="fulfillmentFilter"
                        value="Fulfilled"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Fulfilled</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="
                        addFilter('fulfillmentFilter', 'Partially Fulfilled')
                      "
                    >
                      <input
                        v-model="fulfillmentFilter"
                        value="Partially Fulfilled"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Partially Fulfilled</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('fulfillmentFilter', 'Unfulfilled')"
                    >
                      <input
                        v-model="fulfillmentFilter"
                        value="Unfulfilled"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Unfulfilled</span>
                    </li>
                    <button
                      class="btn clear_filter_link px-3 p-two"
                      :disabled="fulfillmentFilter.length == 0"
                      @click="clearFilter('fulfillmentFilter')"
                    >
                      Clear
                    </button>
                  </ul>
                </div>
              </div>

              <div class="dropdown">
                <button
                  id="dropdownMenuButton"
                  class="filter_button dropdown-toggle h-100"
                  type="button"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  Payment Status
                </button>

                <div
                  class="dropdown-menu paymentStatusDropdown"
                  aria-labelledby="dropdownMenuButton"
                >
                  <ul class="p-0 m-0">
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('paymentFilter', 'Paid')"
                    >
                      <input
                        v-model="paymentFilter"
                        value="Paid"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Paid</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('paymentFilter', 'Partially Refunded')"
                    >
                      <input
                        v-model="paymentFilter"
                        value="Partially Refunded"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Partially Refunded</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('paymentFilter', 'Partially Paid')"
                    >
                      <input
                        v-model="paymentFilter"
                        value="Partially Paid"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Partially Paid</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('paymentFilter', 'Refunded')"
                    >
                      <input
                        v-model="paymentFilter"
                        value="Refunded"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span class="p-two">Refunded</span>
                    </li>
                    <li
                      class="dropdown-item px-3"
                      @click="addFilter('paymentFilter', 'Unpaid')"
                    >
                      <input
                        v-model="paymentFilter"
                        value="Unpaid"
                        type="checkbox"
                        class="me-1 black-checkbox"
                      >
                      <span>Unpaid</span>
                    </li>
                    <button
                      class="btn clear_filter_link px-3 p-two"
                      :disabled="paymentFilter.length == 0"
                      @click="clearFilter('paymentFilter')"
                    >
                      Clear
                    </button>
                  </ul>
                </div>
              </div>

              <div class="dropdown">
                <button
                  id="dropdownMenuButton"
                  class="filter_button dropdown-toggle h-100"
                  type="button"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-haspopup="true"
                  aria-expanded="false"
                  @click.stop
                >
                  Sort
                </button>

                <div
                  class="dropdown-menu sortingOptionsDropdown"
                  aria-labelledby="dropdownMenuButton"
                >
                  <ul
                    v-for="(filters, type, $index) in sortingOptions"
                    :key="$index"
                    class="p-0 m-0"
                  >
                    <li
                      v-for="(value, filter) in filters"
                      :key="filter"
                      class="dropdown-item px-3"
                    >
                      <input
                        :id="filter"
                        v-model="selectedSorting"
                        :value="{ type, label: filter, order: value }"
                        type="radio"
                        class="me-1 form-check-input"
                      >
                      <label
                        :for="filter"
                        class="form-check-label p-two w-100"
                      >
                        {{ filter }}
                      </label>
                    </li>
                  </ul>
                  <button
                    class="btn clear_filter_link px-3 p-two"
                    @click="clearFilter('selectedSorting')"
                  >
                    Clear All
                  </button>
                  <button
                    class="btn clear_filter_link px-3 p-two float-end"
                    @click="saveSortingOption"
                  >
                    Save
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="searchbar-wrapper">
            <div class="input-group w-100">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <i class="fas fa-search gray_icon" />
                </span>
              </div>
              <input
                v-model="searchKeyword"
                type="text"
                placeholder="Search Orders"
                class="form-control"
              >
            </div>
          </div>

          <div class="tags_display">
            <span class="pagination-text p-two">
              Showing {{ searchedRows.length }} of
              {{ rowsCount.total }} results.
            </span>

            <div class="filter-tags-container">
              <span
                v-show="filterRadio !== null"
                class="filter_tags me-2"
              >
                {{ filterRadio }}
                <button
                  type="button"
                  class="cleanButton ps-2"
                  style="vertical-align: middle"
                  aria-label="Close"
                  @click="cancelFilter('radio')"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
              </span>

              <span
                v-for="(filter, index) in paymentFilter"
                :key="10 + index"
                class="filter_tags me-2"
              >
                {{ filter }}
                <button
                  type="button"
                  class="cleanButton ps-2"
                  style="vertical-align: middle"
                  aria-label="Close"
                  @click="cancelFilter('paymentFilter', index)"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
              </span>

              <span
                v-for="(filter, index) in fulfillmentFilter"
                :key="20 + index"
                class="filter_tags me-2"
              >
                {{ filter }}
                <button
                  type="button"
                  class="cleanButton ps-2"
                  style="vertical-align: middle"
                  aria-label="Close"
                  @click="cancelFilter('fulfillmentFilter', index)"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
              </span>
            </div>
          </div>
        </div>
        <!-- <span class="badge badge-pill status_badges px-2 py-1">Fulfilled</span> -->

        <div class="responsive-table">
          <div class="table-wrapper">
            <table class="table table-hover table-responsive-md">
              <tr
                v-for="(item, index) in sortedRecords"
                :key="index"
                class="responsive-tr"
              >
                <div
                  class="tr-container"
                  @click="redirectOrderDetail(item.reference_key)"
                >
                  <div class="w-100">
                    <td class="responsive-td h-five">
                      {{ item.order }}
                    </td>
                    <td class="responsive-td float-end p-three">
                      {{ item.total }}
                    </td>
                  </div>

                  <div
                    class="w-100 d-flex"
                    style="justify-content: space-between"
                  >
                    <td class="responsive-td p-three">
                      {{ item.customer }}
                    </td>
                    <td class="responsive-td">
                      <span
                        :class="{
                          'pill-buttercup':
                            item.payment == 'Unpaid' ||
                            item.payment == 'Partially Paid' ||
                            item.payment == 'Partially Refunded',
                        }"
                      >{{ item.payment }}</span>
                    </td>
                  </div>

                  <div
                    class="w-100 d-flex"
                    style="justify-content: space-between"
                  >
                    <td class="responsive-td p-three">
                      {{ item.status }}
                    </td>
                    <td class="responsive-td">
                      <span
                        :class="{
                          'pill-buttercup':
                            item.fulfillment == 'Unfulfilled' ||
                            item.fulfillment == 'Partially Fulfilled',
                        }"
                      >{{ item.fulfillment }}</span>
                    </td>
                  </div>
                  <div class="w-100">
                    <td class="responsive-td p-three">
                      {{ item.time }}
                    </td>
                  </div>
                  <div
                    v-if="item.deliveryDate !== null"
                    class="w-100"
                  >
                    <td class="responsive-td p-three">
                      <slot
                        name="delivery"
                        :item="item"
                      />
                    </td>
                  </div>
                </div>
              </tr>
            </table>
          </div>
        </div>

        <div class="desktop-table">
          <div class="table-wrapper">
            <table class="table table-hover table-responsive-md">
              <thead>
                <tr>
                  <th
                    v-for="header in headers"
                    :key="header.index"
                    @click="dynamicSorting(header.value)"
                    @mouseover="triggerSortIcon(true, header.value)"
                    @mouseleave="triggerSortIcon(false, header.value)"
                  >
                    <div class="w-100">
                      <span class="h-five w-100">{{ header.text }}</span>
                      <div class="sorting-icon ps-1">
                        <span
                          v-show="
                            showSortIcon.status &&
                              showSortIcon.targetHeader == header.value
                          "
                        >
                          <i
                            class="fas"
                            :class="{
                              'fa-arrow-up': isAscending,
                              'fa-arrow-down': !isAscending,
                            }"
                          />
                        </span>
                      </div>
                    </div>
                  </th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(item, index) in sortedRecords"
                  :key="index"
                  @click="redirectOrderDetail(item.reference_key)"
                >
                  <td
                    v-for="(header, index) in headers"
                    :key="index"
                    class="order-datatable p-two"
                    :class="{
                      'fulfillment-col': header.text == 'Fulfillment',
                      'payment-col': header.text == 'Payment',
                      'date-col': header.text == 'Date',
                    }"
                  >
                    <span
                      :class="{
                        'pill-buttercup':
                          item[header.value] == 'Unfulfilled' ||
                          item[header.value] == 'Partially Fulfilled' ||
                          item[header.value] == 'Unpaid' ||
                          item[header.value] == 'Partially Paid' ||
                          item[header.value] == 'Partially Refunded',
                      }"
                      class="p-two"
                    >{{ item[header.value] }}
                      <slot
                        v-if="header.value == 'type'"
                        name="delivery"
                        :item="item"
                      />
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="w-100 justify-content-center d-flex">
          <button
            v-show="sortedRecords.length !== 0 && rowsCount.available !== 0"
            class="primary-white-button"
            style="margin: 10px"
            @click="addShowedRows"
          >
            Load More
          </button>
        </div>
      </template>

      <template v-else>
        <EmptyDataContainer :empty-state="emptyState" />
      </template>
    </div>
  </div>
</template>

<script>
import { startCase } from 'lodash';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);

export default {
  name: 'OrderDatatable',
  mixins: [specialCurrencyCalculationMixin],
  props: {
    headers: { type: Array, default: () => [] },
    datas: { type: Array, default: () => [] },
  },

  data() {
    return {
      isDataEmpty: false,
      searchKeyword: '',
      isAscending: false,
      showSortIcon: { status: false, targetHeader: '' },
      rowsCount: { total: null, digit: null, showed: null, available: null },
      fulfillmentFilter: [],
      paymentFilter: [],
      filterRadio: null,
      fulfillment: ['Unfulfilled', 'Partially Fulfilled', 'Fulfilled'],
      payment: [
        'Unpaid',
        'Partially paid',
        'Partially refunded',
        'Refunded',
        'Paid',
      ],
      emptyState: {
        title: 'order',
        description: 'your orders',
      },
      selectedSorting: {
        type: null,
        label: null,
        order: null,
      },
      sortingOptions: {
        deliverySchedule: {
          'Delivery Schedule (earliest)': 'ascending',
          'Delivery Schedule (latest)': 'descending',
        },
        pickupSchedule: {
          'Pickup Schedule (earliest)': 'ascending',
          'Pickup Schedule (latest)': 'descending',
        },
        orderDate: {
          'Order Date (ascending)': 'ascending',
          'Order Date (descending)': 'descending',
        },
      },
      localStorageKey: 'all orders|selected-datatable-sorting',
    };
  },

  computed: {
    formatData() {
      return this.datas.map((item) => {
        const obj = {};
        obj.id = item.id.toString();
        obj.reference_key = item.reference_key;
        obj.order = `#${item.order_number}`;
        obj.date = item.convertDateTime;
        obj.customer = item.name;
        obj.total = `${item.currency} ${this.specialCurrencyCalculation(
          item.total,
          item.currency === 'RM' ? 'MYR' : item.currency
        )}`;
        obj.payment = item.payment_status;
        obj.fulfillment = item.fulfillment_status;
        obj.status = item.additional_status;
        obj.convertDate = item.convertDate;
        obj.time = item.convertTime;
        obj.deliveryHourType = item.delivery_hour_type;
        obj.deliveryType = item.delivery_type;
        obj.deliveryDate = item.delivery_date;
        obj.deliveryTimeslot = item.delivery_timeslot;
        obj.createdAt = item.created_at;
        return obj;
      });
    },
    // datasByDate(){
    //     return this.sortedRecords.reduce((acc,item) => {
    //         const groupDate = item.convertDate;
    //         if(!acc[groupDate]){
    //             acc[groupDate] = [];
    //         }
    //         acc[groupDate].push(item);
    //         return acc
    //     },{});
    // },

    paginatedDatas() {
      return this.formatData.slice(0, this.rowsCount.showed);
    },

    availableRows() {
      // 2 digits : 20, >2 digits : 100
      const added = this.rowsCount.digit === 2 ? 20 : 100;
      return this.rowsCount.available > added
        ? added
        : this.rowsCount.available;
    },

    filteredTable() {
      return this.paginatedDatas.filter((item) => {
        const payment =
          this.paymentFilter.length !== 0
            ? this.paymentFilter.includes(item.payment)
            : true;
        const fulfillment =
          this.fulfillmentFilter.length !== 0
            ? this.fulfillmentFilter.includes(item.fulfillment)
            : true;
        const status =
          this.filterRadio !== null
            ? this.filterRadio.includes(item.status)
            : true;
        return payment && fulfillment && status;
      });
    },

    searchedRows() {
      //   filter rows by search keywords
      return this.filteredTable.filter((item) => {
        return Object.keys(item).some((k) => {
          return (item[k] ?? '')
            .toString()
            .toLowerCase()
            .includes(this.searchKeyword.toLowerCase());
        });
      });
    },

    sortedRecords() {
      const { type, order } = this.selectedSorting;
      if (['deliverySchedule', 'pickupSchedule'].includes(type)) {
        let scheduledDeliveryOrders = this.searchedRows.filter(
          ({ deliveryType }) =>
            deliveryType ===
            (type === 'deliverySchedule' ? 'delivery' : 'pickup')
        );
        const normalOrders = this.searchedRows.filter(
          ({ deliveryType }) => deliveryType === 'default'
        );
        scheduledDeliveryOrders = scheduledDeliveryOrders.sort((a, b) => {
          const current = dayjs(
            `${a.deliveryDate} ${this.convertTime12To24(a)}`,
            'DD-MM-YYYY HH'
          );
          const next = dayjs(
            `${b.deliveryDate} ${this.convertTime12To24(b)}`,
            'DD-MM-YYYY HH'
          );
          if (order === 'ascending') {
            return current.isAfter(next) ? 1 : -1;
          }
          return current.isAfter(next) ? -1 : 1;
        });
        return [...scheduledDeliveryOrders, ...normalOrders];
      }

      if (type === 'orderDate') {
        return [...this.searchedRows].sort((a, b) => {
          const current = dayjs(a.createdAt);
          const next = dayjs(b.createdAt);
          return order === 'ascending' ? current - next : next - current;
        });
      }
      return this.searchedRows;
    },
  },

  watch: {
    datas() {
      this.dataShowed();
      this.isDataEmpty = this.datas.length === 0;
    },
  },

  mounted() {
    const selectedSortingOption = localStorage.getItem(this.localStorageKey);
    if (selectedSortingOption) {
      this.selectedSorting = JSON.parse(selectedSortingOption);
    }
  },

  methods: {
    transformCase(str) {
      return startCase(str);
    },

    convertTime12To24({ deliveryTimeslot }) {
      const [startTime] = deliveryTimeslot.split(' to ');
      const [time, modifier] = startTime.split(' ');
      // eslint-disable-next-line prefer-const
      let [hours, minutes] = time.split(':');
      if (hours === '12') {
        hours = '00';
      }
      if (modifier === 'pm') {
        hours = parseInt(hours, 10) + 12;
      }
      return `${hours}:${minutes}`;
    },

    dynamicSorting(key) {
      this.isAscending = !this.isAscending;
      this.searchedRows.sort((a, b) => {
        if (
          !Object.prototype.hasOwnProperty.call(a, key) ||
          !Object.prototype.hasOwnProperty.call(b, key)
        ) {
          return 0;
        }
        let itemA = typeof a[key] === 'string' ? a[key].toUpperCase() : a[key];
        let itemB = typeof b[key] === 'string' ? b[key].toUpperCase() : b[key];

        if (this.isAscending) {
          const tempItem = itemA;
          itemA = itemB;
          itemB = tempItem;
        }
        if (itemA < itemB) return 1;
        return itemB < itemA ? -1 : 0;
      });
    },

    dataShowed() {
      const totalLength = this.datas.length;
      const lengthDigit = totalLength.toString().length;
      const initialRowCount = totalLength > 20 ? 20 : totalLength;
      this.rowsCount = {
        total: totalLength,
        digit: lengthDigit,
        showed: initialRowCount,
        available: totalLength - initialRowCount,
      };
    },

    addShowedRows() {
      this.rowsCount.showed += this.availableRows;
      this.rowsCount.available -= this.availableRows;
    },

    triggerSortIcon(_boolean, _header) {
      this.showSortIcon.status = _boolean;
      this.showSortIcon.targetHeader = _header;
    },

    cancelFilter(_type, _index) {
      if (_type === 'paymentFilter') {
        this.paymentFilter.splice(_index, 1);
      } else if (_type === 'fulfillmentFilter') {
        this.fulfillmentFilter.splice(_index, 1);
      } else {
        this.filterRadio = null;
      }
    },

    redirectOrderDetail(referenceKey) {
      this.$inertia.visit(`/orders/details/${referenceKey}`);
    },

    addFilter(_type, value) {
      if (_type === 'paymentFilter') {
        const itemIndex = this.paymentFilter.findIndex(
          (item) => item === value
        );
        // console.log("the item index", itemIndex);
        if (itemIndex === -1) {
          this.paymentFilter.push(value);
        } else {
          this.paymentFilter.splice(itemIndex, 1);
        }
      } else if (_type === 'fulfillmentFilter') {
        const itemIndex = this.fulfillmentFilter.findIndex(
          (item) => item === value
        );
        // console.log("the fulfillement item ", value, itemIndex);
        if (itemIndex === -1) {
          this.fulfillmentFilter.push(value);
        } else {
          this.fulfillmentFilter.splice(itemIndex, 1);
        }
      } else {
        this.filterRadio = value;
      }
    },

    clearFilter(filterType) {
      if (filterType === 'filterRadio') {
        this.filterRadio = null;
      } else if (filterType === 'paymentFilter') {
        this.paymentFilter = [];
      } else if (filterType === 'fulfillmentFilter') {
        this.fulfillmentFilter = [];
      } else if (filterType === 'selectedSorting') {
        this.selectedSorting = {
          type: null,
          label: null,
          order: null,
        };
      }
    },

    saveSortingOption() {
      localStorage.setItem(
        this.localStorageKey,
        JSON.stringify(this.selectedSorting)
      );
      this.$toast.success(
        'Successfully saved',
        `The orders will remain sorted by ${this.selectedSorting.label}`
      );
    },
  },
};
</script>

<style scoped lang="scss">

.datatable-container {
  @media (max-width: $md-display) {
    border-bottom: none !important;
    border-left: none !important;
    border-right: none !important;
  }
}

.pill-buttercup {
  height: auto !important;
}

.fulfillment-col {
  min-width: 130px;
}

.payment-col {
  min-width: 130px;
}

.date-col {
  min-width: 170px;
}

.fa-search {
  padding-left: 20px !important;
}

.filter_button {
  padding: 16px 25px 16px 0;
  line-height: 1;
  background: transparent;
  border: none;
  font-size: $base-font-size;
  font-family: 'Roboto', sans-serif;
  font-weight: 700;

  @media (max-width: $md-display) {
    padding: 0.7rem 0.6rem 0 0;
    font-size: $responsive-base-font-size;
  }

  .filter_button_last {
    border-top-right-radius: 3px;
    border-bottom-right-radius: 3px;
  }
}
.filter_button_first {
  border-left: 0px;
  padding-left: 1rem;
  @media (max-width: $md-display) {
    padding-left: 0;
  }
}

.table-hover tbody tr:hover {
  cursor: pointer;
  background: linear-gradient(180deg, #f9fafb, #f4f6f8);
}

.tags_display {
  padding: 0;
}

.filter-tags-container {
  padding: 0 25px;
  padding-top: 10px;

  @media (max-width: $md-display) {
    padding: 0;
  }
}

.filter_tags {
  display: inline-flex;
  max-width: 100%;
  align-items: center;
  min-height: 2rem;
  padding: 0 0.8rem;
  background-color: var(--p-action-secondary, #dfe3e8);
  border-radius: 3px;
  color: var(--p-text, #212b36);
  font-size: $base-font-size;
  font-family: $base-font-family;
  margin-bottom: 10px;

  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
    margin: 5px;
  }
}

.status_tags {
  font-size: 14px;
  font-weight: 400;
  letter-spacing: 0.025em;
  background: #dfe3e8;
  border: 0 !important;
  font-family: $base-font-family;
}

.half_tags {
  background: #ffea8a;
}

.clear_filter_link {
  color: $h-secondary-pictonBlue;
  font-size: 14px;
}

.clear_filter_link:hover {
  cursor: pointer;
  text-decoration: underline;
}

.status_badges {
  background: #dfe3e8;
  font-size: 14px;
  font-weight: 400;
  font-family: $base-font-family;
}

button:focus,
button:active {
  outline: none !important;
  box-shadow: none;
}

li:hover {
  cursor: pointer;
}

// .input-group {
//   position: relative;
//   //   display: flex;
//   flex-wrap: wrap;
//   align-items: stretch;
//   width: auto;
// }

.order-settings {
  height: auto;
  display: flex;
  align-items: center;
  padding: 0;
}

.dropdown {
  width: auto;
  color: red;
}

.alignToRight {
  justify-content: flex-end;
  text-align: right;
}

.responsive-table {
  display: none;
}
.desktop-table {
  display: block;
}

@media (max-width: $md-display) {
  .responsive-table {
    display: block;
  }

  .desktop-table {
    display: none;
  }

  .order-table {
    padding: 0px;
  }

  .order-function-wrapper {
    padding: 0 0.5rem;
  }

  table .responsive-tr {
    display: block;
    height: auto;
    padding-bottom: 12px;
    background-color: $base-background;
    border: none;
  }

  .tr-container {
    background-color: white;
    padding-top: 12px;
    border-bottom: 1px solid $table-border-color;

    div {
      padding: 0 0.5rem;
    }
  }

  table .responsive-td {
    display: inline-flex;
    width: auto;
    border: none;
    padding: 0;
    height: auto;
    min-height: 28px;
  }
}

th:first-child,
th:last-child {
  width: 130px;
}

.filter-container {
  border-bottom: 1px solid $table-border-color;
  @media (max-width: $md-display) {
    padding: 16px 0;
  }
}

.pagination-text {
  @media (max-width: $md-display) {
    padding-bottom: 12px;
    padding-left: 6px;
  }
}

.sorting-icon {
  width: 18px;
}
</style>

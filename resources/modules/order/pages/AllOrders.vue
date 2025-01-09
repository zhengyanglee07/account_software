<template>
  <div class="mb-4">
    <!-- status dropdown  -->
    <BaseButton
      id="order-status-dropdown-button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      type="white"
      has-edit-icon
      has-dropdown-icon
      class="me-2 mt-0"
    >
      Status
    </BaseButton>
    <BaseDropdown id="order-status-dropdown-list">
      <BaseDropdownOption
        v-for="status in ['Open', 'Closed', 'Archived']"
        :key="status"
        @click="addFilter('filterRadio', status)"
      >
        <BaseFormRadio
          :id="`order-status-${status}`"
          v-model="filterRadio"
          :value="status"
        >
          {{ status }}
        </BaseFormRadio>
      </BaseDropdownOption>
      <BaseDropdownOption>
        <BaseButton
          type="link"
          :disabled="!filterRadio"
          @click="clearFilter('filterRadio')"
        >
          Clear
        </BaseButton>
      </BaseDropdownOption>
    </BaseDropdown>
    <!-- end status dropdown  -->

    <!-- fulfillment dropdown  -->
    <BaseButton
      id="order-fulfillment-dropdown-menu-button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      type="white"
      has-edit-icon
      has-dropdown-icon
      class="me-2 mt-0"
    >
      Fulfillment Status
    </BaseButton>
    <BaseDropdown
      id="order-fulfillment-dropdown-list"
      size="md"
    >
      <BaseDropdownOption
        v-for="status in ['Fulfilled', 'Partially Fulfilled', 'Unfulfilled']"
        :key="status"
      >
        <BaseFormCheckBox
          :id="`order-fulfillment-${status}`"
          v-model="fulfillmentFilter"
          :value="status"
        >
          {{ status }}
        </BaseFormCheckBox>
      </BaseDropdownOption>
      <BaseDropdownOption>
        <BaseButton
          type="link"
          :disabled="fulfillmentFilter.length === 0"
          @click="clearFilter('fulfillmentFilter')"
        >
          Clear
        </BaseButton>
      </BaseDropdownOption>
    </BaseDropdown>
    <!-- end fulfillment dropdown  -->

    <!-- payment dropdown  -->
    <BaseButton
      id="order-payment-dropdown-menu-button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      type="white"
      has-edit-icon
      has-dropdown-icon
      class="me-2 mt-0"
    >
      Payment Status
    </BaseButton>
    <BaseDropdown
      id="order-payment-dropdown-list"
      size="md"
    >
      <BaseDropdownOption
        v-for="status in [
          'Paid',
          'Partially Refunded',
          'Partially Paid',
          'Refunded',
          'Unpaid',
        ]"
        :key="status"
      >
        <BaseFormCheckBox
          :id="`order-payment-${status}`"
          v-model="paymentFilter"
          :value="status"
        >
          {{ status }}
        </BaseFormCheckBox>
      </BaseDropdownOption>
      <BaseDropdownOption>
        <BaseButton
          type="link"
          :disabled="paymentFilter.length === 0"
          @click="clearFilter('paymentFilter')"
        >
          Clear
        </BaseButton>
      </BaseDropdownOption>
    </BaseDropdown>
    <!-- end payment dropdown  -->

    <!-- sort dropdown  -->
    <BaseButton
      id="order-sort-dropdown-menu-button"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      type="white"
      has-edit-icon
      has-dropdown-icon
      class="me-2 mt-0"
    >
      Sort
    </BaseButton>
    <BaseDropdown
      id="order-sort-dropdown-list"
      size="md"
    >
      <template
        v-for="(filters, type, index) in sortingOptions"
        :key="index"
      >
        <BaseDropdownOption
          v-for="(value, filter) in filters"
          :key="filter"
        >
          <BaseFormRadio
            :id="`order-sort-${filter}`"
            :value="filter"
            :model-value="selectedFilter"
            @input="updateSortCondition(type, filter, value)"
          >
            {{ filter }}
          </BaseFormRadio>
        </BaseDropdownOption>
      </template>
      <div class="px-7 py-3 d-flex justify-content-between">
        <BaseButton
          type="link"
          @click="clearFilter('selectedSorting')"
        >
          Clear
        </BaseButton>

        <BaseButton
          type="link"
          @click="saveSortingOption"
        >
          Save
        </BaseButton>
      </div>
    </BaseDropdown>
    <!-- end sort dropdown  -->
    <!-- Filter list -->
    <div>
      <BaseBadge
        v-show="filterRadio"
        :text="filterRadio"
        has-delete-button
        @click="cancelFilter('radio')"
      />

      <BaseBadge
        v-for="(filter, index) in paymentFilter"
        :key="10 + index"
        :text="filter"
        has-delete-button
        @click="cancelFilter('paymentFilter', index)"
      />

      <BaseBadge
        v-for="(filter, index) in fulfillmentFilter"
        :key="20 + index"
        :text="filter"
        has-delete-button
        @click="cancelFilter('fulfillmentFilter', index)"
      />
    </div>
  </div>

  <BaseDatatable
    title="order"
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="sortedRecords"
    :pagination-info="paginatedOrders"
  >
    <template #action-button>
      <BaseButton
        has-add-icon
        href="/orders/create"
      >
        Add Order
      </BaseButton>
    </template>
    <template
      #cell-type="{
        row: { deliveryHourType, deliveryType, deliveryDate, deliveryTimeslot },
      }"
    >
      <div v-if="deliveryHourType === 'custom'">
        {{ deliveryType == 'pickup' ? 'Store Pickup' : 'Scheduled Delivery' }}
        <br>
        {{ deliveryDate }} <br>
        {{ deliveryTimeslot }}
      </div>
    </template>

    <template #cell-payment="{ row: { payment } }">
      <BaseBadge
        :text="payment"
        :type="payment === 'Paid' ? 'success' : 'warning'"
      />
    </template>
    <template #cell-fulfillment="{ row: { fulfillment } }">
      <BaseBadge
        :text="fulfillment"
        :type="fulfillment === 'Fulfilled' ? 'success' : 'warning'"
      />
    </template>
    <template #cell-status="{ row: { status } }">
      <BaseBadge
        v-if="status !== 'Closed'"
        :text="status"
        type="success"
      />
      <BaseBadge
        v-else
        :text="status"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import orderAPI from '@order/api/orderAPI.js';
import { startCase } from 'lodash';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(customParseFormat);

export default {
  mixins: [specialCurrencyCalculationMixin],

  props: {
    dbOrders: {
      type: Array,
      default: () => [],
    },
    paginatedOrders: {
      type: Array,
      required: true,
    },
    currency: {
      type: String,
      default: 'MYR',
    },
  },

  data() {
    return {
      allOrders: [],
      tableHeaders: [
        { name: 'Order', key: 'order' },
        { name: 'Date', key: 'date' },
        { name: 'Customer', key: 'customer' },
        { name: 'Type', key: 'type', custom: true },
        { name: 'Total', key: 'total' },
        { name: 'Payment', key: 'payment', custom: true },
        { name: 'Fulfillment', key: 'fulfillment', custom: true },
        { name: 'Status', key: 'status', custom: true },
      ],
      isDataEmpty: false,
      searchKeyword: '',
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
      selectedFilter: '',
    };
  },

  computed: {
    formatData() {
      return this.allOrders.map((item) => {
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
        obj.editLink = `/orders/details/${item.reference_key}`;
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
    allOrders() {
      this.dataShowed();
      this.isDataEmpty = this.allOrders.length === 0;
    },
  },

  mounted() {
    this.allOrders = this.dbOrders;

    const selectedSortingOption = localStorage.getItem(this.localStorageKey);
    if (selectedSortingOption) {
      this.selectedSorting = JSON.parse(selectedSortingOption);
    }
  },
  methods: {
    updateSortCondition(type, label, order) {
      this.selectedFilter = label;
      this.selectedSorting = {
        type,
        label,
        order,
      };
    },

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

    dataShowed() {
      const totalLength = this.allOrders.length;
      const lengthDigit = totalLength.toString().length;
      const initialRowCount = totalLength;
      // const initialRowCount = totalLength > 20 ? 20 : totalLength;
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
        this.selectedFilter = '';
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

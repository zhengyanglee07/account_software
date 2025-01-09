<template>
  <div class="row justify-content-start">
    <div class="col-md">
      <BaseFormGroup label="Type: ">
        <BaseFormSelect
          v-model="selectBreakdown"
          label-key="name"
          value-key="value"
          :options="breakdownSelection"
        />
      </BaseFormGroup>
    </div>
    <div class="col-lg-4">
      <BaseFormGroup label="Date Range: ">
        <BaseDatePicker
          v-model="dates"
          placeholder="Select date range"
          range
          value-type="date"
          :type="datePickerType"
          :disabled-date="disableAfterToday"
          @change="handleDatesChange"
          @clear="clearDates"
        />
      </BaseFormGroup>
    </div>
    <div class="col-md">
      <BaseFormGroup label="Group By: ">
        <BaseFormSelect
          v-model="dateGroup"
          label-key="name"
          value-key="value"
          :options="groupBySelection"
        />
      </BaseFormGroup>
    </div>
  </div>
  <BaseDatatable
    v-if="selectBreakdown === 'newReturnCust'"
    title="growth"
    no-header
    no-action
    no-sorting
    no-hover
    :table-headers="tableHeaders"
    :table-datas="newReturnCust"
  />
</template>

<script>
/* eslint-disable indent */

import dayjs from 'dayjs';
import uniqBy from 'lodash/uniqBy';
import { calculateOrdersRevenue } from '@people/lib/revenue.js';
import { getFirstDay, getLastDay } from '@shared/lib/date.js';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

const startDate = dayjs().subtract(6, 'days').$d;
const endDate = dayjs().$d;

export default {
  name: 'GrowthReport',

  mixins: [specialCurrencyCalculationMixin],

  props: [
    'allCustomers',
    'allOrders',
    'segments',
    'currency',
    'defaultExchangeRate',
  ],

  data() {
    return {
      customers: [],
      orders: [],
      breakdownSelection: [
        {
          name: 'New/Returning Customer',
          value: 'newReturnCust',
        },
      ],
      groupBySelection: [
        {
          name: 'Day',
          value: 'day',
        },
        {
          name: 'Week',
          value: 'week',
        },
        {
          name: 'Month',
          value: 'month',
        },
      ],
      selectedSegment: 'All',
      selectedData: 'Revenue',
      dates: [startDate, endDate],
      dateFormat: 'YYYY-MM-DD',
      displayDateFormat: 'DD/MM/YYYY',
      dateGroup: 'day',
      datasetForChart: [],
      chartOptions: [
        'Revenue',
        'Orders',
        'Customers',
        'People',
        'Revenue Per Customer',
        'Orders Per Customer',
        'New Customer',
        'Returning Customers',
        'Average Order Value',
      ],
      tableHeaders: [
        {
          name: 'Name',
          key: 'name',
        },
        {
          name: 'Customers',
          key: 'custCount',
        },
        {
          name: 'Orders',
          key: 'orderCount',
        },
        {
          name: `AOV (${this.currency === 'MYR' ? 'RM' : this.currency})`,
          key: 'aov',
        },
        {
          name: `Total Sales (${
            this.currency === 'MYR' ? 'RM' : this.currency
          })`,
          key: 'totalSales',
        },
        {
          name: 'Share of All',
          key: 'shareOfAll',
        },
      ],
      selectBreakdown: 'newReturnCust',
      chartData: [],
      loaded: false,
      chartYAxis: [],
      chartXAxis: [],
      formatData: [],
      exchangeRate: this.defaultExchangeRate.exchangeRate,
    };
  },
  computed: {
    datePickerType() {
      return this.dateGroup === 'month' ? 'month' : 'date';
    },
    datesBetweenRange() {
      let dates = [];
      let theDate = dayjs(this.dates[0]).format('YYYY-MM-DD');
      while (theDate < dayjs(this.dates[1]).format('YYYY-MM-DD')) {
        dates = [theDate, ...dates];
        theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
      }
      return [dayjs(this.dates[1]).format('YYYY-MM-DD'), ...dates];
    },

    newReturnCust() {
      const custTypeArr = ['New Customers', 'Returning Customers'];

      const existedCust = this.getCustBefore();
      const getOrdersBetweenRange = this.filteredByDate(this.allOrders);

      const datas = custTypeArr.reduce((acc, custType) => {
        let custWithinRange = [];
        const getAllCustInRange = getOrdersBetweenRange.reduce(
          (current, order) => {
            custWithinRange = [...custWithinRange, order.processed_contact_id];
            return 0;
          },
          0
        );
        const newCust = [
          ...new Set(
            custWithinRange.filter((val) => !existedCust.includes(val))
          ),
        ];
        const returningCust = [
          ...new Set(
            custWithinRange.filter((val) => existedCust.includes(val))
          ),
        ];

        const data =
          custType === 'New Customers'
            ? {
                name: custType,
                custCount: newCust.length,
                orderCount: this.getRespectiveTotalSales(newCust).orderCount,
                aov: parseFloat(
                  this.getRespectiveTotalSales(newCust).aov
                ).toFixed(2),
                totalSales: parseFloat(
                  this.getRespectiveTotalSales(newCust).totalSales
                ).toFixed(2),
              }
            : {
                name: custType,
                custCount: returningCust.length,
                orderCount:
                  this.getRespectiveTotalSales(returningCust).orderCount,
                aov: parseFloat(
                  this.getRespectiveTotalSales(returningCust).aov
                ).toFixed(2),
                totalSales: parseFloat(
                  this.getRespectiveTotalSales(returningCust).totalSales
                ).toFixed(2),
              };
        return [...acc, data];
      }, []);

      const summary = datas.reduce(
        (acc, data) => {
          return {
            ...acc,
            custCount: (acc.custCount += parseFloat(data.custCount)),
            orderCount: (acc.orderCount += parseFloat(data.orderCount)),
            aov: (acc.aov += parseFloat(data.aov)),
            totalSales: (acc.totalSales += parseFloat(data.totalSales)),
          };
        },
        {
          name: 'Summary',
          custCount: 0,
          orderCount: 0,
          aov: 0,
          totalSales: 0,
          shareOfAll: '100%',
        }
      );

      const final = [
        summary,
        {
          ...datas[0],
          shareOfAll: this.getShareOfAll(
            summary.totalSales,
            datas[0].totalSales
          ),
        },
        {
          ...datas[1],
          shareOfAll: this.getShareOfAll(
            summary.totalSales,
            datas[1].totalSales
          ),
        },
      ];

      return final;
    },

    setYAxis() {
      switch (this.selectedData) {
        case 'Revenue':
          return this.currency === 'MYR'
            ? 'RM (Revenue)'
            : `${this.currency} (Revenue)`;

        case 'Orders':
          return 'Number Of Orders';

        case 'People':
          return 'Number Of People';

        case 'Customers':
          return 'Number Of Customers';

        case 'Revenue Per Customer':
          return this.currency === 'MYR'
            ? 'RM (Revenue Per Customer)'
            : `${this.currency} (Revenue Per Customer)`;

        case 'Orders Per Customer':
          return 'Number Of Orders';

        case 'New Customer':
          return 'Number Of New Customers';

        case 'Returning Customers':
          return 'Number Of Returning Customers';

        case 'Average Order Value':
          return 'Average Order Value (AOV)';
      }
      return 0;
    },
    defaultDates() {
      const date = new Date();

      const startingDate =
        this.dateGroup === 'month'
          ? getFirstDay(date, -2)
          : new Date(Date.now() - 6 * 24 * 60 * 60 * 1000); // default to 6 days before

      return [startingDate, date];
    },
    getDatesArray() {
      const start = dayjs(this.dates[0] ?? this.defaultDates[0]);
      const end = dayjs(this.dates[1] ?? this.defaultDates[1]);
      return [];
    },
    chartStyles() {
      return {
        backgroundColor: 'white',
        'box-shadow': '0 6px 12px rgba(0,0,0,.175)',
      };
    },
  },

  watch: {
    dates: {
      deep: true,
      handler(newValue) {
        const isAllNull = newValue.every((e) => e === null);
        if (!isAllNull) return;
        this.dates = [startDate, endDate];
      },
    },

    dateGroup(val) {
      const startingDate = this.dates[0];

      if (this.dateGroup === 'month' && startingDate) {
        this.dates = [getFirstDay(startingDate), getLastDay(this.dates[1])];
      }
    },
  },

  async mounted() {
    // default to showing all contacts
    // this.orders = [...this.allOrders];
    this.orders = [...this.allOrders];
    this.customers = [...this.allCustomers];

    // load initial segment data & chart
    this.loaded = false;
    try {
      // refresh chart
      this.selectData(this.selectedData);
    } catch (err) {
      console.error(err);
    } finally {
      this.loaded = true;
    }
  },
  methods: {
    clearDates() {
      this.dates = [];
    },
    getOrdersMaxDate(orders) {
      const orderDates = orders.map((o) => dayjs(o.created_at));
      return dayjs.max(orderDates).format(this.displayDateFormat);
    },
    handleDatesChange(newDates) {
      const startingDate = newDates[0];
      let endingDate = newDates[1];

      if (this.dateGroup === 'month' && endingDate?.getDate() === 1) {
        endingDate = getLastDay(endingDate);
      }

      this.dates = [startingDate, endingDate];
    },
    filteredByDate(datas) {
      return datas.filter((data) => {
        const date = data.created_at.split('T')[0];
        const start = dayjs(this.dates[0]).format('YYYY-MM-DD');
        const end = dayjs(this.dates[1]).format('YYYY-MM-DD');
        return (date >= start && date <= end) || this.dates.length == 0;
      });
    },

    getCustBefore() {
      const orderBefore = this.getLastDate(this.allOrders);
      const custBefore = orderBefore.reduce((acc, order) => {
        return [...acc, order.processed_contact_id];
      }, []);
      return [...new Set(custBefore)];
    },

    getShareOfAll(total, respectiveVal) {
      const val = Number.isNaN((respectiveVal / total) * 100)
        ? 0
        : ((respectiveVal / total) * 100).toFixed(2) ?? 0;

      return `${val.toString()}%`;
    },

    getRespectiveTotalSales(array) {
      let orderCount = 0;
      const custTypeTotalSales = array.reduce((sum, cust) => {
        const filteredOrder = this.filteredByDate(this.allOrders).filter(
          (order) => order.processed_contact_id === cust
        );
        if (filteredOrder.length === 0) return sum + 0;
        orderCount += filteredOrder.length;
        const salesWithinRangeTest = filteredOrder.reduce((acc, order) => {
          const { currency } = order; // order currency
          const defaultCurrency = this.currency;
          const exchangeRate =
            currency === defaultCurrency
              ? 1
              : parseFloat(this.defaultExchangeRate.exchangeRate);
          const taxable = (val, tax) =>
            parseFloat(val) / (1 + parseFloat(tax) / 100);
          const orderDiscountRatio = (
            sellingPrice,
            orderGross,
            discountVal
          ) => {
            return sellingPrice.reduce((accu, selling) => {
              return accu + (selling / orderGross) * discountVal;
            }, 0);
          };
          const orderDiscountPercentage = (
            orderGross,
            discountVal,
            productGross
          ) => (productGross * (orderGross / discountVal)) / 100;
          let productSellingPrice = [];
          const grossSales = order.order_details.reduce((gross, product) => {
            const productActualGross = order.is_product_include_tax
              ? product.is_taxable
                ? taxable(product.total, order.tax_rate)
                : parseFloat(product.total)
              : parseFloat(product.total);
            productSellingPrice = [
              ...productSellingPrice,
              parseFloat(productActualGross),
            ];
            return gross + productActualGross;
          }, 0);

          let discount = 0;
          if (order.order_discount.length > 0) {
            discount = order.order_discount.reduce((totalDiscount, item) => {
              let discountVal = 0;
              // because previously the discount table doesnt have discount type, so the old order discount will all be 0
              if (item.promotion_category === 'Product') {
                discountVal = parseFloat(item.discount_value) / 100;
              } else if (
                item.promotion_category === 'Order' &&
                item.discount_type === 'percentage'
              ) {
                discountVal = orderDiscountPercentage(
                  parseFloat(order.subtotal),
                  parseFloat(item.discount_value) / 100,
                  grossSales
                );
              } else if (
                item.promotion_category === 'Order' &&
                item.discount_type === 'fixed'
              ) {
                discountVal = orderDiscountRatio(
                  productSellingPrice,
                  parseFloat(order.subtotal),
                  parseFloat(item.discount_value) / 100
                );
              } else if (
                item.promotion_category === 'Order' &&
                item.discount_type === null
              ) {
                discountVal = 0;
              } else {
                // for shipping fee discount
                discountVal = parseFloat(item.discount_value) / 100;
              }
              return discountVal + totalDiscount;
            }, 0);
          }

          const refundAmt = parseFloat(order.refunded);
          const shippingFee = order.is_product_include_tax
            ? parseFloat(order.shipping) - parseFloat(order.shipping_tax)
            : parseFloat(order.shipping);

          const totalSalesPerOrder =
            grossSales -
            discount -
            refundAmt +
            shippingFee +
            parseFloat(order.taxes);

          return acc + totalSalesPerOrder;
        }, 0);
        return sum + salesWithinRangeTest;
      }, 0);

      const aov = custTypeTotalSales / orderCount;

      return {
        orderCount,
        totalSales: custTypeTotalSales.toFixed(2),
        aov: Number.isNaN(aov) ? 0 : aov.toFixed(2),
      };
    },

    getLastDate(datas) {
      return datas.filter((data) => {
        const date = data.created_at.split('T')[0];
        const end = dayjs(this.dates[0]).format('YYYY-MM-DD');
        return date < end || this.dates.length === 0;
      });
    },

    getUpperBoundDate() {
      // use default upper bound if no date is selected by user
      if (!this.dates[1]) {
        return dayjs(this.defaultDates[1]).format(this.displayDateFormat);
      }

      const selectedUpperBoundDate =
        this.dateGroup === 'month'
          ? getFirstDay(this.dates[1], 1)
          : this.dates[1];

      return dayjs(selectedUpperBoundDate).format(this.displayDateFormat);
    },

    /**
     * Check whether date is in between start and end
     *
     * Note: all the date params should be in the format described by
     * displayDateFormat (DD/MM/YYYY)
     *
     * @param date
     * @param start
     * @param end
     *
     */
    isDisplayDateInBetween(date, start, end) {
      return dayjs(this.reformatDisplayDate(date)).isBetween(
        this.reformatDisplayDate(start),
        this.reformatDisplayDate(end),
        undefined,
        '[)'
      );
    },

    /**
     * Reformat display date format to calculation format (YYYY-MM-DD)
     *
     * @param date
     * @returns {string}
     */
    reformatDisplayDate(date) {
      return dayjs(date, this.displayDateFormat).format(this.dateFormat);
    },
    enumerateBetweenDates(start, end) {
      const date = start.clone();
      const dates = [];

      while (date.isSameOrBefore(end)) {
        dates.push(date.format(this.displayDateFormat));
        date.add(1, `${this.dateGroup}s`);
      }
      return dates;
    },
    async handleSegmentChange(e) {
      const id = e.target.value;

      if (id === 'All') {
        this.orders = [...this.allOrders];
        this.customers = [...this.allCustomers];
        this.selectData(this.selectedData);
        return;
      }

      const segment = this.segments.find((s) => s.id === parseInt(id));

      try {
        const res = await this.$axios.post('/report/segment/contacts', {
          id: segment.id,
        });
        this.customers = res.data.contacts;
        this.orders = this.allOrders.filter((o) =>
          this.customers.some((c) => c.id === o.processed_contact_id)
        );

        this.selectData(this.selectedData);
      } catch (err) {
        this.$toast.error(
          'Error',
          'Failed to fetch segment contacts information'
        );
      }
    },
    revenueChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const orders = this.orders.map((o) => {
        return {
          total: o.total,
          currency: o.currency,
          exchange_rate: o.exchange_rate,
          createdAt: dayjs(o.created_at).format(this.displayDateFormat),
        };
      });

      const groupedOrders = this.groupBy(orders, 'createdAt');
      // console.log(groupedOrders);
      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);
        const revenue = Object.keys(groupedOrders)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce(
            (acc, d) => acc + calculateOrdersRevenue(groupedOrders[d]),
            0
          );

        // const convertedRevenue =
        //   revenue / (this.currency === 'MYR' || parseFloat(this.exchangeRate));

        // this.chartYAxis.push(convertedRevenue.toFixed(2));
        this.chartYAxis.push(revenue.toFixed(2));
      });
    },
    ordersChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const ordersData = this.orders.map((o) => {
        return {
          createdAt: dayjs(o.created_at).format(this.displayDateFormat),
        };
      });

      const groupedOrders = this.groupBy(ordersData, 'createdAt');

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const ordersCount = Object.keys(groupedOrders)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce((acc, d) => acc + groupedOrders[d].length, 0);
        this.chartYAxis.push(ordersCount);
      });
    },
    customersChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const ordersData = this.orders.map((o) => ({
        ...o,
        createdAt: dayjs(o.created_at).format(this.displayDateFormat),
      }));

      const groupedOrders = this.groupBy(
        ordersData,
        'createdAt',
        'processed_contact_id'
      );

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const customersCount = Object.keys(groupedOrders)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce((acc, d) => acc + groupedOrders[d].length, 0);
        this.chartYAxis.push(customersCount);
      });
    },
    peopleChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const peopleData = this.customers.map((c) => {
        return {
          createdAt: dayjs(new Date(c.created_at)).format(
            this.displayDateFormat
          ),
        };
      });

      // below here is to loop the data using date and enter it inside the vue chart js
      const groupedCustomers = this.groupBy(peopleData, 'createdAt');

      this.getDatesArray.forEach((date, i) => {
        // nextDate will be undefined in last index, so we will get
        // upper bound nextDate from dates/defaultDates (if date range is null)
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const peopleCount = Object.keys(groupedCustomers)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce((acc, d) => acc + groupedCustomers[d].length, 0);
        this.chartYAxis.push(peopleCount);
      });
    },
    revenuePerCustomerChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const orders = this.orders.map((o) => {
        return {
          total: o.total,
          currency: o.currency,
          exchange_rate: o.exchange_rate,
          processedContactId: o.processed_contact_id,
          createdAt: dayjs(o.created_at).format(this.displayDateFormat),
        };
      });

      const groupedOrders = this.groupBy(orders, 'createdAt');

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const datesWithinRange = Object.keys(groupedOrders).filter((d) =>
          this.isDisplayDateInBetween(d, date, nextDate)
        );

        const revenue = datesWithinRange.reduce(
          (acc, d) => acc + calculateOrdersRevenue(groupedOrders[d]),
          0
        );
        // ) / (this.currency === 'MYR' || parseFloat(this.exchangeRate));

        const uniqueCustomers = datesWithinRange.reduce((acc, d) => {
          return acc + uniqBy(groupedOrders[d], 'processedContactId').length;
        }, 0);

        this.chartYAxis.push((revenue / uniqueCustomers).toFixed(2));
      });
    },
    ordersPerCustomerChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const orders = this.orders.map((o) => {
        return {
          processedContactId: o.processed_contact_id,
          createdAt: dayjs(o.created_at).format(this.displayDateFormat),
        };
      });

      const groupedOrders = this.groupBy(orders, 'createdAt');

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const datesWithinRange = Object.keys(groupedOrders).filter((d) =>
          this.isDisplayDateInBetween(d, date, nextDate)
        );

        const ordersCount = datesWithinRange.reduce(
          (acc, d) => acc + groupedOrders[d].length,
          0
        );

        const uniqueCustomers = datesWithinRange.reduce((acc, d) => {
          return acc + uniqBy(groupedOrders[d], 'processedContactId').length;
        }, 0);

        this.chartYAxis.push(ordersCount / uniqueCustomers);
      });
    },
    newCustomerChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const customerFirstOrders = this.customers
        .filter((c) => !!c.orders[0])
        .map((c) => ({
          createdAt: dayjs(c.orders[0].created_at).format(
            this.displayDateFormat
          ),
        }));

      const groupedCustomerFirstOrders = this.groupBy(
        customerFirstOrders,
        'createdAt'
      );

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const newCustomersCount = Object.keys(groupedCustomerFirstOrders)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce((acc, d) => acc + groupedCustomerFirstOrders[d].length, 0);
        this.chartYAxis.push(newCustomersCount);
      });
    },
    returningCustomerChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      // find the created_at of second order
      const returningCustomers = this.customers
        .filter((c) => c.orders.length >= 2)
        .map((c) => ({
          createdAt: dayjs(c.orders[1].created_at).format(
            this.displayDateFormat
          ),
        }));

      const groupedReturningCustomers = this.groupBy(
        returningCustomers,
        'createdAt'
      );

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const returningCustomersCount = Object.keys(groupedReturningCustomers)
          .filter((d) => this.isDisplayDateInBetween(d, date, nextDate))
          .reduce((acc, d) => acc + groupedReturningCustomers[d].length, 0);
        this.chartYAxis.push(returningCustomersCount);
      });
    },
    averageOrderValueChart() {
      this.chartXAxis = [];
      this.chartYAxis = [];

      const orders = this.orders.map((o) => {
        return {
          total: o.total,
          currency: o.currency,
          exchange_rate: o.exchange_rate,
          processedContactId: o.processed_contact_id,
          createdAt: dayjs(o.created_at).format(this.displayDateFormat),
        };
      });

      const groupedOrders = this.groupBy(orders, 'createdAt');

      this.getDatesArray.forEach((date, i) => {
        const nextDate = this.getDatesArray[i + 1] || this.getUpperBoundDate();
        this.chartXAxis.push(date);

        const datesWithinRange = Object.keys(groupedOrders).filter((d) =>
          this.isDisplayDateInBetween(d, date, nextDate)
        );

        const revenue = datesWithinRange.reduce(
          (acc, d) => acc + calculateOrdersRevenue(groupedOrders[d]),
          0
        );
        // ) / (this.currency === 'MYR' || parseFloat(this.exchangeRate));

        const ordersCount = datesWithinRange.reduce((acc, d) => {
          return acc + groupedOrders[d].length;
        }, 0);

        this.chartYAxis.push((revenue / ordersCount).toFixed(2));
      });
    },
    disableAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0));
    },

    /**
     * Group an array by property provided.
     * Optionally group by with unique member with property provided.
     *
     * @param objectArray
     * @param property
     * @param uniqueMemberProperty
     * @returns {*}
     */
    groupBy(objectArray, property, uniqueMemberProperty = null) {
      return objectArray.reduce((acc, obj) => {
        const key = obj[property];
        if (!acc[key]) {
          acc[key] = [];
        }

        if (
          !uniqueMemberProperty ||
          !acc[key].find(
            (o) => o[uniqueMemberProperty] === obj[uniqueMemberProperty]
          )
        ) {
          // Add object to list for given key's value
          acc[key].push(obj);
        }

        return acc;
      }, {});
    },
    selectData(value) {
      this.assignDate();
      if (value === 'Revenue') {
        this.revenueChart();
      } else if (value === 'Orders') {
        this.ordersChart();
      } else if (value === 'Customers') {
        this.customersChart();
      } else if (value === 'People') {
        this.peopleChart();
      } else if (value === 'Revenue Per Customer') {
        this.revenuePerCustomerChart();
      } else if (value === 'Orders Per Customer') {
        this.ordersPerCustomerChart();
      } else if (value === 'New Customer') {
        this.newCustomerChart();
      } else if (value === 'Returning Customers') {
        this.returningCustomerChart();
      } else if (value === 'Average Order Value') {
        this.averageOrderValueChart();
      } else {
        return this.$toast.error(
          'Error',
          'There Has Been An Error Loading The Graph!'
        );
      }

      this.getDatasets();
    },
    assignDate() {
      this.chartData = this.getDatesArray.reduce((acc, current) => {
        acc[current] = [];
        return acc;
      }, {});
    },
    getDatasets() {
      // get the Data for the Chart
      this.datasetForChart = {
        labels: this.chartXAxis,
        datasets: [
          {
            label: this.selectedData,
            backgroundColor: '#e8f7fb',
            borderWidth: '2',
            borderColor: 'rgba(88,101,255,1)',
            data: this.chartYAxis,
          },
        ],
        options: {
          responsive: true,
          maintainAspectRatio: false,
          // tooltips: false,
          scales: {
            xAxes: [
              {
                display: true,
                ticks: {
                  autoSkip: true,
                  maxTicksLimit: 15.1,
                },
                scaleLabel: {
                  display: true,
                  labelString: `Date (in ${this.dateGroup})`,
                },
              },
            ],
            yAxes: [
              {
                ticks: {
                  beginAtZero: 'true',
                },
                display: true,
                scaleLabel: {
                  display: true,
                  labelString: this.setYAxis,
                },
              },
            ],
          },
        },
      };
    },

    getTimestamp(date) {
      return Date.parse(date) / 1000;
    },
  },
};
</script>

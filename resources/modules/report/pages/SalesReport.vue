<template>
  <div class="row justify-content-start">
    <div class="col-md filter-wrapper">
      <BaseFormGroup label="Type: ">
        <BaseFormSelect
          v-model="showType"
          label-key="name"
          value-key="value"
          :options="showSelection"
        />
      </BaseFormGroup>
    </div>
    <div class="col-lg-4 filter-wrapper">
      <BaseFormGroup label="Date Range: ">
        <BaseDatePicker
          v-model="dateRange"
          :type="datePickerType"
          :disabled-date="disableAfterToday"
          placeholder="Select date range"
          range
          value-type="date"
          @change="handleDatesChange"
          @clear="clearDates"
        />
      </BaseFormGroup>
    </div>
    <div
      v-if="showType === 'salesOverTime'"
      class="col-md filter-wrapper"
    >
      <BaseFormGroup label="Group By: ">
        <BaseFormSelect
          v-model="dateGroup"
          label-key="name"
          value-key="value"
          :options="dateGroupSelection"
        />
      </BaseFormGroup>
    </div>
  </div>
  <div v-if="showType === 'salesOverTime'">
    <BaseDatatable
      v-if="dateGroup === 'day'"
      title="sale"
      no-header
      no-action
      no-sorting
      no-hover
      :table-headers="tableHeadersSOTbyDay"
      :table-datas="dailySalesSummary"
    />
    <BaseDatatable
      v-else
      title="sale"
      no-header
      no-action
      no-sorting
      no-hover
      :table-headers="tableHeadersSOTbyMonth"
      :table-datas="monthlySalesSummary"
    />
  </div>
  <div v-else>
    <BaseDatatable
      title="product"
      no-header
      no-action
      no-sorting
      no-hover
      :table-headers="tableHeadersSBP"
      :table-datas="productSalesSummary"
    />
  </div>
</template>

<script>
import dayjs from 'dayjs';
import { getFirstDay, getLastDay } from '@shared/lib/date.js';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

const startDate = dayjs().subtract(6, 'day').$d;
const endDate = dayjs().$d;

export default {
  name: 'SalesReport',

  mixins: [specialCurrencyCalculationMixin],

  props: ['allOrders', 'defaultCurrency', 'currencies', 'allProducts'],

  data() {
    return {
      tableHeadersSOTbyDay: [
        {
          name: 'Day',
          key: 'date',
          width: '20%',
        },
        {
          name: 'Orders',
          key: 'order_count',
        },
        {
          name: `Gross Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'gross_sales',
        },
        {
          name: `Discounts (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'discounts',
        },
        {
          name: `Returns (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'return_amount',
        },
        {
          name: `Net Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'net_sales',
        },
        {
          name: `Shipping (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'shipping',
        },
        {
          name: `Taxes (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'taxes',
        },
        {
          name: `Total Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'total_amount',
        },
      ],
      tableHeadersSOTbyMonth: [
        {
          name: 'Month',
          key: 'month',
          width: '20%',
        },
        {
          name: 'Orders',
          key: 'order_count',
        },
        {
          name: `Gross Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'gross_sales',
        },
        {
          name: `Discounts (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'discounts',
        },
        {
          name: `Returns (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'return_amount',
        },
        {
          name: `Net Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'net_sales',
        },
        {
          name: `Shipping (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'shipping',
        },
        {
          name: `Taxes (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'taxes',
        },
        {
          name: `Total Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'total_amount',
        },
      ],
      tableHeadersSBP: [
        {
          name: 'Name',
          key: 'name',
          width: '20%',
        },
        {
          name: 'Net Qty',
          key: 'netQty',
        },
        {
          name: `Gross Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'grossSales',
        },
        {
          name: `Discounts (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'discount',
        },
        {
          name: `Returns (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'return',
        },
        {
          name: `Net Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'netSales',
        },
        {
          name: `Taxes (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'taxes',
        },
        {
          name: `Total Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'totalSales',
        },
      ],
      salesSummaryInitialValues: {
        order_count: 0,
        gross_sales: 0.0,
        shipping: 0.0,
        taxes: 0.0,
        return_amount: 0.0,
        total_amount: 0.0,
        discounts: 0.0,
        net_sales: 0.0,
      },
      dateRange: [startDate, endDate],
      defaultDate: [new Date(String(startDate)), new Date(String(endDate))],
      dateGroup: 'day',
      selectedData: 'Total Sales',
      showType: 'salesOverTime',
      showSelection: [
        {
          name: 'Sales Over Time',
          value: 'salesOverTime',
        },
        {
          name: 'Sales By Product',
          value: 'salesByProduct',
        },
      ],
      dateGroupSelection: [
        {
          name: 'Day',
          value: 'day',
        },
        {
          name: 'Month',
          value: 'month',
        },
      ],
    };
  },

  computed: {
    datePickerType() {
      return this.dateGroup === 'month' ? 'month' : 'date';
    },

    getProductName() {
      let getAllOrderProduct = [];
      return this.datesBetweenRange.reduce((current, date) => {
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.date === date
        );
        if (ordersAtThatDay.length !== 0) {
          getAllOrderProduct = ordersAtThatDay.reduce((acc, order) => {
            const orderDetail = order.order_details;
            const getPerOrderProduct = orderDetail.reduce((cur, item) => {
              return [...cur, item.product_name];
            }, []);
            return [...getAllOrderProduct, ...getPerOrderProduct, ...acc];
          }, []);
        }
        return [...new Set(getAllOrderProduct)];
      }, []);
    },

    getMonth() {
      let monthArr = [];
      const betweenMonth = this.datesBetweenRange.reduce((current, date) => {
        const monthInValue = date.substring(0, 7);
        monthArr = [...monthArr, monthInValue];
        return [...new Set(monthArr)];
      });
      let monthInAlphabetArr = [];

      return betweenMonth.reduce((acc, monthWithYear) => {
        const year = monthWithYear.substring(0, 4);
        const month = monthWithYear.substring(5, 7);
        let monthInAlphabet = '';
        switch (month) {
          case '01':
            monthInAlphabet = 'Jan';
            break;
          case '02':
            monthInAlphabet = 'Feb';
            break;
          case '03':
            monthInAlphabet = 'Mar';
            break;
          case '04':
            monthInAlphabet = 'Apr';
            break;
          case '05':
            monthInAlphabet = 'May';
            break;
          case '06':
            monthInAlphabet = 'Jun';
            break;
          case '07':
            monthInAlphabet = 'Jul';
            break;
          case '08':
            monthInAlphabet = 'Aug';
            break;
          case '09':
            monthInAlphabet = 'Sep';
            break;
          case '10':
            monthInAlphabet = 'Oct';
            break;
          case '11':
            monthInAlphabet = 'Nov';
            break;
          case '12':
            monthInAlphabet = 'Dec';
            break;
          default:
            break;
        }

        const monthYear = `${monthInAlphabet} ${year}`;
        monthInAlphabetArr = [...monthInAlphabetArr, monthYear];
        return monthInAlphabetArr;
      }, 0);
    },

    monthlySalesSummary() {
      const c = (val) => {
        return this.specialCurrencyCalculation(val);
      };
      const monthlySales = this.getMonth.reduce((acc, month) => {
        const data = {
          month,
          order_count: this.getMonthlyOrderCount(month),
          gross_sales: c(this.getMonthlyGrossSales(month)),
          shipping: c(this.getMonthlyShipping(month)),
          taxes: c(this.getMonthlyTaxes(month)),
          return_amount: c(this.getMonthlyReturns(month)),
          total_amount: c(this.getMonthlyTotalSales(month)),
          discounts: c(this.getMonthlyDiscount(month)),
          net_sales: c(this.getMonthlyNetSales(month)),
        };
        return [...acc, data];
      }, []);

      const getTotal = (key) =>
        monthlySales.reduce((acc, curr) => acc + parseFloat(curr[key]), 0);

      const totalSalesSummary = {
        month: 'Summary',
        order_count: getTotal('order_count'),
        gross_sales: c(getTotal('gross_sales')),
        shipping: c(getTotal('shipping')),
        taxes: c(getTotal('taxes')),
        return_amount: c(getTotal('return_amount')),
        total_amount: c(getTotal('total_amount')),
        discounts: c(getTotal('discounts')),
        net_sales: c(getTotal('net_sales')),
      };
      return [totalSalesSummary, ...monthlySales];
    },

    datesBetweenRange() {
      let dates = [];
      let theDate = dayjs(this.dateRange[0]).format('YYYY-MM-DD');
      while (theDate < dayjs(this.dateRange[1]).format('YYYY-MM-DD')) {
        dates = [theDate, ...dates];
        theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
      }
      return [dayjs(this.dateRange[1]).format('YYYY-MM-DD'), ...dates];
    },

    dailySalesSummary() {
      const dailySalesSummary = this.datesBetweenRange.reduce((acc, date) => {
        if (!acc[date]) acc[date] = [];
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const salesSummary = ordersAtThatDay.reduce(
          (col, order) => {
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
              let productActualGross = parseFloat(product.total);
              if (order.is_product_include_tax)
                productActualGross = product.is_taxable
                  ? taxable(product.total, order.tax_rate)
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

            col = {
              order_count: (col.order_count += 1),
              gross_sales: (col.gross_sales += grossSales),
              // shipping : acc.shipping += divideByExchangeRate(order.shipping),
              // taxes : acc.taxes += divideByExchangeRate(order.taxes),
              // return_amount : acc.return_amount += divideByExchangeRate(order.refunded),
              // total_amount : acc.total_amount += divideByExchangeRate(order.total)
              shipping: (col.shipping += shippingFee),
              taxes: (col.taxes += parseFloat(order.taxes)),
              return_amount: (col.return_amount += parseFloat(order.refunded)),
              total_amount: (col.total_amount +=
                grossSales -
                refundAmt -
                discount +
                parseFloat(order.taxes) +
                shippingFee),
              discounts: (col.discounts += discount),
              net_sales: (col.net_sales += grossSales - refundAmt - discount),
            };

            return col;
          },
          { ...this.salesSummaryInitialValues }
        );
        const data = {
          date,
          order_count: salesSummary.order_count,
          gross_sales: this.specialCurrencyCalculation(
            salesSummary.gross_sales
          ),
          shipping: this.specialCurrencyCalculation(salesSummary.shipping),
          taxes: this.specialCurrencyCalculation(salesSummary.taxes),
          return_amount: this.specialCurrencyCalculation(
            salesSummary.return_amount
          ),
          total_amount: this.specialCurrencyCalculation(
            salesSummary.total_amount
          ),
          discounts: this.specialCurrencyCalculation(salesSummary.discounts),
          net_sales: this.specialCurrencyCalculation(salesSummary.net_sales),
        };
        return [...acc, data];
      }, []);

      const getTotal = (key) =>
        dailySalesSummary.reduce((acc, curr) => acc + parseFloat(curr[key]), 0);

      const totalSalesSummary = {
        date: 'Summary',
        order_count: getTotal('order_count'),
        gross_sales: this.specialCurrencyCalculation(getTotal('gross_sales')),
        shipping: this.specialCurrencyCalculation(getTotal('shipping')),
        taxes: this.specialCurrencyCalculation(getTotal('taxes')),
        return_amount: this.specialCurrencyCalculation(
          getTotal('return_amount')
        ),
        total_amount: this.specialCurrencyCalculation(getTotal('total_amount')),
        discounts: this.specialCurrencyCalculation(getTotal('discounts')),
        net_sales: this.specialCurrencyCalculation(getTotal('net_sales')),
      };

      return [totalSalesSummary, ...dailySalesSummary];
    },

    productSalesSummary() {
      const c = (val) => {
        return this.specialCurrencyCalculation(val);
      };
      const productSales = this.getProductName.reduce((acc, productName) => {
        const data = {
          name: productName,
          netQty: this.getProductSoldQty(productName),
          grossSales: c(this.getProductGrossSales(productName)),
          discount: c(this.getProductDiscount(productName)),
          return: c(this.getProductReturn(productName)),
          netSales: c(this.getProductNetSales(productName)),
          taxes: c(this.getProductTaxes(productName)),
          totalSales: c(this.getProductTotalSales(productName)),
        };
        return [...acc, data];
      }, []);

      const getTotal = (key) =>
        productSales.reduce((acc, curr) => acc + parseFloat(curr[key]), 0);

      const totalSalesSummary = {
        name: 'Summary',
        netQty: getTotal('netQty'),
        grossSales: c(getTotal('grossSales')),
        discount: c(getTotal('discount')),
        return: c(getTotal('return')),
        netSales: c(getTotal('netSales')),
        taxes: c(getTotal('taxes')),
        totalSales: c(getTotal('totalSales')),
      };

      return [totalSalesSummary, ...productSales];
    },
  },

  watch: {
    dateRange: {
      deep: true,
      handler(newValue) {
        const isAllNull = newValue.every((e) => e === null);
        if (!isAllNull) return;
        this.dateRange = [startDate, endDate];
      },
    },
    showType(val) {
      if (val === 'salesByProduct') {
        this.dateGroup = 'day';
      }
    },
  },

  methods: {
    clearDates() {
      this.dateRange = [startDate, endDate];
      this.$refs.defaultDate.currentValue = [
        new Date(String(startDate)),
        new Date(String(endDate)),
      ];
    },

    handleDatesChange(newDates) {
      const startingDate = newDates[0];
      let endingDate = newDates[1];

      if (this.dateGroup === 'month' && endingDate?.getDate() === 1) {
        endingDate = getLastDay(endingDate);
      }

      this.dateRange = [startingDate, endingDate];
    },

    handleDateGroupChange() {
      const startingDate = this.dateRange[0];
      if (this.dateGroup === 'month' && startingDate) {
        this.dateRange = [
          getFirstDay(startingDate),
          getLastDay(this.dateRange[1]),
        ];
      }
    },

    disableAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0));
    },

    toTwoDecimal(param) {
      return param.toFixed(2);
    },

    filteredByDate(datas) {
      return datas.filter((data) => {
        const date = data.convertedDateTime;
        const start = dayjs(this.dateRange[0]).format('YYYY-MM-DD');
        const end = dayjs(this.dateRange[1]).format('YYYY-MM-DD');
        return (date >= start && date <= end) || this.dateRange.length === 0;
      });
    },

    monthIntoNumber(monthWithYear) {
      const year = monthWithYear.substring(4, 8);
      const month = monthWithYear.substring(0, 3);
      let monthInNumber = '';

      switch (month) {
        case 'Jan':
          monthInNumber = '01';
          break;
        case 'Feb':
          monthInNumber = '02';
          break;
        case 'Mar':
          monthInNumber = '03';
          break;
        case 'Apr':
          monthInNumber = '04';
          break;
        case 'May':
          monthInNumber = '05';
          break;
        case 'Jun':
          monthInNumber = '06';
          break;
        case 'Jul':
          monthInNumber = '07';
          break;
        case 'Aug':
          monthInNumber = '08';
          break;
        case 'Sep':
          monthInNumber = '09';
          break;
        case 'Oct':
          monthInNumber = '10';
          break;
        case 'Nov':
          monthInNumber = '11';
          break;
        case 'Dec':
          monthInNumber = '12';
          break;
        default:
          break;
      }

      const monthYear = `${year}-${monthInNumber}`;
      return monthYear;
    },

    getProductSoldQty(product) {
      const order = this.filteredByDate(this.allOrders);
      const getTotalSoldQty = order.reduce((current, item) => {
        const getPerOrderSoldQty = item.order_details.reduce((acc, count) => {
          if (count.product_name !== product) return acc + 0;
          const total = parseFloat(count.quantity);
          return total + acc;
        }, 0);
        return getPerOrderSoldQty + current;
      }, 0);
      return getTotalSoldQty;
    },

    getProductGrossSales(product) {
      const orderAtFilteredDay = this.filteredByDate(this.allOrders);
      const taxable = (val, tax) =>
        parseFloat(val) / (1 + parseFloat(tax) / 100);
      const getTotalGrossSales = orderAtFilteredDay.reduce((current, order) => {
        const { currency } = order;
        const selectedCurrency = this.currencies.find(
          (currencyType) => currencyType.currency === currency
        );
        const exchangeRate =
          currency === this.defaultCurrency
            ? 1
            : parseFloat(selectedCurrency.exchangeRate);

        const grossSales = order.order_details.reduce((gross, item) => {
          if (item.product_name !== product) return gross + 0;
          let productActualGross = parseFloat(item.total);
          if (order.is_product_include_tax)
            productActualGross = item.is_taxable
              ? taxable(item.total, order.tax_rate)
              : parseFloat(item.total);
          return gross + productActualGross;
        }, 0);
        return grossSales / exchangeRate + current;
      }, 0);
      return getTotalGrossSales;
    },

    getProductDiscount(product) {
      const orderAtFilteredDay = this.filteredByDate(this.allOrders);
      const taxable = (val, tax) =>
        parseFloat(val) / (1 + parseFloat(tax) / 100);
      const orderDiscountRatio = (sellingPrice, orderGross, discountVal) =>
        (sellingPrice / orderGross) * discountVal;
      const orderDiscountPercentage = (orderGross, discountVal, productGross) =>
        (productGross * (orderGross / discountVal)) / 100;
      const getTotalDiscount = orderAtFilteredDay.reduce((current, order) => {
        const { currency } = order;
        const selectedCurrency = this.currencies.find(
          (currencyType) => currencyType.currency === currency
        );
        const exchangeRate =
          currency === this.defaultCurrency
            ? 1
            : parseFloat(selectedCurrency.exchangeRate);

        const totalDiscount = order.order_details.reduce(
          (allDiscount, item) => {
            if (item.product_name === product) {
              let discount = 0;
              let productActualGross = parseFloat(item.total);
              if (order.is_product_include_tax)
                productActualGross = item.is_taxable
                  ? taxable(item.total, order.tax_rate)
                  : parseFloat(item.total);
              // console.log(productActualGross)
              if (order.order_discount.length > 0) {
                discount = order.order_discount.reduce((all, dis) => {
                  let discountVal = 0;
                  if (dis.promotion_category === 'Product') {
                    discountVal = parseFloat(item.discount) / 100;
                  } else if (
                    dis.promotion_category === 'Order' &&
                    dis.discount_type === 'percentage'
                  ) {
                    discountVal = orderDiscountPercentage(
                      parseFloat(order.subtotal),
                      parseFloat(dis.discount_value) / 100,
                      productActualGross
                    );
                  } else if (
                    dis.promotion_category === 'Order' &&
                    dis.discount_type === 'fixed'
                  ) {
                    discountVal = orderDiscountRatio(
                      productActualGross,
                      parseFloat(order.subtotal),
                      parseFloat(dis.discount_value) / 100
                    );
                  } else if (
                    dis.promotion_category === 'Order' &&
                    dis.discount_type === null
                  ) {
                    discountVal = 0;
                  }

                  return discountVal + all;
                }, 0);
              }
              return discount + allDiscount;
            }
            return 0 + allDiscount;
          },
          0
        );
        return totalDiscount / exchangeRate + current;
      }, 0);
      return getTotalDiscount;
    },

    getProductReturn(product) {
      const orderAtFilteredDay = this.filteredByDate(this.allOrders);
      let returnAmt = 0;
      const getTotalReturn = orderAtFilteredDay.reduce((current, order) => {
        const { currency } = order;
        const selectedCurrency = this.currencies.find(
          (currencyType) => currencyType.currency === currency
        );
        const exchangeRate =
          currency === this.defaultCurrency
            ? 1
            : parseFloat(selectedCurrency.exchangeRate);
        if (!parseFloat(order.refunded)) return current + 0;
        const getReturnAmt = order.order_details.reduce((acc, item) => {
          if (item.product_name !== product) return acc + 0;
          returnAmt =
            parseFloat(item.total) -
            parseFloat(item.discount) / 100 +
            parseFloat(item.tax);
          return acc + returnAmt;
        }, 0);
        return getReturnAmt / exchangeRate + current;
      }, 0);
      return getTotalReturn;
    },

    getProductTaxes(product) {
      const orderAtFilteredDay = this.filteredByDate(this.allOrders);
      const getTotalTaxes = orderAtFilteredDay.reduce((current, order) => {
        const { currency } = order;
        const selectedCurrency = this.currencies.find(
          (currencyType) => currencyType.currency === currency
        );
        const exchangeRate =
          currency === this.defaultCurrency
            ? 1
            : parseFloat(selectedCurrency.exchangeRate);
        const getTaxes = order.order_details.reduce((acc, item) => {
          if (item.product_name !== product) return acc + 0;
          return parseFloat(item.tax) + acc;
        }, 0);
        return getTaxes / exchangeRate + current;
      }, 0);
      return getTotalTaxes;
    },

    getProductNetSales(product) {
      const grossSales = this.getProductGrossSales(product);
      const totalDiscount = this.getProductDiscount(product);
      const totalReturn = this.getProductReturn(product);

      return grossSales - totalDiscount - totalReturn;
    },

    getProductTotalSales(product) {
      const netSales = this.getProductNetSales(product);
      const totalTaxes = this.getProductTaxes(product);
      return netSales + totalTaxes;
    },

    getMonthlyOrderCount(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        return ordersAtThatDay.length + current;
      }, 0);
    },

    getMonthlyGrossSales(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      const taxable = (val, tax) =>
        parseFloat(val) / (1 + parseFloat(tax) / 100);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const dailyGrossSum = ordersAtThatDay.reduce((acc, order) => {
          const grossSales = order.order_details.reduce((gross, product) => {
            let productActualGross = parseFloat(product.total);
            if (order.is_product_include_tax)
              productActualGross = product.is_taxable
                ? taxable(product.total, order.tax_rate)
                : parseFloat(product.total);
            return gross + productActualGross;
          }, 0);
          return acc + grossSales;
        }, 0);
        return current + dailyGrossSum;
      }, 0);
    },

    getMonthlyDiscount(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const dailyDiscount = ordersAtThatDay.reduce((acc, order) => {
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
            let productActualGross = parseFloat(product.total);
            if (order.is_product_include_tax)
              productActualGross = product.is_taxable
                ? taxable(product.total, order.tax_rate)
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
          return discount + acc;
        }, 0);
        return current + dailyDiscount;
      }, 0);
    },

    getMonthlyReturns(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const dailyReturns = ordersAtThatDay.reduce((acc, order) => {
          return acc + parseFloat(order.refunded);
        }, 0);
        return current + dailyReturns;
      }, 0);
    },

    getMonthlyNetSales(monthly) {
      const monthlyGross = this.getMonthlyGrossSales(monthly);
      const monthlyDiscount = this.getMonthlyDiscount(monthly);
      const monthlyReturns = this.getMonthlyReturns(monthly);

      return monthlyGross - monthlyDiscount - monthlyReturns;
    },

    getMonthlyShipping(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const dailyShipping = ordersAtThatDay.reduce((acc, order) => {
          const shippingFee = order.is_product_include_tax
            ? parseFloat(order.shipping) - parseFloat(order.shipping_tax)
            : parseFloat(order.shipping);
          return acc + shippingFee;
        }, 0);
        return current + dailyShipping;
      }, 0);
    },

    getMonthlyTaxes(monthly) {
      const monthYearInNum = this.monthIntoNumber(monthly);
      return this.datesBetweenRange.reduce((current, date) => {
        if (monthYearInNum !== date.substring(0, 7)) return current + 0;
        const ordersAtThatDay = this.allOrders.filter(
          (order) => order.convertedDateTime === date
        );
        const dailyTaxes = ordersAtThatDay.reduce((acc, order) => {
          return acc + order.taxes;
        }, 0);
        return current + dailyTaxes;
      }, 0);
    },

    getMonthlyTotalSales(monthly) {
      const monthlyNetSales = this.getMonthlyNetSales(monthly);
      const monthlyShipping = this.getMonthlyShipping(monthly);
      const monthlyTaxes = this.getMonthlyTaxes(monthly);

      return monthlyNetSales + monthlyShipping + monthlyTaxes;
    },
  },
};
</script>

<style scoped>
@media screen and (min-width: 416px) {
  .filter-wrapper {
    width: 300px;
    flex: none;
  }
}
</style>

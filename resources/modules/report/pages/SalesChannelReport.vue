<template>
  <BaseFormGroup label="Date Range: " col="lg-4">
    <BaseDatePicker
      v-model="dateRange"
      placeholder="Select date range"
      range
      value-type="date"
      :disabled-date="disableAfterToday"
      @change="handleDatesChange"
      @clear="clearDates"
    />
  </BaseFormGroup>

  <BaseDatatable
    title="sale"
    no-header
    no-action
    no-hover
    :table-headers="tableHeaders"
    :table-datas="salesChannelData"
  />
</template>

<script>
import dayjs from 'dayjs';
import { getFirstDay, getLastDay } from '@shared/lib/date.js';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

const startDate = dayjs().subtract(6, 'days').$d;
const endDate = dayjs().$d;

export default {
  name: 'SalesChannelReport',
  mixins: [specialCurrencyCalculationMixin],

  props: {
    allOrders: {
      type: Array,
      required: true,
    },
    defaultCurrency: {
      type: String,
      required: true,
    },
    currencies: {
      type: Array,
      required: true,
    },
  },

  data() {
    return {
      tableHeaders: [
        {
          name: 'Sales Channels',
          key: 'salesChannel',
          width: '60%',
        },
        {
          name: 'Customers',
          key: 'custCount',
        },
        {
          name: 'Orders',
          key: 'order',
        },
        {
          name: `AOV (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'aov',
        },
        {
          name: `Total Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'totalSales',
        },
      ],

      dateRange: [],
      salesChannel: [
        'Funnel',
        'Online Store',
        // 'Mini Store',
        // 'Shopee',
        'Manual',
      ],
    };
  },

  computed: {
    datesBetweenRange() {
      let dates = [];
      // const [startDate, endDate] = this.dateRange;
      let theDate = dayjs(this.dateRange[0]).format('YYYY-MM-DD');
      while (theDate < dayjs(this.dateRange[1]).format('YYYY-MM-DD')) {
        dates = [theDate, ...dates];
        theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
      }
      return [dayjs(endDate).format('YYYY-MM-DD'), ...dates];
    },

    salesChannelData() {
      const salesChannelData = this.salesChannel.reduce((acc, sc) => {
        const data = {
          salesChannel: sc,
          custCount: this.getCustCount(sc),
          returningCust: this.getReturningCust(sc),
          order: this.getOrder(sc),
          orderCust: this.getOrderDivCust(sc),
          aov: this.specialCurrencyCalculation(this.getAOV(sc)),
          salesCust: this.specialCurrencyCalculation(this.getSalesDivCust(sc)),
          totalSales: this.specialCurrencyCalculation(this.getTotalSales(sc)),
          tbo: this.getTBO(sc),
        };
        return [...acc, data];
      }, []);
      return salesChannelData;
    },
  },

  methods: {
    clearDates() {
      this.dateRange = [];
    },

    disableAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0));
    },

    handleDatesChange(newDates) {
      const startingDate = newDates[0];
      const endingDate = newDates[1];
      this.dateRange = [startingDate, endingDate];
    },

    filteredByDate(datas) {
      return datas.filter((data) => {
        const convertedDate = new Date(data.convertedDateTime * 1000);
        const date = dayjs(convertedDate).format('YYYY-MM-DD');
        const start = dayjs(this.dateRange[0]).format('YYYY-MM-DD');
        const end = dayjs(this.dateRange[1]).format('YYYY-MM-DD');
        return (date >= start && date <= end) || this.dateRange.length === 0;
      });
    },

    getLastDate(datas) {
      return datas.filter((data) => {
        const convertedDate = new Date(data.convertedDateTime * 1000);
        const date = dayjs(convertedDate).format('YYYY-MM-DD');
        const end = dayjs(this.dateRange[1]).format('YYYY-MM-DD');
        return date <= end || this.dateRange.length === 0;
      });
    },

    getCustCount(storeType) {
      let accumulateCustCount = [];
      const filteredOrder = this.filteredByDate(this.allOrders);
      const orderAtThatStore = filteredOrder.filter(
        (order) => order.acquisition_channel === storeType
      );

      if (orderAtThatStore.length > 0) {
        accumulateCustCount = orderAtThatStore.reduce((acc, current) => {
          acc = [...acc, current.processed_contact_id];
          return acc;
        }, []);
      }
      return [...new Set(accumulateCustCount)].length;
    },

    getReturningCustArr(storeType) {
      let accumulateCustCount = [];
      const filteredOrder = this.filteredByDate(this.allOrders);
      const orderAtThatStore = filteredOrder.filter(
        (order) => order.acquisition_channel === storeType
      );

      if (orderAtThatStore.length > 0) {
        accumulateCustCount = orderAtThatStore.reduce((acc, current) => {
          acc = [...acc, current.processed_contact_id];
          return acc;
        }, []);
      }

      const findDuplicates = (arr) =>
        arr.filter((item, index) => arr.indexOf(item) !== index);
      const duplicateCust = [...new Set(findDuplicates(accumulateCustCount))];
      return duplicateCust;
    },

    getReturningCust(storeType) {
      const duplicateCust = this.getReturningCustArr(storeType);
      return duplicateCust.length > 0
        ? ((duplicateCust.length / this.getCustCount(storeType)) * 100).toFixed(
            2
          )
        : 0;
    },

    getOrder(storeType) {
      const filteredOrder = this.filteredByDate(this.allOrders);
      const orderAtThatStore = filteredOrder.filter(
        (order) => order.acquisition_channel === storeType
      );
      return orderAtThatStore.length > 0 ? orderAtThatStore.length : 0;
    },

    getOrderDivCust(storeType) {
      const orderNum = this.getOrder(storeType);
      const custCount = this.getCustCount(storeType);

      return Number.isNaN(orderNum / custCount)
        ? 0
        : (orderNum / custCount).toFixed(2);
    },

    getTotalSales(storeType) {
      const filteredOrder = this.filteredByDate(this.allOrders);
      const orderAtThatStore = filteredOrder.filter(
        (order) => order.acquisition_channel === storeType
      );
      let totalSales = 0;
      if (orderAtThatStore.length !== 0) {
        totalSales = orderAtThatStore.reduce((acc, order) => {
          const { currency } = order;
          const selectedCurrency = this.currencies.find(
            (currencyType) => currencyType.currency === currency
          );
          const exchangeRate =
            currency === this.defaultCurrency
              ? 1
              : parseFloat(selectedCurrency.exchangeRate);

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

          const totalSalesPerOrder =
            grossSales -
            discount -
            refundAmt +
            shippingFee +
            parseFloat(order.taxes);

          return acc + totalSalesPerOrder / exchangeRate;
        }, 0);
      }
      return totalSales;
    },

    getAOV(storeType) {
      const totalSales = this.getTotalSales(storeType);
      const orderCount = this.getOrder(storeType);

      return Number.isNaN(totalSales / orderCount)
        ? 0
        : (totalSales / orderCount).toFixed(2);
    },

    getSalesDivCust(storeType) {
      const totalSales = this.getTotalSales(storeType);
      const custCount = this.getCustCount(storeType);

      return Number.isNaN(totalSales / custCount)
        ? 0
        : (totalSales / custCount).toFixed(2);
    },

    getTBO(storeType) {
      const filteredOrder = this.getLastDate(this.allOrders);
      const returnCustID = this.getReturningCustArr(storeType);
      let TBODay = 0;
      let TBOHour = 0;
      let totalTBO = 0;

      const totalReturnCustTBO = returnCustID.reduce((total, cust) => {
        const orderAtThatStore = filteredOrder.filter(
          (order) =>
            order.acquisition_channel === storeType &&
            order.processed_contact_id === cust
        );

        let accumulateTBO = 0;

        for (let i = 0; i < orderAtThatStore.length; i++) {
          if (i !== orderAtThatStore.length - 1) {
            const prevTimeStamp =
              orderAtThatStore[i + 1].convertedDateTime * 1000;
            const curTimeStamp = orderAtThatStore[i].convertedDateTime * 1000;

            const timebetween = curTimeStamp - prevTimeStamp;
            const oneDay = 1000 * 60 * 60 * 24;
            const dayBetween = timebetween / oneDay;

            accumulateTBO = dayBetween + accumulateTBO;
          }
        }
        return total + accumulateTBO / (orderAtThatStore.length - 1);
      }, 0);

      totalTBO = totalReturnCustTBO / returnCustID.length;
      TBODay = Math.floor(totalTBO);
      TBOHour = Math.ceil((totalTBO - TBODay) * 24);

      if (totalTBO < 1) {
        TBOHour = Math.ceil(totalTBO * 24);
      }
      return Number.isNaN(totalTBO) ? 0 : `${TBODay}d ${TBOHour}h`;
    },
  },
};
</script>

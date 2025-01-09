<template>
  <BaseFormGroup
    label="Date Range: "
    class="w-300px"
  >
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
    title="funnel"
    no-header
    no-hover
    no-edit-action
    no-delete-action
    :table-headers="tableHeaders"
    :table-datas="funnelSalesSummary"
  >
    <template #action-options="{ row: { refKey } }">
      <BaseDropdownOption
        text="View"
        :link="`/report/funnel/${refKey}`"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween'

dayjs.extend(isBetween)

export default {
  props: {
    funnels: {
      type: Array,
      default: () => [],
    },
    currency: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        {
          name: 'Name',
          key: 'name',
          width: '50%',
        },
        {
          name: 'Opted In',
          key: 'optIn',
        },
        {
          name: 'Orders',
          key: 'orderCount',
        },
        {
          name: 'AOV',
          key: 'aov',
        },
        // {
        //   name: 'Sales/Cust.',
        //   key: 'salesDivCust',
        // },
        {
          name: 'Total Sales',
          key: 'totalSales',
        },
      ],
      dateRange: [],
    };
  },

  computed: {
    funnelSalesSummary() {
      const curFunnel = this.funnels.reduce((total, funnel) => {
        let custArr = [];
        const [startDate = '2019-01-01', endDate = dayjs()] = this.dateRange ?? [];
        const optInCount = Object.entries(funnel.optins).reduce((acc, [date, submissionCount]) => {
          if(dayjs(date).isBetween(dayjs(startDate), dayjs(endDate))) {
            acc += submissionCount ?? 0;
          }
          return acc;
        }, 0);

        const orderWithinRange = this.dateRange.length
          ? funnel.orders.filter((order) =>
              this.dateRange.includes(
                order.convertedDateTime.substring(0, 10)
              )
            )
          : funnel.orders;

        const funnelDefaultCurrency = funnel.currency;

        const totalSales = orderWithinRange.reduce((acc, order) => {
          const exchangeRate =
            order.currency === funnelDefaultCurrency
              ? 1
              : parseFloat(order.exchange_rate);

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

          custArr = [...custArr, order.processed_contact_id];
          return acc + totalSalesPerOrder / exchangeRate;
        }, 0);

        const custCount = [...new Set(custArr)].length;
        const aovPerFunnel = totalSales / orderWithinRange.length;
        const salesDivCust = totalSales / custCount;

        const currency = funnel.currency === 'MYR' ? 'RM' : funnel.currency;

        const data = {
          name: funnel.funnel_name,
          optIn: optInCount,
          orderCount: orderWithinRange.length,
          aov:
            currency +
            (Number.isNaN(aovPerFunnel) ? 0 : aovPerFunnel.toFixed(2)),
          salesDivCust:
            currency +
            (Number.isNaN(salesDivCust) ? 0 : salesDivCust.toFixed(2)),
          totalSales: currency + (totalSales ? totalSales.toFixed(2) : 0),
          refKey: funnel.reference_key,
        };
        return [...total, data];
      }, []);
      return curFunnel;
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
  },
};
</script>

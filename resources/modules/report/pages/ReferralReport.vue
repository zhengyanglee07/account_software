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
    title="referral campaign"
    no-header
    no-hover
    no-delete-action
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableDatas"
  >
    <template #action-options="{ row: { viewLink } }">
      <BaseDropdownOption
        text="View"
        :link="viewLink"
      />
    </template>
  </BaseDatatable>
</template>

<script>
import dayjs from 'dayjs';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import cloneDeep from 'lodash/cloneDeep';

const startDate = '2019-01-01';
const endDate = dayjs().$d;

export default {
  name: 'ReferralReport',
  mixins: [specialCurrencyCalculationMixin],

  props: {
    campaigns: {
      type: Array,
      required: true,
    },
    referralCampaignsTitle: {
      type: Array,
      required: true,
    },
    defaultCurrency: {
      type: String,
      requried: true,
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
          name: 'Campaign Name',
          key: 'name',
          width: '30%',
        },
        {
          name: 'Particapants',
          key: 'referralCount',
        },
        {
          name: 'Referred Clicks',
          key: 'clicks',
        },
        {
          name: 'Referred Leads',
          key: 'leads',
        },
        {
          name: 'Referred Customers',
          key: 'customers',
        },
        {
          name: `Referred Sales (${
            this.defaultCurrency === 'MYR' ? 'RM' : this.defaultCurrency
          })`,
          key: 'sales',
        },
      ],

      dateRange: [],
    };
  },

  computed: {
    datesBetweenRange() {
      let dates = [];
      // const [startDate, endDate] = this.dateRange;
      let theDate = dayjs(this.dateRange[0] ?? '2019-01-01').format(
        'YYYY-MM-DD'
      );
      while (
        theDate < dayjs(this.dateRange[1] ?? dayjs()).format('YYYY-MM-DD')
      ) {
        dates = [theDate, ...dates];
        theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
      }
      return [dayjs(endDate).format('YYYY-MM-DD'), ...dates];
    },

    tableDatas() {
      return this.campaigns.map((campaign) => ({
        id: campaign.id,
        name: campaign.title,
        participants:
          this.getFilteredData(campaign.referralProcessedContacts)?.length ?? 0,
        referralCount:
          this.getFilteredData(campaign.referralProcessedContacts)?.length ?? 0,
        clicks: this.getFilteredData(campaign.clicks)?.length ?? 0,
        leads: this.getFilteredData(campaign.leads)?.length ?? 0,
        customers: this.getFilteredData(campaign.customers)?.length ?? 0,
        sales: this.specialCurrencyCalculation(
          this.totalSalesData(campaign.orders)
        ),
        viewLink: `/report/referral/${campaign.reference_key}`,
      }));
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

    getFilteredData(data) {
      const filteredData = data.filter((d) =>
        this.datesBetweenRange.includes(d.convertedDateTime)
      );
      return filteredData;
    },

    totalSalesData(orders) {
      const ordersData = this.getFilteredData(orders);
      const taxable = (val, tax) =>
        parseFloat(val) / (1 + parseFloat(tax) / 100);
      const orderDiscountRatio = (sellingPrice, orderGross, discountVal) => {
        return sellingPrice.reduce((accu, selling) => {
          return accu + (selling / orderGross) * discountVal;
        }, 0);
      };

      const orderDiscountPercentage = (orderGross, discountVal, productGross) =>
        (productGross * (orderGross / discountVal)) / 100;

      const totalSales = ordersData.reduce((acc, order) => {
        let productSellingPrice = [];
        const grossSales = order.order_details.reduce((gross, product) => {
          const taxableProduct = product.is_taxable
            ? taxable(product.total, order.tax_rate)
            : parseFloat(product.total);
          const productActualGross = order.is_product_include_tax
            ? taxableProduct
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

        const totalAmt =
          grossSales -
          refundAmt -
          discount +
          parseFloat(order.taxes) +
          shippingFee;

        acc += totalAmt;
        return acc;
      }, 0);

      return totalSales;
    },
  },
};
</script>

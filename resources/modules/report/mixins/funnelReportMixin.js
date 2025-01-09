export default {
    data() {
        return {
            isLandingPagePresent: true,
            selectedFunnel: [],
            selectedFunnelName: '',
            selectedFunnelKey: '',
            allLandingPage: [],
            formCount: [],
            landingpageOrder: [],
            mergedLandingPages: [],
            funnelCurrency: '',
            exchangeRate: 1,
            funnels: [],
            selectedCurrency: [],
            funnelData: [],
        };
    },

    computed: {
        grossSales() {
            // // await this.combineLandingPageData();
            // console.log(this.mergedLandingPages, 'here');
            if (this.mergedLandingPages !== null) {
                const findFunnel = this.funnels.find((funnel) => {
                    return funnel.id === this.selectedFunnel;
                });
                const grossSales = findFunnel.orders.reduce((total, order) => {
                    return (
                        total +
                        (order.is_product_include_tax
                            ? parseFloat(order.subtotal) /
                              (1 + order.tax_rate / 100)
                            : parseFloat(order.subtotal))
                    );
                }, 0);
                return grossSales.toFixed(2);
            }
            return 0.0;
        },

        formattedGrossSales() {
            const a = parseFloat(this.grossSales);
            // console.log(typeof a, a)
            return Math.abs(a) > 99999
                ? `${Math.sign(a) * (Math.abs(a) / 1000000).toFixed(2)  }M`
                : Math.abs(a) > 999
                ? `${Math.sign(a) * (Math.abs(a) / 1000).toFixed(2)  }K`
                : a.toFixed(2);
        },

        totalPageViews() {
            if (this.mergedLandingPages !== null) {
                const totalPageViews = this.mergedLandingPages.reduce(
                    (total, current) => {
                        return total + current.page_views;
                    },
                    0
                );
                return totalPageViews !== 0 ? totalPageViews : 1;
            }
            return 0;
        },

        totalOrderCount() {
            if (this.mergedLandingPages !== null) {
                const findFunnel = this.funnels.find((funnel) => {
                    return funnel.id === this.selectedFunnel;
                });
                return findFunnel.orders.length;
            }
            return 0;
        },

        totalCustCount() {
            if (this.mergedLandingPages !== null) {
                const findFunnel = this.funnels.find((funnel) => {
                    return funnel.id === this.selectedFunnel;
                });

                const custArr = findFunnel.orders.reduce((total, order) => {
                    return [...total, order.processed_contact_id];
                }, []);
                return [...new Set(custArr)].length;
            }
            return 0;
        },

        getAOV() {
            return (this.totalSales / this.totalOrderCount).toFixed(2);
        },

        totalSalesDivCust() {
            return (this.totalSales / this.totalCustCount).toFixed(2);
        },

        totalSales() {
            if (this.mergedLandingPages !== null) {
                const findFunnel = this.funnels.find((funnel) => {
                    return funnel.id === this.selectedFunnel;
                });
                const totalSales = findFunnel.orders.reduce((total, order) => {
                    const grossSales = order.is_product_include_tax
                        ? parseFloat(order.subtotal) /
                          (1 + order.tax_rate / 100)
                        : parseFloat(order.subtotal);
                    const discount =
                        order.order_discount.length > 0
                            ? order.order_discount.reduce(
                                  (totalDiscount, item) => {
                                      return (
                                          totalDiscount +
                                          parseFloat(item.discount_value)
                                      );
                                  },
                                  0
                              )
                            : 0;

                    const taxes = parseFloat(order.taxes);
                    const refund = parseFloat(order.refunded);
                    const shipping = parseFloat(order.shipping);

                    return (
                        total +
                        (grossSales - discount - refund + taxes + shipping)
                    );
                }, 0);
                return totalSales.toFixed(2);
            }
            return 0.0;
        },

        EarningPerPageViews() {
            if (this.mergedLandingPages !== null) {
                return (this.grossSales / this.totalPageViews).toFixed(2);
            }
            return 0.0;
        },

        formattedEarningPerPageViews() {
            const a = parseFloat(this.EarningPerPageViews);
            // console.log(typeof a, a)
            return Math.abs(a) > 99999
                ? `${Math.sign(a) * (Math.abs(a) / 1000000).toFixed(2)  }M`
                : Math.abs(a) > 999
                ? `${Math.sign(a) * (Math.abs(a) / 1000).toFixed(2)  }K`
                : a.toFixed(2);
        },

        averageValue() {
            if (this.mergedLandingPages !== null) {
                return (this.grossSales / this.totalSalesCount).toFixed(2);
            }
            return 0.0;
        },

        formattedAverageValue() {
            const a = parseFloat(this.averageValue);
            // console.log(typeof a, a)
            return Math.abs(a) > 99999
                ? `${Math.sign(a) * (Math.abs(a) / 1000000).toFixed(2)  }M`
                : Math.abs(a) > 999
                ? `${Math.sign(a) * (Math.abs(a) / 1000).toFixed(2)  }K`
                : a.toFixed(2);
        },
    },

    methods: {
        funnelIsSelected(funnel_id) {
            return new Promise((resolve, reject) => {
                // console.log(funnel_id);
                //   if(this.index===this.funnels.length){
                // 	return;
                //   }
                this.selectedFunnel = funnel_id;
                //   console.log(this.funnels.find(
                // 	(item) => item.id == funnel_id
                //   ), 'heree');
                this.selectedFunnelName = this.funnels.find(
                    (item) => item.id == funnel_id
                ).funnel_name;
                this.selectedFunnelKey = this.funnels.find(
                    (item) => item.id == funnel_id
                ).reference_key;
                this.loading = true;
                axios
                    .get(`/reports/get/${  funnel_id}`)
                    .then((response) => {
                        // console.table(response.data.allLandingPage)
                        // console.table(response.data.formCount)
                        // console.table(response.data.landingpageOrder)
                        this.loading = false;
                        this.isLandingPagePresent =
                            response.data.allLandingPage.length > 0;
                        this.allLandingPage = response.data.allLandingPage;
                        this.formCount = response.data.formCount;
                        this.landingpageOrder = response.data.landingpageOrder;
                        this.funnelCurrency = response.data.funnelCurrency;
                        this.exchangeRate = response.data.exchangeRate;

                        this.combineLandingPageData();
                        resolve(response.data);
                    })
                    .catch((error) => {
                        reject(error);
                        console.log(error);
                    });
            });
        },

        combineLandingPageData() {
            // 	var funnel_id;
            // await this.funnelIsSelected(funnel_id);

            const landingpages = this.allLandingPage.map((item, index) => {
                const landing_id = item.id;
                const total_optin = this.formCount.reduce((total, current) => {
                    if (current.landing_id == landing_id) {
                        return total + current.submit_count;
                    }
                    return total;
                }, 0);
                const sales_count = this.landingpageOrder.filter((item) => {
                    return item.landing_id == landing_id;
                });
                const sales_total_value = this.landingpageOrder.reduce(
                    (total, current) => {
                        // console.log(this.selectedCurrency);
                        // console.log(this.selectedCurrency.find(data=> data.currency === current.currency));
                        if (current.landing_id == landing_id) {
                            // var currency = this.selectedCurrency.find(data=> data.currency === current.currency)
                            // this.exchangeRate = currency.suggestRate || currency.exchangeRate;

                            // let currentValue = parseFloat(current.total / this.exchangeRate);
                            const currentValue = parseFloat(current.total);
                            // console.log(currentValue);

                            // currency = this.selectedCurrency.find(data=> data.currency === this.funnelCurrency);
                            // this.exchangeRate = currency.suggestRate || currency.exchangeRate;

                            // currentValue = parseFloat(currentValue * this.exchangeRate)
                            // console.log(currentValue);
                            total += currentValue;
                            return total;
                        }
                        return total;
                    },
                    0
                );
                // console.log("landing " + landing_id + " total sales count is", JSON.stringify(sales_count), sales_count.length);
                // console.log("landing " + landing_id + " total sales amount is", sales_total_value);
                item.opt_ins = total_optin;
                item.sales_count = sales_count.length;
                item.sales_total_value = sales_total_value;
                return item;
            });
            this.mergedLandingPages = landingpages;
            //   console.log(this.mergedLandingPages);
        },

        optInRate(optIn, unique_page_views) {
            unique_page_views = unique_page_views > 0 ? unique_page_views : 1;
            const optInPercentage = (optIn / unique_page_views) * 100;
            return optInPercentage.toFixed(2);
        },

        salesRate(sales_count, unique_page_views) {
            unique_page_views = unique_page_views > 0 ? unique_page_views : 1;
            const salesPercentage = (sales_count / unique_page_views) * 100;
            return salesPercentage.toFixed(2);
        },

        allEarningPerPageView(total_sales, total_page_views) {
            total_page_views = total_page_views > 0 ? total_page_views : 1;
            const allEarningPerPageView = total_sales / total_page_views;
            return allEarningPerPageView.toFixed(2);
        },

        uniqueEarningPerPageView(sales_total_value, unique_page_views) {
            unique_page_views = unique_page_views > 0 ? unique_page_views : 1;
            const uniqueEarningPerPageView =
                sales_total_value / unique_page_views;
            return uniqueEarningPerPageView.toFixed(2);
        },

        setCurrency(currency) {
            this.selectedCurrency = currency;
        },

        setAllFununel(funnels) {
            this.funnels = funnels;
        },

        async funnelDataTable() {
            for (let i = 0; i < this.funnels.length; i++) {
                const { id, funnel_name, currency, reference_key } =
                    this.funnels[i];
                const x = await this.funnelIsSelected(id);
                this.funnelData.push({
                    ...x,
                    name: funnel_name,
                    // sales: grossSales.toFixed(2),
                    // orders: (grossSales / totalSalesCount).toFixed(2),
                    // earnings: (grossSales / totalPageViews).toFixed(2),
                    optins: this.funnels.optins ? this.funnels.optins : 0,
                    orders: this.totalOrderCount,
                    aov: this.getAOV,
                    salesDivCust: this.totalSalesDivCust,
                    // currency: currency === 'MYR' ? 'RM' : currency,
                    totalSales: this.totalSales,
                    refKey: reference_key,
                });
            }

            return this.funnelData.map((funnel) => {
                return {
                    name: funnel.name,
                    optins: funnel.optins,
                    orders: funnel.orders,
                    aov: funnel.aov,
                    salesDivCust: funnel.salesDivCust,
                    totalSales: funnel.totalSales,
                    // for action
                    refKey: funnel.refKey,
                };
            });
        },
    },
};

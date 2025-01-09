l
<template>
  <div class="row">
    <!-- <div class="h-three header">
        Dashboard
        <button
          type="button  "
          class="cleanButton"
          data-bs-target="#dashboard-info-modal"
          data-bs-toggle="modal"
        >
          <i
            class="fa fa-question-circle"
            title="Tutorial Guide"
          />
        </button>
      </div> -->
    <!-- Left Column -->
    <div class="dashboard__column col-lg-8">
      <div class="dashboard__column dashboard__column--left">
        <!-- Reports -->
        <div
          class="card card-xl-stretch mb-xl-8"
          style="margin-bottom: 18px"
        >
          <div class="card-header border-0">
            <div class="dashboard__sub-title-container p-0">
              <!-- <i
                  class="fas fa-chart-bar"
                  aria-hidden="true"
                />&nbsp;&nbsp; -->
              <h3 class="card-title fw-bolder text-dark">
                Reports
              </h3>
            </div>
          </div>

          <!-- Report Cards -->
          <div class="card-body report-card-container row px-9 py-0">
            <div class="col-xl-3 my-0 mb-3">
              <!--begin::Statistics Widget 5-->
              <Link
                class="card statistic-card hoverable card-xl-stretch"
                href="/orders"
              >
                <!--begin::Body-->
                <div class="card-body">
                  <!--begin::Svg Icon | path: icons/duotune/general/gen032.svg-->
                  <span class="svg-icon svg-icon-dark svg-icon-3x ms-n1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 26"
                      fill="none"
                    >
                      <rect
                        x="8"
                        y="9"
                        width="3"
                        height="10"
                        rx="1.5"
                        fill="black"
                      />
                      <rect
                        opacity="0.5"
                        x="13"
                        y="5"
                        width="3"
                        height="14"
                        rx="1.5"
                        fill="black"
                      />
                      <rect
                        x="18"
                        y="11"
                        width="3"
                        height="8"
                        rx="1.5"
                        fill="black"
                      />
                      <rect
                        x="3"
                        y="13"
                        width="3"
                        height="6"
                        rx="1.5"
                        fill="black"
                      />
                    </svg>
                  </span>
                  <!--end::Svg Icon-->
                  <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                    New Order <br>
                  </div>
                  <div class="fw-bold text-gray-800">
                    {{ unfulfilledOrderCount }}
                  </div>
                </div>
                <!--end::Body-->
              </Link>
              <!--end::Statistics Widget 5-->
            </div>
            <!-- <div class="report-card__sub-title p-two">
                      New Order <br>
                      (Unfulfilled)
                    </div>
                    <div class="report-card__count h-two">
                      {{ unfulfilledOrderCount }}
                    </div>
                  </div> -->
            <div class="col-xl-3 my-0 mb-3">
              <!--begin::Statistics Widget 5-->
              <Link
                class="card statistic-card hoverable card-xl-stretch"
                href="/orders"
              >
                <!--begin::Body-->
                <div class="card-body">
                  <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm008.svg-->
                  <span class="svg-icon svg-icon-dark svg-icon-3x ms-n1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 26"
                      fill="none"
                    >
                      <path
                        opacity="0.3"
                        d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.3C3.50001 2.1 3 2.20001 3 3.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                        fill="black"
                      />
                      <path
                        d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H10C9.4 14.6 9 15 9 15.6C9 16.2 9.4 16.6 10 16.6H12C12.6 16.6 13 16.2 13 15.6Z"
                        fill="black"
                      />
                      <path
                        d="M15 18.6C15 20.3 16.3 21.6 18 21.6C19.7 21.6 21 20.3 21 18.6V12.5C21 12.2 20.6 12 20.3 12.2L19 13.6L17.7 12.3C17.3 11.9 16.7 11.9 16.3 12.3L15 13.6V18.6Z"
                        fill="black"
                      />
                    </svg>
                  </span>
                  <!--end::Svg Icon-->
                  <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                    Total Order
                  </div>
                  <div class="fw-bold text-gray-800">
                    {{ allOrders.length }}
                  </div>
                </div>
                <!--end::Body-->
              </Link>
              <!--end::Statistics Widget 5-->
            </div>
            <div class="col-xl-3 my-0 mb-3">
              <!--begin::Statistics Widget 5-->
              <Link
                class="card statistic-card hoverable card-xl-stretch"
                href="/report/sales"
              >
                <!--begin::Body-->
                <div class="card-body">
                  <!--begin::Svg Icon | path: icons/duotune/finance/fin006.svg-->
                  <span class="svg-icon svg-icon-dark svg-icon-3x ms-n1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 26"
                      fill="none"
                    >
                      <path
                        opacity="0.3"
                        d="M20 15H4C2.9 15 2 14.1 2 13V7C2 6.4 2.4 6 3 6H21C21.6 6 22 6.4 22 7V13C22 14.1 21.1 15 20 15ZM13 12H11C10.5 12 10 12.4 10 13V16C10 16.5 10.4 17 11 17H13C13.6 17 14 16.6 14 16V13C14 12.4 13.6 12 13 12Z"
                        fill="black"
                      />
                      <path
                        d="M14 6V5H10V6H8V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V6H14ZM20 15H14V16C14 16.6 13.5 17 13 17H11C10.5 17 10 16.6 10 16V15H4C3.6 15 3.3 14.9 3 14.7V18C3 19.1 3.9 20 5 20H19C20.1 20 21 19.1 21 18V14.7C20.7 14.9 20.4 15 20 15Z"
                        fill="black"
                      />
                    </svg>
                  </span>
                  <!--end::Svg Icon-->
                  <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                    Total Sales <br>
                  </div>
                  <div class="fw-bold text-gray-800">
                    {{ currencyArray.prefix }} {{ formattedSales }}
                  </div>
                </div>
                <!--end::Body-->
              </Link>
              <!--end::Statistics Widget 5-->
            </div>
            <div class="col-xl-3 my-0 mb-3">
              <!--begin::Statistics Widget 5-->
              <Link
                class="card statistic-card hoverable card-xl-stretch mb-5"
                href="/people"
              >
                <!--begin::Body-->
                <div class="card-body">
                  <!--begin::Svg Icon | path: icons/duotune/graphs/gra007.svg-->
                  <span class="svg-icon svg-icon-dark svg-icon-3x ms-n1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 26"
                      fill="none"
                    >
                      <path
                        opacity="0.3"
                        d="M10.9607 12.9128H18.8607C19.4607 12.9128 19.9607 13.4128 19.8607 14.0128C19.2607 19.0128 14.4607 22.7128 9.26068 21.7128C5.66068 21.0128 2.86071 18.2128 2.16071 14.6128C1.16071 9.31284 4.96069 4.61281 9.86069 4.01281C10.4607 3.91281 10.9607 4.41281 10.9607 5.01281V12.9128Z"
                        fill="black"
                      />
                      <path
                        d="M12.9607 10.9128V3.01281C12.9607 2.41281 13.4607 1.91281 14.0607 2.01281C16.0607 2.21281 17.8607 3.11284 19.2607 4.61284C20.6607 6.01284 21.5607 7.91285 21.8607 9.81285C21.9607 10.4129 21.4607 10.9128 20.8607 10.9128H12.9607Z"
                        fill="black"
                      />
                    </svg>
                  </span>
                  <!--end::Svg Icon-->
                  <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
                    Total People
                  </div>
                  <div class="fw-bold text-gray-800">
                    {{ accountPlanTotal.total_people }}
                  </div>
                </div>
                <!--end::Body-->
              </Link>
              <!--end::Statistics Widget 5-->
            </div>
          </div>
        </div>
        <!-- report cards end -->

        <div class="dashboard__row row">
          <div class="col-md-12">
            <div
              class="card card-flush h-xl-100"
              style="margin-bottom: 18px"
            >
              <div class="card-header border-0">
                <h3 class="card-title fw-bolder text-dark">
                  <span class="mt-2">Achievements</span>
                  <BaseBadge
                    class="ms-3"
                    :text="subscriptionPlan.plan"
                    type="secondary"
                  />
                </h3>
                <div class="card-toolbar">
                  <a
                    v-if="isOwner == '1'"
                    href="/subscription/plan/upgrade"
                    class="w-100"
                    style="text-align: center"
                  >
                    <BaseButton
                      size="sm"
                      @click="handleClickEvent"
                    >Upgrade</BaseButton>
                    <!-- <button
                        type="submit"
                        class="primary-white-button"
                        :disabled="subscriptionPlan.id >= 4"
                      >
                        Upgrade
                      </button> -->
                  </a>
                </div>
              </div>

              <!-- Archievements Counts -->
              <div class="card-body">
                <!--begin::Item-->
                <div
                  class="right-bar__details p-two"
                  style="padding: 0 !important"
                >
                  <!--begin::Wrapper-->

                  <div class="d-flex align-items-center me-3">
                    <div class="flex-grow-1">
                      <template
                        v-for="(
                          { key, label, progressBar }, index
                        ) in quotaSummary"
                        :key="index"
                      >
                        <div class="right-bar__details-row">
                          <span class="text-gray-800 fs-5 fw-bolder lh-0 py-3">
                            {{ label }}
                          </span>
                          <div class="d-flex align-items-center w-100 mw-125px">
                            <div
                              class="progress h-6px w-100 me-2 bg-light-info"
                            >
                              <div
                                :class="`progress-bar ${
                                  quotaData[key].percent === 100
                                    ? 'bg-danger'
                                    : progressBar
                                }`"
                                role="progressbar"
                                :style="`width: ${quotaData[key].percent}%`"
                                aria-valuenow="2"
                                aria-valuemin="0"
                                aria-valuemax="100"
                              />
                            </div>
                            <span class="text-gray-400 fw-bold">
                              {{ quotaData[key].usage }}
                            </span>
                          </div>
                          <!-- <span class="text-gray-500 fw-bold d-block fs-6">{{ accountPlanTotal.total_landingpage }}</span> -->
                        </div>
                        <div class="separator separator-dashed my-4" />
                      </template>
                    </div>
                  </div>
                  <!--end::Wrapper-->
                </div>
                <!--end::Item-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- to do checklist -->
    <div class="dashboard__column col-lg-4">
      <div class="card card-xl-stretch">
        <div class="card-header border-0">
          <h3 class="card-title fw-bolder text-dark">
            Todo
          </h3>
        </div>

        <div class="card-body pt-2">
          <BaseFormSelect
            v-model="selectedToDoHeader"
            :options="todoHeader"
          />

          <div class="right-bar__details p-two">
            <label
              v-for="(details, index) in todoDetails[selectedToDoHeaderIndex]"
              :key="index"
              class="right-bar__details-row to-do-checkbox my-3"
              :value="checkedDetails[selectedToDoHeaderIndex]"
            >
              <div class="d-flex align-items-center">
                <span
                  class="bullet bullet-vertical h-40px"
                  :class="details.className"
                />
                <div
                  class="form-check form-check-custom form-check-solid form-check-sm mx-6"
                >
                  <input
                    :id="details"
                    v-model="checkedDetails[selectedToDoHeaderIndex]"
                    :name="selectedToDoHeader"
                    :value="details"
                    type="checkbox"
                    class="form-check-input"
                  >
                </div>
                <div
                  class="flex-grow-1"
                  style="cursor: pointer"
                >
                  <span class="to-do-checkmark" />
                  <span
                    class="to-do-details text-gray-800 text-hover-primary fw-bolder fs-6 text-wrap"
                  >
                    {{ details.title }}
                  </span>
                </div>
              </div>
              <div
                v-if="details.url"
                class="my-4"
              >
                <a :href="details.url">
                  <i
                    class="fas fa-external-link-square-alt"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    style="color: grey; font-size: 15px"
                  />
                </a>
              </div>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import InformationModal from '@shared/components/InformationModal.vue';

export default {
  name: 'Dashboard',

  components: {
    // InformationModal,
  },

  props: {
    account: {
      type: Object,
      required: true,
    },
    accountPlanTotal: {
      type: Object,
      required: true,
    },
    allOrders: {
      type: Array,
      default: () => [],
    },
    currencyArray: {
      type: Array,
      required: true,
    },
    isOwner: {
      type: Boolean,
      default: false,
    },
    miniStoreCheckedList: {
      type: Object,
      required: true,
    },
    subscriptionPlan: {
      type: Object,
      required: true,
    },
    saleChannel: {
      type: String,
      default: 'store',
    },
    totalSales: {
      type: String,
      default: '0',
    },
    permissionQuota: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      selectedToDoHeader: 'Online Store',
      todoHeader: [
        // 'Get Leads',
        // 'Sell Products',
        // 'Sell Multiple Products',
        'Send Marketing Email',
        'Funnel',
        'Online Store',
        'Mini Store',
      ],
      todoDetails: [
        // [
        //   {
        //     title: 'Add a funnel',
        //     url: '/funnels',
        //   },
        //   {
        //     title: 'Set up first step',
        //   },
        //   {
        //     title: 'Set up remaining steps',
        //   },
        //   {
        //     title: 'Test & launch',
        //   },
        // ],
        // [
        //   {
        //     title: 'Add a product',
        //     url: '/product/create',
        //   },
        //   {
        //     title: 'Add a funnel',
        //     url: '/funnels',
        //   },
        //   {
        //     title: 'Set up first step',
        //   },
        //   {
        //     title: 'Set up remaining steps',
        //   },
        //   {
        //     title: 'Set up shipping methods',
        //     url: '/shipping/settings',
        //   },
        //   {
        //     title: 'Connect payment',
        //     url: '/payment/settings',
        //   },
        //   {
        //     title: 'Test & launch',
        //   },
        // ],
        // [
        //   {
        //     title: 'Add a product',
        //     url: '/product/create',
        //   },
        //   {
        //     title: 'Add a theme',
        //     url: '/online-store/themes',
        //   },
        //   {
        //     title: 'Set up home page',
        //   },
        //   {
        //     title: 'Set up menu',
        //     url: '/online-store/menu/create',
        //   },
        //   {
        //     title: 'Set up shipping methods',
        //     url: '/shipping/settings',
        //   },
        //   {
        //     title: 'Connect payment',
        //     url: '/payment/settings',
        //   },
        //   {
        //     title: 'Test & launch',
        //   },
        // ],
        [
          {
            title: 'Set up a sender email',
            url: '/emails/settings',
          },
          {
            title: 'Add an email',
            url: '/emails',
          },
          {
            title: 'Test & send',
          },
        ],
        [
          {
            title: 'Add a product',
            url: '/product/create',
          },
          {
            title: 'Design your funnel',
            url: '/funnels',
          },
          {
            title: 'Set up a payment method',
            url: '/payment/settings',
          },
          {
            title: 'Set up a shipping method',
            url: '/shipping/settings',
          },
          {
            title: 'Test your funnel',
          },
        ],
        [
          {
            title: 'Add a product',
            url: '/product/create',
          },
          {
            title: 'Design your online store',
            url: '/online-store/themes',
          },
          {
            title: 'Edit navigation menu',
            url: '/online-store/menu',
          },
          {
            title: 'Set up a payment method',
            url: '/payment/settings',
          },
          {
            title: 'Set up a shipping method',
            url: '/shipping/settings',
          },
          {
            title: 'Test your online store',
            url: this.$page.props.onlineStoreDomain,
          },
        ],
        [
          {
            title: 'Add a product',
            url: '/product/create',
          },
          {
            title: 'Set up your mini store',
            url: '/mini-store/setup',
          },
          {
            title: 'Set up a payment method',
            url: '/payment/settings',
          },
          {
            title: 'Set up a shipping method',
            url: '/shipping/settings',
          },
          {
            title: 'Test the mini store',
            url: this.$page.props.miniStoreDomain,
          },
        ],
      ],
      checkedDetails: [[], [], [], [], [], [], []],
      quotaSummary: [
        { key: 'total_funnel', label: 'Sales Funnel', progressBar: 'bg-info' },
        {
          key: 'total_landingpage',
          label: 'Landing Pages',
          progressBar: 'bg-success',
        },
        {
          key: 'total_online_store_pages',
          label: 'Online Store Pages',
          progressBar: 'bg-warning',
        },
        { key: 'total_product', label: 'Products', progressBar: 'bg-primary' },
        { key: 'total_people', label: 'People', progressBar: 'bg-info' },
        {
          key: 'total_automation',
          label: 'Automations',
          progressBar: 'bg-warning',
        },
        { key: 'total_email', label: 'Email Sent', progressBar: 'bg-success' },
        {
          key: 'total_image_storage',
          label: 'Media Storage',
          progressBar: 'bg-primary',
        },
      ],
    };
  },

  computed: {
    unfulfilledOrderCount() {
      return this.allOrders.reduce((acc, curr) => {
        if (curr.fulfillment_status === 'Unfulfilled') acc += 1;
        return acc;
      }, 0);
    },

    pieChartText() {
      return `${25 * this.subscriptionPlan.id}%`;
    },

    pieChartPercent() {
      let deg2 = null;
      const { id } = this.subscriptionPlan;

      switch (id) {
        case 1:
          deg2 = '0deg';
          break;
        case 2:
          deg2 = '50deg';
          break;
        default:
          deg2 = '180deg';
      }

      return {
        '--color1': id === 1 || id === 2 ? '#f2f2f2' : ' #7766F7',
        '--color2': id === 2 || id === 4 ? ' #7766F7' : 'transparent',
        '--color3': id === 2 ? 'transparent' : ' #7766F7',
        '--deg1': id === 1 || id === 2 ? '270deg' : '90deg',
        '--deg2': deg2,
      };
    },

    selectedToDoHeaderIndex() {
      return this.todoHeader.indexOf(this.selectedToDoHeader);
    },

    formattedSales() {
      const totalSales = parseInt(this.totalSales.replaceAll(',', ''));

      if (totalSales >= 100000) {
        return `${this.roundUp(totalSales, 1000000)}M`;
      }

      if (totalSales >= 1000) {
        return `${this.roundUp(totalSales, 1000)}K`;
      }

      return totalSales.toFixed(2);
    },

    mediaStorageInGB() {
      const { total_image_storage: totalImageStorage } = this.accountPlanTotal;
      return (totalImageStorage / 1024 / 1024).toFixed(2);
    },
    quotaData() {
      const quotaObj = {
        'add-funnel': 'total_funnel',
        'add-landing-page': 'total_landingpage',
        'create-os-page': 'total_online_store_pages',
        'add-product': 'total_product',
        'add-people': 'total_people',
        'add-automation': 'total_automation',
        'send-email': 'total_email',
        'upload-image': 'total_image_storage',
      };
      const tempData = {};
      this.permissionQuota.forEach((item) => {
        const property = quotaObj[item.slug];
        if (!property) return;
        const isStorage = item.slug === 'upload-image';
        const usage = isStorage
          ? this.mediaStorageInGB
          : this.accountPlanTotal[property];
        const percent =
          usage >= item.max ? 100 : Math.round((usage / item.max) * 100);
        tempData[property] = {
          usage,
          max: item.max,
          percent,
        };
      });
      return tempData;
    },
  },

  watch: {
    selectedToDoHeader() {
      localStorage.selectedToDoHeader = this.selectedToDoHeader;
    },

    checkedDetails: {
      deep: true,
      handler() {
        this.saveCheckedItemsIntoLocalStorage();
      },
    },
  },

  mounted() {
    switch (true) {
      case localStorage.selectedToDoHeader:
        this.selectedToDoHeader = localStorage.selectedToDoHeader;
        break;
      case this.saleChannel === 'store':
        this.selectedToDoHeader = 'Online Store';
        break;
      case this.saleChannel === 'mini-store':
        this.selectedToDoHeader = 'Mini Store';
        break;
      default:
        this.selectedToDoHeader = 'Online Store';
    }

    const checkedItems = JSON.parse(localStorage.getItem('checkedDetails'));

    if (!localStorage.checkedDetails) {
      this.checkedDetails[6] = this.miniStoreCheckedList ?? [];
      this.saveCheckedItemsIntoLocalStorage();
    }

    if ((checkedItems ?? []).length !== 7) {
      this.saveCheckedItemsIntoLocalStorage();
    }

    if (checkedItems) {
      this.checkedDetails = checkedItems;
    }
  },

  methods: {
    completeAllToDo() {
      const index = this.selectedToDoHeaderIndex;
      this.checkedDetails[index] = this.todoDetails[index];
    },

    roundUp(totalSales, range = 1000) {
      return (Math.abs(totalSales) / range).toFixed(2);
    },

    saveCheckedItemsIntoLocalStorage() {
      localStorage.setItem(
        'checkedDetails',
        JSON.stringify(this.checkedDetails)
      );
    },
  },
};
</script>

<style scoped lang="scss">
@mixin md_padding {
  @media (max-width: $md-display) {
    padding-left: $mobile-align-left-padding;
    padding-right: $mobile-align-left-padding;
  }
}

i {
  font-size: 14px;
}

.statistic-card {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
}

.report-card-container.row > * {
  padding-right: 5px;
}

.dashboard {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: flex-start;

  &__row {
    display: flex;
    flex-direction: row;
  }

  &__column {
    display: flex;
    flex-direction: column;

    &--left {
      // padding: 20px;
      margin-right: 0px;
      // padding: 20px;
      // width: 64%;
    }

    &--right {
      margin-left: 0px;
      // padding: 20px;
      // width: 33%;
    }
  }

  &__card {
    background: #fff;
    border-radius: 5px;
    border: 1px solid #ced4da;
    box-shadow: 0 1px 1px #000;
    margin-bottom: 20px;
  }

  &__card-header {
    padding: 20px 20px 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    width: 100%;
    border-bottom: 0.25px solid #ced4da;
    margin-bottom: 20px;

    @include md_padding;
  }

  &__card-container {
    padding: 0 20px 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;

    @include md_padding;
  }

  &__card-title {
    font-size: 14px;
  }

  &__sub-title-container {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    width: 100%;
    padding: 0 0 20px 0;
  }

  &__pie {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
    padding-top: 8px;
  }

  &__piechart {
    width: 113px;
    height: 113px;
    border-radius: 50%;
    border: 3px solid #f2f2f2;
    padding: 17px;
    background: linear-gradient(var(--deg1), var(--color1) 50%, var(--color2) 0)
        padding-box,
      linear-gradient(var(--deg2), #f2f2f2 50%, var(--color3) 0) padding-box;
  }

  &__piechart--center {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    padding: 25px;
    border: 3px solid #f2f2f2;
    background: #fff;
    position: absolute;
  }

  &__piechart-text {
    position: absolute;
  }

  // &__sub-title {
  //   font-weight: 700;
  //   padding-left: 12px;
  // }
}

.right-bar {
  &__details {
    display: flex;
    flex-direction: column;
    width: 100%;

    :first-child {
      margin-top: 0;
    }

    @include md_padding;
  }

  &__details-row {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    white-space: nowrap;
  }
}

.header {
  margin: 0;
  font-size: 21px;
  font-weight: bold;
  font-family: $base-font-family;
  overflow-wrap: break-word;
  padding: 0;

  margin-bottom: 20px;
  line-height: 0;
}

@media (max-width: $md-display) {
  .header {
    padding-left: 16px;
  }
}

@media (min-width: $md-display) {
  .dashboard__column--left {
    margin-right: 10px !important;
    padding: 0;
    margin-top: 0;
  }
}
</style>

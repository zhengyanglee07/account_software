<template>
  <SubscriptionUpsellBanner />
  <div class="page">
    <aside
      id="offcanvasRight"
      class="overflow-hidden"
      :class="isInMobile ? 'offcanvas offcanvas-start' : 'page__side-nav'"
      tabindex="-1"
      aria-labelledby="offcanvasRightLabel"
    >
      <div
        class="position-sticky bg-white w-250px"
        style="top: 0px"
        :class="
          isInMobile ? 'offcanvas-header px-3 py-1' : 'page__side-nav--header'
        "
      >
        <Link class="logo" href="/dashboard">
          <img src="@shared/assets/media/hypershapes-logo.png" />
        </Link>
      </div>
      <div
        class="nav-item-wrapper"
        :class="
          isInMobile ? 'offcanvas-body pt-0 px-0' : 'page__side-nav--body'
        "
      >
        <div class="nav-list" style="height: 100%">
          <nav class="sidebar-nav">
            <ul class="px-0" style="list-style: none; padding-bottom: 6rem">
              <NavItem
                v-for="(nav, $index) in navigations"
                :key="$index"
                v-bind="nav"
                :is-show="nav.isShow"
                :is-dropdown="Boolean(nav.children)"
                :is-sales-channel-title="nav.isSalesChannelTitle"
                :is-active="!Boolean(nav.children) && $page.url === nav.link"
                :data-bs-dismiss="
                  !Boolean(nav.children) && isInMobile ? 'offcanvas' : null
                "
                :is-nav-group-opened="selectedNavGroup === nav?.link"
                @click="selectedNavGroup = nav.link"
              >
                <div
                  v-if="nav.children"
                  :id="nav.link"
                  :class="
                    selectedNavGroup === nav.link ? 'collapse show' : 'collapse'
                  "
                >
                  <NavItem
                    v-for="(child, $subIndex) in nav.children"
                    :key="$subIndex"
                    v-bind="child"
                    is-sub-navigation
                    :is-active="$page.url === child.link"
                    :data-bs-dismiss="isInMobile ? 'offcanvas' : null"
                  />
                </div>
              </NavItem>
            </ul>
          </nav>
        </div>
      </div>
    </aside>
    <main class="page__main">
      <header class="page__header">
        <div class="wrapper">
          <div class="header_right_nav">
            <a
              href="https://hypershapes.freshdesk.com/support/solutions"
              target="_blank"
              class="text-black"
              style="font-size: 13px"
            >
              Help
            </a>
            <span class="mx-4 fs-6"> | </span>
            <div
              id="dropdownMenuButton"
              data-bs-toggle="dropdown"
              data-offset="10px, 0px"
              aria-expanded="false"
              style="width: auto"
            >
              <div
                id="accountName"
                class="text-black"
                style="cursor: pointer; font-size: 13px"
              >
                <span style="padding-right: 5px">{{
                  $page.props.username
                }}</span>
                <i class="fas fa-caret-down" />
              </div>
            </div>

            <ul
              class="dropdown-menu profile_dropdown"
              aria-labelledby="dropdownMenuButton"
            >
              <li
                v-for="(dropdownList, index) in profileDropdown"
                :key="index"
                aria-haspopup="true"
                class="profile_dropdown_li"
              >
                <div v-if="dropdownList.selection !== 'Log Out'">
                  <Link
                    class="profile_dropdown_li_link_text"
                    :href="dropdownList.link"
                  >
                    <div>
                      <i
                        :class="dropdownList.icon"
                        class="profile_dropdown_li_icon"
                      />
                      <span style="padding-left: 5px">{{
                        dropdownList.selection
                      }}</span>
                    </div>
                  </Link>
                </div>
                <div v-else>
                  <Link class="profile_dropdown_li_link_text" @click="logout">
                    <div>
                      <i
                        :class="dropdownList.icon"
                        class="profile_dropdown_li_icon"
                      />
                      <span style="padding-left: 5px">{{
                        dropdownList.selection
                      }}</span>
                    </div>
                  </Link>
                </div>
              </li>
            </ul>
          </div>
          <div class="sidebar_hide_button ms-3">
            <button
              v-if="isInMobile"
              id="sidebar_button"
              class="sidebar_button"
              type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#offcanvasRight"
              aria-controls="offcanvasRight"
            >
              <i class="fa-solid fa-bars" />
            </button>
          </div>
        </div>
      </header>
      <div class="page__content d-flex flex-column flex-column-fluid">
        <div
          v-if="$page.url !== '/apps/all' && (pageTitle ?? setupPageName)"
          class="d-flex justify-content-start align-items-center mt-6 mb-4"
        >
          <BaseButton
            v-if="backToPreviousPageUrl"
            type="link"
            class="me-3"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Back to previous page"
            :href="backToPreviousPageUrl"
          >
            <i class="fa-solid fa-arrow-left fs-3" />
          </BaseButton>
          <h1 class="fs-2 mb-0">
            {{ pageTitle ?? setupPageName }}
          </h1>
        </div>
        <slot />
      </div>
    </main>

    <LimitModal :permission-data="permissionData" />
  </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, provide, onMounted, watch } from 'vue';
import NavItem from '@shared/components/NavItem.vue';
import eventBus from '@services/eventBus.js';
import SubscriptionUpsellBanner from '@subscription/components/SubscriptionUpsellBanner.vue';
import useCheckPermission from '@shared/hooks/useCheckPermission.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  isLocalEnv: {
    type: Boolean,
    default: false,
  },
  enabledSalesChannels: {
    type: Array,
    default: () => [],
  },
  currencyDetails: {
    type: Array,
    default: () => [],
  },
  account: {
    type: Object,
    required: true,
  },
});

provide('currencyArray', props.currencyDetails);

const pageTitle = ref(null);
const selectedNavGroup = ref(null);
const setupPageName = ref(null);
const backToPreviousPageUrl = ref(null);

const isShowTemplatesTab = computed(() => {
  if (props.isLocalEnv) return true;
  return [
    40, // 'steve@rocketlaunch.my'
    1246, // 'tzyong990808@gmail.com'
  ].includes(props.account.id);
});
const { enabledApps } = usePage().props;

const navigations = [
  {
    title: 'Dashboard',
    link: '/dashboard',
    iconURL: 'dashboard-icon.svg',
  },
  {
    title: 'Orders',
    link: '/orders',
    iconURL: 'orders-icon.svg',
  },
  {
    title: 'People',
    link: 'people',
    iconURL: 'people-icon.svg',
    children: [
      {
        title: 'All People',
        link: '/people',
      },
      {
        title: 'Segments',
        link: '/people/segments',
      },
      {
        title: 'Tags',
        link: '/people/tags',
      },
      {
        title: 'Custom Fields',
        link: '/people/custom-fields',
      },
      {
        title: 'Forms',
        link: '/people/forms',
      },
    ],
  },
  {
    title: 'Products',
    link: 'products',
    iconURL: 'product-icon.svg',
    children: [
      {
        title: 'All Products',
        link: '/products',
      },
      {
        title: 'Product Categories',
        link: '/product/category',
      },
      {
        title: 'Product Labels',
        link: '/product/badges',
        isShow: props.isLocalEnv,
      },
      {
        title: 'Inventory',
        link: '/product/inventory',
      },
    ],
  },
  {
    title: 'Marketing',
    link: 'marketing',
    iconURL: 'marketing-icon.svg',
    children: [
      {
        title: 'Promotions',
        link: '/marketing/promotions',
      },
      {
        title: 'Popups',
        link: '/marketing/popups',
      },
      // {
      //   title: 'Social Proofs',
      //   link: '/marketing/social-proof',
      // },
    ],
  },
  {
    title: 'Affiliate',
    link: 'affiliate',
    iconURL: 'affiliate-icon.svg',
    children: [
      {
        title: 'Campaigns',
        link: '/affiliate/members/campaigns',
      },
      {
        title: 'All Affiliates',
        link: '/affiliate/members',
      },
      {
        title: 'Commissions',
        link: '/affiliate/members/commissions',
      },
      {
        title: 'Payouts',
        link: '/affiliate/members/payouts',
      },
      {
        title: 'Groups',
        link: '/affiliate/members/groups',
      },
    ],
  },
  // {
  //   title: 'Referral',
  //   link: 'referral',
  //   iconURL: 'referral-icon.svg',
  //   children: [
  //     {
  //       title: 'Campaigns',
  //       link: '/referral/campaigns',
  //     },
  //   ],
  // },
  {
    title: 'Emails',
    link: '/emails',
    iconURL: 'email-icon.svg',
  },
  {
    title: 'Automations',
    link: '/automations',
    iconURL: 'automation-icon.svg',
  },
  {
    title: 'Reports',
    link: 'reports',
    iconURL: 'report-icon.svg',
    children: [
      {
        title: 'Sales Report',
        link: '/report/sales',
      },
      {
        title: 'Sales Channel Report',
        link: '/report/sales-channel',
      },
      {
        title: 'Funnel Report',
        link: '/report/funnel',
      },
      {
        title: 'Product Report',
        link: '/report/product',
      },
      {
        title: 'Email Report',
        link: '/emails/report',
      },
      // {
      //   title: 'Growth Report',
      //   link: '/report/growth',
      // },
      // {
      //   title: 'Referral Report',
      //   link: '/report/referral',
      // },
      {
        title: 'Affiliate Report',
        link: '/report/affiliate',
      },
    ],
  },
  {
    title: 'SALES CHANNEL',
    isSalesChannelTitle: true,
  },
  {
    title: 'Funnels',
    link: '/funnels',
    iconURL: 'funnel-icon.svg',
    isShow: props.enabledSalesChannels.includes('funnel'),
  },
  {
    title: 'Online Store',
    link: 'onlineStore',
    iconURL: 'online-store-icon.svg',
    isShow: props.enabledSalesChannels.includes('online-store'),
    children: [
      {
        title: 'Themes',
        link: '/online-store/themes',
      },
      {
        title: 'Pages',
        link: '/online-store/pages',
      },
      {
        title: 'Menus',
        link: '/online-store/menu',
      },
      {
        title: 'Preferences',
        link: '/online-store/preferences',
      },
    ],
  },
  // {
  //   title: 'Mini Store',
  //   link: 'miniStore',
  //   iconURL: 'mini-store-icon.svg',
  //   isShow: props.enabledSalesChannels.includes('mini-store'),
  //   children: [
  //     {
  //       title: 'Setup',
  //       link: '/mini-store/setup',
  //     },
  //   ],
  // },
  {
    title: 'Templates',
    link: '/templates',
    iconURL: 'apps-icon.svg',
    isShow: isShowTemplatesTab.value,
  },
  {
    title: 'Apps',
    link: '/apps/all',
    iconURL: 'apps-icon.svg',
  },
  {
    title: 'Settings',
    link: '/settings/all',
    iconURL: 'setting-icon.svg',
  },
];

const profileDropdown = [
  {
    selection: 'My Profile',
    link: '/myprofile',
    icon: 'fas fa-user',
  },
  {
    selection: 'Billing Setting',
    link: '/billing/setting',
    icon: 'fas fa-file-invoice-dollar',
  },
  // {
  //   selection: 'Affiliate',
  //   link: 'affiliate.dashboard',
  //   icon: 'fas fa-users',
  // },
  {
    selection: 'Log Out',
    link: 'logout',
    icon: 'fas fa-sign-out-alt',
  },
];

const navItemArray = computed(() => {
  return navigations.reduce((acc, curr) => {
    acc = [...acc, curr];
    if (curr.children) {
      acc = [...acc, ...curr.children];
    }
    return acc;
  }, []);
});

const permissionData = computed(() => usePage().props.permissionData);

const isInMobile = computed(() =>
  typeof window !== `undefined` ? window?.screen.width <= 480 : false
);

router.on('navigate', (event) => {
  pageTitle.value = null;
  const title = navItemArray.value.find(
    (e) => e.link === usePage().url.split('?')[0]
  )?.title;
  if (title) {
    pageTitle.value = title;
    setupPageName.value = null;
    backToPreviousPageUrl.value = null;
  }
});

const logout = () => {
  localStorage.clear();
  return router.post('/logout');
};

eventBus.$on('setup-page', ({ pageName, backTo }) => {
  setupPageName.value = pageName;
  backToPreviousPageUrl.value = backTo;
});

watch(usePage().url, () => {
  useCheckPermission();
});

onMounted(() => {
  useCheckPermission();
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  bootstrap?.then(({ Tooltip }) => {
    tooltipTriggerList.map((tooltipTriggerEl) => {
      return new Tooltip(tooltipTriggerEl);
    });
  });
});
</script>

<style scoped lang="scss">
.nav-item-wrapper {
  height: calc(100vh - 56px);
  overflow-y: hidden;
  --scrollbar-space: 0.5rem;

  @media (max-width: 480px) {
    overflow-y: auto;
    overflow-y: overlay;
  }
}

.nav-item-wrapper:hover {
  overflow-y: auto;
  overflow-y: overlay;
}

.nav-item-wrapper::-webkit-scrollbar {
  width: calc(0.4rem + var(--scrollbar-space));
}

.nav-item-wrapper::-webkit-scrollbar-thumb {
  background-clip: content-box;
  border-left: 0.4rem solid #eff2f5;
}

.page {
  background: #eff2f5;

  &__side-nav {
    display: block;
    overflow-x: hidden;
    background-color: #ffffff;
    width: 250px;
    height: 100vh;
    float: left;
    position: sticky;
    top: 0;
    letter-spacing: 0.5px;
    z-index: 101;
    box-shadow: 0 0 28px 0 rgb(82 63 105 / 5%);
  }

  .logo {
    height: 56px;
    display: flex;
    justify-content: left;
    align-items: center;
    padding: 0 25px;

    img {
      width: 180px;
    }
  }

  &__main {
    width: calc(100% - 250px);
    margin-left: 250px;
    min-height: 100vh;

    @media (max-width: 480px) {
      width: 100%;
      margin-left: 0;
    }
  }

  &__header {
    background-color: #ffffff;
    box-shadow: none;
    color: #202930;
    height: 56px;
    font-size: 20px;
    align-items: center;
    display: flex;
    padding: 16px 0;
    position: sticky;
    top: 0;
    width: calc(100%);
    z-index: 9;
    border-bottom: 1px solid #eff2f5;

    @media (max-width: 480px) {
      width: 100%;
      margin-left: 0;
    }
  }

  &__content {
    background: #eff2f5;
    padding: 0px 30px 80px 30px;
    min-height: calc(100vh - 56px);

    @media (max-width: 480px) {
      padding: 0px 10px 80px;
    }
  }
}

.wrapper {
  padding: 16px 30px;
  display: flex;
  width: 100%;

  @media (max-width: 480px) {
    padding: 20px 10px 20px;
  }

  .sidebar_hide_button {
    .sidebar_button {
      background-color: transparent;
      color: black;
      border: none;
      padding-bottom: 5px;
    }

    .sidebar_button:hover,
    .sidebar_button:focus,
    .sidebar_button:active {
      outline: none;
      cursor: pointer;
    }
  }
}

.header_right_nav {
  justify-content: flex-end;
  display: flex;
  align-items: center;
  padding: 0 !important;
  margin-left: auto;

  .help_text,
  #accountName {
    color: #202930;
  }

  .help_text:hover,
  #accountName:hover {
    color: #009ef7 !important;
  }
}

.fa-arrow-left:hover {
  color: black;
}

.profile_dropdown_li {
  color: #455765;
  border-radius: 6px;
  padding: 11px 50px 11px 25px;
  margin: 0 2.4%;
}

.profile_dropdown_li_icon {
  font-size: 1rem;
  margin-left: -5px;
  text-align: center;
  width: 20px;
}

.profile_dropdown_li_link_text {
  white-space: nowrap;
  font-family: Poppins, Helvetica, 'sans-serif';
  font-weight: 500;
  font-size: 13px;
  color: #455765;
  text-decoration: none;
}

.profile_dropdown_li_link_text:hover {
  color: #009ef7;
  text-decoration: none;
}

.profile_dropdown_li:hover {
  background-color: #f4f4f4;
}

.offcanvas-start {
  width: 250px;
}
</style>

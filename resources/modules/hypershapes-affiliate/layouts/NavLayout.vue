<template>
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
        <Link
          class="logo"
          href="/dashboard"
        >
          <img
            class="logo"
            src="@shared/assets/media/hypershapes-logo.png"
            style="object-fit: scale-down; height: 40px"
          >
        </Link>
      </div>
      <div
        class="nav-item-wrapper"
        :class="
          isInMobile ? 'offcanvas-body pt-0 px-0' : 'page__side-nav--body'
        "
      >
        <div
          class="nav-list"
          style="height: 100%"
        >
          <nav class="sidebar-nav">
            <ul
              class="px-0"
              style="list-style: none; padding-bottom: 6rem"
            >
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
              >
                <div
                  v-if="nav.children"
                  :id="nav.link"
                  class="collapse"
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
          <span
            class="badge badge-light-primary me-2 mt-2 mb-0"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#affiliate-tier-modal"
          >
            {{ affiliateUser.tier }}
          </span>
          <div class="header_right_nav">
            <div
              id="dropdownMenuButton"
              data-bs-toggle="dropdown"
              data-offset="10px, 0px"
              aria-expanded="false"
              style="width: auto"
            >
              <div
                id="accountName"
                style="cursor: pointer; font-size: 13px; padding-right: 18px"
              >
                <span style="padding-right: 5px">{{ affiliateUserName }}</span>
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
                  <Link
                    class="profile_dropdown_li_link_text"
                    @click="logout"
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
              </li>
            </ul>
          </div>
          <div class="sidebar_hide_button">
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
        <div class="d-flex justify-content-start align-items-center my-6">
          <h1 class="fs-2 mb-0">
            {{ pageTitle }}
          </h1>
        </div>
        <slot />
      </div>
    </main>

    <!-- <LimitModal :permission-data="permissionData" /> -->
    <BaseModal
      modal-id="affiliate-tier-modal"
      title="Affiliate Tier"
    >
      <p>
        You are in <strong>{{ affiliateUser?.tier ?? 'Standard' }}</strong> tier
        now.
        <span v-if="affiliateUser.tier !== 'Expert'">
          You will be automatically upgraded to
          <strong>
            {{ affiliateUser?.tier === 'Standard' ? 'Pro' : 'Expert' }}
          </strong>
          tier after
          {{
            (affiliateUser.tier === 'Standard'
              ? proTierCount
              : expertTierCount) - affiliateUser.successReferralCount
          }}
          more persons subscribe to Hypershapes paid plan through your affiliate
          link.
        </span>
        <span v-if="affiliateUser?.tier === 'Expert'">
          Share your affiliate link and earn 40% of commission from level 1
          sublines + 10% commission in level 2 sublines.
        </span>
      </p>

      <BaseDatatable
        no-action
        no-header
        :table-headers="tableHeaders"
        :table-datas="tableDatas"
      >
        <template #cell-count="{ row: { count } }">
          {{ count }} <span v-if="count === 0"> (Default) </span>
        </template>
        <template #cell-rates="{ row: { rates } }">
          <div
            v-for="(rate, i) in rates"
            :key="i"
            class="text-gray-400 fs-6"
          >
            {{ rate }}
          </div>
        </template>
      </BaseDatatable>
    </BaseModal>
  </div>
</template>

<script>
import NavItem from '@shared/components/NavItem.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: {
    NavItem,
  },

  props: {
    affiliateUser: {
      type: Object,
      default: () => ({}),
    },
    environment: {
      type: String,
      default: () => 'local',
    },
  },

  setup(props) {
    const pageTitle = ref(null);

    const navigations = ref([
      {
        title: 'Dashboard',
        link: '/dashboard',
        iconURL: 'dashboard-icon.svg',
      },
      {
        title: 'Payouts',
        link: '/payouts',
        iconURL: 'marketing-icon.svg',
      },
      {
        title: 'Profile',
        link: '/profile',
        iconURL: 'people-icon.svg',
      },
    ]);
    const profileDropdown = ref([
      {
        selection: 'My Profile',
        link: '/profile',
        icon: 'fas fa-user',
      },
      {
        selection: 'Log Out',
        link: '/logout',
        icon: 'fas fa-sign-out-alt',
      },
    ]);

    router.on('navigate', (event) => {
      pageTitle.value = null;
      const title = navigations.value.find(
        (e) => e.link === usePage().url
      )?.title;
      if (title) pageTitle.value = title;
    });

    const tableHeaders = ref([
      {
        name: 'Tier',
        key: 'tier',
        sortable: false,
      },
      {
        name: 'Successful Referrals',
        key: 'count',
        custom: true,
        sortable: false,
      },
      {
        name: 'Commission Rate',
        key: 'rates',
        custom: true,
        sortable: false,
      },
    ]);
    const tableDatas = ref([
      { tier: 'Standard', count: 0, rates: ['Level 1 - 20.00%'] },
      {
        tier: 'Pro',
        count: props.environment === 'staging' ? 1 : 10,
        rates: ['Level 1 - 30.00%', 'Level 2 - 5.00%'],
      },
      {
        tier: 'Expert',
        count: props.environment === 'staging' ? 5 : 50,
        rates: ['Level 1 - 40.00%', 'Level 2 - 10.00%'],
      },
    ]);

    return {
      pageTitle,
      navigations,
      profileDropdown,
      tableHeaders,
      tableDatas,
    };
  },

  computed: {
    affiliateUserName() {
      return this.affiliateUser.first_name;
    },

    // permissionData() {
    //   return this.$page.props.permissionData;
    // },

    isInMobile() {
      return window?.screen.width <= 480;
    },

    proTierCount() {
      return this.environment === 'staging' ? 1 : 10;
    },

    expertTierCount() {
      return this.environment === 'staging' ? 5 : 50;
    },
  },

  mounted() {
    this.$nextTick(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="custom-tooltip"]')
      );
      bootstrap?.then(({ Tooltip }) => {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new Tooltip(tooltipTriggerEl, {
            trigger: 'manual',
          });
        });
      });
    });
  },

  methods: {
    logout() {
      localStorage.clear();
      return this.$inertia.get('/logout');
    },
  },
};
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

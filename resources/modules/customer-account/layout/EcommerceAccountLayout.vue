<template>
  <div class="customer-online-store">
    <h5
      class="account-title customer-account-onlineStore-h-two text-uppercase d-none d-lg-block"
    >
      {{ currentPage?.name }}
    </h5>
    <div class="customer-onlineStore-container">
      <div
        class="row w-100 mx-0"
        style="min-height: 600px"
      >
        <div class="col-2 d-block d-lg-none">
          <h5
            class="account-title customer-account-onlineStore-h-two text-uppercase"
          >
            My Account
          </h5>
          <BaseFormGroup>
            <BaseFormSelect
              v-model="selectedPage"
              label-key="name"
              value-key="path"
              :options="
                navMenuItems.concat([
                  { name: 'Logout', path: '/logout', pathTitle: 'logout' },
                ])
              "
              @update:modelValue="$inertia.visit(selectedPage)"
            />
          </BaseFormGroup>
        </div>
        <div class="col-2 d-none d-lg-block">
          <!-- SideBar Menu -->
          <div class="left_container d-block">
            <div class="left_container_content">
              <ul class="content_nav">
                <li class="content_nav_title text-uppercase pb-3">
                  My Account
                </li>
                <li
                  v-for="(navMenuItem, index) in navMenuItems"
                  :id="navMenuItem.name"
                  :key="index"
                  class="content_nav_title"
                  :class="{
                    default_nav: isCurrentPath(navMenuItem.path),
                  }"
                >
                  <!-- <span class="border-left-side-menu"></span> -->
                  <Link
                    :href="navMenuItem.path"
                    class="content_nav_title_redirect customer-account-onlineStore-p-two"
                  >
                    {{ navMenuItem.name }}
                  </Link>
                </li>
                <li class="content_nav_title">
                  <span class="border-left-side-menu" />
                  <Link
                    class="content_nav_title_redirect customer-account-onlineStore-p-two"
                    @click="logout"
                  >
                    Logout
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Contents -->
        <div class="col-10 p-5">
          <slot name="content" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3';
import { useStore } from 'vuex';
import { computed, watch } from 'vue';

export default {
  setup() {
    const store = useStore();

    const isCurrentPath = (navItemPath) =>
      typeof window !== `undefined`
        ? window.location.pathname === navItemPath
        : '';
    const navMenuItems = [
      // {
      //   name: 'Dashboard',
      //   path: '/dashboard',
      //   pathTitle: 'dashboard',
      // },
      {
        name: 'Order History',
        path: '/orders/dashboard',
        pathTitle: 'order',
      },
      {
        name: 'Address',
        path: '/address/book',
        pathTitle: 'address',
      },
      // {
      //   name: 'Subscription',
      //   path: '/subscriptions/dashboard',
      //   pathTitle: 'subscription',
      // },
      {
        name: 'Store Credits',
        path: '/store-credit',
        pathTitle: 'store-credit',
      },
      // {
      //   name: 'Affiliates',
      //   path: '',
      //   pathTitle: 'affiliates',
      // },
      {
        name: 'Referral',
        path: '/referral',
        pathTitle: 'referral',
      },
      {
        name: 'Profile',
        path: '/account/settings',
        pathTitle: 'account',
      },
      // {
      //   name: 'Logout',
      //   path: '/logout',
      //   pathTitle: 'logout',
      // },
    ];

    const logout = () => {
      return router.visit('/logout', {
        onSuccess: () => {
          store.commit('customerAccount/loginCustomerInfo', null, {
            root: true,
          });
        },
      });
    };

    const currentPage = computed(() => {
      const page = navMenuItems.find((nav) =>
        typeof window !== `undefined`
          ? window.location.pathname === nav.path
          : ''
      );
      return page;
    });

    return { navMenuItems, isCurrentPath, logout, currentPage };
  },
  data() {
    return {
      selectedPage:
        typeof window !== `undefined` ? window.location.pathname : '',
    };
  },
};
</script>

<style lang="scss" scoped>
.row.w-100.mx-0 {
  @media (max-width: 767px) {
    min-height: unset !important;
  }
}

.account-title {
  text-align: center;
  margin-top: 50px;
  padding: 16px 0;
  margin-bottom: 0;
  @media (max-width: 767px) {
    margin-top: 0;
    padding-top: 0;
    margin-bottom: 2rem;
  }
}

.left_container {
  z-index: 1;

  @media (max-width: 767px) {
    float: none;
    margin: 0;
    max-width: initial;
    border-right: none !important;
  }
}

.content_nav {
  padding-left: 0;
  margin-bottom: 0;
  list-style: none;
  &_title,
  &_title--collapse {
    font-size: $base-font-size;
    border-left: 4px solid transparent;

    &_icon {
      float: left;
      width: 20px;
      margin-right: 10px;
      img {
        width: 15px;
        transform: scale(3.2);
        position: relative;
      }
    }
    &_redirect {
      color: $base-font-color;
      background-color: transparent;
      outline: none;
      border: none;
      text-decoration: none;
      font-family: 'Roboto', sans-serif;
    }
    // &_redirect:hover,
    // &_redirect:active,
    // &_redirect:focus {
    //   color: $h-primary;
    //   text-decoration: none;
    // }

    .nav {
      cursor: pointer;
      background-color: transparent;
      padding: 7px 5px 7px 16px;
      margin-left: -24px;
      width: 110%;
      border-left: 4px solid transparent;

      // &:hover {
      //   padding: 7px 5px 7px 16px;
      //   margin-left: -24px;
      //   width: 110%;
      //   background-color: #f4f4f4;
      //   border-color: $h-primary;
      // }

      // &-dropdown {
      //     margin-top: 5px;
      // }
    }
  }

  &_title {
    padding: 7px 0 7px 16px;
    // &:hover:not(:first-child) {
    //   background-color: #efefef;
    //   border-left: 4px solid $h-primary;
    //   // padding-left: 16px;
    // }
    &:hover:first-child {
      cursor: default;
    }
  }

  &_title--collapse {
    padding-left: 20px;
    // &:hover {
    //     background-color: #fff;
    // }
  }
}

.content_nav_title {
  //   border-left: 4px solid transparent !important;
  //   border: 1px solid lightgray;
  //   border-bottom: none;
  //   border-right: none;
  //   position: relative;
  //   padding: 16px 0 16px 30px;

  &:first-child {
    font-weight: 600;
    border-bottom: 1px solid lightgray;
  }

  //   &:last-child {
  //     border-bottom: 1px solid lightgray;
  //   }
}

// .content_nav_title:hover {
//   // border-left: 4px solid black !important;

//   .border-left-side-menu {
//     width: 4px;
//     background-color: black;
//     position: absolute;
//     left: 0;
//     top: 0;
//     height: 100%;
//   }
// }

.content_nav_title_redirect:hover {
  color: black;
  font-size: 14px !important;
  font-weight: 700 !important;
  font-family: 'Roboto', sans-serif !important;
}

.default_nav {
  //   border-left: 4px solid black !important;
  //   background-color: #efefef;

  //   .border-left-side-menu {
  //     width: 4px;
  //     background-color: black;
  //     position: absolute;
  //     left: 0;
  //     top: 0;
  //     height: 100%;
  //   }

  .content_nav_title_redirect {
    font-size: 14px !important;
    font-weight: 700 !important;
    font-family: 'Roboto', sans-serif !important;
  }
}

.customer-online-store {
  .customer-onlineStore-container {
    padding: 0;
    // border: 1px solid lightgray;
    // border-radius: 5px;
    // height: 500px;
    margin-top: 30px;
    margin-bottom: 100px;
    // box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);
    width: 81.2%;
    margin-left: auto;
    margin-right: auto;

    @media (max-width: $md-display) {
      width: 100%;
      margin-left: 0;
      margin-right: 0;
    }
  }

  .left_container {
    position: relative;
    background-color: transparent;
    border: none;
    // border-right: 1px solid lightgray;
    width: 100%;
    height: 100%;
  }

  .shipping-information-header {
    font-weight: 700 !important;
    margin-bottom: 20px;
    font-size: 18px;
    line-height: 1.1;
    color: inherit;
  }
  .customer-store {
    padding: 20px 40px;
    margin-bottom: 32px;
    // overflow-y: scroll;
  }

  @media (max-width: 767px) {
    .col-2,
    .col-10 {
      width: 100%;
    }
  }
}

:deep {
  .input-oneline {
    width: 100% !important;
    padding-bottom: 20px !important;
  }

  .input-row-lg {
    width: 100%;
    padding-bottom: 0;

    @media (min-width: 992px) {
      padding-bottom: 20px;
    }
  }

  .form-group-append {
    width: 100%;
    .input-group {
      border-color: $table-border-color;
      .input-group-append {
        .input-group-text {
          border-left: none;
          border-right: 1px solid #ced4da;
          background-color: white;
          padding: 0 3px;
          height: 100%;
          border-top-left-radius: 0;
          border-bottom-left-radius: 0;
          border-color: $table-border-color;
          padding-right: 10px;
        }
      }
      .form-control {
        border-right: 0;
        height: 36px;
        padding-left: 10px;
        border-color: $table-border-color;
        &:disabled {
          background-color: #efefef;
        }
      }
    }
  }

  .setting-page__form-input {
    height: 36px;
    width: 100%;
    border: 1px solid #ced4da;
    border-radius: 2.5px;
    padding: 0 12px;
    background-color: #fff;

    &:focus {
      outline: none;
      border-color: $base-font-color;
    }
    &:disabled {
      background-color: #e9ecef;
    }

    &::placeholder {
      color: #ced4da;
    }
  }

  .error-font-size {
    font-size: $base-font-size !important;
    font-weight: 400 !important;
    font-family: 'Roboto', sans-serif !important;
  }

  .error-message {
    color: red;
    padding: 0;
    margin: 0;
  }

  .setting-page__form-footer {
    display: flex;
    justify-content: flex-end;
  }

  .datatableEditButton {
    background-color: transparent !important;
    border: none;
    color: black;
    margin: 10px;
    padding: 0;
    cursor: pointer;
    &:hover {
      color: currentColor;
    }

    .fa-pencil-alt,
    .fa-eye,
    .fa-ellipsis-h,
    .fa-copy {
      background-color: transparent;
      outline: none;
      &:hover,
      &:focus,
      &:active {
        color: $h-primary;
      }
    }
    .fa-trash-alt,
    .fa-trash {
      &:hover,
      &:focus,
      &:active {
        color: #ea1914;
      }
    }
    &:hover > i.fa-trash-alt + span,
    &:hover > i.fa-trash-alt,
    &:hover > i.fa-trash {
      color: #ea1914;
    }
  }

  //buttons
  .default-button {
    padding: 6px 16px !important;
    font-size: $button-font-size;
    border-radius: 2.5px;
    text-transform: capitalize;
    letter-spacing: 0.5px;
    text-decoration: none;

    @media (max-width: $md-display) {
      font-size: 12px;
    }

    &:focus {
      outline: none;
    }
  }

  .white-button {
    @extend .default-button;
    background-color: white !important;
    color: $base-font-color !important;
  }

  .primary-white-button {
    @extend .default-button;

    background-color: $h-primary-text;
    color: $h-primary;
    border: 0.5px solid $h-primary !important;
    min-height: 36px;
    height: auto;
    width: 125px;

    // &:hover {
    //   text-decoration: none !important;
    //   cursor: pointer;
    //   background-color: rgb(92 54 254 /0.05) !important; //$h-primary
    // }

    &:disabled {
      $disabled: #d0d2d3;
      color: $disabled !important;
      border-color: $disabled !important;
      background-color: #ffffff !important;
      cursor: default;
      &:hover {
        background-color: rgba(208, 210, 211, 0.05) !important; //$disabled
      }
    }
  }

  .purple-button {
    @extend .default-button;
    background-color: $h-primary;
    color: $h-primary-text !important;
    border: none;

    span {
      color: $h-primary-text;
      font-size: 12px;
    }

    &:hover {
      background-color: $h-primary-hover;
      color: $h-primary-text;
      text-decoration: none;
      cursor: pointer;
    }

    &:disabled {
      $disabled: #d0d2d3;
      background-color: $disabled;
      cursor: default;
      &:hover {
        background-color: lighten($disabled, 2%);
        background-image: none;
      }
    }
  }

  .primary-small-square-button {
    @extend .purple-button;
    width: 100px;
    height: 36px;
  }

  .cancel-button {
    text-decoration: underline;
    padding-right: 20px;
    font-size: $base-font-size;
    background-color: transparent;
    border: none;
    letter-spacing: 1px;
    color: $base-font-color;

    &:hover,
    &:focus,
    &:active {
      cursor: pointer !important;
      color: $h-primary-hover !important;
      outline: none;
    }

    @media (max-width: $md-display) {
      font-size: 10px;
    }
  }

  .cleanButton {
    background-color: transparent;
    border: none;
    padding: 5px;
    cursor: pointer;
    text-decoration: none;
  }

  .cleanButton:hover,
  .cleanButton:active,
  .cleanButton:focus {
    color: $h-primary;
    outline: none;
    cursor: pointer;
  }

  .customer-account-h-two {
    font-size: 25px !important;
    font-weight: 900 !important;
    font-family: 'Inter', sans-serif !important;
  }
  .customer-account-h-five {
    font-size: 14px !important;
    font-weight: 700 !important;
    font-family: 'Roboto', sans-serif !important;
  }

  .customer-account-p-three {
    font-size: 12px !important;
    font-weight: 400 !important;
    font-family: 'Roboto', sans-serif !important;
  }

  .customer-account-onlineStore-h-two {
    font-size: 24px !important;
    font-weight: 600 !important;
    font-family: 'Inter', sans-serif !important;
  }

  .customer-account-onlineStore-h-three {
    font-size: 20px !important;
    font-weight: 500 !important;
    font-family: 'Inter', sans-serif !important;
  }

  .customer-account-onlineStore-h-four {
    font-size: 18px !important;
    font-weight: 500 !important;
    font-family: 'Inter', sans-serif !important;
  }

  .customer-account-onlineStore-h-five {
    font-size: 14px !important;
    font-weight: 700 !important;
    font-family: 'Roboto', sans-serif !important;
  }

  .customer-account-onlineStore-p-two {
    font-size: 14px !important;
    font-weight: 400 !important;
    font-family: 'Roboto', sans-serif !important;
  }

  .customer-account-onlineStore-p-three {
    font-size: 12px !important;
    font-weight: 400 !important;
    font-family: 'Roboto', sans-serif !important;
    // font-style: italic !important;
  }

  .customer-account-onlineStore-p-four {
    font-size: 14px !important;
    font-weight: 400 !important;
    font-family: 'Roboto', sans-serif !important;
    font-style: italic !important;
  }
}
</style>

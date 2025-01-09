<template>
  <div v-if="isShow">
    <div
      v-if="!isSalesChannelTitle"
      :style="{
        'margin-top': title === 'Apps' ? '40px' : 0,
      }"
    >
      <li
        class="nav-item"
        :class="{
          'nav-item--dropdown': isSubNavigation,
          'nav-item--active': isActive,
        }"
      >
        <img
          v-if="iconURL"
          class="nav-item__icon"
          :src="
            images[`/resources/modules/shared/assets/media/${iconURL}`].default
          "
        >

        <template v-if="!isDropdown">
          <span
            v-if="isSubNavigation"
            class="menu-bullet"
          >
            <span
              class="bullet bullet-dot"
              style=""
            />
          </span>
          <Link
            class="nav-item__title w-100"
            :href="link"
            :class="{
              'nav-item__title--active': isActive,
            }"
          >
            {{ title }}
          </Link>
          <span
            v-if="title === 'Orders'"
            class="counter ms-auto"
          >
            {{ $page.props.totalOpenOrder }}
          </span>
          <span
            v-if="title === 'All Affiliates'"
            class="counter ms-auto"
          >
            {{ $page.props.totalPendingAffiliateMember }}
          </span>
          <span
            v-if="title === 'Commissions'"
            class="counter ms-auto"
          >
            {{ $page.props.totalPendingAffiliateCommissions }}
          </span>
          <!-- <span
            v-if="title === 'Payouts'"
            class="counter ms-auto"
          >
            {{
              $page.props.affiliateMemberName
                ? $page.props.affiliateMemberPendingPayouts
                : $page.props.totalPendingAffiliatePayouts
            }}
          </span> -->
        </template>

        <template v-else>
          <button
            type="button"
            class="nav-item__title"
            :class="{
              'me-4': ['Online Store', 'Mini Store'].includes(title),
            }"
            data-bs-toggle="collapse"
            aria-expanded="false"
            :data-bs-target="`#${link}`"
            :aria-controls="`${link}`"
            @click="arrowToggled = !arrowToggled"
          >
            <span>{{ title }}</span>
            <span
              class="arrow ms-auto"
              :class="{
                'me-0': ['Online Store', 'Mini Store'].includes(title),
                'arrow-dropdown': arrowToggled,
              }"
              @click="arrowToggled = !arrowToggled"
            />
          </button>
          <Component
            :is="salesChannelStoreURL ? 'a' : 'button'"
            v-if="['Online Store', 'Mini Store'].includes(title)"
            class="preview-store-button ms-auto mx-5 bg-transparent border-0 p-0"
            :title="previewIconTooltipTitle"
            :href="salesChannelStoreURL ? salesChannelStoreURL : '#'"
            :class="{
              'text-gray-100': !salesChannelStoreURL,
            }"
            :style="{
              cursor: !salesChannelStoreURL ? 'default' : 'pointer',
            }"
            target="_blank"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            @click.stop
          >
            <i
              class="fa-solid"
              :class="salesChannelStoreURL ? 'fa-eye' : 'fa-eye-slash'"
            />
          </Component>
        </template>
      </li>

      <!-- Sub navigations here -->
      <slot />
    </div>
    <div v-else>
      <div class="sales-channel-title">
        SALES CHANNEL
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  isShow: {
    type: Boolean,
    default: true,
  },
  isDropdown: {
    type: Boolean,
    default: false,
  },
  isSubNavigation: {
    type: Boolean,
    default: false,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  link: {
    type: String,
    default: null,
  },
  iconURL: {
    type: String,
    default: null,
  },
  isSalesChannelTitle: {
    type: Boolean,
    default: false,
  },
  children: {
    type: Array,
    default: () => [],
  },
  isNavGroupOpened: {
    type: Boolean,
    default: false,
  },
});

const arrowToggled = ref(false);

const images = ref({});
const page = usePage();

images.value = import.meta.glob(
  '/resources/modules/shared/assets/media/*.svg',
  { eager: true }
);

const salesChannelStoreURL = computed(() => {
  return page.props[
    props.title === 'Online Store' ? 'onlineStoreDomain' : 'miniStoreDomain'
  ];
});

const previewIconTooltipTitle = computed(() =>
  salesChannelStoreURL.value
    ? 'View Store'
    : `No domain for ${props.title}. Set up now in domain setting`
);

watchEffect(() => {
  if (!props.isNavGroupOpened) {
    arrowToggled.value = false;
  }
});
</script>

<style scoped lang="scss">
@mixin active {
  background-color: rgba(245, 248, 250, 0.8);
}

.nav-item {
  padding: 9px 0 9px 25px;
  display: flex;
  align-items: center;
  letter-spacing: 0;
  font-size: 13px;
  font-weight: 500;
  font-family: Poppins, Helvetica, 'sans-serif';

  &__icon {
    float: left;
    width: 18px;
    position: relative;
  }

  &__title {
    color: #455765;
    background-color: transparent;
    outline: none;
    padding: 0;
    border: none;
    text-decoration: none;
    margin: 0 15px;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
    text-align: left;
    display: inline-flex;
    align-items: center;

    &--active {
      color: #009ef7;
    }

    @media (max-width: 480px) {
      font-size: 13px;
    }

    &:hover {
      color: #009ef7;
    }
  }

  &--dropdown {
    padding-left: 36px;
  }

  &--active {
    @include active;
  }

  &--preview {
    color: black;
    right: 0;
    position: relative;
    margin-left: auto;
  }

  &:hover {
    color: #009ef7;
  }
}

.preview-store-button {
  &:hover {
    color: black;
  }
}

.menu-bullet {
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
}

.sales-channel-title {
  color: #a1a5b7;
  border: 0;
  margin-top: 40px;
  padding: 7px 20px 7px;
  font-size: 13px;
  font-weight: 300;
  font-family: Poppins, Helvetica, 'sans-serif';
}

.arrow {
  flex-shrink: 0;
  width: 10px;
  height: 0.8rem;
  margin-right: 12px;
  transform: rotate(180deg);
  transition: transform 0.2s ease-in-out;
  background-repeat: no-repeat;
  background-position: center;
  background-color: transparent;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 9' fill='%23A1A5B7'%3e%3cpath fill-rule='evenodd' clip-rule='evenodd' d='M2.06463 4.42111C1.96161 4.22088 1.9809 3.9637 2.12863 3.78597L5.12847 0.177181C5.31402 -0.046034 5.63049 -0.060261 5.83532 0.145404C6.04015 0.351069 6.05578 0.698744 5.87023 0.921959L3.19406 4.14137L5.84414 7.06417C6.03896 7.27904 6.03835 7.62686 5.84278 7.84105C5.64721 8.05524 5.33073 8.05469 5.13591 7.83982L2.14806 4.54449C2.1141 4.50704 2.08629 4.46541 2.06463 4.42111Z'/%3e%3c/svg%3e");
}

.arrow-dropdown {
  transition: transform 0.2s ease-in-out;
  transform: rotate(270deg);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 9' fill='%23A1A5B7'%3e%3cpath fill-rule='evenodd' clip-rule='evenodd' d='M2.06463 4.42111C1.96161 4.22088 1.9809 3.9637 2.12863 3.78597L5.12847 0.177181C5.31402 -0.046034 5.63049 -0.060261 5.83532 0.145404C6.04015 0.351069 6.05578 0.698744 5.87023 0.921959L3.19406 4.14137L5.84414 7.06417C6.03896 7.27904 6.03835 7.62686 5.84278 7.84105C5.64721 8.05524 5.33073 8.05469 5.13591 7.83982L2.14806 4.54449C2.1141 4.50704 2.08629 4.46541 2.06463 4.42111Z'/%3e%3c/svg%3e");
}

.counter {
  display: flex;
  margin-left: auto;
  align-items: stretch;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  background-color: #d9dcde;
  border-radius: 20px;
  padding: 0 0.25rem;
  text-align: center;
  font-size: 12px;
  margin-right: 25px;
}
</style>

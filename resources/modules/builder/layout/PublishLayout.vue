<template>
  <slot />

  <!-- Legal policy navigation item footer -->
  <footer
    v-if="isMiniStoreCheckout && legalPolicy.length > 0"
    class="mt-5 mb-20 container border-top"
    style="max-width: 1080px"
  >
    <div class="p-3">
      <span v-for="(data, index) in legalPolicy" :key="index">
        <BaseButton
          v-if="
            data.legal_policy_type?.template.replace(/<[^>]*>?/gm, '').length >
            0
          "
          is-open-in-new-tab
          type="link"
          :href="'/legal/' + data.legal_policy_type?.type"
          class="me-5"
        >
          {{ data.legal_policy_type?.name }}
        </BaseButton>
      </span>
    </div>
  </footer>

  <template
    v-if="!isThemePreview && !isTemplatePreview && !isMiniStoreCheckout"
  >
    <AffiliateBadge
      v-if="
        pageType === 'landing'
          ? funnel.has_affiliate_badge
          : storePreferences?.has_affiliate_badge
      "
      :affiliate-id="affiliateId"
    />

    <SocialProofNotification />
  </template>
</template>

<script setup>
import { useStore } from 'vuex';
import { Head, usePage } from '@inertiajs/vue3';
import {
  computed,
  onMounted,
  onBeforeMount,
  watch,
  watchEffect,
  ref,
  useAttrs,
  provide,
} from 'vue';
import clone from 'clone';
import SocialProofNotification from '@onlineStore/components/SocialProofNotification.vue';
import AffiliateBadge from '@shared/components/AffiliateBadge.vue';
import Cookies from 'js-cookie';
import axios from 'axios';

const props = defineProps({
  isPublish: {
    type: Boolean,
    default: false,
  },
  pageType: {
    type: String,
    required: true,
    validator: (value) => {
      return [
        'landing',
        'page',
        'header',
        'footer',
        'template',
        'theme',
        'mini-store',
      ].includes(value);
    },
  },
  domain: {
    type: Object,
    default: null,
  },
  affiliateId: {
    type: Number,
    default: null,
  },
  headerDesign: {
    type: Object,
    default: null,
  },
  footerDesign: {
    type: Object,
    default: null,
  },
  products: {
    type: Array,
    default: null,
  },
  categories: {
    type: Array,
    default: null,
  },
  currencyDetails: {
    type: Object,
    default: null,
  },
  menuListArray: {
    type: Array,
    default: null,
  },
  storePreferences: {
    type: Object,
    default: null,
  },
  socialProof: {
    type: Object,
    default: null,
  },
  storeDetails: {
    type: Object,
    required: true,
  },
  facebookPixel: {
    type: Object,
    default: null,
  },
  storeData: {
    type: Object,
    default: () => {},
  },
  allCurrencies: {
    type: Array,
    default: () => [],
  },
  customerInfo: {
    type: Object,
    default: () => {},
  },
  referralCampaigns: {
    type: Array,
    default: () => [],
  },
  allPages: {
    type: Array,
    default: () => [],
  },
  funnel: {
    type: Object,
    default: () => {},
  },
  locationModal: {
    type: Object,
    default: () => {},
  },
  twoStepFormData: {
    type: Object,
    default: () => {},
  },
  themeStyles: {
    type: Object,
    default: () => {},
  },
});

// For location modal

provide('currencyArray', props.allCurrencies);
provide('allPages', props.allPages);

const parsedCartItems = ref([]);
const store = useStore();
const KeyForDesign =
  props.pageType === 'theme' ? 'template_objects' : 'element';

store.commit('builder/setResponsiveMode', 'desktop');

store.commit('builder/setInitialDesign', {
  type: 'headerDesign',
  design: (props.headerDesign ?? {})[KeyForDesign],
});

store.commit('builder/setInitialDesign', {
  type: 'footerDesign',
  design: (props.footerDesign ?? {})[KeyForDesign],
});

store.commit('onlineStore/setIsPublish', {
  isPublish: props.isPublish,
});

store.commit(
  'onlineStore/setCurrency',
  {
    currency: props.currencyDetails,
  },
  { root: true }
);

store.commit(
  'onlineStore/setProducts',
  {
    products: props.products,
    categories: props.categories,
  },
  { root: true }
);

if (props.pageType === 'mini-store') {
  store.commit('miniStore/setMiniStoreDetails', props.storeDetails, {
    root: true,
  });
}

if (props.pageType !== 'landing' || props.pageType !== 'mini-store') {
  store.commit('onlineStore/setMenuListArray', props.menuListArray, {
    root: true,
  });
}

if (props.socialProof) {
  store.commit('onlineStore/setSocialProof', props.socialProof, {
    root: true,
  });
}

if (props.referralCampaigns) {
  store.commit('onlineStore/setReferralCampaigns', props.referralCampaigns, {
    root: true,
  });
}

// if(props.legalPolicy) {
//   store.commit('onlineStore/setLegalPolicy', props.legalPolicy, {
//     root: true,
//   });
// }

if (props.isPublish) {
  // store.commit('onlineStore/setFacebookPixel', {
  //   facebookPixel: props.facebookPixel,
  // });
  // if (props.facebookPixel?.facebook_selected) {
  //   /*eslint-disable */
  //   !(function (f, b, e, v, n, t, s) {
  //     if (f.fbq) return;
  //     n = f.fbq = function () {
  //       n.callMethod
  //         ? n.callMethod.apply(n, arguments)
  //         : n.queue.push(arguments);
  //     };
  //     if (!f._fbq) f._fbq = n;
  //     n.push = n;
  //     n.loaded = !0;
  //     n.version = '2.0';
  //     n.queue = [];
  //     t = b.createElement(e);
  //     t.async = !0;
  //     t.src = v;
  //     s = b.getElementsByTagName(e)[0];
  //     s.parentNode.insertBefore(t, s);
  //   })(
  //     window,
  //     document,
  //     'script',
  //     'https://connect.facebook.net/en_US/fbevents.js'
  //   );
  //   fbq('init', props.facebookPixel?.pixel_id);
  //   store.dispatch('onlineStore/fetchInitialDataFB');
  //   store.dispatch('onlineStore/pageViewFB');
  // }
}

store.commit('pageMetadata/setStoreDetails', props.storeDetails, {
  root: true,
});

store.commit('customerAccount/loginCustomerInfo', props.customerInfo, {
  root: true,
});

/**
 * @feature theme-builder
 * To update theme elements' styles
 */

const legalPolicy = computed(() => props.storeData?.legalPolicy);

const isOnlineStore = computed(() =>
  ['page', 'online-store'].includes(props.pageType)
);
const isMiniStore = computed(() => props.pageType === 'mini-store');

const isThemePreview = computed(() => props.pageType === 'theme');

const isTemplatePreview = computed(() =>
  ['userTemplate', 'template'].includes(props.pageType)
);

const isMiniStoreCheckout = computed(() =>
  typeof window !== `undefined`
    ? window?.location?.pathname === '/checkout/mini-store'
    : false
);

const getSections = (sectionType) =>
  store.getters['builder/allSections'](sectionType);

const headerSections = computed(() => getSections('header'));
const footerSections = computed(() => getSections('footer'));

parsedCartItems.value = clone(store.state.onlineStore.cartItems);

const screenWidth = computed(() =>
  typeof window !== `undefined` ? window.screen.width : 0
);

watch(
  screenWidth,
  (newValue) => {
    switch (true) {
      case newValue <= 480:
        store.commit('builder/setResponsiveMode', 'mobile');
        break;
      case newValue > 480 && newValue <= 1080:
        store.commit('builder/setResponsiveMode', 'tablet');
        break;
      default:
        store.commit('builder/setResponsiveMode', 'desktop');
    }
  },
  { immediate: true }
);

watchEffect(() => {
  parsedCartItems.value = clone(store.state.onlineStore.cartItems);
  return parsedCartItems.value;
});

onBeforeMount(() => {
  store.commit('onlineStore/setCartItems');
});
</script>

<style lang="scss">
#app {
  background: white;
  font-family: 'Lato', sans-serif;
}

// contenteditable contents
// paragraph, heading, faq, imagebox and testimonial
.text-content-wrapper span {
  outline: none;
  & * {
    margin-bottom: 0;
    white-space: pre-wrap;
  }

  &:deep(p),
  &:deep(pre),
  &:deep(ul),
  &:deep(ol) {
    margin-bottom: 0;
  }

  &:deep(li) {
    margin-left: 1.3em;
    margin-bottom: 0;
  }
}
.text-content-wrapper {
  h1,
  h2,
  h3,
  h4,
  h5,
  h6,
  p,
  pre,
  li {
    margin-bottom: 0;
  }
}
</style>

<style scoped lang="scss">
.mini-store-header {
  border-bottom: 1px solid lightgray;
  top: 0;
  z-index: 2;
  width: 100%;
  position: sticky;
}
</style>

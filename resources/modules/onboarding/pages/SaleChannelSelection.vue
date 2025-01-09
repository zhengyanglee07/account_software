<template>
  <div class="selection-head">
    <div
      class="fw-bolder d-flex align-items-center text-dark mb-5"
      style="font-size: 19.5px"
    >
      Choose a Sales Channel Type
    </div>
    <div class="row">
      <div
        v-for="channelDetail in channelDetails"
        :key="channelDetail.type"
        class="col-lg-4"
      >
        <!-- test -->
        <OnboardingSelectionCard
          :is-active="selectedSalesChannel === channelDetail.type"
          style="height: 300px"
          :channel="channelDetail.type"
          @click="selectedSalesChannel = channelDetail.type"
        >
          <template #image>
            <img alt="img" :src="channelDetail.image" style="width: 60px" />
          </template>
          <template #title>
            {{ channelDetail.title }}
          </template>
          <template #description>
            {{ channelDetail.description }}
          </template>
        </OnboardingSelectionCard>
      </div>
    </div>
    <BaseButton
      style="float: right; margin: 20px 0"
      @click="update(selectedSalesChannel)"
    >
      Continue <i class="ms-2 fa-solid fa-arrow-right-long" />
    </BaseButton>
  </div>
</template>
<script>
import Cookies from 'js-cookie';
import funnelIcon from '@shared/assets/media/primary-funnel-icon.svg';
import onlineStoreIcon from '@shared/assets/media/primary-online-store-icon.svg';
import miniStoreIcon from '@shared/assets/media/primary-mini-store-icon.svg';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';
import OnboardingSelectionCard from '@onboarding/components/OnboardingSelectionCard.vue';

export default {
  name: 'SaleChannelTypePage',
  components: { OnboardingSelectionCard },
  layout: OnboardingLayout,
  data() {
    return {
      selectedSalesChannel: 'funnel',
      channelDetails: [
        {
          title: 'Funnel Setup',
          type: 'funnel',
          description:
            'Convert your visitors into customers with beautiful landing pages and upsell them with just one click. Best for those who sell services and digital products.',
          image: funnelIcon,
        },
        {
          title: 'Online Store Setup',
          type: 'online-store',
          description:
            ' Customize the design of your online store to make your brand stand out from the crowd. Best for those who want to create a unique shopping experience for their customers.',
          image: onlineStoreIcon,
        },
        // {
        //   title: 'Mini Store Setup',
        //   type: 'mini-store',
        //   description:
        //     'Start selling online in just 10 minutes! No designer or programmer required! Best for those who want to sell online with minimum effort.',
        //   image: miniStoreIcon,
        // },
      ],
    };
  },

  methods: {
    url() {
      switch (Cookies.get('saleChannel')) {
        case 'mini-store':
          return '/dashboard';
        case 'online-store':
          return '/dashboard';
        default:
          return '/dashboard';
      }
    },
    update(data) {
      Cookies.set('saleChannel', data);
      const promise1 = axios.post('/sale-channel/save', {
        saleChannelType: data,
      });
      const promise2 =
        data === 'mini-store' ? axios.post('/domain/ministore') : null;
      Promise.allSettled([promise1, promise2])
        .then((response) => {
          // axios.put('/send/onboarding_email');
          localStorage.setItem('selectedSalesChannel', data);
          this.$inertia.visit('/dashboard', { replace: true });
        })
        .catch((error) => {
          // this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },
  },
};
</script>

<style scoped lang="scss">
.main-title {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 100%;
  margin-bottom: 20px;

  &__title {
    height: 50px;
  }
}

:deep(.d-flex) {
  display: block !important;
}

:deep(.text-start) {
  text-align: center !important;
}

@media (max-width: 991px) {
  :deep(.btn.btn-outline.btn-outline-dashed.btn-outline-default) {
    height: 210px !important;
  }
}

@media (max-width: 767px) {
  :deep(.btn.btn-outline.btn-outline-dashed.btn-outline-default) {
    height: 195px !important;
  }
}

@media (max-width: 530px) {
  :deep(.btn.btn-outline.btn-outline-dashed.btn-outline-default) {
    height: 260px !important;
  }
}
</style>

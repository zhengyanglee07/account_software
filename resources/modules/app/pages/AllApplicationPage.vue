<template>
  <div class="settings_whole">
    <h1 class="fs-2 my-6">Sale Channels</h1>
    <!-- Sale Channels -->
    <div class="settings_container">
      <div class="row g-5">
        <!-- <div class="col-xl-4">
          <BaseCard
            no-card-bottom-margin
            style="height: 220px"
          >
            <div class="setting_card_image">
              <img
                src="@shared/assets/media/primary-mini-store-icon.svg"
                alt="Mini Store"
              >
            </div>
            <div
              class="setting_card_details"
              style="padding: 5px 20px; min-height: 45px"
            >
              <div>
                <p class="setting_card_details_title">
                  Mini Store
                </p>
                <template
                  v-if="
                    state.selectedSaleChannel.includes('mini-store') && false
                  "
                >
                  <a
                    href="javascript:void(0)"
                    @click="setupMiniStore"
                  >
                    Setup now
                  </a>
                </template>
                <template v-if="!state.miniStoreErr">
                  <p class="text-danger setting_card_details_description p-two">
                    No domain for Mini Store.
                  </p>
                  <Link href="/domain/settings">
                    Setup Mini Store domain here
                  </Link>
                </template>
              </div>
              <BaseFormSwitch
                :model-value="checkSaleChannel('mini-store')"
                @click="selected('mini-store')"
              />
            </div>
          </BaseCard>
        </div> -->

        <div class="col-xl-4">
          <BaseCard no-card-bottom-margin style="height: 220px">
            <div class="setting_card_image" style="height: 120px">
              <img
                src="@shared/assets/media/primary-online-store-icon.svg"
                alt="Online Store"
              />
            </div>
            <div
              class="setting_card_details"
              style="padding: 5px 20px; min-height: 55px"
            >
              <div class="setting_card_details_text">
                <p class="setting_card_details_title">Online Store</p>
                <template v-if="!state.onlineStoreErr">
                  <p class="text-danger setting_card_details_description p-two">
                    No domain for Online Store.
                  </p>
                  <Link href="/domain/settings">
                    Setup Online Store domain here
                  </Link>
                </template>
              </div>
              <BaseFormSwitch
                :model-value="checkSaleChannel('online-store')"
                @click="selected('online-store')"
              />
            </div>
          </BaseCard>
        </div>
        <div class="col-xl-4">
          <BaseCard no-card-bottom-margin style="height: 220px">
            <div class="setting_card_image">
              <img
                src="@shared/assets/media/primary-funnel-icon.svg"
                alt="Funnel"
              />
            </div>
            <div
              class="setting_card_details"
              style="padding: 5px 20px; min-height: 45px"
            >
              <div class="setting_card_details_text">
                <p class="setting_card_details_title">Sales Funnel</p>
              </div>
              <BaseFormSwitch
                :model-value="checkSaleChannel('funnel')"
                @click="selected('funnel')"
              />
            </div>
          </BaseCard>
        </div>
      </div>
    </div>
    <!-- Sale Channels end-->

    <!-- Apps -->
    <div class="justify-content-between" style="padding-top: 30px">
      <h2>Apps</h2>
    </div>
    <div class="settings_container">
      <div class="row pt-5 g-5">
        <div
          v-for="(app, type) in state.deliveryApps"
          :key="type"
          class="col-xl-4"
          @click="checkApp(type) ? redirectToSetup(type) : ''"
        >
          <BaseCard no-card-bottom-margin style="height: 240px">
            <div class="setting_card_image">
              <img
                :src="app['logo']"
                :alt="app['name']"
                :style="`transform: scale(${
                  app['name'] === 'Facebook Pixel & Conversion API' ? 2.5 : 2
                })`"
              />
            </div>
            <div
              class="setting_card_details"
              style="padding: 5px 20px; min-height: 45px"
            >
              <div class="setting_card_details_text">
                <p class="setting_card_details_title">
                  {{ app['name'] }}
                </p>
                <template v-if="checkApp(type)">
                  <a href="javascript:void(0)" @click="redirectToSetup(type)">
                    Settings
                  </a>
                </template>
              </div>
              <BaseFormSwitch
                :model-value="checkApp(type)"
                @click="selectApp(type)"
              />
            </div>
          </BaseCard>
        </div>
      </div>
    </div>

    <!-- Features -->
    <!-- <div class="justify-content-between" style="padding-top: 30px">
      <h2>Features</h2>
    </div>
    <div class="settings_container">
      <div class="row pt-5 g-5">
        <div
          v-for="(app, type) in state.featureApps"
          :key="type"
          class="col-xl-4"
        >
          <BaseCard no-card-bottom-margin style="height: 240px">
            <div
              class="setting_card_details"
              style="padding: 5px 20px; min-height: 45px"
            >
              <div class="setting_card_details_text">
                <p class="setting_card_details_title">
                  {{ app['name'] }}
                </p>
              </div>
              <BaseFormSwitch
                :model-value="
                  !!usePage().props.enabledApps?.find((e) => e.type === type)
                "
                @click="saveFeature(type)"
              />
            </div>
          </BaseCard>
        </div>
      </div>
    </div> -->
  </div>
</template>
<script setup>
import appsAPI from '@app/api/appsAPI.js';
import delyvaIcon from '@shared/assets/media/Delyva-Logo.svg';
import facebookIcon from '@shared/assets/media/facebook.svg';
import { reactive, inject, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const $toast = inject('$toast');

const props = defineProps({
  saleChannels: { type: Array, default: () => {} },
  apps: { type: Array, default: () => {} },
  selectedType: { type: Array, default: () => {} },
  domainsInfo: { type: Array, default: () => {} },
  selectedAppType: { type: Array, default: () => {} },
});

const state = reactive({
  selectedSaleChannel: [],
  selectedApp: [],
  currentChannel: '', // check current activated
  miniStoreErr: true,
  onlineStoreErr: true,
  delyvaErr: true,
  deliveryApps: {
    delyva: {
      name: 'Delyva',
      logo: delyvaIcon,
    },
  },
});

const setupMiniStore = () => {
  appsAPI
    .saveMiniStore()
    .then(() => {
      router.visit('/mini-store/setup');
    })
    .catch((error) => {
      $toast.error('Error', 'Unexpected Error Occured');
      console.log(error);
    });
};

const checkSaleChannel = (data) => {
  const arr = props.saleChannels;
  const obj = arr.find((o) => o.type === data);
  return state.selectedSaleChannel.includes(obj.type);
};

const saveFeature = (feature) => {
  appsAPI.updateFeature(feature).then(({ data }) => {
    $toast.success(
      'Success',
      `Successfully ${data ? 'enabled' : 'disabled'} ${feature}`
    );
    window.location.reload();
  });
};

const save = () => {
  appsAPI
    .updateSalesChannel(state.selectedSaleChannel, state.currentSaleChannel)
    .then(({ data }) => {
      $toast.success('Success', data.message);
      if (!data.link) {
        window.location.replace('/apps/all');
      } else {
        router.visit(data.link);
      }
    })
    .catch((error) => {
      $toast.error('Error', 'Unexpected Error Occured');
    });
};

const update = (type) => {
  if (!state.selectedSaleChannel.includes(type)) {
    state.selectedSaleChannel.push(type);
    state.currentSaleChannel = type;
  } else {
    const index = state.selectedSaleChannel.findIndex(
      (selectedType) => selectedType === type
    );
    state.selectedSaleChannel.splice(index, 1);
  }
  save();
};

const selected = (data) => {
  const arr = props.saleChannels;
  const obj = arr.find((o) => o.type === data);
  update(obj.type);
};

const checkApp = (data) => {
  const arr = props.apps;
  const obj = arr.find((o) => o.type === data);
  return state.selectedApp.includes(obj.type);
};

const saveApp = () => {
  appsAPI
    .updateApps(state.selectedApp, state.currentChannel)
    .then(({ data }) => {
      $toast.success('Success', data.message);
      if (
        ['easyparcel', 'lalamove'].includes(state.currentChannel) &&
        checkApp(state.currentChannel)
      ) {
        router.visit(`/apps/${state.currentChannel}`);
      } else if (!data.link) {
        router.visit('/apps/all');
      } else {
        router.visit(data.link);
      }
    })
    .catch((error) => {
      $toast.error('Error', 'Unexpected Error Occured');
    });
};

const updateApp = (type) => {
  state.currentChannel = type;
  if (!state.selectedApp.includes(type)) {
    state.selectedApp.push(type);
  } else {
    const index = state.selectedApp.findIndex(
      (selectedAppType) => selectedAppType === type
    );
    state.selectedApp.splice(index, 1);
  }
  saveApp();
};

const selectApp = (data) => {
  const arr = props.apps;
  const obj = arr.find((o) => o.type === data);
  updateApp(obj.type);
};
const redirectToSetup = (type) => {
  try {
    if (type === 'delyva') router.visit('/delyva/setup');
    else if (type === 'facebook') router.visit('/facebook/setting');
    else router.visit(`/apps/${type}`);
  } catch {
    $toast.error('Error', 'Unexpected Error Occured');
  }
};

onMounted(() => {
  state.selectedSaleChannel = props.selectedType;
  state.selectedApp = props.selectedAppType;
  if (state.selectedSaleChannel.includes('mini-store')) {
    state.miniStoreErr = props.domainsInfo.some((e) => e.type === 'mini-store');
  }
  if (state.selectedSaleChannel.includes('online-store')) {
    state.onlineStoreErr = props.domainsInfo.some(
      (e) => e.type === 'online-store'
    );
  }
  if (state.selectedApp.includes('delyva')) {
    state.delyvaErr = props.domainsInfo.some((e) => e.type === 'delyva');
  }
});
</script>

<style lang="scss" scoped>
.setting_whole {
  @media (max-width: $md-display) {
    padding: 0 16px;
  }
}

.setting_card {
  height: auto;
  width: 100%;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  box-shadow: 0px 1px 1px 0px rgb(0 0 0 / 20%);
  border: 1px solid #ced4da;
  border-radius: 5px;
  background-color: white;
}

.setting_card_details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 20px;
  margin: 0;

  p {
    margin: 0;
    padding: 0;
  }
  .setting_card_details_text {
    width: 80%;
  }
  .setting_card_details_description {
    color: gray;
    @media (max-width: $md-display) {
      font-size: 12px !important;
    }
  }

  .setting_card_details_title {
    font-weight: bold;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
  }
}

.setting_card_image {
  height: 130px;
  display: flex;
  justify-content: center;
  // border-bottom: 1px solid #d3d3d3;
  align-items: center;
  width: 100%;

  img {
    width: 6em;
    filter: brightness(1);
  }
}
.delivery-apps:hover {
  color: #7766f7;
}
</style>

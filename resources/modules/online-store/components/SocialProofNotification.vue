<template>
  <div
    v-if="showModal"
    id="provely-pop-up"
    :class="position"
    :style="[
      {
        bottom:
          isProductDescriptionPage && position === 'bottom' ? '75px' : '0',
      },
      'z-index: 2147483645; position: fixed; height: 80px;',
    ]"
  >
    <div :class="layoutClass">
      <a
        v-show="notify?.event_type === 'product' || 'form'"
        :href="event?.url"
        target="_blank"
        class="modal-link"
      >
        <div class="d-flex">
          <div class="preview__image">
            <div
              v-show="notify.image_path && image === null"
              class="preview__image-mask"
            />
            <img :src="notify.image_path || image">
          </div>
          <div class="preview__details">
            <!-- <b
              v-show="notify?.event_type === 'form'"
              class="preview__details-title"
            >Form Submission</b> -->
            <p
              :style="{
                '-webkit-line-clamp': 2,
              }"
            >
              <b
                id="name"
                :style="{
                  'text-overflow': ellipsis,
                  overflow: hidden,
                  'white-space': nowrap,
                }"
                :class="fontClass"
              >{{ name }} </b><br>
              <span
                v-if="notify?.event_type === 'form'"
                id="eventMessage"
                class="preview__details-action"
              >
                {{ ' ' + eventMessage }}</span>
              <!-- <span
                v-if="notify?.event_type === 'form'"
                id="eventName"
                :class="fontClass"
              >{{ eventName }}</span> -->
            </p>
            <p
              v-if="notify?.event_type !== 'form' && notify.message === null"
              :style="{
                '-webkit-line-clamp': 1,
              }"
            >
              <span
                id="eventMessage"
                class="preview__details-action"
              >{{
                eventMessage
              }}</span>
              <span
                id="eventName"
                :class="fontClass"
              >{{ eventName }}</span>
            </p>
            <p
              v-if="notify.message !== null && notify?.event_type !== 'form'"
              :style="{
                '-webkit-line-clamp': 1,
              }"
            >
              <span class="preview__details-action">{{ notify.message }}</span>
            </p>
            <p class="preview__details-time">
              {{ timeMessage }}
            </p>
            <a
              href="https://my.hypershapes.com"
              target="_blank"
              class="verified-text"
            >
              <i class="fas fa-check-square" />
              <span> Verified by Hypershapes</span>
            </a>
          </div>
        </div>
      </a>
    </div>
  </div>
</template>
<script setup>
import clone from 'clone';
import { ref, onMounted, computed, onBeforeMount } from 'vue';
import { useStore } from 'vuex';

const store = useStore();
const showModal = ref(false);
const index = ref(-1);
const event = ref({});
const notify = ref({});
const setting = ref({});
const loading = ref(true);
const processedContacts = ref([]);
const selectedFunnelId = ref([]);
const screen = ref({ width: 0 });
const mobile = ref('bottom');
const desktop = ref('bottom-left');
const loop = ref('');
const display = ref(0);
const delay = ref(0);
const isProductDescriptionPage = ref(false);

const name = computed(() => {
  const fname =
    // eslint-disable-next-line no-nested-ternary
    notify?.value?.is_anonymous || event?.value?.fname === null
      ? 'Someone'
      : notify?.value?.show_first_name
      ? event?.value?.fname?.split(' ')[0]
      : event?.value?.fname;
  const city =
    event?.value?.city === null ? '' : ` from ${event?.value?.city}, `;
  const country =
    event?.value?.country === null ? '' : `${event?.value?.country}`;
  const message = fname + city + country;
  // return notify?.value?.message === null || notify?.value?.message === ''
  //   ? message
  //   : '';
  return message;
});

const eventMessage = computed(() => {
  if (notify?.value?.message === null || notify?.value?.message === '') {
    switch (notify?.value?.event_type) {
      case 'product':
        return ' just purchased ';
      case 'form':
        return 'just submitted a form';
      default:
        return '';
    }
  } else return notify?.value?.message;
});

const eventName = computed(() => {
  if (notify?.value?.message === null || notify?.value?.message === '') {
    switch (notify?.value?.event_type) {
      case 'product':
        return event?.value?.product_name;
      case 'form':
        return event?.value?.form_title;
      default:
        return '';
    }
  }
  return '';
});

const image = computed(() => {
  switch (notify?.value?.event_type) {
    case 'product': {
      return event?.value?.product_image === '/images/product-default-image.png'
        ? 'https://cdn.hypershapes.com/assets/product-default-image.png'
        : event?.value?.product_image;
    }
    case 'form':
      return 'https://media.hypershapes.com/images/avatar.jpeg';
    default:
      return '';
  }
});

const timeMessage = computed(() => {
  const timeNow = new Date();
  const convertEventTime = new Date(event?.value?.created_at);
  const diff = (timeNow.getTime() - convertEventTime.getTime()) / 1000;
  const time =
    notify?.value?.event_type === 'product' || 'form'
      ? (convertEventTime, diff, Math.floor(diff / 60))
      : '';

  const timeContext =
    time > 60
      ? `${Math.floor(time / 60).toString()} hours ago`
      : `${time.toString()} mins ago`;
  return time > notify?.value?.greater_than_time ? 'Recently' : timeContext;
});

const layoutClass = computed(() => {
  switch (notify?.value?.layout_type) {
    case 'squared':
      return 'preview-container';
    case 'squared-padding':
      return event?.value?.form_title;
    case 'rounded':
      return 'preview-container notification--rounded';
    case 'rounded-padding':
      return 'preview-container notification--rounded-padding';
    default:
      return '';
  }
});

const fontClass = computed(() => {
  switch (notify?.value?.event_type) {
    case 'product':
      return 'preview__details-title';
    case 'form':
      return 'preview__details-action';
    default:
      return '';
  }
});

const position = computed(() =>
  screen?.value?.width <= 480
    ? mobile?.value ?? 'bottom'
    : desktop?.value ?? 'bottom-left'
);

const initializeNotification = () => {
  const { socialProof } = clone(store.state.onlineStore);
  if (!Object.keys(socialProof).length) return;
  setting.value = socialProof?.socialProofSetting;
  if (!setting.value) {
    setting.value = {
      is_loop_notification: true,
      is_random_notification: false,
      display_time: 10,
      delay_time: 5,
    };
  }
  display.value = setting.value.display_time;
  delay.value = setting.value.delay_time;
  notify.value = socialProof?.notification;
  processedContacts.value = setting?.value?.is_random_notification
    ? socialProof.processedContacts.sort(() => Math.random() - 0.5)
    : socialProof.processedContacts;
  if (processedContacts.value !== null) {
    selectedFunnelId.value =
      processedContacts.value.length > 0
        ? processedContacts.value[0].funnel_id
        : selectedFunnelId.value;
    processedContacts.value?.sort(
      (b, a) => new Date(a.created_at) - new Date(b.created_at)
    );
  }
  mobile.value = notify.value.mobile_position;
  desktop.value = notify.value.desktop_position;
  if (socialProof.eventDetails?.length >= 1) loading.value = false;
};

const show = async () => {
  if (
    !setting.value?.is_loop_notification &&
    index.value === processedContacts.value.length - 1
  ) {
    clearInterval(loop.value);
    return;
  }
  if (index.value === processedContacts.value.length - 1) {
    index.value = -1;
  }
  index.value += 1;

  setTimeout(() => {
    event.value = processedContacts?.value[index?.value];
    showModal.value = true;
  }, delay.value * 1000);

  setTimeout(() => {
    showModal.value = false;
  }, (delay.value + display.value) * 1000);
};

const modalTransition = async () => {
  await show();
  loop.value = setInterval(show, (delay.value + display.value) * 1000);
};

onBeforeMount(() => {
  window.addEventListener('resize', screen.value);
  screen.value.width = window.screen.width;
});

onMounted(async () => {
  const url = window.location.pathname;
  if (/\/products\//i.test(url) && url !== '/products/all')
    isProductDescriptionPage.value = true;
  initializeNotification();
  // console.log(Object.keys(store.state.builder.funnelSettings ?? {}).length !== 0)
  const isFunnel =
    Object.keys(store.state.builder.funnelSettings ?? {}).length !== 0;
  const funnelId = isFunnel ? store.state.builder.funnelSettings.id : '';

  if (
    ((isFunnel &&
      notify.value?.display_type === 'funnel' &&
      selectedFunnelId.value?.includes(funnelId)) ||
      (!isFunnel && notify.value?.display_type === 'online-store')) &&
    !loading.value
  ) {
    modalTransition();
  }
});
</script>
<style scoped lang="scss">
p {
  margin-bottom: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
}

.preview {
  &-container {
    border: 1px solid #ced4da;
    width: 275px;
    height: 60px;
    padding: 0;
    overflow: hidden;
    margin: 10px 5px 5px;
    border-radius: 5px;
    position: relative;
    min-height: 60px;
    background: #fff;
  }

  &__image {
    width: 60px;
    height: 60px;
    // margin: auto 0;
    display: flex;
    justify-content: center;
    align-items: center;

    &-mask {
      position: absolute;
      width: 60px;
      height: 100%;
      background: #000;
      opacity: 0.1;
      top: 0px;
    }

    img {
      object-fit: cover;
      width: 100%;
      height: 100%;
    }
  }

  &__details {
    padding: 3px 12px 3px 15px;
    margin: auto 0;
    line-height: 1.1;
    max-width: 215px;
    position: relative;
    top: -2px;
    width: -webkit-fill-available !important;

    &-title {
      font-size: 10px;
      font-weight: 700;
    }

    &-action {
      font-size: 10px;
    }

    &-time {
      font-size: 8px;
      color: #808080;
      padding-top: 4px;
    }
  }
}

.verified-text {
  text-decoration: none;
  position: absolute;
  right: 10px;
  bottom: 2px;
  font-size: 8px;
  color: gray;

  i {
    font-size: 8px;
    color: $h-primary;
  }

  span {
    color: $h-primary;
    font-size: 8px;
    font-weight: bold;
  }
}

.notification {
  &--rounded {
    $border: 3px;
    border-radius: 60px;
    .preview__image-mask {
      border-radius: 50%;
      border: 3px solid #fff;
    }

    img {
      border: 4px solid #fff;
      border-radius: 30px;
      margin-top: -1px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }

    .verified-text {
      right: 20px;
    }
  }

  &--rounded-padding {
    $border: 8px;
    border-radius: 60px;
    .preview__image-mask {
      border-radius: 50%;
      border: $border solid #fff;
    }

    img {
      border: $border solid #fff;
      border-radius: 60px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }

    .verified-text {
      right: 20px;
    }
  }

  &--squared-padding {
    $border: 8px;
    .preview__image-mask {
      border: $border solid #fff;
      border-radius: 10px;
    }

    img {
      border: $border solid #fff;
      border-radius: 10px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }
  }
}

.bottom-right {
  bottom: 10px;
  right: 10px;
}

.bottom-left {
  bottom: 10px;
  left: 10px;
}

.top-right {
  top: 10px;
  right: 10px;
}

.top-left {
  top: 10px;
  left: 10px;
}

.modal-link {
  color: black;
  text-decoration: none;
}

.top {
  top: 0;
  @media (max-width: 480px) {
    width: 100%;
    .preview-container {
      width: 100%;
      margin: 0px;
    }
    .preview__details {
      width: 100%;
      max-width: initial;
    }
  }
}

.bottom {
  bottom: 0;
  @media (max-width: 480px) {
    width: 100%;
    .preview-container {
      width: 100%;
      margin: 0px;
    }
    .preview__details {
      width: 100%;
      max-width: initial;
    }
  }
}
</style>

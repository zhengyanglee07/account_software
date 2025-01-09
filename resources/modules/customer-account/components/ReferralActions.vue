<template>
  <div class="row w-100 p-0 gx-5">
    <div
      v-for="(action, index) in actions"
      :id="'inviter-action-' + index"
      :key="'inviter-action-' + index"
      :class="responsiveMode === 'mobile' ? 'col-12 mb-4' : 'col-6'"
    >
      <BaseCard
        no-card-bottom-margin
        class="border h-100"
      >
        <div class="d-flex align-items-center">
          <div class="me-6">
            <img
              type="image"
              class="reward-image"
              :src="customImage(index) || images[image(action)]?.default"
            >
          </div>
          <div class="flex-column justify-content-center">
            <h6 class="m-0">
              {{
                action.type !== 'custom'
                  ? actionTitle(action.type, action.referralType)
                  : action.title
              }}
              <i
                v-if="
                  (action.type === 'join' && actionInfo?.joined) ||
                    (action.type === 'custom' &&
                      actionInfo?.logs?.includes(action.id)) ||
                    (action.type === 'sign-up' &&
                      action.referralType === 'invitee') ||
                    (action.type === 'purchase' &&
                      action.referralType === 'invitee' &&
                      actionInfo?.isPurchased)
                "
                class="fa-solid fa-circle-check ms-1"
                style="color: #50cd89"
              />
            </h6>
            <BaseBadge
              v-if="
                (action.type === 'purchase' || action.type === 'sign-up') &&
                  action.referralType === 'inviter'
              "
              class="counter"
              :text="
                action.type === 'purchase'
                  ? actionInfo?.orderCount || 0
                  : actionInfo?.signUpCount || 0
              "
            />
            <p class="m-0 my-1">
              + {{ action.points }} points
            </p>
            <div
              v-if="action?.type === 'custom'"
              class="mb-0"
              @click="referralActionLog(action)"
            >
              <p
                v-if="
                  action.type === 'custom' &&
                    actionInfo?.logs?.includes(action.id)
                "
                class="mb-0"
              >
                <BaseButton
                  size="sm"
                  is-open-in-new-tab
                  :type="
                    action.type === 'custom' &&
                      actionInfo?.logs?.includes(action.id)
                      ? 'secondary'
                      : 'primary'
                  "
                  :href="
                    action?.url?.includes('https://')
                      ? action?.url
                      : 'https://' + action?.url
                  "
                >
                  Earn Points Now
                </BaseButton>
              </p>
              <ActionButton
                v-else
                :button-settings="{
                  settings: generalSettings,
                  styles: styleSettings,
                }"
                :button-index="index"
                :href="
                  action?.url?.includes('https://')
                    ? action?.url
                    : 'https://' + action?.url
                "
              />
            </div>
          </div>
        </div>
      </BaseCard>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toRefs, ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useStore } from 'vuex';
import ActionButton from '@customerAccount/components/ReferralActionsButton.vue';

import cloneDeep from 'lodash/cloneDeep';

import { kebabCaseLize } from '@builder/store/helpers.js';

export default {
  components: {
    ActionButton,
  },
  props: {
    actions: {
      type: Array,
      default: () => [],
    },
    inviteeActions: {
      type: Array,
      default: () => [],
    },
    referralPoints: {
      type: Number,
      default: () => 0,
    },
    refKey: {
      type: String,
      default: () => null,
    },
    referralCode: {
      type: String,
      default: () => null,
    },
    referralActionInfo: {
      type: Object,
      default: () => null,
    },
    settingActions: {
      type: Array,
      default: () => null,
    },
    generalSettings: {
      type: Object,
      default: null,
    },
    styleSettings: {
      type: Object,
      default: null,
    },
    mode: {
      type: String,
      default: () => null,
    },
  },
  emits: ['get'],

  setup(props, context) {
    const points = ref(0);
    const actionInfo = ref(null);
    const store = useStore();
    const defaultStyles = cloneDeep(props.styleSettings);

    const actionTitle = (type, referralActionType) => {
      switch (type) {
        case 'sign-up':
          return `${
            referralActionType === 'inviter' ? 'Refer' : 'Become'
          } a new sign up`;
        case 'join':
          return 'Join this campaign';
        default:
          return `${
            referralActionType === 'inviter' ? 'Refer' : 'Become'
          } a new customer`;
      }
    };
    const inviteeActionTitle = (type) => {
      switch (type) {
        case 'sign-up':
          return 'Become a new sign up';
        default:
          return 'Become a new customer';
      }
    };
    const customImage = (index) => {
      return props.settingActions?.find((el, i) => i === index)?.imagePath;
    };
    const image = (action) => {
      switch (action.type) {
        case 'sign-up':
          return '/resources/modules/shared/assets/media/refer-new-sign-up-icon.svg';
        case 'join':
          return '/resources/modules/shared/assets/media/refer-new-sign-up-icon.svg';
        case 'purchase':
          return '/resources/modules/shared/assets/media/refer-new-customer-icon.svg';
        default:
          return '/resources/modules/shared/assets/media/refer-new-go-icon.svg';
      }
    };
    async function referralActionLog(action) {
      if (props.mode === 'Builder') return;
      const code =
        props.referralCode ??
        JSON.parse(localStorage.getItem('funnel#user'))?.referralCode;
      try {
        axios
          .post('/referral-action-log', {
            ref: props.refKey,
            code,
            action: action.id,
          })
          .then((res) => {
            context.emit('get');
          });
      } catch (error) {
        console.log(error);
      }
    }

    const images = ref({});
    images.value = import.meta.globEager(
      '/resources/modules/shared/assets/media/refer-new-*.svg'
    );

    const settingIndex = computed(() => {
      return store.getters['builder/settingIndex'];
    });

    onMounted(() => {
      points.value = props.referralPoints;
      actionInfo.value = props.referralActionInfo;
    });

    return {
      actionTitle,
      inviteeActionTitle,
      referralActionLog,
      points,
      images,
      actionInfo,
      image,
      customImage,
      kebabCaseLize,
    };
  },

  computed: {
    responsiveMode() {
      return this.$store.state?.builder?.responsiveMode;
    },
  },

  watch: {
    referralActionInfo: {
      deep: true,
      handler(newValue) {
        if (newValue) {
          this.actionInfo = newValue;
        }
      },
    },
    referralPoints(newValue) {
      this.points = newValue;
    },
  },
};
</script>

<style scoped lang="scss">
.row {
  margin-left: 0 !important;
  margin-right: 0 !important;
}

.row > * {
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.counter {
  position: absolute;
  right: 15px;
  align-items: stretch;
  overflow: hidden;
  flex-shrink: 0;
  background-color: #d9dcde;
  border-radius: 20px;
  padding: 0.5 0.5rem;
  text-align: center;
  font-size: 12px;
  margin-right: 25px;
  top: 35%;
}

.col-6 {
  width: 49% !important;
  margin-bottom: 2%;

  &:nth-child(odd) {
    margin-right: 1%;
  }
  &:nth-child(even) {
    margin-left: 1%;
  }
}

.reward-image {
  width: 45px;
  height: 45px;
  min-width: 45px;
  min-height: 45px;
  object-fit: cover;
}
</style>

<template>
  <!-- <h6 class="mb-3">
    Unlock your rewards here
  </h6> -->

  <div class="row w-100 p-0 gx-5">
    <div
      v-for="(reward, index) in rewards"
      :key="index"
      :class="responsiveMode === 'mobile' ? 'col-12 mb-4' : 'col-6'"
    >
      <BaseCard
        :id="`reward-${index}`"
        no-card-bottom-margin
        class="border h-100"
      >
        <div class="d-flex align-items-center">
          <div class="me-6">
            <img
              class="image-style reward-image"
              :src="
                availableRewards?.length
                  ? availableRewards[index]?.imagePath
                    ? availableRewards[index]?.imagePath
                    : rewardImages[
                      `/resources/modules/shared/assets/media/rewards-unlock-icon.svg`
                    ]?.default
                  : rewardImages[
                    `/resources/modules/shared/assets/media/rewards-unlock-icon.svg`
                  ]?.default
              "
              type="image"
            >
          </div>
          <div class="flex-column justify-content-center">
            <h6>{{ reward.title }}</h6>
            <p
              v-if="
                referralPoints >= reward.point_to_unlock && reward.instruction
              "
              class="mb-0"
            >
              <span
                v-for="(instruction, i) in reward.instruction.split('\n')"
                :key="i"
              >
                {{ instruction }}
                <br>
              </span>
            </p>
            <BaseFormGroup
              v-if="referralPoints >= reward.point_to_unlock"
              no-margin
            >
              <BaseFormInput
                v-if="reward.type === 'promo-code'"
                id="promo-code"
                :model-value="reward.promoCode"
                type="text"
                :disabled="true"
              >
                <template #append>
                  <BaseButton
                    :id="`clipboard-button-promo-${index}`"
                    type="link"
                    class="promo-copy-btn"
                    data-bs-toggle="custom-tooltip"
                    data-bs-placement="bottom"
                    title="Copied to Clipboard!"
                    :data-clipboard-text="reward.promoCode"
                  >
                    <i class="far fa-copy" />
                  </BaseButton>
                </template>
              </BaseFormInput>
              <ActionButton
                v-if="reward.type === 'downloadable-content'"
                id="content"
                type="link"
                is-open-in-new-tab
                :href="
                  reward?.value?.includes('https://')
                    ? reward.value
                    : 'https://' + reward.value
                "
                :button-settings="{
                  settings: generalSettings,
                  styles: styleSettings,
                }"
              >
                <template #btn-text>
                  {{ reward?.text || 'Click here to view' }}
                </template>
              </ActionButton>
            </BaseFormGroup>
            <ActionButton
              v-else
              size="sm"
              type="primary"
              :disabled="mode === 'Builder'"
              :button-settings="{
                settings: generalSettings,
                styles: styleSettings,
              }"
              @click="alert"
            >
              <template #btn-text>
                {{ reward.point_to_unlock }} Points To Unlock
              </template>
            </ActionButton>
          </div>
        </div>
      </BaseCard>
    </div>
  </div>
</template>
<script>
import Clipboard from 'clipboard';
import { onMounted, computed, watch, ref } from 'vue';
import ActionButton from '@customerAccount/components/ReferralActionsButton.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: {
    ActionButton,
  },
  props: {
    rewards: {
      type: Array,
      default: () => [],
    },
    referralPoints: {
      type: Number,
      default: () => 0,
    },
    mode: {
      type: String,
      default: () => 'Published',
    },
    listOfRewards: {
      type: Array,
      default: null,
    },
    generalSettings: {
      type: Object,
      default: null,
    },
    styleSettings: {
      type: Object,
      default: null,
    },
  },

  setup(props) {
    const rewardImages = ref({});
    const availableRewards = ref([]);

    onMounted(() => {
      availableRewards.value = props.listOfRewards;
    });

    watch(() => {
      if (props.listOfRewards) availableRewards.value = props.listOfRewards;
    });

    rewardImages.value = import.meta.globEager(
      '/resources/modules/shared/assets/media/rewards-unlock-icon.svg'
    );

    return {
      availableRewards,
      rewardImages,
    };
  },
  computed: {
    responsiveMode() {
      return this.$store.state?.builder?.responsiveMode;
    },
  },

  mounted() {
    setTimeout(() => {
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

      const cb = new Clipboard('.promo-copy-btn');

      cb.on('success', function (e) {
        bootstrap?.then(({ Tooltip }) => {
          // Show success message in toolti
          Tooltip.getInstance(document.getElementById(e.trigger.id)).show();
          // Hide tooltip after 1000ms
          window.setTimeout(function () {
            Tooltip.getInstance(document.getElementById(e.trigger.id)).hide();
          }, 1000);
        });
        e.clearSelection();
      });
    }, 1000);
  },
  methods: {
    alert() {
      alert('You need more points to unlock this reward');
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

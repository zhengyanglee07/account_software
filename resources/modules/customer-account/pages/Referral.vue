<template>
  <EcommerceAccountLayout>
    <template #content>
      <BaseCard v-if="referralCampaign">
        <BaseFormGroup
          label="Referral Link"
          :no-margin="true"
          class="mb-2"
        >
          <BaseFormInput
            id="referral-link"
            class="append-button-activated"
            :model-value="
              (isLocalEnv ? 'http://' : 'https://') +
                domain.domain +
                '?invite=' +
                referralCampaign.referralCode
            "
            type="text"
            name="referral-link"
            :disabled="true"
          >
            <template #append>
              <BaseButton
                id="clipboard-button-campaign"
                style="
                  border-top-left-radius: 0;
                  border-bottom-left-radius: 0;
                  z-index: auto;
                "
                type="primary"
                is-open-in-new-tab
                class="campaign-copy-btn"
                data-bs-toggle="custom-tooltip"
                data-bs-placement="bottom"
                title="Copied to Clipboard!"
                data-bs-title="Copied to Clipboard!"
                :data-clipboard-text="
                  (isLocalEnv ? 'http://' : 'https://') +
                    domain.domain +
                    '?invite=' +
                    referralCampaign.referralCode
                "
                @click="handleClick('copy')"
              >
                <i class="far fa-copy" />
              </BaseButton>
            </template>
          </BaseFormInput>
        </BaseFormGroup>
        <BaseFormGroup>
          <div :style="buttonLayout">
            <ShareButtonElementComponent
              v-for="(listItem, index) in listItems"
              :key="listItem.id"
              :list-item="listItem"
              :index="index"
              :settings="settings"
              :styles="styles"
              :title="title(listItem.text)"
              :description="description(listItem.text)"
              @click="handleClick(listItem.value)"
            />
          </div>
        </BaseFormGroup>
        <h2 class="text-center mb-4">
          Your Points: {{ points || 0 }}
        </h2>
        <ReferralActions
          :ref-key="referralCampaign?.reference_key"
          :actions="referralCampaign?.actions ?? []"
          :invitee-actions="referralCampaign?.inviteeActions ?? []"
          :referral-points="points"
          :referral-code="referralCampaign?.referralCode"
          :referral-action-info="actionInfo"
          @get="getReferral()"
        />
        <h6 class="mb-3">
          Unlock your rewards here
        </h6>

        <ReferralRewards
          :rewards="referralCampaign?.rewards ?? []"
          :referral-points="points"
        />
      </BaseCard>
      <BaseCard
        v-if="!referralCampaign"
        has-header
        title="No referral campaign at this moment"
      />
    </template>
  </EcommerceAccountLayout>
</template>

<script>
import { useStore } from 'vuex';
import { computed } from 'vue';
import Clipboard from 'clipboard';
import EcommerceAccountLayout from '@customerAccount/layout/EcommerceAccountLayout.vue';
import ReferralActions from '@customerAccount/components/ReferralActions.vue';
import ReferralRewards from '@customerAccount/components/ReferralRewards.vue';
import PublishLayout from '@builder/layout/PublishLayout.vue';
import ShareButtonElementComponent from '@builder/components/ShareButtonElementComponent.vue';
import referralsAPI from '@onlineStore/api/referralsAPI.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: {
    EcommerceAccountLayout,
    ReferralActions,
    ReferralRewards,
    ShareButtonElementComponent,
  },
  layout: PublishLayout,
  props: {
    referralCampaign: {
      type: Array,
      default: () => {},
    },
    domain: {
      type: Object,
      default: () => {},
    },
    isLocalEnv: {
      type: Boolean,
      default: false,
    },
    userReferralPoints: {
      type: Number,
      default: 0,
    },
    storeDetails: {
      type: Object,
      default: () => {},
    },
    referralActionInfo: {
      type: Object,
      default: () => null,
    },
    customerInfo: {
      type: Object,
      default: null,
    },
  },

  setup() {
    const store = useStore();
    const mode = computed(() => store.state.builder.mode);
    const settingIndex = computed(() => store.getters['builder/settingIndex']);
    return {
      settingIndex,
      mode,
    };
  },

  data() {
    return {
      points: 0,
      actionInfo: null,
      settings: {
        listItems: [
          {
            id: 0,
            icon: 'fab fa-facebook',
            text: 'Facebook',
            value: 'facebook',
          },
          {
            id: 1,
            icon: 'fab fa-twitter',
            text: 'Twitter',
            value: 'twitter',
          },
          {
            id: 2,
            icon: 'fab fa-whatsapp',
            text: 'Whatsapp',
            value: 'whatsapp',
          },
          {
            id: 3,
            icon: 'fab fa-envelope',
            text: 'Email',
            value: 'email',
          },
          {
            id: 4,
            icon: 'fab fa-linkedin',
            text: 'Linkedin',
            value: 'linkedin',
          },
          {
            id: 5,
            icon: 'fab fa-telegram',
            text: 'Telegram',
            value: 'telegram',
          },
          {
            id: 6,
            icon: 'fab fa-facebook-messenger',
            text: 'Messenger',
            value: 'messenger',
          },
        ],
        buttonShape: '0px',
        gridColumns: ['Auto', 'Auto', 'Auto'],
        alignment: ['flex-start', 'flex-start', 'flex-start'],
        targetUrlMode: 'custom',
        customLink: `${this.isLocalEnv ? 'http://' : 'https://'}${
          this.domain?.domain
        }?invite=${this.referralCampaign?.referralCode}`,
      },

      styles: {
        buttonSize: [50, 50, 50],
        iconFontSize: [1.9, 1.9, 1.9],
        spacing: [2, 2, 2],
        verticalSpacing: [0, 0, 0],
      },
    };
  },

  computed: {
    listItems() {
      const items = this.referralCampaign?.social_network_enabled
        ? this.settings.listItems.filter((el) =>
            this.referralCampaign?.referralSocialNetworks?.includes(el.value)
          )
        : [];
      if (this.referralCampaign?.share_email_enabled)
        items.push({
          id: 3,
          icon: 'fab fa-envelope',
          text: 'Email',
          value: 'email',
        });
      return items;
    },
    buttonLayout() {
      const isAutoColumns =
        this.settings.gridColumns[this.settingIndex].toLowerCase() === 'auto';
      return {
        flexWrap: 'wrap',
        overflow: 'hidden',
        display: isAutoColumns ? 'flex' : 'grid',
        gridTemplateColumns: isAutoColumns
          ? ''
          : `repeat(${
              this.settings.gridColumns[this.settingIndex]
            }, minmax(0px, 1fr))`,
        justifyContent: this.settings.alignment[this.settingIndex],
        gridGap: `1px 0px`,
      };
    },
  },
  watch: {
    userReferralPoints: {
      handler(newVal) {
        this.points = newVal;
      },
    },
  },

  mounted() {
    this.points = this.userReferralPoints;
    this.actionInfo = this.referralActionInfo;
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

      const cb = new Clipboard('.campaign-copy-btn');

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
    async handleClick(type) {
      if (this.mode === 'Published') {
        await referralsAPI.clickSocialShare(
          this.referralCampaign?.referralCode,
          this.referralCampaign?.reference_key,
          type
        );
      }
    },
    title(text) {
      switch (text) {
        case 'Twitter':
          return this.referralCampaign?.social_message || '';
        case 'Email':
          return this.referralCampaign?.email_subject || '';
        default:
          return this.referralCampaign?.social_message || '';
      }
    },
    description(text) {
      switch (text) {
        case 'Email':
          return this.referralCampaign?.email_message || '';
        default:
          return '';
      }
    },
    async getReferral() {
      await referralsAPI
        .getReferralPoints(
          this.referralCampaign?.id,
          this.referralCampaign.referralCode
        )
        .then(
          ({
            data: {
              points,
              joined,
              logData,
              referOrderCount,
              referSignUpCount,
              isPurchased,
              isRootUser,
            },
          }) => {
            this.points = points;
            this.actionInfo = {
              joined,
              logs: logData,
              orderCount: referOrderCount,
              signUpCount: referSignUpCount,
              isPurchased,
              isRootUser,
            };
          }
        );
    },
  },
};
</script>
<style>
.append-button-activated .input-group-text {
  padding: 0 !important;
}
</style>
<style scoped>
@media (max-width: 450px) {
  .share-button-wrapper {
    width: calc(50% - 2px);
  }
}
</style>

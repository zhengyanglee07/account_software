<template>
  <div>
    <section
      v-if="$page.props?.participant?.status === 'approved'"
      class="row"
    >
      <BaseCard>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="clicks"
            text_title="Clicks"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="totalCommissions.toFixed(2)"
            :text_title="`Total Commissions (${defaultCurrency})`"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="pendingCommissions.toFixed(2)"
            :text_title="`Pending Commissions (${defaultCurrency})`"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="approvedCommissions.toFixed(2)"
            :text_title="`Approved Commissions (${defaultCurrency})`"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="withdrawnCommissions.toFixed(2)"
            :text_title="`Withdrawn Commissions (${defaultCurrency})`"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="disapprovedCommissions.toFixed(2)"
            :text_title="`Disapproved Commissions (${defaultCurrency})`"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="disapprovedPayouts.toFixed(2)"
            :text_title="`Disapproved Payouts (${defaultCurrency})`"
          />
        </div>
      </BaseCard>
      <div class="col-8">
        <BaseCard
          has-header
          title="Share Your Unique Links"
        >
          <BaseFormGroup
            v-for="(refLink, refLinkIdx) in participant.referralLinks"
            :key="refLinkIdx"
            label="Share your affiliate referral links to your friends and followers"
          >
            <BaseFormInput
              :id="`referral_link-${refLinkIdx}`"
              :model-value="refLink"
              readonly
            >
              <template #append>
                <i
                  :id="`referral_clipboard-${refLinkIdx}`"
                  class="copy-btn fas fa-link hoverable"
                  :data-clipboard-target="`#referral_link-${refLinkIdx}`"
                  data-bs-toggle="custom-tooltip"
                  data-bs-placement="bottom"
                  data-bs-title="Copied to Clipboard!"
                  title="Copied to Clipboard!"
                />
              </template>
            </BaseFormInput>
            <div class="text-muted fs-7 mt-1">
              You will earn commission when someone place order through your
              affiliate link. Refer to the commission rate
              <a
                href="/affiliates/campaigns"
                class="text-primary"
                style="cursor: 'pointer'"
              >here</a>
            </div>
          </BaseFormGroup>

          <!-- <BaseFormGroup
            label="Share your unique affiliate invitation link to your friends and followers"
            description="Those sign up with your invitation link will become your sublines. You will earn Level-2 commissions when your subline successfully refer an order"
          >
            <BaseFormInput
              id="invitation_link"
              :model-value="participant.affiliateInvitationLink"
              readonly
            >
              <template
                #append
                class="input-group"
              >
                <i
                  id="invitation_clipboard"
                  class="copy-btn fas fa-link hoverable"
                  data-clipboard-target="#invitation_link"
                  data-bs-toggle="custom-tooltip"
                  data-bs-placement="bottom"
                  data-bs-title="Copied to Clipboard!"
                  title="Copied to Clipboard!"
                />
              </template>
            </BaseFormInput>
          </BaseFormGroup> -->

          <BaseFormGroup
            v-if="promoCodes.length !== 0"
            label="Your promo code"
          >
            <template
              v-for="(promoCode, promoIdx) in promoCodes"
              :key="promoIdx"
            >
              <BaseFormInput
                :id="`promo-code-${promoIdx}`"
                :model-value="promoCode"
                readonly
              >
                <template #append>
                  <i
                    :id="`promo-code-clipboard-${promoIdx}`"
                    class="copy-btn fas fa-link hoverable"
                    :data-clipboard-target="`#promo-code-${promoIdx}`"
                    data-bs-toggle="custom-tooltip"
                    data-bs-placement="bottom"
                    title="Copied to Clipboard!"
                  />
                </template>
              </BaseFormInput>
            </template>
          </BaseFormGroup>
        </BaseCard>
      </div>
      <div class="col-4">
        <BaseCard
          title="Sublines Overview"
          has-header
        >
          <div
            v-for="(el, i) in levels"
            :key="i"
          >
            <div class="d-flex flex-stack m-2">
              <span class="text-muted fw-bold fs-6"> Level {{ i + 1 }} </span>
              <span class="text-muted fw-bold fs-6">
                {{ el?.length }} sublines
              </span>
            </div>
            <div
              v-if="(levels?.length || 1) - 1 !== i"
              class="separator separator-dashed my-3"
            />
          </div>
        </BaseCard>
      </div>
    </section>

    <section v-else>
      <BaseDatatable
        no-action
        no-search
        :table-headers="[]"
        :table-datas="[]"
        :custom-description="'Store owner is reviewing your application. You will see the details after approved.'"
        :title="'Be Patient'"
      >
        <template #action-button>
          <p class="text-gray-400 fs-5 fw-bold mb-13">
            * We will notify you by email when you are approved.
          </p>
        </template>
      </BaseDatatable>
    </section>
  </div>
</template>

<script>
import AffiliateCard from '@shared/components/AffiliateCard.vue';
import Clipboard from 'clipboard';
import AffiliateLayout from '@shared/layout/AffiliateLayout.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  components: { AffiliateCard },
  layout: AffiliateLayout,
  props: {
    participant: {
      type: Object,
      default: () => ({}),
    },
    clicks: {
      type: Number,
      default: 0,
    },
    defaultCurrency: {
      type: String,
      default: '$',
    },
    totalCommissions: {
      type: Number,
      default: 0,
    },
    pendingCommissions: {
      type: Number,
      default: 0,
    },
    approvedCommissions: {
      type: Number,
      default: 0,
    },
    disapprovedCommissions: {
      type: Number,
      default: 0,
    },
    withdrawnCommissions: {
      type: Number,
      default: 0,
    },
    promoCodes: {
      type: Array,
      required: true,
    },
    disapprovedPayouts: {
      type: Number,
      default: 0,
    },
    levels: {
      type: Array,
      default: () => [],
    },
  },
  mounted() {
    setTimeout(() => {
      const cb = new Clipboard('.copy-btn');

      cb.on('success', (e) => {
        e.clearSelection();
      });
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('.fa-link')
      );
      bootstrap?.then(({ Tooltip }) => {
        const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
          const tooltip = new Tooltip(tooltipTriggerEl, {
            trigger: 'click',
          });
          tooltipTriggerEl.onmouseleave = () => {
            tooltip.hide();
          };
          return tooltip;
        });
      });
    }, 1000);
  },
};
</script>

<style scoped lang="scss">
.col-8 {
  width: 66.66666667%;
  @media (max-width: 980px) {
    width: 100%;
  }
}
.col-4 {
  width: 33.33333333%;
  @media (max-width: 980px) {
    width: 100%;
  }
}
</style>

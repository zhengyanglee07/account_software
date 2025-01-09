<template>
  <BasePageLayout
    :page-name="pageTitle"
    back-to="/marketing/promotions"
  >
    <template #left>
      <PromotionGeneralTitle :type="type" />
      <PromotionSettings
        v-if="!isLoading"
        :type="type"
        :timezone="timeZone"
        :promotion-detail="promotionDetail?.data"
      />
      <PromotionExtraCondition
        :type="type"
        :loading="isLoading"
      />
    </template>
    <template #right>
      <PromotionSummary
        :setting="setting"
        :currency="currencyStr"
      />
    </template>
    <template #footer>
      <BaseButton
        type="link"
        class="me-6"
        href="/marketing/promotions"
        @click="handleClickEvent"
      >
        Cancel
      </BaseButton>
      <BaseButton
        id="add-promotion-button"
        :disabled="!!loading"
        @click="savePromotion"
      >
        {{ loading ? 'Saving...' : 'Save' }}
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script>
import PromotionGeneralTitle from '@promotion/components/PromotionGeneralTitle.vue';
import PromotionSummary from '@promotion/components/PromotionSummary.vue';
import PromotionExtraCondition from '@promotion/components/PromotionExtraCondition.vue';
import PromotionSettings from '@promotion/components/PromotionSettings.vue';
import { mapActions, mapMutations, mapGetters, mapState } from 'vuex';
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import promotionAPI from '@promotion/api/promotionAPI.js';

export default {
  name: 'AddNewPromotion',
  components: {
    PromotionGeneralTitle,
    PromotionSummary,
    PromotionExtraCondition,
    PromotionSettings,
  },
  mixins: [PromotionCentraliseMixins],
  props: {
    promotionType: { type: String, default: 'manual' },
    promotionDetail: { type: Object, default: () => {} },
    currencyStr: { type: String, default: 'RM' },
    timeZone: { type: String, default: '"Asia/Singapore"' },
    type: { type: String, default: 'new' },
  },
  data() {
    return {
      isLoading: false,
      loading: '',
      promotion: {},
    };
  },
  computed: {
    pageTitle() {
      return `${this.type === 'new' ? 'Add' : 'Edit'} ${
        this.promotionType === 'automatic' ? `Automatic Discount` : `Promo Code`
      }`;
    },
  },
  methods: {
    ...mapMutations('promotions', [
      'setCurrency',
      'emitPromoType',
      'updatePromoError',
      'initializePromotionState',
    ]),
    ...mapActions('promotions', [
      'loadPromotionSetting',
      'loadSelected',
      'setDateAccordingTimezone',
    ]),
    savePromotion() {
      if (this.setting.isExpiryDate && this.setting.endDate === '') {
        this.$toast.error('Error', 'Please enter end date for this promotion');
      } else {
        this.loading = true;
        console.log('saving....');
        let morphType = 'order-discount';
        if (this.setting.discountType === 'freeShipping') {
          morphType = 'free-shipping';
        } else if (this.setting.discountType === 'productBased') {
          morphType = 'product-discount';
        }
        promotionAPI
          .update({
            type: this.promotionType,
            promotionId: 'id' in this.promotion ? this.promotion.id : null,
            promotionType: morphType,
            promotionSetting: this.setting,
          })
          .then((response) => {
            //   console.log(response)
            this.$toast.success(
              'Success',
              `Successful ${
                this.type === 'new' ? 'Added' : 'Updated'
              } Promotion`
            );
            this.$inertia.visit('/marketing/promotions');
          })
          .catch((error) => {
            //   console.log(error.response)
            this.$toast.error(
              'Error',
              'Failed to save. Please check the error'
            );
            console.log(error, 'error');
            this.updatePromoError(error.response.data);
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
    ordinalSuffix() {
      const value = this.setting.minimumQuantity;
      let suffix = 'th';
      if (value === 1) {
        suffix = 'st';
      } else if (value === 2) {
        suffix = 'nd';
      } else if (value === 3) {
        suffix = 'rd';
      }
      return suffix;
    },
    async updateLoadingState() {
      // console.log('updateState')
      this.isLoading = false;
    },
  },

  async mounted() {
    this.initializePromotionState();
    this.promotion = this.promotionDetail?.data ?? {};
    if (this.type === 'edit') {
      this.isLoading = true;
      await this.loadPromotionSetting(this.promotion);
      await this.loadSelected();
      await this.updateLoadingState();
    }
    this.emitPromoType(this.promotionType);
    this.setCurrency(this.currencyStr);
    if (this.type !== 'edit') {
      this.setDateAccordingTimezone(this.timeZone);
    }
    // this.$store.commit('promotions/emitPromoType',( this.promotionType ));
  },
};
</script>

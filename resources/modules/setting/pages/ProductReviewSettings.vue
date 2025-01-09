<template>
  <BasePageLayout
    page-name="Product Review Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Review appearance">
      <template #description>
        Choose the layout for the reviews display in your stores.
      </template>

      <template #content>
        <BaseFormGroup>
          <BaseFormSwitch v-model="displayReview">
            Allow Review Widget
          </BaseFormSwitch>
        </BaseFormGroup>
        <BaseFormGroup label="Layout">
          <BaseFormSelect
            v-model="layoutType"
            label-key="name"
            value-key="value"
            :options="layoutOptions"
          />
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton @click="saveReviewAppearance">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <div class="setting-page__dividor" />

    <BaseSettingLayout
      v-if="showReviewWithRewards"
      title="Collect Review"
    >
      <template #description>
        General settings for ways of collect reviews.
      </template>

      <template #content>
        <BaseFormGroup label="Auto approve and publish reviews">
          <BaseFormSelect
            v-model="autoApproveType"
            label-key="type"
            value-key="value"
            :options="autoApproveOptions"
          />
        </BaseFormGroup>
        <BaseFormGroup label="Type of discount">
          <BaseFormRadio
            v-model="discountType"
            value="0"
            @click="err = false"
          >
            No discount
          </BaseFormRadio>
          <BaseFormRadio
            v-model="discountType"
            value="1"
            @click="err = false"
          >
            Promo code
          </BaseFormRadio>
          <template v-if="discountType === '1'">
            <BaseFormSelect
              v-model="discountId"
              label-key="code"
              value-key="id"
              :options="promotionCodes.filter((el) => el?.code)"
              @input="err = false"
            />
          </template>
          <span
            v-show="err === true"
            class="text-danger"
          >* This field is required</span>
        </BaseFormGroup>

        <BaseFormGroup label="Image of product reviews">
          <BaseFormRadio
            v-model="imageOption"
            value="required"
          >
            Image is required
          </BaseFormRadio>
          <BaseFormRadio
            v-model="imageOption"
            value="optional"
          >
            Image is optional
          </BaseFormRadio>
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton @click="save">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import 'vue-select/dist/vue-select.css';
import eventBus from '@services/eventBus.js';

export default {
  name: 'ProductReviewSettings',

  props: {
    settings: Object,
    promotions: Array,
  },

  data() {
    return {
      autoApproveOptions: [
        { type: 'All Reviews', value: 'all' },
        { type: '5 Star Reviews', value: '5-stars' },
        { type: '4 Stars And Above', value: '4-stars' },
        { type: '3 Stars And Above', value: '3-stars' },
        { type: 'Never', value: 'never' },
      ],
      autoApproveType: '4-stars',
      discountType: '1',
      discountId: '',
      err: false,
      imageOption: 'required',
      layoutOptions: [
        { name: 'Grid', value: 'grid' },
        { name: 'List', value: 'list' },
      ],
      displayReview: true,
      layoutType: 'grid',
      reviewSettings: {
        display_review: 1,
        auto_approve_type: '4-stars',
        discount_type: 1,
        promotion_id: '',
        image_option: 'required',
        layout_type: 'grid',
      },
    };
  },
  computed: {
    promotionCodes() {
      return this.promotions.map((promotion) => {
        return {
          id: promotion.id,
          code: promotion.discount_code,
        };
      });
    },
    showReviewWithRewards() {
      const { featureForPaidPlan } = this.$page.props.permissionData;
      return !featureForPaidPlan.includes('review-with-rewards');
    },
  },
  mounted() {
    if (this.settings) this.reviewSettings = this.settings;
    this.autoApproveType = this.reviewSettings.auto_approve_type;
    this.discountType = String(this.reviewSettings.discount_type);
    this.discountId =
      this.reviewSettings.promotion_id !== null
        ? this.reviewSettings.promotion_id
        : '';
    this.imageOption = this.reviewSettings.image_option;
    this.displayReview = Boolean(this.reviewSettings.display_review);
    this.layoutType = this.reviewSettings.layout_type;
  },

  methods: {
    save() {
      if (this.discountId === '' && this.discountType === '1') {
        this.err = true;
        this.$toast.error('Failed', 'Check the required field!');
        return;
      }

      axios
        .post('/settings/product-review/edit', {
          autoApproveType: this.autoApproveType,
          discountType: this.discountType,
          promotionId: this.discountId,
          imageOption: this.imageOption,
        })
        .then(({ data }) => {
          this.$toast.success('Success', data.message);
        })
        .catch((err) => console.error(err));
    },

    saveReviewAppearance() {
      axios
        .post('/settings/review-appearance/edit', {
          displayReview: this.displayReview,
          layoutType: this.layoutType,
        })
        .then(({ data }) => {
          this.$toast.success('Success', data.message);
        })
        .catch((err) => console.error(err));
    },
  },
};
</script>

<style scoped lang="scss">
.setting-page {
  @media (max-width: $md-display) {
    padding-top: 0px;
  }
}

.primary-small-square-button {
  margin-left: auto;
}

.setting-input {
  height: 36px;
  width: 50%;
  border: 1px solid #ced4da;
  margin-right: 20px;
  padding: 10px;
  border-radius: 2.5px;
}

.toast.show {
  display: none;
  max-width: unset;
  width: inherit;
  //   border: 1px solid red;
  @media (max-width: $md-display) {
    display: block;
    position: fixed;
    // width: 300px;
    font-size: $responsive-base-font-size;
    text-align: center;
    top: 35px;
    width: 100%;
    left: 0;
    right: 0;
    height: 41px;
  }
}

.toast-body {
  font-size: 12px;
}

input[type='radio'].purple-radio {
  z-index: 1;
  top: 6px;
  cursor: pointer;
}
</style>

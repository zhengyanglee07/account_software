<template>
  <BaseTab
    :tabs="tabs"
    :default-index="type && type === 'automatic' ? 1 : 0"
    @click="updateSelectedType"
  />

  <BaseDatatable
    v-if="parsedDataArray"
    title="promotion"
    :table-headers="tableHeaders"
    :table-datas="parsedDataArray"
    @delete="deletePromotion"
  >
    <template #action-button>
      <BaseButton
        id="add-manual-promotion-button"
        class="me-2"
        has-add-icon
        @click="createPromotion('manual')"
      >
        Add promo code
      </BaseButton>

      <BaseButton
        id="add-automatic-promotion-button"
        has-add-icon
        @click="createPromotion('automatic')"
      >
        Add automatic discount
      </BaseButton>
    </template>

    <template #cell-promotion_name="{ row: { promotion_name, discount_code } }">
      <div class="d-flex">
        <p
          class="text-truncate"
          style="max-width: 350px"
        >
          {{ promotion_name }}
        </p>
        <BaseButton
          type="link"
          @click="copyText(discount_code)"
        >
          <i
            v-if="selectedType === 'manual'"
            class="ms-2 far fa-copy"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Copied to Clipboard!"
          />
        </BaseButton>
      </div>
    </template>
    <template #cell-promotion_status="{ row }">
      <span
        class="badge"
        :class="`text-capitalize ${
          isExceedLimit(row) || row.promotion_status !== 'active'
            ? 'badge-warning'
            : 'badge-success'
        }`"
      >
        {{ isExceedLimit(row) ? 'Inactive' : row.promotion_status }}
      </span>
    </template>
    <template
      #cell-promotion_usage="{ row: { extra_condition, promotion_usage } }"
    >
      {{ promotion_usage }} /
      {{
        extra_condition.store_limit_type == 'limited'
          ? extra_condition.store_limit_value
          : '∞'
      }}
    </template>
  </BaseDatatable>
</template>

<script>
import { Modal, Tooltip } from 'bootstrap';
import promotionAPI from '@promotion/api/promotionAPI.js';
import cloneDeep from 'lodash/cloneDeep';

export default {
  name: 'AllPromotion',
  props: {
    allPromotion: { type: Array, default: () => [] },
    type: { type: String, default: 'manual' },
  },

  data() {
    return {
      selectedPromotion: {},
      selectedType: this.type !== '' ? this.type : 'manual',
      loading: true,
      tabs: [
        { label: 'Promo Codes', target: 'manual' },
        { label: 'Automatic Discounts', target: 'automatic' },
      ],
    };
  },
  computed: {
    tableHeaders() {
      const manualHeader = [
        { name: 'Title', key: 'promotion_name', custom: true },
        { name: 'Category', key: 'promotion_category' },
        { name: 'Status', key: 'promotion_status', custom: true },
        { name: 'Usage', key: 'promotion_usage', custom: true },
        {
          name: 'Active From',
          key: 'start_date',
          isDateTime: true,
        },
      ];

      const automatedHeader = [
        { name: 'Title', key: 'promotion_name' },
        { name: 'Category', key: 'promotion_category' },
        { name: 'Status', key: 'promotion_status', custom: true },
        { name: 'Usage', key: 'promotion_usage' },
        {
          name: 'Active From',
          key: 'start_date',
          isDateTime: true,
        },
      ];
      return this.selectedType === 'manual' ? manualHeader : automatedHeader;
    },
    parsedDataArray() {
      return cloneDeep(this.promotionArray)[this.selectedType]?.map((m) => ({
        ...m,
        editLink: `/promotion/${m.promotion_method}/edit/${m.promotionURL}`,
      }));
    },
    emptyState() {
      return {
        title:
          this.selectedType === 'manual' ? 'promo code' : 'automatic discount',
        description:
          this.selectedType === 'manual'
            ? 'promo codes'
            : 'automatic discounts',
      };
    },
    promotionArray() {
      return {
        manual: this.manualPromotion,
        automatic: this.automaticPromotion,
      };
    },
    manualPromotion() {
      return this.allPromotion.filter((data) => {
        return data.promotion_method === 'manual';
      });
    },
    automaticPromotion() {
      return this.allPromotion.filter((data) => {
        return data.promotion_method === 'automatic';
      });
    },
  },
  mounted() {
    setTimeout(() => {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('.fa-copy')
      );
      const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
        const tooltip = new Tooltip(tooltipTriggerEl, {
          trigger: 'click',
        });
        tooltipTriggerEl.onmouseleave = () => {
          tooltip.hide();
        };
        return tooltip;
      });
    }, 1000);

    this.selectedType = this.type ?? 'manual';
    this.loading = false;
  },

  methods: {
    isExceedLimit(promotion) {
      if (
        !promotion ||
        promotion.extra_condition.store_limit_type !== 'limited'
      )
        return false;
      return (
        promotion.promotion_usage >= promotion.extra_condition.store_limit_value
      );
    },
    updateSelectedType(type) {
      this.selectedType = type;
    },
    copyText(promoCode) {
      navigator.clipboard.writeText(promoCode);
    },
    addNewPromotion() {
      new Modal('promotion-discount-type-modal').show();
    },
    editPromotion(urlId, type) {
      this.$inertia.visit(`/promotion/${type}/edit/${urlId}`);
    },

    deletePromotion(id) {
      promotionAPI
        .delete(id, this.selectedType)
        .then((response) => {
          this.$inertia.visit('/marketing/promotions');
          this.$toast.success('Success', 'Promotion has been deleted');
        })
        .catch((error) => {});
    },

    createPromotion(type) {
      this.$inertia.visit(`/promotion/${type}/create/new`);
    },
  },
};
</script>

<style>
td {
  min-width: 100px;
}
</style>

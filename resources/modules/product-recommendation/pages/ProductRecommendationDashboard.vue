<template>
  <BaseDatatable
    no-action
    :table-headers="tableHeaders"
    :table-datas="tableProductRecommendationsData"
    title="product recommendations"
  >
    <template #action-button>
      <BaseButton
        v-if="selected.length > 1 && false"
        has-edit-icon
        type="secondary"
        data-bs-toggle="modal"
        data-bs-target="#edit-priority-modal"
      >
        Edit Priority
      </BaseButton>
    </template>

    <template #cell-status="{ row }">
      <div class="d-flex justify-content-end">
        <div @click="checkPermission">
          <BaseFormSwitch
            :model-value="selected.includes(row.type)"
            :disabled="disabled || isOnlyForPaidPlan"
            @change="update(row, $event)"
          />
        </div>
      </div>
    </template>
  </BaseDatatable>

  <EditPriorityModal
    :items="productRecommendationsData.filter((e) => e.status === 'active')"
    type="recommendation"
    @updatePriority="reorder"
  />
</template>

<script>
import EditPriorityModal from '@product/components/EditPriorityModal.vue';
import eventBus from '@services/eventBus.js';

export default {
  components: {
    EditPriorityModal,
  },

  props: {
    productRecommendations: Array,
  },

  data() {
    return {
      disabled: false,
      selected: [
        'category',
        'frequently-bought-together',
        'best-selling',
        'recently-viewed',
      ],
      productRecommendationsData: [],
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */
        {
          name: 'Priority',
          key: 'index',
          order: 1,
        },
        {
          name: 'Type',
          key: 'name',
          order: 0,
          width: '50%',
        },
        { name: 'Pages', key: 'pageType', order: 0, width: '25%' },
        { name: 'Status', key: 'status', custom: true },
      ],
    };
  },

  computed: {
    tableProductRecommendationsData() {
      return this.productRecommendationsData.map((item, index) => ({
        index: index + 1,
        priority: item.priority,
        name: item.name,
        type: item.type,
        pageType: 'Product description page',
        status: item.status,
      }));
    },
    isOnlyForPaidPlan() {
      const { featureForPaidPlan } = this.$page.props.permissionData;
      return featureForPaidPlan.includes('product-recommendation');
    },
  },

  mounted() {
    this.productRecommendationsData = this.productRecommendations
      .map((e) => ({ ...e, ...{ name: this.description(e.type) } }))
      .sort((a, b) => a.priority - b.priority);
    this.selected = this.productRecommendations
      .filter((e) => e.status === 'active')
      .map((el) => el.type);
  },

  methods: {
    checkPermission() {
      if (this.isOnlyForPaidPlan) {
        eventBus.$emit('trigger-paid-plan-modal');
      }
    },
    description(type) {
      switch (type) {
        case 'category':
          return 'Products from the same category';
        case 'best-selling':
          return 'Best Selling';
        case 'recently-viewed':
          return 'Recently Viewed';
        default:
          return 'Products which frequently bought together';
      }
    },

    update(value, e, reorder = false) {
      if (reorder) this.selected = value;
      this.selected = this.selected.filter((el) => el !== value.type);
      if (e?.target?.checked) this.selected.push(value.type);
      this.disabled = true;
      axios
        .post(`/product-recommendation/status/update`, {
          selectedTypes: this.selected,
        })
        .then((response) => {
          this.productRecommendationsData = response.data
            .map((el) => ({ ...el, ...{ name: this.description(el.type) } }))
            .sort((a, b) => a.priority - b.priority);
          this.$toast.success('Success', "Items' priority is updated.");
          this.disabled = false;
        })
        .catch((error) => {
          this.$toast.error('Error', error);
        });
    },

    reorder(e) {
      this.update(
        e.map((el) => el.type),
        false,
        true
      );
    },
  },
};
</script>

<style></style>

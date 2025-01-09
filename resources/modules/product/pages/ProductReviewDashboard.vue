<template>
  <div class="table-container">
    <BaseDatatable
      :table-headers="tableHeaders"
      :table-datas="datas"
      :empty-state="emptyState"
      title="product review"
      no-edit-action
      @select-row="viewReview"
      @delete="removeRecord"
    >
      <template #cell-title="{ row: item }">
        <BaseButton
          type="link"
          class="title fw-bold text-gray-600"
          @click="navigate(item)"
        >
          {{ item.productTitle }} &nbsp;
          <i class="fas fa-external-link-alt" />
        </BaseButton>
      </template>

      <template #cell-image="{ row: { image } }">
        <img
          v-if="image"
          :src="image"
          class="img-thumbnail image-container"
          width="45px"
          height="45px"
        >
        <span
          v-else
          class="text-muted"
        >No Image</span>
      </template>

      <template #cell-status="{ row: { status } }">
        <BaseBadge
          :text="status"
          :type="status === 'Published' ? 'success' : 'warning'"
        />
      </template>

      <template #cell-rating="{ row: { rating } }">
        <div style="display: inline-table">
          <span>
            <i class="fa fa-star checked" />
            <i :class="rating < 2 ? 'far fa-star' : 'fa fa-star checked'" />
            <i :class="rating < 3 ? 'far fa-star' : 'fa fa-star checked'" />
            <i :class="rating < 4 ? 'far fa-star' : 'fa fa-star checked'" />
            <i :class="rating < 5 ? 'far fa-star' : 'fa fa-star checked'" />
          </span>
        </div>
      </template>

      <template #buttonAction="scopedData">
        <td class="datatable-actions-button">
          <div style="font-size: 13px">
            <button class="datatableEditButton">
              <i
                class="fas fa-eye"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="View"
              />
            </button>

            <button
              type="button"
              class="dropdown-toggle-split datatableEditButton"
              data-bs-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
              @click="selectAction = true"
            >
              <i
                class="fas fa-ellipsis-h"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="More Changes"
              />
              <span class="sr-only">Toggle Dropdown</span>
            </button>

            <div class="dropdown-menu">
              <a
                class="dropdown-item"
                @click="
                  changeReviewStatus(
                    scopedData.item.id,
                    scopedData.item.status === 'Published'
                      ? 'unpublished'
                      : 'published',
                    scopedData.item.reviewIndex
                  )
                "
              >Change to
                {{
                  scopedData.item.status === 'Published'
                    ? 'Unpublished'
                    : 'Published'
                }}</a>
              <button
                class="dropdown-item"
                data-bs-toggle="modal"
                data-bs-target="#product-review-delete-modal"
                @click="selectedDelete(scopedData.item.id)"
              >
                Delete
              </button>
            </div>
          </div>
        </td>
      </template>

      <template #action-options="{ row: item }">
        <BaseDropdownOption
          text="View"
          @click="viewReview(item.id)"
        />
        <BaseDropdownOption
          :text="item.status === 'Published' ? 'Unpublish' : 'Publish'"
          @click="
            changeReviewStatus(
              item.id,
              item.status === 'Published' ? 'unpublished' : 'published',
              item.reviewIndex
            )
          "
        />
      </template>
    </BaseDatatable>

    <ProductReviewsDetailsModal
      :modal-id="`view-review-detail-modal`"
      :product-review="selectedData"
    />
  </div>
</template>

<script>
// import VueJqModal from '@shared/components/VueJqModal.vue';
import ProductReviewsDetailsModal from '@onlineStore/components/ProductReviewsDetailsModal.vue';
import { Modal } from 'bootstrap';

export default {
  components: {
    ProductReviewsDetailsModal,
  },
  props: {
    reviews: {
      type: Array,
      default: () => [],
    },
    miniStoreDomain: {
      type: String,
      default: () => null,
    },
    onlineStoreDomain: {
      type: String,
      default: () => null,
    },
    enabledSalesChannels: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        {
          name: 'Image',
          key: 'image',
          custom: true,
        },
        {
          name: 'Product Title',
          key: 'title',
          custom: true,
        },
        {
          name: 'Rating',
          key: 'rating',
          custom: true,
        },
        { name: 'Status', key: 'status', custom: true },
        { name: 'Date', key: 'date' },
      ],
      modalId: 'product-review-delete-modal',
      selectedId: '',
      parsedDatas: [],
      selectedData: {},
      selectAction: false,
      emptyState: {
        title: 'review',
        description: 'reviews',
      },
    };
  },
  computed: {
    datas() {
      return this.parsedDatas.map((item, index) => {
        return {
          productTitle: item.productTitle,
          productPath: item.productPath,
          productSaleChannels: item.productSaleChannels,
          reviewIndex: index,
          status: item.status.charAt(0).toUpperCase() + item.status.slice(1),
          rating: item.star_rating,
          date: item.convertTime,
          image: item.image_collection ? item.image_collection[0] : null,
          id: item.id,
        };
      });
    },
  },

  mounted() {
    this.parsedDatas = this.reviews;
  },

  created() {},
  methods: {
    navigate(item) {
      this.selectAction = true;
      if (!this.enabledSalesChannels.length)
        return this.$toast.error('Error', `There is no any active stores!`);
      if (!item.productSaleChannels.length)
        return this.$toast.error(
          'Error',
          `${item.productTitle} is not active in any stores!`
        );
      if (
        item.productSaleChannels.length === 1 &&
        item.productSaleChannels[0] === 'funnel'
      )
        return this.$toast.error(
          'Error',
          `Make sure ${item.productTitle} is active in mini store or online store to enable the review widget!`
        );
      const filteredSaleChannels = item.productSaleChannels.filter((x) =>
        this.enabledSalesChannels.includes(x)
      );
      if (
        filteredSaleChannels.length === 1 &&
        filteredSaleChannels[0] === 'funnel'
      )
        return this.$toast.error(
          'Error',
          `Make sure mini store or online store is active!`
        );
      if (!filteredSaleChannels.length)
        return this.$toast.error(
          'Error',
          `Make sure ${item.productTitle}'s sale channel (mini store / online store) is active!`
        );
      let storeDomain = null;
      if (filteredSaleChannels.includes('mini-store') && this.miniStoreDomain)
        storeDomain = this.miniStoreDomain;
      if (
        filteredSaleChannels.includes('online-store') &&
        this.onlineStoreDomain
      )
        storeDomain = this.onlineStoreDomain;
      if (!storeDomain)
        return this.$toast.error(
          'Error',
          `Make sure sale channels consist the domain url!`
        );
      return window.open(
        `${storeDomain}/products/${item.productPath}`,
        '_blank'
      );
    },
    removeRecord(index) {
      axios
        .post(`/deleteReview/${index}`)
        .then((response) => {
          this.$toast.success('Success', response.data.message);
          this.$inertia.visit(window.location.pathname, { replace: true });
        })
        .catch((err) => {
          this.$toast.error(
            'Error',
            'Unexpected error occurs! Try to refresh the page!'
          );
          console.log(err);
        });
    },
    selectedDelete(id) {
      this.selectedId = id;
      this.selectAction = true;
    },
    closeDeleteModal() {
      Modal.getInstance(
        document.getElementById('product-review-delete-modal')
      ).hide();
    },

    viewReview(id) {
      this.selectedId = id;
      this.selectedData = this.parsedDatas.find(
        (e) => e.id === this.selectedId
      );
      if (!this.selectAction) {
        new Modal(document.getElementById('view-review-detail-modal')).show();
      }
      this.selectAction = false;
    },

    changeReviewStatus(id, status, index) {
      this.selectAction = true;
      axios
        .post(`/changeReviewStatus/${id}`, {
          status,
        })
        .then(({ data }) => {
          this.parsedDatas[index].status = status;
          this.$toast.success(
            'Success',
            `Successfully changed to ${data.status}!`
          );
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.checked,
.far {
  color: #ff9900;
}
.image-container {
  width: 45px;
  height: 45px;
  min-width: 45px;
  object-fit: scale-down;
}
</style>

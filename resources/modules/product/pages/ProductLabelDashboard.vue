<template>
  <div class="table-container">
    <Datatable
      :headers="tableHeaders"
      :datas="tableProductBadgesData"
      :empty-state="emptyState"
      @select-row="edit"
    >
      <template #name>
        <SettingPageHeader
          has-previous-button
          title="All Product Labels"
        />
      </template>

      <template #headerButton>
        <button
          class="createNewButton"
          @click="addProductBadge"
        >
          <i class="fas fa-plus" />&nbsp;&nbsp;Add Product Label
        </button>
        <button
          v-show="badges.length > 1"
          class="createNewButton"
          style="margin-left: 15px"
          data-bs-toggle="modal"
          data-bs-target="#edit-priority-modal"
        >
          <i class="fas fa-pencil-alt" />&nbsp;&nbsp; Edit Priority
        </button>
      </template>

      <template #Image>
        <th>Label Design</th>
      </template>

      <template #ImageData="scopedData">
        <td>
          <span
            :class="
              'product-badge text-break badge-' + scopedData.item.badgeDesign
            "
            :style="{
              'background-color': scopedData.item.backgroundColor,
              color: scopedData.backgroundColor,
              'font-size':
                scopedData.item.badgeDesign === 'circle'
                  ? 10 + 'px'
                  : 12 + 'px',
            }"
          >
            <span
              :class="scopedData.item.fontFamily"
              :style="{
                color: scopedData.item.textColor,
                'background-color': 'transparent !important',
              }"
            >
              {{ scopedData.item.text }}
            </span>
          </span>
        </td>
      </template>

      <template #buttonHeader>
        <th class="datatable-actions">
          Actions
        </th>
      </template>

      <template #buttonAction="scopedData">
        <td class="datatable-actions-button">
          <div class="btn-group">
            <button class="datatableEditButton">
              <a
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Edit"
                @click="edit(scopedData)"
              ><i
                class="fas fa-pencil-alt"
              /></a>
            </button>

            <button
              class="datatableEditButton"
              data-bs-toggle="modal"
              data-bs-target="#delete-product-badge-modal"
              @click="selectedDelete(scopedData.item.id)"
            >
              <a
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Delete"
              ><i
                class="fas fa-trash-alt"
              /></a>
            </button>
          </div>
        </td>
      </template>
    </Datatable>

    <DeleteConfirmationModal
      :modal-id="modalId"
      title="Product Badge"
      @cancel="closeDeleteModal"
      @save="deleteProductBadge"
    />

    <EditPriorityModal
      :items="productBadgesData"
      type="badge"
      @updatePriority="updateBadge"
    />
  </div>
</template>

<script>
import EditPriorityModal from '@product/components/EditPriorityModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'ProductLabelDashboard',

  components: {
    EditPriorityModal,
  },

  props: {
    badges: Array,
  },

  data() {
    return {
      productBadgesData: [],
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */
        {
          text: 'Label Name',
          value: 'badgeName',
          order: 0,
          width: '15%',
        },
        { text: 'Priority', value: 'priority', order: 1, width: '10%' },
        { text: 'Products', value: 'productsValue', order: 0, width: '40%' },
      ],
      modalId: 'delete-product-badge-modal',
      selectedId: '',
      redirectEdit: true,
      emptyState: {
        title: 'product badges',
        description: 'product badges',
      },
    };
  },

  computed: {
    tableProductBadgesData() {
      return this.productBadgesData.map((item, index) => ({
        id: item.id,
        badgeName: item.badge_name,
        text: item.text,
        priority: index + 1,
        fontFamily: item.font_family,
        backgroundColor: item.background_color,
        textColor: item.text_color,
        badgeDesign: item.badge_design,
        productsValue:
          item.select_products !== 'all'
            ? item.selectedProduct
            : 'All Products',
        urlId: item.urlId,
      }));
    },
  },

  mounted() {
    if (this.badges)
      this.productBadgesData = this.badges.map((el) => ({
        ...el,
        name: el.badge_name,
      }));
  },

  methods: {
    addProductBadge() {
      // window.location.href = `/product/badges/add`;
      this.$inertia.visit('/product/badges/add');
    },

    selectedDelete(id) {
      this.redirectEdit = false;
      this.selectedId = id;
    },

    triggerErrorToast() {
      this.$toast.error(
        'Error',
        'Something went wrong. Please try again later.'
      );
    },

    deleteProductBadge() {
      axios
        .delete(`/product-badge/delete/${this.selectedId}`)
        .then((response) => {
          this.$toast.success('Success', response.data.message);
          this.closeDeleteModal();
          this.productBadgesData = response.data.badges;
          this.redirectEdit = true;
        })
        .catch((error) => {
          this.triggerErrorToast();
          throw new Error(error);
        });
    },

    closeDeleteModal() {
      Modal.getInstance(
        document.getElementById('delete-product-badge-modal')
      ).hide();
    },

    edit(value) {
      if (this.redirectEdit)
        this.$inertia.visit(`/product/badges/${value.item.urlId}`);
    },

    updateBadge(value) {
      this.productBadgesData = value.map((el) => ({
        ...el,
        name: el.badge_name,
      }));
    },
  },
};
</script>

<style scoped lang="scss">

.product-badge {
  position: relative;
  margin: 0;
}

.product-badge.badge-rectangular {
  border-radius: 0px;
  width: auto;
  height: auto;
  padding: 0.5em 1.5em;
  margin: 12px 0;
}

.product-badge.badge-circle {
  border-radius: 50%;
  width: 5em;
  height: 5em;
}

.product-badge-text {
  background-color: transparent !important;
}

.product-badge {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  text-align: center;
  font-size: 10px;
  line-height: 1.1;
  z-index: 10;
  max-width: 100%;
}
</style>

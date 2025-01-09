<template>
  <BaseTab
    :tabs="tabs"
    :defaultIndex="defaultTabIndex"
    @click="toggleProductStatusTab"
  />
  <BaseDatatable
    title="product"
    :table-headers="tableHeaders"
    :table-datas="datas"
    :pagination-info="formattedPaginatedProduct"
    is-server-side-search
    @selectAll="handleSelectAllProducts"
    @delete="removeRecord"
    :custom-description="
      this.selectedProductStatus === 'draft' &&
      'No products have been marked as drafts at this time.'
    "
  >
    <template #cell-checkbox="{ row: { id } }">
      <BaseFormCheckBox
        v-model="checkedProductIds"
        :value="id"
        @change="handleSelectProduct"
      />
    </template>
    <template #cell-productImagePath="{ row: { productImagePath } }">
      <img
        :src="productImagePath"
        style="object-fit: scale-down; width: 45px; height: 45px"
      />
    </template>
    <template #cell-productStatus="{ row: { productStatus, saleChannels } }">
      <BaseBadge
        class="text-capitalize"
        :text="productStatus"
        :type="productStatus === 'active' ? 'success' : 'warning'"
      />
      <div v-if="false" class="dropdown-menu" style="min-width: 8em">
        <span
          v-show="activeSaleChannelsData.includes('funnel')"
          class="dropdown-item"
          style="padding: 0"
        >
          <i
            class="dot fas fa-circle"
            :style="{
              color:
                productStatus === 'active'
                  ? saleChannels.includes('funnel')
                    ? 'green'
                    : '#bbb'
                  : '#bbb',
            }"
          />
          Funnel
        </span>
        <span
          v-show="activeSaleChannelsData.includes('online-store')"
          class="dropdown-item"
          style="padding: 0"
        >
          <i
            class="dot fas fa-circle"
            :style="{
              '--dotColor':
                productStatus === 'active'
                  ? saleChannels.includes('online-store')
                    ? 'green'
                    : '#bbb'
                  : '#bbb',
            }"
          />
          Online Store
        </span>
        <span
          v-show="activeSaleChannelsData.includes('mini-store')"
          class="dropdown-item"
          style="padding: 0"
        >
          <i
            class="dot fas fa-circle"
            :style="{
              '--dotColor':
                productStatus === 'active'
                  ? saleChannels.includes('mini-store')
                    ? 'green'
                    : '#bbb'
                  : '#bbb',
            }"
          />
          Mini Store
        </span>
      </div>
    </template>
    <template #action-button>
      <BaseButton
        id="bulk-edit"
        has-edit-icon
        has-dropdown-icon
        type="secondary"
        data-bs-toggle="dropdown"
        aria-expanded="false"
      >
        Bulk Edit
      </BaseButton>

      <BaseDropdown id="bulk-edit">
        <BaseDropdownOption
          text="Add Category"
          is-open-in-new-tab
          data-bs-toggle="modal"
          data-bs-target="#product-add-category-modal"
        />
        <BaseDropdownOption
          text="Remove Category"
          is-open-in-new-tab
          data-bs-toggle="modal"
          data-bs-target="#product-remove-category-modal"
        />
        <BaseDropdownOption
          text="Add Global Customization"
          is-open-in-new-tab
          data-bs-toggle="modal"
          data-bs-target="#add-shared-option-modal"
        />
        <BaseDropdownOption
          text="Remove Global Customization"
          is-open-in-new-tab
          data-bs-toggle="modal"
          data-bs-target="#remove-shared-option-modal"
        />
      </BaseDropdown>

      <BaseButton
        id="add-product-button"
        has-add-icon
        @click="toggleProductTypeModal"
      >
        Add Product
      </BaseButton>
    </template>
    <template #action-options="{ row: { reference_key, viewLink } }">
      <BaseDropdownOption
        text="Duplicate"
        @click="duplicateProduct(reference_key)"
      />
      <BaseDropdownOption text="View" :link="viewLink" is-open-in-new-tab />
    </template>
  </BaseDatatable>
  <!-- <div class="table-container">
    <Datatable
      :headers="tableHeaders"
      :datas="datas"
      :empty-state="emptyState"
      title="products"
    >
      <template #name>
        <SettingPageHeader
          has-previous-button
          title="All Products"
        />
      </template>
      <template #headerButton>
        <Link href="/product/create">
          <button class="createNewButton">
            <i class="fas fa-plus" />&nbsp;&nbsp;Add Product
          </button>
        </Link>
        <button
          id="dropdownMenuButton"
          class="cleanButton dropdown-toggle"
          type="button"
          data-disabled="true"
          data-bs-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          &nbsp;&nbsp;
          <i
            class="fas fa-pencil-alt"
            style="margin-right: 10px"
          />
          Bulk Edit
        </button>
        <div
          class="dropdown-menu dropend"
          aria-labelledby="dropdownMenuButton"
        >
          <template>
            <a
              class="dropdown-item"
              style="cursor: pointer; color: #202930"
              data-bs-toggle="modal"
              data-bs-target="#product-add-category-modal"
            >
              Add Category
            </a>
            <a
              class="dropdown-item"
              style="cursor: pointer; color: #202930"
              data-bs-toggle="modal"
              data-bs-target="#product-remove-category-modal"
            >
              Remove Category
            </a>
          </template>

          <a
            class="dropdown-item"
            style="cursor: pointer; color: #202930"
            data-bs-toggle="modal"
            data-bs-target="#add-shared-option-modal"
          >
            Add Global Customization
          </a>
          <a
            class="dropdown-item"
            style="cursor: pointer; color: #202930"
            data-bs-toggle="modal"
            data-bs-target="#remove-shared-option-modal"
          >
            Remove Global Customization
          </a>
        </div>
      </template>

      <template #Input>
        <th
          style="width: auto"
          class="text-uppercase"
        >
          <div style="text-align: center">
            <label
              class="m-0 pb-0 kt-checkbox kt-checkbox--bold kt-checkbox--brand"
            >
              <input
                id="all-checkbox"
                type="checkbox"
                name="checkbox"
                class="black-checkbox"
                @change="handleSelectAllProducts($event)"
              >
              <span />
            </label>
          </div>
        </th>
      </template>

      <template #InputData="scopedData">
        <td style="text-align: center">
          <div>
            <label
              class="kt-checkbox kt-checkbox--bold kt-checkbox--brand mb-0"
            >
              <input
                type="checkbox"
                :checked="checkedProductIds.includes(scopedData.item.id)"
                name="checkbox"
                class="black-checkbox"
                @change="handleSelectProduct($event, scopedData.item.id)"
              >
              <span />
            </label>
          </div>
        </td>
      </template>

      <template #Image>
        <th>Image</th>
      </template>

      <template #customCol>
        <th class="datatable-actions">
          Status
        </th>
      </template>

      <template #buttonHeader>
        <th class="datatable-actions">
          Actions
        </th>
      </template>

      <template #ImageData="scopedData">
        <td>
          <img
            :src="scopedData.item.productImagePath"
            class="img-thumbnail image-container"
            width="45px"
            height="45px"
          >
        </td>
      </template>

      <template #customColData="scopedData">
        <td class="datatable-actions-button position-relative">
          <span
            class="text-capitalize status-pill"
            :class="{
              '': scopedData.item.productStatus === 'draft',
              'pill-green': scopedData.item.productStatus === 'active',
            }"
          >
            {{ scopedData.item.productStatus }}
          </span>
          <div
            v-show="activeSaleChannelsData.includes('funnel')"
            class="dropdown-menu"
            style="min-width: 8em"
          >
            <span
              class="dropdown-item"
              style="padding: 0"
            >
              <i
                class="dot fas fa-circle"
                :style="{
                  '--dotColor':
                    scopedData.item.productStatus === 'active'
                      ? scopedData.item.saleChannels.includes('funnel')
                        ? 'green'
                        : '#bbb'
                      : '#bbb',
                }"
              />
              Funnel
            </span>
            <span
              v-show="activeSaleChannelsData.includes('online-store')"
              class="dropdown-item"
              style="padding: 0"
            >
              <i
                class="dot fas fa-circle"
                :style="{
                  '--dotColor':
                    scopedData.item.productStatus === 'active'
                      ? scopedData.item.saleChannels.includes('online-store')
                        ? 'green'
                        : '#bbb'
                      : '#bbb',
                }"
              />
              Online Store
            </span>
            <span
              v-show="activeSaleChannelsData.includes('mini-store')"
              class="dropdown-item"
              style="padding: 0"
            >
              <i
                class="dot fas fa-circle"
                :style="{
                  '--dotColor':
                    scopedData.item.productStatus === 'active'
                      ? scopedData.item.saleChannels.includes('mini-store')
                        ? 'green'
                        : '#bbb'
                      : '#bbb',
                }"
              />
              Mini Store
            </span>
          </div>
        </td>
      </template>

      <template #buttonAction="scopedData">
        <td class="datatable-actions-button">
          <div style="font-size: 13px">
            <button
              class="datatableEditButton"
              target="_blank"
              @click="navigateToDescription(scopedData.item)"
            >
              <i
                class="fas fa-eye"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="View"
              />
            </button>
            <Link
              :href="'/product/' + scopedData.item.reference_key"
              class="datatableEditButton"
            >
              <i
                class="fas fa-pencil-alt"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Edit"
              />
            </Link>

            <button
              type="button"
              class="dropdown-toggle-split datatableEditButton"
              data-bs-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
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
                  changeProductStatus(
                    scopedData.item.id,
                    scopedData.item.productStatus === 'active'
                      ? 'draft'
                      : 'active',
                    scopedData.item.productIndex
                  )
                "
              >Change to
                {{
                  scopedData.item.productStatus === 'active'
                    ? 'Draft'
                    : 'Active'
                }}</a>
              <button
                class="dropdown-item"
                data-bs-toggle="modal"
                data-bs-target="#product-page-delete-modal"
                @click="selectedId = scopedData.item.id"
              >
                Delete
              </button>
            </div>
          </div>
        </td>
      </template>
    </Datatable>

    <DeleteConfirmationModal
      :modal-id="modalId"
      title="Product"
      @cancel="closeDeleteModal"
      @save="removeRecord(selectedId)"
    />
  </div> -->
  <ProductAddCategoryModal
    modal-id="product-add-category-modal"
    :categories="categories"
    :checked-product-ids="checkedProductIds"
    @update="clearCheckedProductds"
  />
  <ProductRemoveCategoryModal
    :modal-id="'product-remove-category-modal'"
    :categories="categories"
    :checked-product-ids="checkedProductIds"
    @update="clearCheckedProductds"
  />
  <ProductAddGlobalCustomizationModal
    modal-id="add-shared-option-modal"
    :shared-options="sharedOptions"
    :checked-product-ids="checkedProductIds"
    @update="clearCheckedProductds"
  />
  <ProductRemoveGlobalCustomizationModal
    modal-id="remove-shared-option-modal"
    :shared-options="sharedOptions"
    :checked-product-ids="checkedProductIds"
    @update="clearCheckedProductds"
  />

  <ProductTypeModal />
</template>

<script>
import ProductAddCategoryModal from '@product/components/ProductAddCategoryModal.vue';
import ProductRemoveCategoryModal from '@product/components/ProductRemoveCategoryModal.vue';
import ProductAddGlobalCustomizationModal from '@product/components/ProductAddGlobalCustomizationModal.vue';
import ProductRemoveGlobalCustomizationModal from '@product/components/ProductRemoveGlobalCustomizationModal.vue';
import ProductTypeModal from '@product/components/ProductTypeModal.vue';
import cloneDeep from 'lodash/cloneDeep';
import { Modal } from 'bootstrap';

export default {
  components: {
    ProductAddCategoryModal,
    ProductRemoveCategoryModal,
    ProductAddGlobalCustomizationModal,
    ProductRemoveGlobalCustomizationModal,
    ProductTypeModal,
  },
  props: {
    products: {
      type: Array,
      default: () => [],
    },
    currency: {
      type: String,
      default: 'MYR',
    },
    exceptDefault: {
      type: Array,
      default: () => [],
    },
    environment: {
      type: String,
      default: 'production',
    },
    storeDomain: {
      type: String,
      default: null,
    },
    activeSaleChannels: {
      type: Array,
      default: () => [],
    },
    categories: {
      type: Array,
      default: () => [],
    },
    sharedOptions: {
      type: Array,
      default: () => [],
    },
    paginatedProducts: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      tableHeaders: [
        { key: 'checkbox', sortable: false },
        {
          name: 'Image',
          key: 'productImagePath',
          custom: true,
          sortable: false,
        },
        { name: 'Title', key: 'productTitle' },
        {
          name: 'Price',
          key: 'productPrice',
        },
        { name: 'Type', key: 'type' },
        {
          name: 'Status',
          key: 'productStatus',
          custom: true,
        },
      ],
      modalId: 'product-page-delete-modal',
      selectedId: '',
      parsedDatas: [],
      emptyState: {
        title: 'product',
        description: 'products',
      },
      activeSaleChannelsData: [],
      checkedProductIds: [],
      productTypeModal: null,
      tabs: [
        {
          label: 'Active',
          target: 'active',
        },
        {
          label: 'Draft',
          target: 'draft',
        },
      ],
      selectedProductStatus: 'active',
      defaultTabIndex: 0,
      formattedPaginatedProduct: [],
    };
  },
  computed: {
    datas() {
      return this.parsedDatas.map((item, index) => {
        return {
          productTitle: item.productTitle,
          productStatus: item.status,
          id: item.id,
          productIndex: index,
          path: item.path,
          productImagePath:
            item.productImagePath ||
            'https://cdn.hypershapes.com/assets/product-default-image.png',
          productPrice: item.displayPrice,
          type: item.type,
          reference_key: item.reference_key,
          editLink: `/product/${item.reference_key}`,
          saleChannels: item.selectedSaleChannels,
          viewLink: `${this.pagePrefixURL}/${item.path}`,
        };
      });
    },
    pagePrefixURL() {
      return this.environment === 'local'
        ? `http://${this.storeDomain}/products`
        : `https://${this.storeDomain}/products`;
    },
  },
  mounted() {
    this.formattedPaginatedProduct = this.paginatedProducts;
    const status = this.getQueryStatus();
    if (status) {
      this.updatePaginatedLink(status);
      this.toggleProductStatusTab(status, false);
    }

    this.$store.commit('product/initialOptionsState');
    this.parsedDatas = cloneDeep(this.products);
    this.activeSaleChannelsData = cloneDeep(this.activeSaleChannels);
  },

  methods: {
    getQueryStatus() {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get('status');
    },
    updatePaginatedLink(status) {
      this.formattedPaginatedProduct.links.map((m) => {
        if (/active|draft/i.test(m.url)) {
          m.url = m.url.replace(/draft|active/gi, status);
          return m;
        }
        m.url = `${m.url}&status=${status}`;
        return m;
      });
    },
    duplicateProduct(refKey) {
      axios
        .get(`/product/duplicate/${refKey}`)
        .then(() => {
          this.$toast.success('Success', 'Successfully Duplicate Product');
          window.location.reload();
        })
        .catch((error) => {
          this.$toast.error(
            'Error',
            error?.response?.data?.message ?? 'Unexpected Error Occurred'
          );
        });
    },
    toggleProductTypeModal() {
      this.productTypeModal = new Modal(
        document.getElementById('product-type-modal')
      );
      this.productTypeModal.show();
    },
    removeRecord(index) {
      axios
        .post('/deleteProduct', {
          id: index,
        })
        .then((response) => {
          if (response.data === 'fail')
            return this.$toast.error(
              'Error',
              'Product are not allowed to delete'
            );
          this.$inertia.visit('/products');
          return this.$toast.success('Success', 'Product Deleted');
        });
    },

    closeDeleteModal() {
      Modal.getInstance(
        document.getElementById('product-page-delete-modal')
      ).hide();
    },

    navigateToDescription(product) {
      window.open(`${this.pagePrefixURL}/${product.path}`, '_blank');
    },

    changeProductStatus(id, status, index) {
      axios
        .post(`/changeProductStatus/${id}`, {
          status,
        })
        .then(({ data }) => {
          this.parsedDatas[index].status = status;
          this.$toast.success('Success', data.status);
        });
    },

    handleSelectAllProducts(e) {
      const checked = e;
      if (checked) {
        this.checkedProductIds = this.parsedDatas.map((product) => product.id);
      } else {
        this.checkedProductIds = [];
      }
    },

    handleSelectProduct(e) {
      const { checked } = e.target;
      if (!checked)
        document.getElementById('select-all-checkbox').checked = false;
    },
    clearCheckedProductds() {
      this.checkedProductIds = [];
      document.getElementById('select-all-checkbox').checked = false;
    },
    toggleProductStatusTab(status, isRedirect = true) {
      this.selectedProductStatus = status;
      this.defaultTabIndex = this.tabs.findIndex((e) => e.target === status);
      if (isRedirect) this.$inertia.visit(`/products?status=${status}`);
    },
  },
};
</script>

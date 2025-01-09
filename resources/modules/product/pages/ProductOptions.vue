<template>
  <BaseDatatable
    title="customization"
    :table-headers="tableHeaders"
    :table-datas="datas"
    no-delete-action
    no-edit-action
  >
    <template #cell-values="{ row: { label } }">
      <span v-if="label !== ''">
        {{ label }}
      </span>
      <BaseBadge v-else text="Empty" type="warning" />
    </template>
    <template #action-button>
      <BaseButton has-add-icon @click="toggleAddGlobalCustomization('create')">
        Add Customization
      </BaseButton>
      <!-- <BaseButton>Edit Priority</BaseButton> -->
    </template>
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        is-open-in-new-tab
        @click="toggleAddGlobalCustomization(item.id)"
      />
      <BaseDropdownOption
        v-if="!item.noOfProduct"
        text="Delete"
        is-open-in-new-tab
        @click="removeRecord(item.id)"
      />
    </template>
  </BaseDatatable>
  <AddProductModal
    modal-id="add-global-customization-modal"
    title="Add Customization"
    size="xl"
  >
    <template #body>
      <div class="col-xl-7" :style="{ 'border-right': '1px solid #EFF2F5' }">
        <div
          class="row"
          :style="{ 'max-height': '40vh', 'overflow-y': 'auto' }"
        >
          <NewSharedProductOption
            :option-type="'shared'"
            :type="type"
            :currency="defaultCurrency"
          />
        </div>
      </div>
      <div class="col-xl-5">
        <BaseDatatable
          title="product"
          :table-headers="productHeaders"
          :table-datas="productDatas"
          no-hover
          no-action
          max-height="30vh"
          @selectAll="handleSelectAllProducts"
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
        </BaseDatatable>
      </div>
    </template>

    <template #footer>
      <BaseButton id="save-global-customization" @click="saveSharedOption()">
        Save
      </BaseButton>
    </template>
  </AddProductModal>
  <!-- <EditPriorityModal
      :items="optionType === 'variation' ? variationDatas : customizationDatas"
      :type="optionType === 'variation' ? 'variation' : 'customization'"
      @updatePriority="updateItems"
    >
      <template #headerButton>
        <div class="p-1">
          <button
            class="primary-small-square-button"
            :style="{
              'background-color':
                optionType === 'variation' ? '#5c36ff !important' : '#7766F7',
            }"
            @click="optionType = 'variation'"
          >
            Variations
          </button>
          <button
            class="primary-small-square-button"
            :style="{
              'background-color':
                optionType === 'customization'
                  ? '#5c36ff !important'
                  : '#7766F7',
            }"
            @click="optionType = 'customization'"
          >
            Customizations
          </button>
        </div>
      </template>
    </EditPriorityModal> -->
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import AddProductModal from '@product/components/AddProductModal.vue';
import NewSharedProductOption from '@product/components/NewSharedProductOption.vue';
// import EditPriorityModal from '@product/components/EditPriorityModal.vue';
import { Modal } from 'bootstrap';
import cloneDeep from 'clone';
import axios from 'axios';

export default {
  components: {
    AddProductModal,
    NewSharedProductOption,
    // EditPriorityModal,
  },
  provide() {
    // use function syntax so that we can access `this`
    return {
      variants: this.variants,
    };
  },
  props: {
    productOptionArray: {
      type: [Array, Object],
      default: () => [],
    },
    variants: {
      type: [Array, Object],
      default: () => [],
    },
    currency: {
      type: String,
      default: 'RM',
    },
    products: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        // { name: 'Priority', key: 'priority' },
        { name: 'Display Name', key: 'display_name' },
        { name: 'Type', key: 'type' },
        { name: 'Values', key: 'values', custom: true },
        {
          name: 'Products',
          key: 'noOfProduct',
        },
      ],
      showLimitModal: false,
      modalId: 'product-option-delete-modal',
      selectedId: null,
      type: '',
      defaultCurrency: '',
      // tabType: '',
      customizationArray: [],
      // Variant
      selectedVariantId: 'add',
      optionType: 'global-variations',
      variationArray: [],
      tabs: [
        { label: 'Global Variations', target: 'global-variations' },
        { label: 'Global Customizations', target: 'global-customizations' },
      ],
      productHeaders: [
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
      ],
      checkedProductIds: [],
    };
  },
  computed: {
    ...mapState('product', [
      'inputOptions',
      'deletedInputId',
      'customizationsIsValid',
      'existsName',
    ]),
    datas() {
      const datas = this.customizationArray;
      return datas.map((el, i) => ({
        ...el,
        priority: i + 1,
      }));
    },
    customizationDatas() {
      return this.customizationArray.map((item, index) => {
        return {
          id: item.id,
          name: item.name,
          priority: index + 1,
        };
      });
    },
    variationDatas() {
      const variants = cloneDeep(this.variationArray);
      return variants.map((item, index) => {
        return {
          id: item.id,
          name: item.variant_name,
          priority: index + 1,
        };
      });
    },
    productDatas() {
      return this.products.filter(e => e.type !== 'course').map((item, index) => {
        return {
          productTitle: item.productTitle,
          productStatus: item.status,
          id: item.id,
          productIndex: index,
          path: item.path,
          productImagePath:
            item.productImagePath ||
            'https://cdn.hypershapes.com/assets/product-default-image.png',
          productPrice: item.productPrice,
          type: item.type === 'physical' ? 'Physical' : 'Virtual',
          reference_key: item.reference_key,
          editLink: `/product/${item.reference_key}`,
          saleChannels: item.selectedSaleChannels,
          viewLink: `${this.pagePrefixURL}/${item.path}`,
        };
      });
    },
  },
  // watch: {
  //   tabType(newValue) {
  //     if (newValue !== null) $(`.nav-item a[href="#${newValue}"]`).tab('show');
  //   },
  // },
  mounted() {
    setTimeout(() => {
      const cusModal = document.getElementById(
        'add-global-customization-modal'
      );

      cusModal.addEventListener('show.bs.modal', () => {
        return this.resetCustomizationErrors();
      });
    }, 1000);
  },
  created() {
    this.customizationArray = this.productOptionArray;
    this.variationArray = this.variants;
    // window.addEventListener('beforeunload', function (event) {
    //   this.sessionStorage.removeItem('type');
    //   this.tabtype = null;
    // });
    // this.tabType = sessionStorage.getItem('type');
    this.defaultCurrency = this.currency;
  },
  methods: {
    ...mapMutations('product', [
      'pushOptionArray',
      'checkError',
      'resetOptionArray',
      'resetDeletedArray',
      'currentProductOption',
      'resetCustomizationErrors',
    ]),
    saveSharedOption() {
      this.checkError({ type: 'shared' });
      const status = this.customizationsIsValid;
      if (status) {
        this.loading = true;
        this.disabled = true;
        axios
          .post('/product/option/save', {
            productOption: this.inputOptions,
            deletedInputId: this.deletedInputId,
            productIds: this.checkedProductIds,
          })
          .then(({ data }) => {
            this.$toast.success(
              'Succcess',
              'Successful added product customization!'
            );
            this.customizationArray = data;
            this.modlEl.hide();
            // sessionStorage.setItem('type', 'customization');
          })
          .finally(() => {
            this.loading = false;
            this.disabled = false;
            this.$inertia.visit('/product/customizations');
          });
      } else {
        this.$toast.error('Error', 'Fail To Save Global Customization');
      }
    },
    removeRecord(index) {
      // eslint-disable-next-line no-restricted-globals
      const answer = confirm('Are you sure want to delete this record?');
      if (answer) {
        axios.get(`/delete/option/${index}`).then((response) => {
          this.$toast.success('Success', 'Product Option Deleted');
          this.customizationArray = response.data;
          this.modlEl.hide();
          // window.location.href = '/product/options';
        });
      }
    },

    toggleAddGlobalVariation() {
      this.selectedVariantId = 'add';
      eventBus.$emit(`shared-variant-type-modal`, 'add');
      this.edit_global_variant_modal = new Modal(
        document.getElementById('add-global-variation-modal')
      );
      this.edit_global_variant_modal.show();

      // window.location.pathname = '/variants/add';
    },
    toggleAddGlobalCustomization(type) {
      this.type = type;
      if (this.type === 'create') {
        this.checkedProductIds = [];
        this.resetOptionArray();
        this.resetDeletedArray();
        this.currentProductOption([]);
        this.pushOptionArray({ type: 'inputOptions', optionType: 'shared' });
      } else {
        const selected = this.productOptionArray.find((e) => e.id === type);
        this.checkedProductIds = cloneDeep(selected?.productIds ?? []);
        // if (!this.existsName?.length) {
        //   axios.get(`/get/exists/create`).then((response) => {
        //     this.$store.dispatch('product/fetchName', {
        //       nameArray: response.data.filter((e) => e !== selected.name),
        //     });
        //   });
        // } else {
        //   this.$store.dispatch('product/fetchName', {
        //     nameArray: this.existsName.filter((e) => e !== selected.name),
        //   });
        // }

        eventBus.$emit('updateSharedCustomization');
      }
      this.$nextTick(() => {
        this.modlEl = new Modal(
          document.getElementById('add-global-customization-modal')
        );
        this.modlEl.show();
      });
    },

    closeDeleteModal() {
      Modal.getInstance(
        document.getElementById('product-option-delete-modal')
      ).hide();
    },
    updateItems(e) {
      switch (this.optionType) {
        case 'global-variations':
          this.variationArray = e;
          return this.variationArray;
        case 'global-customizations':
          this.customizationArray = e;
          return this.customizationArray;
        default:
          return '';
      }
    },
    handleSelectAllProducts(e) {
      const checked = e;
      if (checked) {
        this.checkedProductIds = this.products.map((product) => product.id);
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
  },
};
</script>

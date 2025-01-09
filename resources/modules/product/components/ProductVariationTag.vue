<template>
  <BaseDatatable
    title="variation"
    :table-headers="tableHeaders"
    :table-datas="datas"
    no-delete-action
    no-edit-action
  >
    <template #cell-variantType="{ row: item }">
      <span class="text-capitalize">
        {{ item.variantType }}
      </span>
    </template>
    <template #action-button>
      <BaseButton
        has-add-icon
        @click="toggleAddGlobalVariation"
      >
        Add Global Variation
      </BaseButton>
      <BaseButton
        has-add-icon
        @click="toggleAddGlobalCustomization('create')"
      >
        Add Global Customization
      </BaseButton>
      <!-- <BaseButton>Edit Priority</BaseButton> -->
    </template>
    <template #action-options="{ row: item }">
      <BaseDropdownOption
        text="Edit"
        is-open-in-new-tab
        @click="toggleModal(item.id)"
      />
      <BaseDropdownOption
        v-if="!item.products"
        text="Delete"
        is-open-in-new-tab
        @click="deleteSharedVariant(item.id)"
      />
    </template>
  </BaseDatatable>
  <Teleport to="body">
    <AddProductModal
      modal-id="add-global-variation-modal"
      :title="modalTitle"
    >
      <template #body>
        <GlobalVariantModal :selected-variant-id="selectedId" />
      </template>
      <template #footer>
        <BaseButton @click="activateEditVariantFunction">
          Save
        </BaseButton>
      </template>
    </AddProductModal>
  </Teleport>
</template>

<script>
/* eslint-disable camelcase */
import GlobalVariantModal from '@product/components/GlobalVariantModal.vue';
import AddProductModal from '@product/components/AddProductModal.vue';
import { Modal } from 'bootstrap';
import cloneDeep from 'lodash/cloneDeep';

export default {
  components: {
    GlobalVariantModal,
    AddProductModal,
  },

  props: [
    'variants',
    'addVariant',
    'toggleAddGlobalVariation',
    'toggleAddGlobalCustomization',
  ],

  data() {
    return {
      tableHeaders: [
        // { name: 'Priority', key: 'priority',  },
        { name: 'Unique Name', key: 'uniqueName' },
        { name: 'Display Name', key: 'displayName' },
        { name: 'Type', key: 'variantType', custom: true },
        { name: 'Value', key: 'variantValues' },
        {
          name: 'Products',
          key: 'products',
        },
      ],
      modalId: 'product-variant-delete-modal',
      selectedId: '',
      option: '',
      emptyState: {
        title: 'global variation',
        description: 'global variations',
      },
    };
  },

  computed: {
    datas() {
      const variants = cloneDeep(this.variants);

      return variants.map((item, index) => {
        let filteredValues = '';

        item.values.forEach(({ variant_value }) => {
          if (filteredValues === '') {
            filteredValues = variant_value;
          } else {
            filteredValues = filteredValues.concat(`, ${variant_value}`);
          }
        });

        return {
          priority: index + 1,
          uniqueName: item.variant_name,
          displayName: item.display_name,
          variantType: item.type === 'colour' ? 'color' : item.type,
          variantValues: filteredValues,
          products: item.count,
          id: item.id,
        };
      });
    },

    modalTitle() {
      if (this.option === 'add') {
        return 'Add Global Variation';
      }
      return 'Edit Global Variation';
    },
  },

  mounted() {
    eventBus.$on('shared-variant-type-modal', (data) => {
      this.option = data;
    });
  },

  methods: {
    deleteSharedVariant(id) {
      this.selectedId = id;
      // eslint-disable-next-line no-restricted-globals
      const answer = confirm('Are you sure want to delete this record?');
      if (answer) {
        axios
          .delete(`/deleteSharedVariant/${this.selectedId}`)
          .then(({ data }) => {
            this.$toast.success('Success', 'Delete Successfully');
            this.$inertia.visit('/product/options');
          })
          .catch((e) => console.log(e));
      }
    },

    toggleModal(id) {
      this.selectedId = id;
      eventBus.$emit(`shared-variant-type-modal`, id);
      this.$nextTick(() => {
        this.modalEl = new Modal(
          document.getElementById('add-global-variation-modal')
        );
        this.modalEl.show();
      });
    },

    activateEditVariantFunction() {
      eventBus.$emit('activate-save-shared-variant-function');
    },
  },
};
</script>

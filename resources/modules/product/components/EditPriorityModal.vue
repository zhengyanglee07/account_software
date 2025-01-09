<template>
  <div>
    <BaseModal
      modal-id="edit-priority-modal"
      :small="true"
      :data_keyboard="'false'"
      :data_backdrop="'static'"
      title="Edit Priority"
    >
      <!-- <EditPriorityDatatable
        v-model="orderItems"
        :items="orderItems"
        @input="reorderItems"
      /> -->

      <BaseDatatable
        no-header
        no-search
        no-action
        :table-headers="tableHeaders"
        :table-datas="orderItems"
      >
        <!-- <Draggable
        class="w-100"
        style="display: contents"
        ghost-class="ghost"
        :list="value"
        @change="reorderItems(orderItems)"
      >
        <template #item="{ element: item, index: itemIndex }">
          <tr
            style="cursor: grab"
            class=""
          >
            <td>
              <i
                class="fas fa-ellipsis-v me-3"
                style="color: #e0e0e0"
              />
              {{ item.name }}
            </td>
            <td>
              {{ itemIndex + 1 }}
            </td>
          </tr>
        </template>
      </Draggable> -->
      </BaseDatatable>

      <template #footer>
        <BaseButton
          type="primary"
          @click="saveReOrderPriority"
        >
          Save
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script>
// import draggable from 'vuedraggable';
// import EditPriorityDatatable from '@product/components/EditPriorityDatatable.vue';

export default {
  name: 'EditPriorityModal',

  components: {
    // Draggable: draggable,
  },

  props: {
    items: {
      type: [Object, Array],
      default: () => [],
    },
    type: {
      type: String,
      default: () => '',
    },
  },

  data() {
    return {
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */

        { name: '', key: 'draggable-icon', order: '0', width: '50px' },
        { name: 'Name', key: 'name', order: 0 },
        { name: 'Priority', key: 'priority', order: 0 },
      ],
      orderItems: [],
      // value: [],
    };
  },
  watch: {
    items(newValue) {
      if (newValue) {
        this.orderItems = newValue;
      }
    },
  },
  mounted() {
    this.orderItems = this.items;
  },

  methods: {
    typePostUrl() {
      switch (this.type) {
        case 'category':
          return '/product/edit-category-priority';
        case 'customization':
          return '/product/edit-customization-priority';
        case 'variation':
          return '/product/edit-variant-priority';
        case 'badge':
          return '/product/edit-badge-priority';
        default:
          return '';
      }
    },

    saveReOrderPriority() {
      if (this.typePostUrl() === '') {
        this.$emit('updatePriority', this.orderItems);
        document.getElementById('close_edit_priority_modal').click();
        return;
      }
      axios
        .post(this.typePostUrl(), {
          items: this.orderItems,
        })
        .then((response) => {
          this.$toast.success('Success', "Items' priority is updated.");
          this.$emit('updatePriority', response.data.items);
          document.getElementById('close_edit_priority_modal').click();
        })
        .catch((error) => {
          this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },
    reorderItems(e) {
      this.orderItems = e;
    },
  },
};
</script>
<style lang="scss" scoped></style>

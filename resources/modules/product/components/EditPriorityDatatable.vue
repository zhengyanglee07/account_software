<template>
  <div>
    <div class="datatable-container">
      <div class="table-wrapper table-responsive border-0">
        <table class="table table-hover">
          <thead>
            <tr>
              <th
                class="nav-second-col"
                style="margin-left: auto"
              >
                <span class="ms-4">Item Name</span>
              </th>
              <th class="nav-third-col">
                Priority
              </th>
            </tr>
          </thead>
          <tbody>
            <Draggable
              class="w-100"
              style="display: contents"
              ghost-class="ghost"
              :list="value"
              @change="emitter(value)"
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
            </Draggable>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import cloneDeep from 'lodash/cloneDeep';

export default {
  name: 'EditPriorityDatatable',

  components: {
    Draggable: draggable,
  },

  props: {
    items: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        /**
         * @param text : column header title
         * @param value : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */

        { text: '', value: 'draggable-icon', order: '0', width: '50px' },
        { text: 'Name', value: 'name', order: 0 },
        { text: 'Priority', value: 'priority', order: 0 },
      ],
      value: [],
    };
  },

  watch: {
    items: {
      deep: true,
      handler(items) {
        if (items) {
          this.value = cloneDeep(items);
        }
      },
    },
  },

  methods: {
    emitter(e) {
      this.$emit('input', e);
    },
  },
};
</script>

<style lang="scss" scoped>
.datatable-container {
  th {
    border-bottom: 1px solid #ced4da !important;
  }

  tbody tr:last-child,
  tr:last-child td {
    border-bottom: 0;
  }

  @media (max-width: $md-display) {
    padding-left: 0;
    padding-right: 0;
  }
}
</style>

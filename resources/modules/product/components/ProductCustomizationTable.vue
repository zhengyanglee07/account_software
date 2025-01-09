<template>
  <BaseDatatable
    no-header
    no-edit-action
    no-search
    no-sorting
    no-action
    :table-headers="tableHeaders"
    :table-datas="optionArray"
  >
    <template #cell-values="{ row: { label } }">
      <span v-if="label !== ''">
        {{ label }}
      </span>
      <BaseBadge
        v-else
        text="Empty"
        type="warning"
      />
    </template>
    <!-- <template #cell-draggable-icon>
      <i
        class="fas fa-ellipsis-v"
        style="color: #e0e0e0"
      />
    </template> -->
  </BaseDatatable>
  <div v-if="false">
    <div class="datatable-container">
      <div class="table-wrapper table-responsive border-0">
        <table class="table table-hover">
          <thead>
            <tr>
              <th
                v-for="(header, index) in tableHeaders"
                :key="index"
                class="user-select-none"
              >
                <div class="w-100">
                  <span style="white-space: nowrap">{{ header.text }}</span>
                </div>
              </th>
            </tr>
          </thead>
          <!-- <tbody class="w-100"> -->
          <Draggable
            class="w-100"
            style="display: contents"
            ghost-class="ghost"
            :value="value"
            @input="emitter"
          >
            <tr
              v-for="(option, optionIndex) in optionArray"
              :key="optionIndex"
              style="cursor: grab"
              class=""
            >
              <td style="">
                <i
                  class="fas fa-ellipsis-v"
                  style="color: #e0e0e0"
                />
              </td>
              <td
                v-for="(header, headerIndex) in tableHeaders"
                :key="headerIndex"
                class="p-auto p-two"
                :style="{ display: header.text === '' ? 'none' : 'auto' }"
              >
                <span v-if="header.value !== ''">{{
                  option[header.value]
                }}</span>
              </td>
            </tr>
          </Draggable>
          <!-- </tbody> -->
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
  components: { draggable },
  props: ['datas', 'value'],
  data() {
    return {
      tableHeaders: [
        // { key: 'draggable-icon', custom: true },
        { name: 'Display Name', key: 'display_name' },
        { name: 'Type', key: 'type' },
        { name: 'Values', key: 'values', custom: true },
      ],
      modalId: 'product-option-delete-modal',
    };
  },
  computed: {
    optionArray() {
      const optionArray = [];
      this.datas.forEach((option) => {
        const inputArray = [];
        option.inputs.forEach((input) => {
          inputArray.push(input.label);
        });
        optionArray.push({
          display_name: option.display_name,
          type: option.type,
          label: inputArray.join(', ', ''),
        });
      });
      return optionArray;
    },
  },
  methods: {
    emitter(e) {
      // console.log(e);
      this.$emit('input', e);
    },
  },
};
</script>

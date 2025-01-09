<template>
  <BaseDatatable
    no-header
    no-edit-action
    no-search
    no-sorting
    no-action
    :table-headers="tableHeaders"
    :table-datas="datas"
  >
    <!-- <template #cell-draggable-icon>
      <i
        class="fas fa-ellipsis-v"
        style="color: #e0e0e0"
      />
    </template> -->
    <template #cell-type="{ row: item }">
      <span class="text-capitalize">
        {{ item.type }}
      </span>
    </template>
  </BaseDatatable>
</template>

<script>
/* eslint-disable camelcase */
// import draggable from 'vuedraggable';

export default {
  components: {
    // draggable,
  },

  props: ['currentVariants', 'swapVariant'],

  data() {
    return {
      tableHeaders: [
        // { key: 'draggable-icon', custom: true },
        { name: 'Display Name', key: 'name' },
        { name: 'Type', key: 'type', custom: true },
        { name: 'Values', key: 'variantValue' },
      ],
      variantOptions: [],
    };
  },

  computed: {
    datas() {
      return this.currentVariants.map((item) => {
        let name = '';

        item.valueArray.forEach(({ variant_value }) => {
          if (name === '') {
            name = variant_value;
          } else {
            name = name.concat(`, ${variant_value}`);
          }
        });

        return {
          name: item.display_name || item.name,
          type: item.type === 'colour' ? 'color' : item.type,
          variantValue: name,
        };
      });
    },
  },

  watch: {
    currentVariants: {
      immediate: true,
      deep: true,
      handler(e) {
        this.variantOptions = e;
      },
    },
  },

  methods: {
    onDragOption(e) {
      this.swapVariant(e);
      // console.log(e);
    },
  },
};
</script>

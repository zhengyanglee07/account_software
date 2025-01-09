<template>
  <BaseModal
    title="Add Global Customization"
    :modal-id="modalId"
    size="lg"
  >
    <BaseDatatable
      title="global customization"
      no-action
      no-header
      :table-headers="tableHeaders"
      :table-datas="sharedOptions"
      checbox-id="add-global-customization-checkbox"
      @selectAll="handleSelectAll"
    >
      <template #cell-checkbox="{ row: data }">
        <BaseFormCheckBox
          v-model="inputOptionId"
          :value="data.id"
          @change="handleSelect"
        />
      </template>
      <template #cell-values="{ index }">
        <span v-if="optionValues[index] !== ''">
          {{ optionValues[index] }}
        </span>
        <BaseBadge
          v-else
          text="Empty"
          type="warning"
        />
      </template>
    </BaseDatatable>
    <template #footer>
      <BaseButton
        id="save-shared-option"
        :disabled="!sharedOptions.length || addingCustomization"
        @click="addSharedOption"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>
'
<script>
import { Modal } from 'bootstrap';
import cloneDeep from 'lodash/cloneDeep';

export default {
  props: {
    modalId: String,
    checkedProductIds: Array,
    sharedOptions: Array,
  },
  data() {
    return {
      addingCustomization: false,
      tableHeaders: [
        { key: 'checkbox', sortable: false },
        { name: 'Display Name', key: 'display_name' },
        {
          name: 'Type',
          key: 'type',
        },
        { name: 'Values', key: 'values', custom: true },
      ],
      inputOptionId: [],
    };
  },
  computed: {
    emptyCheckedProducts() {
      return this.checkedProductIds?.length === 0;
    },
    optionValues() {
      const optionDatas = cloneDeep(this.sharedOptions);
      const optionArray = [];
      optionDatas
        .sort((a, b) => {
          if (a?.id < b?.id) {
            return 1;
          }
          if (a?.id > b.id) {
            return -1;
          }
          return 0;
        })
        .forEach((option) => {
          const inputArray = [];
          option.inputs.forEach((input) => {
            inputArray.push(input.label);
          });
          optionArray.push(inputArray.join(', '));
        });
      return optionArray;
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);
      modalEl.addEventListener('show.bs.modal', () => {
        this.inputOptionId = [];
        document.getElementById(
          'add-global-customization-checkbox'
        ).checked = false;
      });
    }, 1000);
  },
  methods: {
    handleSelectAll(e) {
      this.inputOptionId = [];
      if (e) {
        this.sharedOptions.forEach((option) => {
          this.inputOptionId.push(option.id);
        });
      }
    },
    handleSelect(e) {
      const { checked } = e.target;
      if (!checked)
        document.getElementById(
          'add-global-customization-checkbox'
        ).checked = false;
    },
    addSharedOption() {
      this.errMessage = '';
      if (this.inputOptionId?.length < 1) {
        this.$toast.error('Failed', 'Please select at least one option.');
        return;
      }

      if (this.emptyCheckedProducts) {
        this.$toast.error('Failed', 'Please select at least one product.');
        return;
      }

      this.addingCustomization = true;
      this.$axios
        .post('/bulk-products/option/add', {
          productIds: this.checkedProductIds,
          optionIds: this.inputOptionId,
        })
        .then(() => {
          Modal.getInstance(
            document.getElementById('add-shared-option-modal')
          ).hide();
          this.inputOptionId = [];
          this.$emit('update');
          this.$toast.success(
            'Success',
            'Successfully added global customization.'
          );
        })
        .catch((err) => {
          console.log(err);

          this.$toast.error(
            'Failed',
            'Failed to add the global customization.'
          );
        })
        .finally(() => {
          this.addingCustomization = false;
        });
    },
  },
};
</script>

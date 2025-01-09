<template>
  <BaseModal
    title="Remove Global Customization"
    :modal-id="modalId"
    size="lg"
  >
    <BaseDatatable
      title="global customization"
      no-action
      no-header
      checbox-id="remove-global-customization-checkbox"
      :table-headers="tableHeaders"
      :table-datas="sharedOptions"
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
        id="remove-shared-option"
        :disabled="!sharedOptions.length || removingCustomization"
        @click="removeSharedOption"
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
      removingCustomization: false,
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
        ?.forEach((option) => {
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
          'remove-global-customization-checkbox'
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
          'remove-global-customization-checkbox'
        ).checked = false;
    },
    removeSharedOption() {
      this.errMessage = '';
      if (this.inputOptionId?.length < 1) {
        this.$toast.error('Failed', 'Please select at least one option.');
        return;
      }

      if (this.emptyCheckedProducts) {
        this.$toast.error('Failed', 'Please select at least one product.');
        return;
      }

      this.removingCustomization = true;
      this.$axios
        .post('/bulk-products/option/remove', {
          productIds: this.checkedProductIds,
          optionIds: this.inputOptionId,
        })
        .then(() => {
          Modal.getInstance(
            document.getElementById('remove-shared-option-modal')
          ).hide();
          this.inputOptionId = [];
          this.$emit('update');
          this.$toast.success(
            'Success',
            'Successfully removed global customization.'
          );
        })
        .catch((err) => {
          console.log(err);

          this.$toast.error(
            'Failed',
            'Failed to remove the global customization.'
          );
        })
        .finally(() => {
          this.removingCustomization = false;
        });
    },
  },
};
</script>

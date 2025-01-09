<template>
  <BaseModal
    :modal-id="modalId"
    title="Commission Level"
  >
    <BaseFormGroup :col="8">
      <BaseFormInput
        v-if="commissionType === 'percentage'"
        v-model="commission"
        type="number"
        min="1"
        max="100"
      />
      <BaseFormInput
        v-else
        v-model="commission"
        type="number"
      />
    </BaseFormGroup>
    <BaseFormGroup :col="4">
      <BaseFormSelect v-model="commissionType">
        <option value="percentage">
          %
        </option>
        <option value="fixed">
          Fixed
        </option>
      </BaseFormSelect>
    </BaseFormGroup>
    <template #footer>
      <BaseButton @click="editLevel">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import shortid from 'shortid';
import { Modal } from 'bootstrap';

export default {
  name: 'CommissionLevelModal',
  props: {
    modalId: {
      type: String,
      default: '',
    },
    level: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
  data() {
    return {
      commission: 1,
      commissionType: 'percentage',
    };
  },
  watch: {
    level: {
      handler(newValue) {
        this.commission = newValue?.commission_rate ?? 1;
        this.commissionType = newValue?.commission_type ?? 'percentage';
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    editLevel() {
      if (this.commission <= 0) {
        this.$toast.error('Error', 'Commission should be greater than 0');
        return;
      }

      if (this.commissionType === 'percentage' && this.commission > 100) {
        this.$toast.error('Error', 'Maximum commission in percentage is 100');
        return;
      }

      this.$emit('edit-level', {
        id: this.level?.id ?? shortid.generate(),
        commission_rate: this.commission,
        commission_type: this.commissionType,
      });

      // reset local data
      if (this.modalId === 'add-level-modal') {
        this.commission = 1;
        this.commissionType = 'percentage';
      }
      Modal.getInstance(document.getElementById(this.modalId)).hide();
    },
  },
};
</script>

<style scoped></style>

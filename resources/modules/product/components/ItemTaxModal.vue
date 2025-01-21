<template>
  <BaseModal :modal-id="modalId" no-dismiss title="Taxes" no-padding size="lg">
    <BaseDatatable
      title="items"
      :table-headers="TAX_TABLE_HEADERS"
      :table-datas="taxList"
      no-search
      no-action
      no-responsive
    >
      <template #action-button>
        <BaseButton type="light" @click="addTaxType"> Add Tax Type </BaseButton>
      </template>

      <template #cell-taxType="{ index }">
        <BaseMultiSelect
          v-model="taxList[index].taxType"
          :options="taxTypes"
          label="Description"
          :reduce="(option) => option.Code"
        />
      </template>

      <template #cell-numberOfUnits="{ index }">
        <BaseFormInput v-model="taxList[index].numberOfUnits" type="number" />
      </template>

      <template #cell-ratePerUnit="{ index }">
        <BaseFormInput v-model="taxList[index].ratePerUnit" type="number" />
      </template>

      <template #cell-taxRate="{ index }">
        <BaseFormInput v-model="taxList[index].taxRate" type="number">
          <template #append>%</template>
        </BaseFormInput>
      </template>

      <template #cell-taxAmount="{ row }">
        {{ getTaxAmount(taxableAmountWithExemption, row) }}
      </template>
    </BaseDatatable>

    <BaseFormGroup label="Amount Exempted From Tax">
      <BaseFormInput v-model="taxExempt.amount" type="number" />
    </BaseFormGroup>

    <BaseFormGroup label="Amount of Tax Exempted">
      <BaseFormInput v-model="taxExemptedAmount" type="number" disabled />
    </BaseFormGroup>

    <BaseFormGroup label="Details of Tax Exemption">
      <BaseFormInput v-model="taxExempt.reason" type="text" />
    </BaseFormGroup>

    <ContactDetail :countries="countries" :states="states" />
    <template #footer>
      <BaseButton
        type="light"
        data-bs-dismiss="modal"
        @click="cancelAddPeopleProfile"
      >
        Dismiss
      </BaseButton>
      <BaseButton
        id="add-people-button"
        :disabled="saving"
        @click="addPeopleProfile"
      >
        <div v-if="saving">
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
        <span v-else>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';

const props = defineProps({
  modalId: {
    type: String,
    required: true,
  },
  taxTypes: {
    type: Array,
    required: true,
  },
  totalExclusiveTax: {
    type: Number,
    default: 10,
  },
  itemTaxes: {
    type: Array,
    default: () => [],
  },
});

const TAX_TABLE_HEADERS = [
  { name: 'Tax Type', key: 'taxType', custom: true },
  { name: 'Number of Units', key: 'numberOfUnits', custom: true },
  { name: 'Rate Per Unit', key: 'ratePerUnit', custom: true },
  { name: 'Tax Rate', key: 'taxRate', custom: true },
  { name: 'Tax Amount (RM)', key: 'taxAmount', custom: true },
];

const taxList = ref(props.itemTaxes);

watch(() => props.itemTaxes, (newVal) => {
  taxList.value = newVal;
});

const taxExempt = reactive({
  reason: '',
  amount: 0,
});

const addTaxType = () => {
  taxList.value.push({
    taxType: '',
    numberOfUnits: 0,
    ratePerUnit: 0,
    taxRate: 0,
    taxAmount: 0,
  });
};

const taxableAmountWithoutExemption = computed(() => {
  return props.totalExclusiveTax;
});
const taxableAmountWithExemption = computed(() => {
  const hasTaxExemption = taxExempt.amount > 0;
  if (hasTaxExemption) {
    return props.totalExclusiveTax - taxExempt.amount;
  }
  return props.totalExclusiveTax;
});

const taxExemptedAmount = computed(() => {
  const taxAmountWithoutExemption = taxList.value.reduce((acc, tax) => {
    console.log(getTaxAmount(taxableAmountWithoutExemption.value, tax),'aaaaaaaaa')
    return acc + getTaxAmount(taxableAmountWithoutExemption.value, tax);
  }, 0);
  const taxAmountWithExemption = taxList.value.reduce((acc, tax) => {
    return acc + getTaxAmount(taxableAmountWithExemption.value, tax);
  }, 0);
  return taxAmountWithoutExemption - taxAmountWithExemption;
});

const getTaxAmount = (taxable, tax) => {
  if (tax.taxRate > 0) {
    return taxable * (tax.taxRate / 100);
  }
  return tax.ratePerUnit * tax.numberOfUnits;
};
</script>

<style scoped lang="scss">
.modal__dividor {
  width: 100%;
  height: 0.4px;
  background-color: #ced4da;
  margin: 20px 0;
}
</style>

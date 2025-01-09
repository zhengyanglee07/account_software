<template>
  <BasePageLayout
    page-name="Taxes Settings"
    back-to="/settings/all"
    is-setting
  >
    <template #action-button>
      <BaseButton
        href="/tax/settings/new"
        has-add-icon
      >
        Add Tax Rate
      </BaseButton>
    </template>

    <BaseSettingLayout
      title="Countries and Rates"
      is-datatable-only-in-content
    >
      <template #description>
        <p>Set up tax rates baes on your shipping zones policies.</p>
      </template>
      <template #content>
        <BaseDatatable
          title="tax rate"
          no-header
          :table-headers="tableHeaders"
          :table-datas="state.taxCountryAll"
          @delete="deleteTaxRegion"
        />
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Tax Settings">
      <template #description>
        <p>Decide the way to calculate taxes on your store.</p>
      </template>
      <template #content>
        <b>Calculation</b>
        <BaseFormGroup
          label="Include Tax In Product Selling Prices"
          description="Formula: Tax = (Tax * Price) / (1+ Tax)"
          col="10"
        />
        <BaseFormGroup col="2">
          <BaseFormSwitch v-model="state.isProductIncludeTax" />
        </BaseFormGroup>

        <BaseFormGroup
          label="Shipping Fees Taxables"
          description="Include shipping fee in the tax calculation"
          col="10"
        />
        <BaseFormGroup col="2">
          <BaseFormSwitch v-model="state.isTaxIncludeShipping" />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="saveTaxSetting">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import { onMounted, reactive, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import taxAPI from '@setting/api/taxAPI.js';

const $toast = inject('$toast');

const props = defineProps({
  taxSetting: { type: Object, default: () => {} },
  taxCountry: { type: Array, default: () => [] },
});

const tableHeaders = [
  { name: 'Country', key: 'country_name' },
  { name: 'Country taxes (%)', key: 'country_tax' },
];
const state = reactive({
  taxCountryAll: [],
  isProductIncludeTax: false,
  isTaxIncludeShipping: false,
});

const editTax = (urlId) => {
  router.visit(`/tax/settings/edit/${urlId}`);
};

const saveTaxSetting = () => {
  taxAPI
    .update(state.isProductIncludeTax, state.isTaxIncludeShipping)
    .then((response) => {
      $toast.success('Success', 'Tax setting save successfully');
    });
};

const deleteTaxRegion = (id) => {
  taxAPI
    .delete(id)
    .then((response) => {
      $toast.success('Success', 'Tax region deleted');
      router.visit('/tax/settings');
    })
    .catch((error) => {});
};

const loadData = () => {
  if (props.taxSetting || props.taxCountry) {
    state.isTaxIncludeShipping = !!props.taxSetting.is_shipping_fee_taxable;
    state.isProductIncludeTax = !!props.taxSetting.is_product_include_tax;
    state.taxCountryAll = props.taxCountry.map((m) => ({
      ...m,
      editLink: `/tax/settings/edit/${m.urlId}`,
    }));
  }
};

onMounted(() => {
  loadData();
});
</script>

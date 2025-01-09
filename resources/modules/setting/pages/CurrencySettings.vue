<template>
  <BasePageLayout
    page-name="Currency Settings"
    back-to="/settings/all"
    is-setting
  >
    <template #action-button>
      <BaseButton
        data-bs-toggle="modal"
        data-bs-target="#currencyModel"
        has-add-icon
        @click="changeType({ type: 'Add', id: 0 })"
      >
        Add Currency
      </BaseButton>
    </template>

    <BaseSettingLayout
      title="Currency Exchange Rate"
      is-datatable-only-in-content
    >
      <template #description>
        <p>Set multiple currency for your store.</p>
      </template>
      <template #content>
        <CurrencyTable
          :currency-settings="props.currencySettings"
          @changeType="changeType"
        />
        <CurrencyModal
          modal-id="currencyModel"
          :type="state.type"
          :currency-id="state.currencyId"
          :default-currency="defaultCurrency"
          :saved-currency-list="savedCurrencyList"
        />
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Price Rounding">
      <template #description>
        <p>
          Make your currency prices consistent by rounding them up after they're
          converted.
        </p>
      </template>
      <template #content>
        <BaseFormGroup>
          <BaseFormSwitch
            id="currency-price-rounding"
            v-model="state.checked"
          >
            Rounding
          </BaseFormSwitch>
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="save">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import CurrencyTable from '@setting/components/CurrencyDisplayTable.vue';
import CurrencyModal from '@setting/components/CurrencyModal.vue';
import currencyAPI from '@setting/api/currencyAPI.js';
import { reactive, computed, inject, onMounted } from 'vue';

const $toast = inject('$toast');

const props = defineProps({
  currencySettings: { type: Array, default: () => [] },
});

const state = reactive({
  type: '',
  currencyId: 0,
  checked: '',
});

const defaultCurrency = computed(() => props.currencySettings[0].currency);
const savedCurrencyList = computed(() =>
  props.currencySettings.map((m) => m.currency)
);
const isRounding = computed(() => props.currencySettings[1]?.rounding ?? 0);

const changeType = ({ type, id }) => {
  state.type = type;
  state.currencyId = id;
};

const save = () => {
  currencyAPI
    .updateRounding(state.checked)
    .then((response) => {
      $toast.success('Success', 'Settings Saved');
    })
    .catch((error) => {
      $toast.error('Error', 'Fail to Saved');
    });
};

onMounted(() => {
  state.checked = isRounding.value;
});
</script>

<style scoped lang="scss"></style>

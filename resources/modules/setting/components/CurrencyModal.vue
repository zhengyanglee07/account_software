<template>
  <BaseModal
    :title="title"
    :modal-id="props.modalId"
  >
    <BaseFormGroup
      v-if="!currencyData.isDefault"
      :label="subTitle"
    >
      <CurrencySelect
        v-model="currencyData.currency"
        :options="filteredCurrencyList"
      />
    </BaseFormGroup>

    <!-- <label class="setting-title">Main Settings</label> -->
    <BaseFormGroup
      v-if="!currencyData.isDefault"
      :label="`Exchange Rate (1 ${props.defaultCurrency} = ${currencyData.exchangeRate} ${currencyData.currency})`"
      :description="`Suggest exchange rate: ${currencyData.suggestRate}`"
    >
      <BaseFormInput
        id="currency-exchange-rate"
        v-model="currencyData.exchangeRate"
        type="number"
      />
    </BaseFormGroup>
    <BaseFormGroup v-if="!currencyData.isDefault">
      <BaseFormCheckBox
        id="currency-is-default"
        v-model="currencyData.isDefault"
        :value="true"
      >
        Set as default currency
      </BaseFormCheckBox>
    </BaseFormGroup>

    <b>Currency Display</b>
    <p>Price in this currency will be display in the format of:</p>
    <h1>{{ `${currencyData.currency} ${displayPrice}` }}</h1>

    <BaseFormGroup
      label="Thousand Separator"
      col="6"
    >
      <BaseFormSelect
        id="currency-thousand-seperator"
        v-model="currencyData.separatorType"
        :options="['none', ',', '.']"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Decimal Places"
      :description="
        isZeroDecimalCurrency
          ? `${currencyData.currency} is zero decimal currencies. All the
              price in this currency will automatically be ignored`
          : ''
      "
      col="6"
    >
      <BaseFormSelect
        id="currency-decimal-places"
        v-model="currencyData.decimalPlaces"
        :disabled="isZeroDecimalCurrency"
        :options="['2', '0']"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="isLoading"
        @click="saveCurrencySettings"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import currencyList from '@setting/lib/currencies.js';
import currencyAPI from '@setting/api/currencyAPI.js';
import eventBus from '@services/eventBus.js';
import { computed, onMounted, reactive, watch, inject, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const $toast = inject('$toast');

const props = defineProps({
  modalId: { type: String, default: '' },
  type: { type: String, default: '' },
  currencyId: { type: Number, default: null },
  defaultCurrency: { type: String, default: '' },
  savedCurrencyList: { type: Array, default: () => [] },
});

const currencyData = reactive({
  currency: '',
  exchangeRate: '1',
  isDefault: false,
  decimalPlaces: '2',
  separatorType: ',',
  suggestRate: '',
  zeroDecimalCurrencyList: [
    'BIF',
    'CLP',
    'DJF',
    'GNF',
    'JPY',
    'KMF',
    'KRW',
    'MGA',
    'PYG',
    'RWF',
    'UGX',
    'VND',
    'VUV',
    'XAF',
    'XOF',
    'XPF',
  ],
});

const title = computed(() =>
  props.type === 'Add' ? 'Add Currency' : 'Edit Currency'
);
const subTitle = computed(() =>
  props.type === 'Add' ? 'Please select a currency:' : 'Currency'
);
const filteredCurrencyList = computed(() =>
  Object.keys(currencyList).filter(
    (currency) => !props.savedCurrencyList.includes(currency)
  )
);
const isZeroDecimalCurrency = computed(() =>
  currencyData.zeroDecimalCurrencyList.includes(currencyData.currency)
);
const displayPrice = computed(() => {
  let price = parseFloat(1234.5);
  price =
    currencyData.decimalPlaces === '0'
      ? Math.floor(price).toFixed(0)
      : price.toFixed(2);
  if (currencyData.separatorType !== 'none')
    price = price.replace(/\B(?=(\d{3})+\b)/g, currencyData.separatorType);
  return price;
});

const getLiveExchangeRate = () => {
  if (currencyData.currency !== props.defaultCurrency) {
    currencyAPI
      .getLatestRate(currencyData.currency, props.defaultCurrency)
      .then((response) => {
        currencyData.suggestRate = response.data.result;
      });
  }
};

const isLoading = ref(false);
const saveCurrencySettings = () => {
  isLoading.value = true;
  currencyAPI
    .update({
      selectedCurrency: currencyData.currency,
      exchangeRate: currencyData.exchangeRate,
      isDefault: currencyData.isDefault,
      decimalPlaces: currencyData.decimalPlaces,
      separatorType: currencyData.separatorType,
      type: props.type,
      currencyId: props.currencyId,
      suggestRate: currencyData.suggestRate,
    })
    .then(({ data }) => {
      isLoading.value = false;
      $toast.success('Success', 'Successful Added Currency');
      eventBus.$emit(`hide-modal-${props.modalId}`);
      if (props.type === 'Edit') {
        eventBus.$emit('updateCurrency', data);
        currencyData.isDefault = false;
        return;
      }
      router.visit('/currency/settings');
    })
    .catch((error) => {
      isLoading.value = false;
      $toast.error('Error', 'Unsuccessful Added Currency');
    });
};

watch(currencyData, (newVal) => {
  if (props.type === 'Add' && isZeroDecimalCurrency.value) {
    currencyData.decimalPlaces = '0';
  }
  if (props.defaultCurrency !== newVal.currency) {
    getLiveExchangeRate();
  }
});

watch(
  () => props.type,
  (newValue) => {
    if (newValue === 'Add') {
      currencyData.exchangeRate = '1';
      currencyData.isDefault = false;
      currencyData.suggestRate = '';
      currencyData.decimalPlaces = 2;
      currencyData.separatorType = ',';
    }
  }
);

onMounted(() => {
  eventBus.$on(
    'getSelectedCurrency',
    ({
      currency,
      exchangeRate,
      isDefault,
      decimal_places: decimalPlaces,
      separator_type: separatorType,
    }) => {
      currencyData.currency = currency;
      currencyData.exchangeRate = exchangeRate;
      currencyData.isDefault = isDefault === '1';
      currencyData.decimalPlaces = decimalPlaces.toString();
      currencyData.separatorType = separatorType;
    }
  );
});
</script>

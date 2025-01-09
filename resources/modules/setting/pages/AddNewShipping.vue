<template>
  <BasePageLayout
    :page-name="
      props.isNew
        ? 'Add Shipping Zone'
        : `Edit ${props.shippingZone.zone_name} Setting`
    "
    back-to="/shipping/settings"
    is-setting
  >
    <BaseSettingLayout
      :title="props.onboarding ? '' : 'New Zone'"
      :is-onboarding="props.onboarding"
      has-action-button
      back-url="/onboarding/shippings"
      skip-url="/onboarding/shipping/scheduled-delivery"
    >
      <template #description>
        <p>Choose countries and their sub-regions to ship.</p>
      </template>
      <template #content>
        <Component
          :is="props.onboarding ? 'div' : 'BaseCard'"
          class="zone"
        >
          <BaseFormGroup label="Zone Name">
            <BaseFormInput
              v-if="state.isLoading == 'done'"
              id="shipping-zone-name"
              v-model="state.zoneName"
              type="text"
              name="zoneNameInput"
              placeholder="e.g Southeast Asia, Europe, East Malaysia"
            />
          </BaseFormGroup>

          <BaseFormGroup
            v-if="state.selectedCountry !== 'Rest of world'"
            label="Zone Countries"
          >
            <BaseFormSelect
              :model-value="state.selectedCountry"
              placeholder="Add country(s)"
              @input="setSelected($event.target.value)"
            >
              <option
                value=""
                disabled
              >
                Add country(s)
              </option>
              <option
                v-for="(country, index) in countries"
                :key="index"
                :value="country"
              >
                {{ country }}
              </option>
            </BaseFormSelect>
          </BaseFormGroup>

          <BaseFormGroup>
            <table>
              <tbody
                v-for="(data, index) in state.countryData"
                :key="index"
                class="mt-3"
              >
                <tr class="w-100">
                  <td>
                    {{ data.selectedCountry.toUpperCase() }}
                  </td>
                  <td />
                  <td style="text-align: right; padding: 0">
                    <BaseButton
                      v-if="data.selectedCountry !== 'Rest of world'"
                      type="link"
                      @click="removeCountry(data, index)"
                    >
                      <i title="Delete">
                        <img
                          src="@shared/assets/media/trash.svg"
                          alt="delete"
                          style="transform: scale(1); width: 50px"
                        >
                      </i>
                    </BaseButton>
                  </td>
                </tr>
                <tr v-if="data.selectedCountry !== 'Rest of world'">
                  <td colspan="4">
                    <BaseFormGroup>
                      <BaseFormRadio
                        :id="`shipping-zip-codes-${index}`"
                        v-model="data.selectedType"
                        value="zipcodes"
                      >
                        By zip codes
                      </BaseFormRadio>
                    </BaseFormGroup>

                    <BaseFormGroup v-if="data.selectedType == 'zipcodes'">
                      <BaseFormInput
                        :id="`shipping-zip-codes-input-${index}`"
                        type="number"
                        name="form-name-here"
                        @keydown.enter="inputZipcode(data, $event)"
                      />
                      <BaseBadge
                        v-for="(zipcode, idx) in data.zipcode"
                        :key="idx"
                        has-delete-button
                        :text="zipcode"
                        @delete="removeZipcode(data, idx)"
                      />
                    </BaseFormGroup>

                    <BaseFormGroup>
                      <BaseFormRadio
                        :id="`shipping-states-${index}`"
                        v-model="data.selectedType"
                        value="states"
                      >
                        By states
                      </BaseFormRadio>
                    </BaseFormGroup>

                    <BaseCard v-if="data.selectedType == 'states'">
                      <BaseFormGroup
                        v-for="allState in data.state"
                        :key="allState.id"
                        class="shipping-state-form-group"
                        col="3"
                      >
                        <BaseFormCheckBox
                          :id="`shipping-state-${index}-${allState.id}`"
                          v-model="allState.isChecked"
                          :value="true"
                        >
                          {{ allState.stateName }}
                        </BaseFormCheckBox>
                      </BaseFormGroup>
                    </BaseCard>

                    <BaseCard
                      v-if="
                        data.selectedType == 'states' &&
                          data.existedState &&
                          data.existedState.length > 0
                      "
                    >
                      <BaseFormGroup
                        v-for="(existedState, idx) in data.existedState"
                        :key="idx"
                        class="shipping-state-form-group"
                        col="2"
                      >
                        <BaseFormCheckBox
                          :id="`shipping-existed-state-${index}-${idx}`"
                          :disabled="existedState.isDisabled"
                        >
                          {{ existedState.stateName }}
                        </BaseFormCheckBox>
                      </BaseFormGroup>
                    </BaseCard>
                  </td>
                </tr>
              </tbody>
            </table>
          </BaseFormGroup>
        </Component>
      </template>
    </BaseSettingLayout>

    <Component
      :is="component.name === 'flat-rate' ? FlatRate : BasedOnWeight"
      v-for="(component, index) in shippingFeeComponent"
      :key="index"
      :onboarding="onboarding"
      :class="{
        'mt-2': index > 0,
      }"
      :index="component.id"
      :type="component.type"
      :value="component.type === 'bow' ? state.bowData : state.flatData"
      :default-currency="props.defaultCurrency"
      @input="updateShippingSettings"
      @delete-component="deleteShippingSetttings"
    />

    <BaseSettingLayout
      class="text-center"
      :is-onboarding="onboarding"
    >
      <template #content>
        <b>Add Shipping Rates</b>
        <BaseFormGroup>
          <BaseButton
            class="me-3 mb-3"
            @click="addShippingMethods('flat')"
          >
            Flat rate per order
          </BaseButton>

          <BaseButton
            class="mb-3"
            @click="addShippingMethods('bow')"
          >
            Based on weight
          </BaseButton>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>
    <template
      v-if="!props.onboarding"
      #footer
    >
      <BaseButton
        type="link"
        class="me-6"
        @click="redirectBack"
      >
        Cancel
      </BaseButton>
      <BaseButton @click="saveShippingSetting">
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import BasedOnWeight from '@setting/components/ShippingBasedOnWeight.vue';
import FlatRate from '@setting/components/ShippingFlatRate.vue';
import { router } from '@inertiajs/vue3';
import shippingAPI from '@setting/api/shippingAPI.js';
import { computed, onMounted, reactive, inject, ref } from 'vue';
import clone from 'clone';
import eventBus from '@services/eventBus.js';

export default {
  mixins: [specialCurrencyCalculationMixin],
};
</script>
<script setup>
const $toast = inject('$toast');

const props = defineProps({
  onboarding: { type: Boolean, default: false },
  isNew: { type: Boolean, default: true },
  defaultCurrency: { type: String, default: 'RM' },
  shippingZone: { type: Object, default: () => {} },
  shippingMethods: { type: Array, default: () => [] },
  shippingRegion: { type: Array, default: () => [] },
  otherExistedRegion: { type: Array, default: () => [] },
  submitUrl: { type: String, default: '' },
});

const zoneNameFieldInput = ref(null);

const state = reactive({
  zoneName: '',
  country: '',
  region: '',
  countryAPI: [],
  selectedCountry: '',
  isLoading: '',
  selectedState: [],
  stateAPI: [],
  authToken: '',
  bowComponents: [],
  flatComponents: [],
  bowData: [],
  flatData: [],
  countryData: [],
  existedRegion: [],
  nextBowId: -1,
  nextFlatId: -1,
  removedCountry: [],
  allComponentId: -1,
  nextShippingId: -1,
  isExpand: [],
  allAPI: [],
  deletedMethod: [],
  deletedCountry: [],
});

const countries = computed(() =>
  state.isLoading === 'done'
    ? [...new Set(state.countryAPI.map((c) => c.countryName))]
    : []
);
const states = computed(() =>
  state.isLoading === 'done'
    ? [...new Set(state.stateAPI.map((s) => s.state_name))]
    : []
);
const shippingFeeComponent = computed(() => {
  const sortedComponent = [...state.bowComponents, ...state.flatComponents]
    .filter((e) => e)
    .sort((a, b) => (a.index > b.index ? 1 : -1));
  return sortedComponent.map((m) => ({
    ...m,
    type: m.name === 'based-on-weight' ? 'bow' : 'flat',
  }));
});

const inputZipcode = (data, event) => {
  if (!data.zipcode.includes(event.target.value)) {
    data.zipcode.push(event.target.value);
  } else {
    $toast.error('Error', 'Zipcode already existed');
  }
  event.target.value = '';
};
const removeZipcode = (data, index) => {
  data.zipcode.splice(index, 1);
};

const deleteShippingZone = () => {
  shippingAPI.delete(props.shippingZone).then((response) => {
    document.getElementById('deleteShippingZone-close-button')?.click();
    router.visit('/shipping/settings');
  });
};

const removeCountry = (value, index) => {
  state.selectedCountry = '';
  const recoverCountry = state.removedCountry.find(
    (item) => item.countryName === value.selectedCountry
  );

  if (!props.isNew) {
    const currentExistedCountry = props.shippingRegion.find(
      (item) => item.country === value.selectedCountry
    );

    const existedCountry = state.existedRegion
      .map((item) => item.country)
      .indexOf(value.selectedCountry);

    if (currentExistedCountry && existedCountry !== -1) {
      state.deletedCountry.push(currentExistedCountry.id);
      state.existedRegion.splice(existedCountry, 1);
    }
  }

  const idx = state.removedCountry
    .map((data) => data.countryName)
    .indexOf(value.selectedCountry);
  if (recoverCountry) {
    state.countryAPI.unshift(recoverCountry);
    state.removedCountry.splice(idx, 1);
    state.countryData.splice(index, 1);
  } else {
    $toast.error('Error', 'Something went wrong');
  }

  if (!props.isNew) return;
  const restIdx = state.removedCountry
    .map((data) => data.countryName)
    .indexOf('Rest of world');
  const restOfWorld = state.removedCountry.find(
    (item) => item.countryName === 'Rest of world'
  );
  const filteredROW = state.existedRegion.find(
    (rest) => rest.country === 'Rest of world'
  );
  if (restIdx !== -1 && state.countryData.length === 0 && !filteredROW) {
    state.countryAPI.unshift(restOfWorld);
    state.removedCountry.splice(restIdx, 1);
  }
};

const loadData = () => {
  state.zoneName = props.shippingZone.zone_name;
  state.countryData = props.shippingRegion.map((item) => {
    return {
      id: item.id,
      selectedCountry: item.country,
      state: item.state,
      selectedType: item.region_type,
      zipcode: JSON.parse(item.zipcodes),
      existedState: [],
    };
  });

  state.countryData.forEach((data) => {
    if (data.selectedCountry === 'Rest of world') {
      state.selectedCountry = 'Rest of world';
    }

    const otherExistedRegion = props.otherExistedRegion.map((item) => ({
      country: JSON.parse(item.country),
      state: JSON.parse(item.state),
      zone_id: item.zone_id,
      zone_name: item.zone_name,
      account_id: item.account_id,
    }));

    const filteredRegion = otherExistedRegion.filter(
      (region) => region.country === data.selectedCountry
    );

    if (filteredRegion.length > 0) {
      const tempArr = [];
      filteredRegion.forEach((fR) => {
        const stateArr = fR.state.map((s) => s.stateName);
        tempArr.push(...stateArr);
      });

      tempArr.forEach((e) => {
        data.existedState.push({
          stateName: e,
          isDisabled: true,
        });
      });

      const currentArr = data.state.map((s) => s.stateName);
      const allExistedArr = tempArr.concat(currentArr);
      const currentCountry = state.allAPI.find(
        (c) => c.countryName === data.selectedCountry
      );
      const newStateArr = currentCountry.regions.filter(
        (val) => !allExistedArr.includes(val.name)
      );
      newStateArr.forEach((newState) => {
        data.state.push({
          stateName: newState.name,
          isChecked: false,
        });
      });
    } else {
      const currentCountry = state.allAPI.find((c) => {
        return c.countryName === data.selectedCountry;
      });

      if (currentCountry) {
        const currentArr = data.state.map((s) => s.stateName);
        const newStateArr = currentCountry.regions.filter(
          (val) => !currentArr.includes(val.name)
        );
        newStateArr.forEach((newState) => {
          data.state.push({
            stateName: newState.name,
            isChecked: false,
          });
        });
      }
    }
  });

  const shippingMethod = props.shippingMethods.map((m) => {
    const newValue = {};
    newValue.id = m.id;
    newValue.type = m.shipping_method === 'Based On Weight' ? 'bow' : 'flat';
    newValue.shippingName = m.shipping_name;
    newValue.isFreeshipping = m.free_shipping;
    newValue.freeShipFrom = m.free_shipping_on;
    if (m.shipping_method === 'Based On Weight') {
      newValue.firstWeight = m.first_weight;
      newValue.firstMoney = m.first_weight_price;
      newValue.additionalWeight = m.additional_weight;
      newValue.additionalMoney = m.additional_weight_price;
    } else {
      newValue.perOrderRate = m.per_order_rate;
    }
    return newValue;
  });
  state.bowData = [];
  state.flatData = [];

  shippingMethod.forEach((item) => {
    const isBow = item.type === 'bow';
    state.nextShippingId += 1;
    item.index = state.nextShippingId;
    state[`${item.type}Data`].push(item);
    state[`${item.type}Components`].push({
      type: item.type,
      id: isBow ? (state.nextBowId += 1) : (state.nextFlatId += 1),
      name: isBow ? 'based-on-weight' : 'flat-rate',
      index: (state.allComponentId += 1),
    });
  });

  state.nextShippingId = [...state.bowData, ...state.flatData].length;
};

const loadJSON = () => {
  state.isLoading = 'loading';

  shippingAPI.index().then((response) => {
    const allCountryAPI = response.data.API;
    state.allAPI = clone(allCountryAPI);

    if (props.isNew)
      allCountryAPI.unshift({
        countryName: 'Rest of world',
        regions: [],
      });

    const existedRegion = response.data.existedRegion.filter(
      (e) => props.isNew || e.region_type === 'states'
    );

    if (existedRegion) {
      existedRegion.forEach((region) => {
        const regionObj = {
          country: '',
          state: [],
        };
        regionObj.country = JSON.parse(region.country);
        regionObj.state = JSON.parse(region.state);
        state.existedRegion.push(regionObj);
      });

      allCountryAPI.forEach((country, index) => {
        const filteredRegion = state.existedRegion.filter(
          (eR) => eR.country === country.countryName
        );
        const filteredROW = state.existedRegion.filter(
          (rest) => rest.country === 'Rest of world'
        );
        if (props.isNew && filteredROW.length > 0) {
          state.removedCountry.push(country);
          allCountryAPI.splice(index, 1);
        }

        if (filteredRegion.length > 0) {
          const tempArr = [];
          filteredRegion.forEach((fR) => {
            const stateArr = fR.state.map((s) => s.stateName);
            tempArr.push(...stateArr);
          });

          if (!props.isNew && tempArr.length === country.regions.length) {
            state.removedCountry.push(country);
            allCountryAPI.splice(index, 1);
          }
        }
      });

      if (!props.isNew) {
        props.shippingRegion.forEach((cRegion) => {
          const currentCountryIndex = allCountryAPI
            .map((country) => country.countryName)
            .indexOf(cRegion.country);

          if (currentCountryIndex !== -1) {
            state.removedCountry.push(allCountryAPI[currentCountryIndex]);
            allCountryAPI.splice(currentCountryIndex, 1);
          }
        });
        loadData();
      }
    }

    state.countryAPI = allCountryAPI;
    state.isLoading = 'done';
  });
};

const setSelected = (value) => {
  state.selectedCountry = value;
  state.stateAPI = [];
  const countryObj = {
    selectedCountry: '',
    selectedType: 'states',
    zipcode: [],
    state: [],
    existedState: [],
  };

  if (props.isNew && state.selectedCountry === 'Rest of world') {
    countryObj.selectedCountry = 'Rest of world';
  } else {
    const stateAPI = state.countryAPI.find(
      (item) => item.countryName === state.selectedCountry
    );
    if (!stateAPI) return;
    const filteredRegion = state.existedRegion.filter(
      (eR) => eR.country === state.selectedCountry
    );
    if (filteredRegion.length > 0) {
      const tempArr = [];
      filteredRegion.forEach((fR) => {
        const stateArr = fR.state.map((s) => s.stateName);
        tempArr.push(...stateArr);
      });
      tempArr.forEach((e) => {
        countryObj.existedState.push({
          stateName: e,
          isDisabled: true,
        });
      });
      const newStateArr = stateAPI.regions.filter(
        (val) => !tempArr.includes(val.name)
      );
      newStateArr.forEach((newState) => {
        countryObj.state.push({
          stateName: newState.name,
          isChecked: true,
        });
      });
    } else {
      const newStateArr = stateAPI.regions.map((data) => ({
        stateName: data.name,
        isChecked: true,
      }));
      newStateArr.forEach((ns) => {
        countryObj.state.push({
          stateName: ns.stateName,
          isChecked: ns.isChecked,
        });
      });
    }
    countryObj.selectedCountry = value;

    if (!props.isNew) {
      state.countryData.push(countryObj);
      const indicator = state.countryAPI
        .map((item) => item.countryName)
        .indexOf(state.selectedCountry);

      state.removedCountry.push(state.countryAPI[indicator]);
      state.countryAPI.splice(indicator, 1);
    }

    state.selectedCountry = '';
  }
  if (!props.isNew) return;
  state.countryData.push(countryObj);
  const indicator = state.countryAPI
    .map((item) => {
      return item.countryName;
    })
    .indexOf(value);
  state.removedCountry.push(state.countryAPI[indicator]);
  state.countryAPI.splice(indicator, 1);
  const restIndicator = state.countryAPI
    .map((item) => item.countryName)
    .indexOf('Rest of world');
  if (restIndicator !== -1) {
    state.removedCountry.push(state.countryAPI[restIndicator]);
    state.countryAPI.splice(restIndicator, 1);
  }
};

const addShippingMethods = (type) => {
  state[`${type}Components`].push({
    id: type === 'bow' ? (state.nextBowId += 1) : (state.nextFlatId += 1),
    name: type === 'bow' ? 'based-on-weight' : 'flat-rate',
    index: (state.allComponentId += 1),
  });
};

const updateShippingSettings = ({ type, value, index }) => {
  if (!state[`${type}Data`][index]) {
    state.nextShippingId += 1;
    value.index = state.nextShippingId;
  }
  value.type = type;
  state[`${type}Data`][index] = { ...state[`${type}Data`][index], ...value };
};

const deleteShippingSetttings = ({ type, value, index }) => {
  state[`${type}Data`].splice(index, 1, null);
  state[`${type}Components`].splice(index, 1, null);
  state.deletedMethod.push(value.id);
};

const redirectBack = () => {
  router.visit('/shipping/settings');
};

const saveShippingSetting = () => {
  const filteredShippingMethod = [...state.bowData, ...state.flatData].filter(
    (item) => item
  );
  const sortedShippingMethod = filteredShippingMethod.sort((a, b) =>
    a.index > b.index ? 1 : -1
  );

  const filteredCountryData = state.countryData.map((data) => {
    return {
      id: data.id,
      selectedCountry: data.selectedCountry,
      selectedType: data.selectedType,
      state: data.state.filter((e) => e.isChecked),
      zipcode: data.zipcode,
    };
  });
  const isSubRegionNotSelected = filteredCountryData
    .filter((country) => country.selectedType === 'states')
    .some((data) => data.state.length === 0);
  const isZipcodeNotSelected = filteredCountryData
    .filter((country) => country.selectedType === 'zipcodes')
    .some((data) => data.zipcode.length === 0);
  const isRestOfWorld = filteredCountryData
    .map((item) => item.selectedCountry)
    .includes('Rest of world');
  if (state.zoneName === '') {
    $toast.warning('Error', 'Please enter zone name');
    zoneNameFieldInput.value.focus();
  } else if (sortedShippingMethod.length === 0) {
    $toast.warning('Error', 'Please select shipping method.');
  } else if (state.countryData.length === 0) {
    $toast.warning('Error', 'Please select countries');
  } else if (
    (isSubRegionNotSelected || isZipcodeNotSelected) &&
    !isRestOfWorld
  ) {
    $toast.warning('Error', 'Please select zipcode or state');
  } else {
    if (props.onboarding) {
      localStorage.setItem(
        'shippingSettings',
        JSON.stringify({
          link: '/shipping/setting/save',
          shippingMethodData: sortedShippingMethod,
          zoneName: state.zoneName,
          countryData: filteredCountryData,
        })
      );
      router.visit(props.submitUrl, { replace: true });
      return;
    }

    const data = props.isNew
      ? {
          shippingMethodData: sortedShippingMethod,
          zoneName: state.zoneName,
          countryData: filteredCountryData,
        }
      : {
          type: 'edit',
          shippingMethodData: sortedShippingMethod,
          zoneName: state.zoneName,
          countryData: filteredCountryData,
          shippingZone: props.shippingZone,
          deletedCountry: state.deletedCountry,
          deletedMethod: state.deletedMethod,
        };

    shippingAPI[props.isNew ? 'create' : 'update'](data)
      .then((response) => {
        $toast.success(
          'Success',
          `Shipping setting ${props.isNew ? 'save' : 'update'} successfully`
        );
        router.visit('/shipping/settings');
      })
      .catch((error) => {
        console.log(error);
      });
  }
};

onMounted(() => {
  eventBus.$on('self-delivery-submit', () => {
    saveShippingSetting();
  });
  loadJSON();
  if (props.onboarding) {
    const shippingSettings = JSON.parse(localStorage.shippingSettings ?? '{}');

    if (shippingSettings.link === '/shipping/setting/save') {
      state.countryData = shippingSettings.countryData ?? [];
      state.zoneName = shippingSettings.zoneName ?? '';
    }
    if (
      !shippingSettings.shippingMethodData ||
      shippingSettings.shippingMethodData.length === 0
    )
      return;
    const sortedData = shippingSettings.shippingMethodData.sort((a, b) =>
      a.index > b.index ? 1 : -1
    );
    state.allComponentId = sortedData.length;
    state.nextShippingId = sortedData.length;
    sortedData.forEach((e) => {
      state[`${e.type}Data`].push(e);
      addShippingMethods(e.type);
    });
  }
});
</script>
<style scoped>
:deep(.zone) {
  padding-left: 0px !important;
  padding-right: 0px !important;
}

@media screen and (max-width: 415px) {
  .shipping-state-form-group {
    width: 50%;
  }
}
</style>

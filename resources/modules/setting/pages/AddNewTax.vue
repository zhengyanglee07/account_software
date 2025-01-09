<template>
  <BasePageLayout
    :page-name="pageName"
    back-to="/tax/settings"
    is-setting
    @keydown="changedModify()"
  >
    <!-- <BaseButton type="link" href="#" @click="checkedUnsaved">
      <i
        class="fa fa-chevron-left back-to-previous__back-icon"
        aria-hidden="true"
      />
      Back To Tax Setting
    </BaseButton> -->
    <BaseSettingLayout title="Tax regions">
      <template #description>
        <p>
          Charge sales taxes based on the country or country regions. Check with
          tax expert to understand your tax obligations.
        </p>
      </template>
      <template #content>
        <BaseFormGroup
          v-if="type == 'new'"
          label="Select Country"
        >
          <BaseFormCountrySelect
            v-model="selectedCountry"
            :country="selectedCountry"
            :black-list="blackList"
          />
          <!-- <template #label-row-end>
              <BaseButton
                v-if="isSelected"
                type="link"
                @click="resetSelect"
              >
                Edit
              </BaseButton>
            </template> -->
        </BaseFormGroup>

        <BaseFormGroup
          v-if="isSelected"
          label="Tax Name"
          col="6"
        >
          <BaseFormInput
            id="tax-name"
            v-model="taxName"
            type="text"
            placeholder="E.g GST, VAT, Sales tax"
          />
        </BaseFormGroup>
        <BaseFormGroup
          v-if="isSelected"
          label="Country Tax"
          col="6"
        >
          <BaseFormInput
            id="tax-country-tax"
            v-model="countryTax"
            step="0.01"
            type="number"
            min="0"
            placeholder="0"
          >
            <template #append>
              %
            </template>
          </BaseFormInput>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout
      title="Tax of sub-regions (Optional)"
      is-datatable-only-in-content
    >
      <template #description>
        <p>
          Customize region-based tax rates for specific product collections or
          shipping rates when shipping to designated areas.
        </p>
      </template>
      <template #content>
        <BaseDatatable
          id="tax-sub-region-datatable"
          title="sub region"
          no-action
          no-search
          no-sorting
          :table-headers="tableHeaders"
          :table-datas="subRegionSelected.map((m, index) => ({ ...m, index }))"
        >
          <template #action-button>
            <BaseButton
              v-if="isSelected"
              type="secondary"
              @click="manageSubRegion"
            >
              Manage sub-regions
            </BaseButton>
          </template>
          <template #cell-stateTaxName="{ row: { index } }">
            <BaseFormGroup>
              <BaseFormInput
                id="tax-state-name"
                v-model="subRegionSelected[index].stateTaxName"
                placeholder="Tax name"
              />
            </BaseFormGroup>
          </template>
          <template #cell-stateTaxRate="{ row: { index } }">
            <BaseFormGroup>
              <BaseFormInput
                id="tax-state-rate"
                v-model="subRegionSelected[index].stateTaxRate"
                type="number"
                step="0.01"
                min="0"
                placeholder="0"
              >
                <template #append>
                  %
                </template>
              </BaseFormInput>
            </BaseFormGroup>
          </template>
          <template #cell-taxCalculation="{ row: { index } }">
            <BaseFormGroup class="tax-region-calculation">
              <BaseFormSelect
                id="tax-calculation"
                v-model="subRegionSelected[index].taxCalculation"
                :options="taxCalculationOptions"
                label-key="label"
                value-key="value"
              >
                <option
                  v-for="obj in arrayOfObjects"
                  :key="obj.id"
                  :value="obj.value"
                >
                  {{ obj.label }}
                </option>
              </BaseFormSelect>
            </BaseFormGroup>
          </template>

          <template #cell-totalTaxRate="{ row, index }">
            <p class="tax-rate">
              {{ countTotalTax(row, index) }} %
            </p>
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>
    <template #footer>
      <BaseButton
        type="link"
        href="/tax/settings"
        class="me-6"
      >
        Cancel
      </BaseButton>
      <BaseButton @click="saveTaxCountry">
        Save
      </BaseButton>
    </template>
  </BasePageLayout>

  <BaseModal
    title="Manage sub-regions to set tax"
    modal-id="taxModal"
  >
    <BaseFormGroup label="Form Label">
      <BaseFormRadio
        id="tax-no-sub-region"
        v-model="hasSubRegion"
        value="false"
      >
        No sub-regions
      </BaseFormRadio>
      <BaseFormRadio
        id="tax-select-sub-region"
        v-model="hasSubRegion"
        value="true"
      >
        Select sub-regions
      </BaseFormRadio>
    </BaseFormGroup>

    <BaseFormGroup
      v-for="(data, index) in hasSubRegion === 'true' ? subRegion : []"
      :key="index"
      col="3"
    >
      <BaseFormCheckBox
        :id="`tax-sub-region-${index}`"
        v-model="selectedSubRegion"
        :value="data"
      >
        {{ data.stateName }}
      </BaseFormCheckBox>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="saveSelectRegion">
        Save
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    title="You have unsaved changes on this page"
    modal-id="unSavedWarningModal"
  >
    If you leave this page, all unsaved changes will be lost. Are you sure you
    want to leave this page?
    <template #footer>
      <BaseButton
        type="danger"
        @click="redirectBack"
      >
        Leave page
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';
import taxAPI from '@setting/api/taxAPI.js';
import clone from 'clone';

export default {
  name: 'AddNewTax',

  props: ['type', 'taxCountry', 'taxCountryRegion', 'otherTaxCountry'],

  data() {
    return {
      taxName: '',
      countryTax: 0,
      isSelected: false,
      hasSubRegion: 'false',
      subRegion: [],
      subRegionData: [],
      selectedSubRegion: [],
      subRegionSelected: [],
      tableHeaders: [
        { name: 'Sub-regions', key: 'stateName' },
        { name: 'Tax Name', key: 'stateTaxName', custom: true },
        { name: 'Tax rate', key: 'stateTaxRate', custom: true },
        { name: 'Calculation', key: 'taxCalculation', custom: true },
        { name: 'Total tax rate', key: 'totalTaxRate', custom: true },
      ],
      countryAPI: [],
      selectedCountry: '',
      isLoading: '',
      selectedState: [],
      stateAPI: [],
      existedRegion: [],
      removedCountry: [],
      isModify: false,
      taxModal: null,
      blackList: [],
    };
  },

  computed: {
    pageName() {
      if (this.type === 'new') return 'Add Tax Rate';
      return `Edit ${this.taxCountry.country_name} Taxes`;
    },
    // countTotalTax(data,value){
    //     console.log(data,value);
    // },
    countries() {
      if (this.isLoading === 'done') {
        return [...new Set(this.countryAPI.map((c) => c.countryName))];
      }
      return [];
    },
    taxCalculationOptions() {
      return [
        { label: `Added to ${this.countryTax} % federal tax`, value: 'AFT' },
        { label: `Instead of ${this.countryTax} % federal tax`, value: 'IFT' },
        {
          label: `Compound on top of ${this.countryTax} % federal tax`,
          value: 'CFT',
        },
      ];
    },
  },

  watch: {
    selectedCountry() {
      this.stateAPI = [];
      this.isSelected = true;
      if (this.type === 'edit') return;
      const stateAPI = this.countryAPI.find((item) => {
        return item.countryName === this.selectedCountry;
      });
      const subRegion = stateAPI.regions.map((data) => {
        return {
          stateName: data.name,
        };
      });
      subRegion.forEach((ns) => {
        this.subRegion.push({
          id: '',
          stateName: ns.stateName,
          stateTaxName: '',
          stateTaxRate: 0,
          taxCalculation: 'AFT',
          totalTax: 0,
        });
      });
    },
    // hasSubRegion(newValue,oldValue){
    //     console.log(newValue,oldValue);
    //     if(this.newvalue == true){
    //         this.subRegionSelected = {}
    //     }
    // }
  },

  mounted() {
    this.loadJSON();
  },

  methods: {
    closeModal(id) {
      document.getElementById(id)?.click();
    },
    deleteTax() {
      taxAPI
        .delete(this.taxCountry)
        .then((response) => {
          this.closeModal('deleteTaxRegion-close-button');
          this.$inertia.visit('/tax/settings');
        })
        .catch((error) => {});
    },
    countTotalTax(data, index) {
      const value = data;
      if (data.taxCalculation === 'AFT') {
        value.totalTax =
          parseFloat(data.stateTaxRate) + parseFloat(this.countryTax);
      } else if (data.taxCalculation === 'IFT') {
        value.totalTax = parseFloat(data.stateTaxRate);
      } else if (data.taxCalculation === 'CFT') {
        // data.totalTax = parseFloat(this.countryTax) + (100*(parseFloat(this.countryTax)/100)*( parseFloat(data.stateTaxRate)/100));
        value.totalTax =
          ((100 + parseFloat(this.countryTax)) *
            (100 + parseFloat(data.stateTaxRate))) /
            100 -
          100;
      }
      const total = data.totalTax.toFixed(2) || 0;
      if (this.subRegionSelected[index])
        this.subRegionSelected[index].totalTax = total;
      return total;
    },

    saveSelectRegion() {
      if (this.hasSubRegion === 'false') {
        this.subRegionSelected = [];
      } else {
        this.subRegionSelected = this.selectedSubRegion;
      }
      this.taxModal.hide();
    },

    manageSubRegion() {
      if (this.subRegionSelected.length > 0) {
        this.selectedSubRegion = this.subRegionSelected;
      } else if (this.type === 'new') {
        this.selectedSubRegion = this.subRegion;
      } else {
        this.hasSubRegion = 'false';
        this.selectedSubRegion = [];
      }
      this.taxModal = new Modal(document.getElementById('taxModal'));
      this.taxModal.show();
    },

    resetSelect() {
      this.selectedCountry = '';
      this.isSelected = false;
      this.subRegion = [];
      this.countryData = {};
    },

    removeCountry(value, index) {
      const recoverCountry = this.removedCountry.find((item) => {
        return item.countryName === value.selectedCountry;
      });

      const idx = this.removedCountry
        .map((data) => data.countryName)
        .indexOf(value.selectedCountry);
      // console.log(recoverCountry,idx);
      this.countryAPI.push(recoverCountry);
      this.removedCountry.splice(idx, 1);
      this.countryData.splice(index, 1);
    },

    loadJSON() {
      this.isLoading = 'loading';
      taxAPI.index().then((response) => {
        let allCountryAPI = clone(response.data.API);
        const { existedRegion } = response.data;

        if (existedRegion?.length > 0 && this.type === 'new') {
          const existed = allCountryAPI.filter((country) =>
            existedRegion.every((eR) => eR.country_name === country.countryName)
          );
          this.blackList = existed?.map((m) => m.countryShortCode);
          allCountryAPI = allCountryAPI.filter(
            (e) => !this.blackList.includes(e.countryShortCode)
          );
        }
        this.countryAPI = allCountryAPI;
        this.isLoading = 'done';
        this.loadData();
      });
    },

    saveTaxCountry() {
      if (this.selectedCountry !== '') {
        taxAPI
          .updateCountry({
            type: this.type,
            taxName: this.taxName,
            countryTax: this.countryTax,
            selectedCountry: this.selectedCountry,
            selectedSubRegion: this.subRegionSelected,
            taxCountryRegion: this.taxCountryRegion,
          })
          .then((response) => {
            this.$toast.success('Success', 'Tax setting save successfully');
            this.$inertia.visit('/tax/settings');
          });
      } else {
        this.$toast.error('Error', 'Please select a country to set tax ');
      }
    },
    checkCondition() {},

    redirectBack() {
      this.unSavedWarningModal.hide();
      this.$inertia.visit('/tax/settings');
    },

    checkedUnsaved() {
      if (this.isModify) {
        this.unSavedWarningModal = new Modal(
          document.getElementById('unSavedWarningModal')
        );
        this.unSavedWarningModal.show();
      } else {
        this.redirectBack();
      }
    },

    changedModify() {
      // if(this.type=='edit'){
      this.isModify = true;
      // }
    },

    loadData() {
      if (this.type === 'edit') {
        this.isSelected = true;
        this.selectedCountry = this.taxCountry.country_name;
        this.taxName = this.taxCountry.tax_name;
        this.countryTax = this.taxCountry.country_tax;
        this.subRegionSelected = this.taxCountryRegion.map((data) => {
          return {
            id: data.id,
            stateName: data.sub_region_name,
            stateTaxName: data.tax_name,
            stateTaxRate: data.tax_rate,
            taxCalculation: data.tax_calculation,
            totalTax: data.total_tax,
          };
        });
        this.selectedSubRegion = this.subRegionSelected;

        const stateAPI = this.countryAPI.find((item) => {
          return item.countryName === this.selectedCountry;
        });

        const subRegion = stateAPI.regions.map((data) => {
          return {
            stateName: data.name,
          };
        });

        stateAPI.regions.forEach((region) => {
          const selectedRegion = this.subRegionSelected.find(
            (e) => region.name === e.stateName
          );
          if (selectedRegion) this.subRegion.push(selectedRegion);
          else
            this.subRegion.push({
              id: '',
              stateName: region.name,
              stateTaxName: '',
              stateTaxRate: 0,
              taxCalculation: 'AFT',
              totalTax: 0,
            });
        });
        this.hasSubRegion = (this.subRegionSelected.length > 0).toString();
      }
    },
  },
};
</script>

<style lang="scss">
@media screen and (max-width: 415px) {
  #tax-sub-region-datatable {
    .form-group {
      width: 120px;
    }
    td {
      padding-top: 0;
      padding-bottom: 0;
    }
  }
  .tax-region-calculation {
    width: 200px !important;
  }
  .tax-rate {
    width: 60px;
  }
}
</style>

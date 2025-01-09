<template>
  <BaseModal
    modal-id="browseCountryModal"
    title="Edit countries"
  >
    <BaseDatatable
      v-if="filteredItems.length > 0"
      title="countrie"
      no-action
      :table-headers="countryTableHeaders"
      :table-datas="filteredItems"
    >
      <template #cell-country_checkbox="{ row: { disabled, checked, index } }">
        <BaseFormGroup>
          <BaseFormCheckBox
            :id="`promotion-country-select-${index}`"
            :value="true"
            :model-value="!!checked"
            :disabled="disabled"
            @click="addRegion(checked, index)"
          />
        </BaseFormGroup>
      </template>
      <template #cell-zone_name="{ row: { zone_name } }">
        {{ zone_name }}
      </template>
    </BaseDatatable>

    <BaseCard
      v-if="availableRegion.length === 0"
      has-header
      title="No shipping zone yet..."
    >
      <BaseButton
        type="link"
        href="/shipping/settings"
      >
        Click here to Create Shipping Zone
      </BaseButton>
    </BaseCard>

    <template #footer>
      <BaseButton
        data-bs-dismiss="modal"
        @click="saveSelectedCountry"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';
import { mapMutations } from 'vuex';
import promotionAPI from '@promotion/api/promotionAPI.js';

export default {
  name: 'BrowseCountryModal',
  mixins: [PromotionCentraliseMixins],
  data() {
    return {
      countryTableHeaders: [
        { name: '', key: 'country_checkbox', custom: true },
        { name: 'Country', key: 'zone_name' },
      ],
    };
  },
  computed: {
    filteredItems() {
      return this.availableRegion.map((m, index) => ({ ...m, index }));
    },
  },
  methods: {
    ...mapMutations('promotions', [
      'updateAvailableRegion',
      'updateShippingZone',
      'deleteErrorMessages',
    ]),
    closeModal() {
      document.getElementById('browseCountryModal-close-button')?.click();
    },
    loadShippingRegion() {
      promotionAPI.getShippingRegion().then((response) => {
        response.data.forEach((item) => {
          const existedZone = this.availableRegion.find(
            (zone) => zone.id === item.id
          );
          if (!existedZone) {
            this.updateShippingZone({
              id: item.id,
              zone_name: item.zone_name,
              checked: false,
              disabled: false,
            });
          }
          if (this.setting.selectedCountries.length > 0) {
            this.setting.selectedCountries.forEach(({ id }) => {
              const indexOf = this.availableRegion
                .map((data) => data.id)
                .indexOf(id);
              // console.log(indexOf);
              if (indexOf !== -1) {
                this.updateAvailableRegion({
                  value: true,
                  index: indexOf,
                  key: 'checked',
                });
              }
            });
          }
        });
      });
      // if(this.setting.selectedCountries.length > 0 ){
      //     this.setting.selectedCountries.forEach(item =>{
      //         let indexOf =  this.availableRegion.map(country => country.country).indexOf(item.country);
      //         this.$store.commit('promotions/updateAvailableRegion',{value : true, key: 'checked',index: indexOf});
      //         // loadRegion.checked = true ;
      //     })
      // }
    },
    addRegion(checked, index) {
      this.updateAvailableRegion({
        value: !checked,
        key: 'checked',
        index,
      });
    },
    saveSelectedCountry() {
      const checkedRegion = this.availableRegion.filter((item) => item.checked);
      console.log(checkedRegion, 'checkedRegion');
      this.updateSetting('selectedCountries', [...checkedRegion]);
      if (this.hasError('selectedCountries')) {
        this.deleteErrorMessages('selectedCountries');
      }
      this.closeModal();
    },
  },
  mounted() {
    this.loadShippingRegion();
  },
};
</script>

<template>
  <BaseCard has-header title="Zone">
    <BaseFormGroup>
      <BaseFormRadio
        v-for="countryType in ['all', 'specific']"
        :id="`promotion-country-type-${countryType}`"
        :key="countryType"
        :value="countryType"
        :model-value="setting.countryType"
        @input="(val) => updateSetting('countryType', val)"
      >
        {{
          countryType === 'all'
            ? 'All shipping zones'
            : 'Selected shipping zones'
        }}
      </BaseFormRadio>
    </BaseFormGroup>

    <div v-if="setting.countryType == 'specific'">
      <BaseFormGroup
        :error-message="
          hasError('selectedCountries') ? errors['selectedCountries'][0] : ''
        "
      >
        <BaseFormInput
          id="promotion-country-search-input"
          v-model="searchInput"
          type="text"
          data-target="#browseCountryModal"
          placeholder="Search shipping zones"
          @keyup="setFocus"
        >
          <template #prepend>
            <i class="fas fa-search gray_icon" />
          </template>
          <template #append>
            <BaseButton size="md" type="link" @click="setFocus">
              Browse
            </BaseButton>
          </template>
        </BaseFormInput>
      </BaseFormGroup>

      <BaseDatatable
        v-if="setting.selectedCountries.length > 0"
        no-header
        no-search
        no-action
        :table-headers="countryTableHeaders"
        :table-datas="
          setting.selectedCountries.map((m, index) => ({ ...m, index }))
        "
      >
        <template #cell-zone_name="{ row: { zone_name } }">
          {{ zone_name }}
        </template>
        <template #cell-remove="{ row: { index } }">
          <BaseButton
            type="close"
            size="sm"
            aria-label="Close"
            @click="removeRegion(index)"
          />
        </template>
      </BaseDatatable>
    </div>
  </BaseCard>
  <PromotionCountryModal />
</template>

<script>
import { mapMutations } from 'vuex';
import PromotionCountryModal from '@promotion/components/PromotionCountryModal.vue';
import PromotionCentraliseMixins from '@promotion/mixins/PromotionCentraliseMixins.js';

export default {
  name: 'CountrySelectCard',
  components: {
    PromotionCountryModal,
  },
  mixins: [PromotionCentraliseMixins],
  data() {
    return {
      searchInput: '',
      countryTableHeaders: [
        { name: 'Country', key: 'zone_name', custom: true },
        { name: 'Action', key: 'remove', custom: true },
      ],
    };
  },
  methods: {
    ...mapMutations('promotions', [
      'deleteSelectedCountries',
      'updateAvailableRegion',
    ]),
    setFocus() {
      this.triggerModal('browseCountryModal');
    },
    removeRegion(index) {
      this.deleteSelectedCountries(index);
      this.updateAvailableRegion({
        value: false,
        key: 'checked',
        index,
      });
    },
  },
};
</script>

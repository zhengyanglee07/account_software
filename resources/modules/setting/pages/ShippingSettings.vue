<template>
  <BasePageLayout
    page-name="Shipping Setting"
    back-to="/settings/all"
    is-setting
  >
    <template #action-button>
      <BaseButton
        href="/shipping/settings/new"
        has-add-icon
      >
        Add Shipping Zone
      </BaseButton>
    </template>
    <BaseSettingLayout
      title="Shipping Zones and Rates"
      is-datatable-only-in-content
    >
      <template #description>
        <p>
          Choose the shipping zone and delivery charge for your stores shipping
          at checkout.
        </p>
      </template>
      <template #content>
        <BaseDatatable
          title="shipping zone"
          :table-headers="tableHeaders"
          :table-datas="shippingZoneAll"
          no-sorting
          no-action
          no-header
        >
          <template #cell-zoneName="{ row: { isFirstMethods, zoneName } }">
            {{ isFirstMethods ? zoneName : '' }}
          </template>

          <template
            #cell-action="{ row: { isFirstMethods, zoneName, editLink, id } }"
          >
            <BaseButton
              v-if="isFirstMethods"
              :id="`shipping-actions-${zoneName}`"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              type="light"
            >
              Actions
            </BaseButton>

            <BaseDropdown :id="`shipping-actions-list-${zoneName}`">
              <BaseDropdownOption
                text="Edit"
                :link="editLink"
              />
              <BaseDropdownOption
                text="Delete"
                @click="deleteShippingZone(id)"
              />
            </BaseDropdown>
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>

    <ShippingTypeSettings
      type="pickup"
      :time-zone="timeZone"
      :location="location"
    />

    <ShippingTypeSettings
      type="delivery"
      :time-zone="timeZone"
    />
  </BasePageLayout>
</template>

<script>
import ShippingTypeSettings from '@setting/components/ShippingTypeSettings.vue';
import { mapActions } from 'vuex';
import shippingAPI from '@setting/api/shippingAPI.js';
import PickupAndDeliveryMixin from '@setting/mixins/PickupAndDeliveryMixin.js';

export default {
  name: 'ShippingSettings',

  components: {
    ShippingTypeSettings,
  },
  mixins: [PickupAndDeliveryMixin],

  props: ['shippingZone', 'timeZone', 'location'],

  data() {
    return {
      tableHeaders: [
        { name: 'Zone name', key: 'zoneName', custom: true },
        { name: 'Shipping methods', key: 'shippingMethod' },
        { name: 'Calculation', key: 'shippingCalculation' },
        { name: 'Action', key: 'action', custom: true },
      ],
      shippingZoneAll: [],
      tempZone: [],
      deleteModal: null,
    };
  },
  mounted() {
    this.initializePickupDelivery();

    this.shippingZoneAll = this.shippingZone
      .map((m) => {
        return m.shipping_method_details
          .map((mm, index) => ({
            id: m.id,
            zoneName: m.zone_name,
            shippingMethod: mm.shipping_name,
            shippingCalculation: mm.pivot.shipping_method,
            editLink: `/shipping/settings/edit/${m.urlId}`,
            isFirstMethods: index === 0,
          }))
          .flat();
      })
      .flat();
  },
  methods: {
    ...mapActions('settings', ['initializePickupDelivery']),
    deleteShippingZone(id) {
      shippingAPI.delete(id).then((response) => {
        this.$toast.success('Success', 'Shipping zone deleted');
        this.$inertia.visit('/shipping/settings');
      });
    },
  },
};
</script>

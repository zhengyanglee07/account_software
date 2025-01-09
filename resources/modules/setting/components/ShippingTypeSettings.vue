<template>
  <BaseSettingLayout
    :title="settingsData.name"
    :is-onboarding="onboarding"
  >
    <template
      v-if="onboarding"
      #description
    >
      <p>
        {{ settingsData.onboardingDesc }}
      </p>
    </template>
    <template
      v-else
      #description
    >
      <p>{{ settingsData.description1 }}</p>
      <p>
        {{ settingsData.description2 }}
        <Link
          href="/location/settings"
          target="_blank"
        >
          here
        </Link>
      </p>
    </template>
    <template #content>
      <BaseFormGroup :label="`Manage ${settingsData.name}`">
        <BaseFormSwitch
          id="shipping-delivery-hour"
          v-model="isEnable"
        >
          {{ settingsData.isEnableCheckboxLabel }}
        </BaseFormSwitch>
      </BaseFormGroup>
      <TimeslotPicker
        v-if="isSettingsEnabled"
        :time-zone="timeZone"
        :settings="settings"
        :onboarding="onboarding"
        :type="type"
      />
      <BaseModal
        title="Location Information Incomplated"
        modal-id="location-warning"
      >
        Please fill in pickup location in location setting before setting up
        store pickup time slot
        <template #footer>
          <BaseButton @click="redirectTo">
            Redirect
          </BaseButton>
        </template>
      </BaseModal>
    </template>

    <template
      v-if="!onboarding"
      #footer
    >
      <BaseButton
        :disabled="isSaving"
        @click="updateShippingSetting(type, onboarding)"
      >
        Save
      </BaseButton>
    </template>
  </BaseSettingLayout>
</template>

<script>
import PickupAndDeliveryMixin from '@setting/mixins/PickupAndDeliveryMixin.js';
import TimeslotPicker from '@setting/components/TimeslotPicker.vue';
import { router } from '@inertiajs/vue3';
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

export default {
  components: {
    TimeslotPicker,
  },
  mixins: [PickupAndDeliveryMixin],
  props: {
    location: {
      type: Object,
      default: () => {},
    },
    onboarding: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isEnable: false,
    };
  },
  computed: {
    settings() {
      return this[this.type];
    },
    isSettingsEnabled() {
      return this.type === 'delivery'
        ? this.settings.deliveryHourType === 'custom'
        : this.settings.isEnableStorePickup;
    },
    settingsData() {
      const data = {
        delivery: {
          name: 'Delivery Hours',
          isEnableCheckboxLabel:
            'Enable delivery time slots in mini store and online store',
          onboardingDesc:
            'Enable daily delivery time slots and preparation time for your delivery',
          description1: 'Setting up your delivery time slot for your business.',
          description2:
            'The courier services will pickup the items you want to deliver to customers',
        },
        pickup: {
          name: 'Store Pickup',
          isEnableCheckboxLabel:
            'Enable Store Pickup In Mini Store and Online Store',
          onboardingDesc:
            'Allow your customer to pick up his order at your physical store',
          description1: 'Setting up your pickup time slot for your business.',
          description2:
            'Your customers will pickup their orders at your physical store',
        },
      };
      return data[this.type];
    },
  },
  watch: {
    settings(val) {
      if (this.type === 'delivery')
        this.isEnable = val.deliveryHourType === 'custom';
      else this.isEnable = !!val.isEnableStorePickup;
    },
    isEnable(val) {
      this.changeEnableState(val);
    },
    isLoading(newVal) {
      this.initializeDeliveryHour(!newVal);
    },
  },
  mounted() {
    eventBus.$on('scheduled-delivery-submit', () => {
      this.updateShippingSetting('delivery', true);
    });
    eventBus.$on('pickup-submit', () => {
      this.updateShippingSetting('pickup', true);
    });
  },
  methods: {
    redirectTo() {
      this.locationWarning.hide();
      router.visit('/location/settings');
    },
    initializeDeliveryHour(isValid) {
      if (isValid)
        ['delivery', 'pickup'].forEach((type) => this.updateDeliveryHour(type));
    },
    changeEnableState(isChecked) {
      this.initializeDeliveryHour(isChecked);
      if (this.type === 'delivery')
        this.instantUpdateSetting(
          this.type,
          'deliveryHourType',
          isChecked ? 'custom' : 'default'
        );
      else {
        this.instantUpdateSetting(this.type, 'isEnableStorePickup', isChecked);
        if (!this.onboarding && !this.location) {
          this.locationWarning = new Modal(
            document.getElementById('location-warning')
          );
          this.locationWarning.show();
          this.instantUpdateSetting('pickup', 'isEnableStorePickup', false);
          return;
        }
        this.instantUpdateSetting('pickup', 'isEnableStorePickup', isChecked);
      }

      if (this.isSettingsEnabled && this.settings.deliveryHours.length === 0) {
        this.instantUpdateSetting(
          this.type,
          'deliveryHours',
          this.getScheduleHour()
        );
        this.updateDeliveryHour(this.type);
      }
    },
  },
};
</script>

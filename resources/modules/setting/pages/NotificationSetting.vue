<template>
  <BasePageLayout
    page-name="Notification Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Order Email Notification">
      <template #description>
        <p>Determine whether to send email when perform certain action.</p>
      </template>
      <template #content>
        <BaseFormGroup label="Email customer when:">
          <BaseFormSwitch
            id="notification-is-fulfill-an-order"
            v-model="settings.isFulfillNotifiable"
          >
            Fulfill an order
          </BaseFormSwitch>
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton
          :disabled="isLoading"
          @click="save"
        >
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Notification Email Address">
      <template #description>
        <p>
          Determine which email address will receive notification email of a
          specific event.
        </p>
      </template>
      <template #content>
        <label class="mb-2">
          <strong>New order notification email:</strong>
        </label>
        <p>
          <BaseBadge
            v-for="(email, index) in notificationEmailList"
            :key="index"
            :text="email"
          />
          <BaseButton
            type="link"
            class="ms-2"
            data-bs-toggle="modal"
            data-bs-target="#order-notification-email-modal"
          >
            Edit
          </BaseButton>
        </p>
        <OrderNotificationEmailModal
          :notification-email="notificationEmail"
          :save="saveNotificationEmail"
          @email="setNotificationEmail"
        />
      </template>
      <template #footer>
        <BaseButton
          :disabled="isLoading"
          @click="saveNotificationEmail = !saveNotificationEmail"
        >
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import OrderNotificationEmailModal from '@setting/components/OrderNotificationEmailModal.vue';

export default {
  name: 'NotificationSetting',
  components: { OrderNotificationEmailModal },
  props: {
    notifiableSetting: { type: Object, default: () => {} },
  },
  data() {
    return {
      nofiticationEmail: null,
      saveNotificationEmail: false,
      isLoading: false,
      settings: {
        isFulfillNotifiable:
          !!this.notifiableSetting.is_fulfill_order_notifiable,
      },
    };
  },
  computed: {
    notificationEmail() {
      return (
        this.nofiticationEmail || this.notifiableSetting.notification_email
      );
    },
    notificationEmailList() {
      return this.notificationEmail.split(',');
    },
  },
  methods: {
    setNotificationEmail(email) {
      this.nofiticationEmail = email;
    },
    save() {
      this.isLoading = true;
      axios
        .post('/settings/notification/save', {
          setting: this.settings,
        })
        .then((response) => {
          this.$toast.success('Success', 'Successfully Saved');
        })
        .catch((error) => {
          this.$toast.error('Error', 'Save Failed. Please Try Again');
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>

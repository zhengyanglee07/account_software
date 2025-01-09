<template>
  <BasePageLayout
    page-name="Social Proof Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Loop Notification">
      <template #description>
        Determine whether show the notifications that has been shown before.
      </template>

      <template #content>
        <BaseFormGroup label="Loop">
          <BaseFormSwitch v-model="editData.is_loop_notification" />
        </BaseFormGroup>
      </template>

      <template #button />
    </BaseSettingLayout>

    <div class="setting-page__dividor" />
    <BaseSettingLayout title="Random Notification">
      <template #description>
        The notifications will shown in random order when enabled. The
        notifications will be shown follow the latest creation datetime of the
        event by default.
      </template>

      <template #content>
        <BaseFormGroup label="Random">
          <BaseFormSwitch v-model="editData.is_random_notification" />
        </BaseFormGroup>
      </template>

      <template #button />
    </BaseSettingLayout>

    <div class="setting-page__dividor" />
    <BaseSettingLayout title="Display Time">
      <template #description>
        Display each notification for
      </template>

      <template #content>
        <BaseFormGroup label="">
          <BaseFormInput
            v-model="editData.display_time"
            type="number"
            min="1"
            max="30"
            @input="
              if (editData.display_time.length > maxTimeLength)
                editData.display_time = editData.display_time.slice(
                  0,
                  maxTimeLength
                );
            "
          >
            <template #append>
              Seconds
            </template>
          </BaseFormInput>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <div class="setting-page__dividor" />
    <BaseSettingLayout title="Delay Time">
      <template #description>
        Delay between notifications for
      </template>

      <template #content>
        <BaseFormGroup label="">
          <BaseFormInput
            v-model="editData.delay_time"
            type="number"
            min="1"
            max="10"
            @input="
              if (editData.delay_time.length > maxTimeLength)
                editData.delay_time = editData.delay_time.slice(
                  0,
                  maxTimeLength
                );
            "
          >
            <template #append>
              Seconds
            </template>
          </BaseFormInput>
        </BaseFormGroup>
      </template>
    </BaseSettingLayout>

    <div class="setting-page__dividor" />

    <!-- <setting-layout title="Save Settings" >

    <template #content> -->
    <div
      class="setting-page__footer"
      style="align-self: end"
    >
      <BaseButton
        v-show="settings !== null"
        @click="save"
      >
        <span style="color: #fff; font-size: 12px">Save</span>
      </BaseButton>
      <BaseButton
        v-show="settings === null"
        @click="save"
      >
        <span style="color: #fff; font-size: 12px">Confirm</span>
      </BaseButton>
    </div>
    <!-- </template>
  </setting-layout> -->
  </BasePageLayout>
</template>

<script>
export default {
  name: 'SocialProofSettings',

  props: {
    settings: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      editData: {},
      maxTimeLength: 2,
    };
  },

  created() {
    this.editData = this.settings;
    if (this.settings === null) {
      this.editData = {
        is_loop_notification: true,
        is_random_notification: false,
        display_time: 10,
        delay_time: 5,
      };
    }
  },

  methods: {
    save() {
      switch (true) {
        case this.editData.display_time > 30:
          this.editData.display_time = 30;
          break;
        case this.editData.display_time < 1:
          this.editData.display_time = 1;
          break;
        default:
          break;
      }

      switch (true) {
        case this.editData.delay_time > 10:
          this.editData.delay_time = 10;
          break;
        case this.editData.delay_time < 1:
          this.editData.delay_time = 1;
          break;
        default:
          break;
      }

      axios
        .post('/social-proof-save-global-setting/save-edit', {
          isLoopNotification: this.editData.is_loop_notification,
          isRandomNotification: this.editData.is_random_notification,
          displayTime: this.editData.display_time,
          delayTime: this.editData.delay_time,
        })
        .then((response) => {
          this.$toast.success('Success', 'Successfully Saved');
        })
        .catch((error) => {
          this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },
  },
};
</script>

<style scoped lang="scss">
.setting-page {
  @media (max-width: $md-display) {
    padding-top: 0px;
  }
}

.primary-small-square-button {
  margin-left: auto;
}

.setting-input {
  height: 36px;
  width: 50%;
  border: 1px solid #ced4da;
  margin-right: 20px;
  padding: 10px;
  border-radius: 2.5px;
}

.toast.show {
  display: none;
  max-width: unset;
  width: inherit;
  //   border: 1px solid red;
  @media (max-width: $md-display) {
    display: block;
    position: fixed;
    // width: 300px;
    font-size: $responsive-base-font-size;
    text-align: center;
    top: 35px;
    width: 100%;
    left: 0;
    right: 0;
    height: 41px;
  }
}

.toast-body {
  font-size: 12px;
}
</style>

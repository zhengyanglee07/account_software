<template>
  <div class="setting-page right_container_content_inner-page">
    <setting-page-header
      title="Integration Settings"
      previous-button-u-r-l="/integration/setting"
      previous-button-link-title="Back To Settings"
    ></setting-page-header>

    <setting-layout :title="`${data.webinar_type} Settings`" has-action-button>
      <template #description>
        Set up your {{ data.webinar_type }} API key for broadcast.
      </template>

      <template #content>
        <div class="row w-100">
          <div class="setting-page__content-oneline">
            <setting-label title="Display Name" />
            <input
              type="text"
              v-model="data.display_name"
              placeholder="Enter the display name"
              class="setting-page__form-input w-100 form-control"
            />
            <span
              style="color: rgb(184, 184, 184)"
              class="p-three text-capitalize"
            >
              for internal use only
            </span>
            <span
              class="error font-weight-normal"
              v-if="showError && !$v.data.display_name.required"
            >
              Display name is required
            </span>
          </div>
          <div class="setting-page__content-oneline">
            <setting-label title="Integration Key" />
            <input
              type="text"
              v-model="data.api_key"
              placeholder="Enter the integration key"
              class="setting-page__form-input w-100 form-control"
            />
            <span
              class="error font-weight-normal"
              v-if="showError && !$v.data.api_key.required"
            >
              Integration Key is required
            </span>
          </div>
        </div>
      </template>
      <template #button>
        <button
          type="submit"
          class="primary-small-square-button"
          @click="saveWebinarSetting()"
        >
          Save
        </button>
      </template>
    </setting-layout>
  </div>
</template>
<script>
import { required } from '@vuelidate/validators';
export default {
  name: 'AddIntegrationSetting',
  props: {
    integrationSetting: Object,
  },
  data() {
    return {
      data: null,
      showError: false,
    };
  },
  validations() {
    return {
      data: {
        display_name: { required },
        api_key: { required },
      },
    };
  },
  methods: {
    saveWebinarSetting() {
      this.showError = false;
      if (this.$v.$invalid) return (this.showError = true);
      axios.post('/save/integration/settings', this.data).then(({ data }) => {
        if (data.status === 'fail')
          return this.$toast.error(
            'Error',
            'Fail to save integration settings'
          );
        this.$toast.success(
          'Success',
          'Successfully save integration settings'
        );
        window.location.replace('/integration/setting');
      });
    },
  },
  mounted() {
    this.data = this.integrationSetting;
  },
};
</script>

<style lang="scss" scoped>

.row > * {
  padding: 0;
}
</style>

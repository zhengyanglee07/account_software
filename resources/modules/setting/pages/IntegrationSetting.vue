<template>
  <div class="setting-page right_container_content_inner-page">
    <SettingPageHeader title="Integration Settings" />

    <!-- google settings  -->
    <SettingLayout
      v-if="false"
      title=" Google Settings"
      has-action-button
    >
      <template #description>
        Connect to your Google Ads account to quickly sync your segments to
        Google audience via Customer Match.
      </template>

      <template #content>
        <div class="w-100">
          <label class="font-weight-bold setting-page__label">
            Connected account:
          </label>

          <div class="mt-3">
            <span v-if="googleAuths.length === 0">
              No connected account found.
            </span>
            <!-- currently only support one particular social account in one hyper account -->
            <span v-else> Customer ID: {{ googleAuths[0].identifier }} </span>
          </div>
        </div>
      </template>

      <template #button>
        <button
          class="primary-square-button me-2"
          :disabled="googleAuths.length !== 0"
          @click="handleGoogleOAuth"
        >
          Connect
        </button>
        <button
          class="primary-white-button"
          :disabled="googleAuths.length === 0"
          @click="handleDisconnect(googleAuths[0].id)"
        >
          Disconnect
        </button>
      </template>
    </SettingLayout>
    <!-- end google setting  -->

    <!-- fb setting  -->
    <SettingLayout
      v-if="false"
      title="Facebook Settings"
      has-action-button
    >
      <template #description>
        Connect to your Facebook Business Manager account to quickly sync your
        segments to Facebook Custom Audience.
      </template>

      <template #content>
        <div class="w-100">
          <label class="font-weight-bold setting-page__label">
            Connected account:
          </label>

          <div class="mt-3">
            <span v-if="facebookAuths.length === 0">
              No connected account found.
            </span>
            <span
              v-for="a in facebookAuths"
              :key="a.identifier"
            >
              ID: {{ a.identifier }}
            </span>
          </div>
        </div>
      </template>

      <template #button>
        <button
          class="primary-square-button me-2"
          :disabled="facebookAuths.length !== 0"
          @click="handleFacebookAuth"
        >
          Connect
        </button>
        <button
          class="primary-white-button"
          :disabled="facebookAuths.length === 0"
          @click="handleDisconnect(facebookAuths[0].id)"
        >
          Disconnect
        </button>
      </template>
    </SettingLayout>
    <!-- end fb setting  -->
    <!-- webinar setting -->
    <!-- <WebinarSetting
      :all-integration-settings="allIntegrationSettings"
    /> -->
    <!-- end webinar setting -->
    <DisconnectModal
      modal-id="disconnect-modal"
      :media-account-id="selectedMediaAccountId"
      @update-local-account-oauths="handleUpdateLocalAccountOauths"
    />
  </div>
</template>

<script>
// import WebinarSetting from '@setting/pages/WebinarSetting.vue';
import DisconnectModal from '@setting/components/DisconnectModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'IntegrationSetting',
  components: {
    DisconnectModal,
    // WebinarSetting,
  },
  props: {
    accountOauths: [Array, Object],
    allIntegrationSettings: Array,
  },
  data() {
    return {
      localAccountOauths: [],
      selectedMediaAccountId: null,
    };
  },
  computed: {
    googleAuths() {
      return this.localAccountOauths.filter(
        (auth) => auth.provider === 'google'
      );
    },
    facebookAuths() {
      return this.localAccountOauths.filter(
        (auth) => auth.provider === 'facebook'
      );
    },
  },
  watch: {
    accountOauths: {
      handler(val) {
        this.localAccountOauths = [...val];
      },
      immediate: true,
    },
  },
  methods: {
    handleGoogleOAuth() {
      // redirect to hypershapes google oauth route
      window.location.href = '/oauth/google';
    },
    handleFacebookAuth() {
      // TODO
    },
    handleUpdateLocalAccountOauths(id) {
      this.localAccountOauths = this.localAccountOauths.filter(
        (o) => o.id !== id
      );
    },

    async handleDisconnect(id) {
      this.selectedMediaAccountId = id;
      this.disconnect_modal = new Modal(
        document.getElementById('disconnect-modal')
      );
      this.disconnect_modal.show();
    },
  },
};
</script>

<style scoped lang="scss">
*:not(i) {
  font-size: $base-font-size;
  color: $base-font-color;
  font-family: $base-font-family;
}

.back-to-previous {
  color: #ced4da !important;
  text-decoration: none;
  padding-bottom: 12px;
  font-size: 16px;
  width: 20%;

  &:hover {
    text-decoration: none;
  }

  &__back-icon {
    color: #ced4da;
    padding-right: 12px;
  }
}
</style>

<template>
  <vue-jq-modal :modal-id="modalId">
    <template #title> Disconnect social media account </template>

    <template #body>
      Disconnecting your account will unsync all associated audiences from your
      segments. Previously synchronized audiences will still remain in your
      corresponding Social Media Ads, but they will no longer be synchronized
    </template>

    <template #footer>
      <button class="cancel-button" type="button" data-bs-dismiss="modal">
        Cancel
      </button>
      <button
        class="primary-square-button ms-2"
        :disabled="disconnectingAcct"
        @click="disconnectAcct"
      >
        Disconnect
        <i
          v-if="disconnectingAcct"
          class="fas fa-circle-notch fa-spin pe-0 ms-1"
        />
      </button>
    </template>
  </vue-jq-modal>
</template>

<script>
import VueJqModal from '@shared/components/VueJqModal.vue';

export default {
  name: 'DisconnectModal',
  components: {
    VueJqModal,
  },
  props: {
    modalId: String,
    mediaAccountId: Number,
  },
  data() {
    return {
      disconnectingAcct: false,
    };
  },
  methods: {
    async disconnectAcct() {
      this.disconnectingAcct = true;

      try {
        await this.$axios.delete(
          `/integration/setting/disconnect/${this.mediaAccountId}`
        );

        this.$emit('update-local-account-oauths', this.mediaAccountId);
      } catch (err) {
        this.$toast.error('Error', 'Something wrong when disconnecting');
      } finally {
        this.disconnectingAcct = false;
        $(`#${this.modalId}`).modal('hide');
      }
    },
  },
};
</script>

<style scoped></style>

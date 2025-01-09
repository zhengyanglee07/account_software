<template>
  <BaseModal
    title="Are you sure you want to clone this funnel ?"
    :modal-id="modalId"
  >
    <p style="text-align: center; font-size: 14px">
      By clicking on Confirm, you agree to clone the funnel into your account.
    </p>

    <template #footer>
      <BaseButton @click="shareFunnel">
        Confirm
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import BaseModal from '@shared/components/BaseModal.vue';

export default {
  components: {
    BaseModal,
  },

  props: {
    modalId: {
      type: String,
      default: 'confirm-clone-funnel-modal',
    },
    referenceKey: {
      type: String,
      required: true,
    },
  },

  computed: {
    funnelId() {
      return JSON.parse(this.funnel).id;
    },
  },

  methods: {
    shareFunnel() {
      this.$axios
        .post('/funnel/shared/save', {
          reference_key: this.referenceKey,
        })
        .then((response) => {
          window.location.assign('/funnels');
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
</script>

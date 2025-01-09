<template>
  <div>
    <div
      :id="modalId"
      class="modal fade"
      tabindex="-1"
      role="dialog"
      :aria-labelledby="`${modalId}-label`"
      aria-hidden="true"
      style="z-index: 2000; padding-right: 0 !important"
      data-bs-backdrop="static"
    >
      <div
        class="modal-dialog modal-dialog-centered"
        style="max-width: 800px"
        :class="{
          'modal-dialog-scrollable': scrollable,
        }"
      >
        <div
          class="modal-content"
          :class="{ 'm-container--small': small }"
        >
          <div class="m-container__header">
            <p
              id="contactInfoModalLabel"
              class="m-container__header-title mb-0"
              :class="{ 'm-container__header-title--long': small }"
              style="text-align: left; width: 100%"
            >
              <slot name="title" />
            </p>

            <slot name="custom-close-btn">
              <span
                type="button"
                class="close-button"
                data-bs-dismiss="modal"
                aria-label="Close"
                style="font-size: 20px"
                @click="updateEducationalStatus"
              >
                &times;
              </span>
            </slot>
          </div>

          <template v-if="manual">
            <slot name="content" />
          </template>

          <template v-else>
            <div class="modal-body pt-0">
              <div style="min-height: -webkit-fill-available; width: 100%">
                <slot name="body" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  name: 'InformationModal',

  props: {
    modalId: String,

    // manually build modal body
    manual: {
      type: Boolean,
      default: false,
    },

    scrollable: {
      type: Boolean,
      default: false,
    },

    data_backdrop: String,

    small: {
      type: Boolean,
      default: false,
    },

    accountId: {
      type: Number,
    },

    isShow: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      modal: null,
    };
  },

  mounted() {
    bootstrap?.then(({ Modal }) => {
      this.modal = new Modal(document.getElementById('location-modal'));
    });
    if (this.isShow) {
      this.modal.show();
      this.updateEducationalStatus();
    }
  },

  methods: {
    updateEducationalStatus() {
      axios
        .put(`/account/${null}/educated`)
        .then((res) => console.log(res))
        .catch((err) => console.error(err));
    },
  },
};
</script>

<style scoped lang="scss">
.modal-body {
  max-height: 550px;
}

.m-container__header {
  min-height: 50px;
}
</style>

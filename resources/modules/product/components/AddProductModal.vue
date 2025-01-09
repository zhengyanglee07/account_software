<template>
  <BaseModal
    :title="title"
    :modal-id="modalId"
    :static-backdrop="staticBackdrop"
    :size="size"
  >
    <template v-if="manual">
      <slot name="content" />
    </template>

    <template v-else>
      <slot name="body" />
    </template>
    <template
      v-if="!manual"
      #footer
    >
      <slot name="footer" />
    </template>
  </BaseModal>
  <!-- <div
    :id="modalId"
    :ref="modalId"
    style="z-index: 1999"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    :aria-labelledby="`${modalId}-label`"
    aria-hidden="true"
    :data-bs-backdrop="data_backdrop"
    :data-bs-keyboard="data_keyboard"
  >
    <div
      class="modal-dialog modal-dialog-centered"
      style="max-width: 800px; margin-top: 0"
      :class="{ 'modal-dialog-scrollable': scrollable }"
    >
      <div
        class="modal-content"
        :class="{ 'm-container--small': small }"
      >
        <div class="m-container__header">
          <p
            id="contactInfoModalLabel"
            class="m-container__header-title h-three"
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
            >
              &times;
            </span>
          </slot>
        </div>

        <template v-if="manual">
          <slot name="content" />
        </template>

        <template v-else>
          <div class="modal-body">
            <slot name="body" />
          </div>

          <div class="m-container__footer">
            <div style="margin-right: 0; margin-left: auto">
              <slot name="footer" />
            </div>
          </div>
        </template>
        <br>
      </div>
      <br>
    </div>
  </div> -->
</template>

<script>
export default {
  name: 'VueJqModal',
  props: {
    modalId: {
      type: String,
      default: () => '',
    },

    // manually build modal body
    manual: {
      type: Boolean,
      default: false,
    },

    staticBackdrop: {
      type: Boolean,
      default: false,
    },

    title: {
      type: String,
      default: () => '',
    },
    size: {
      type: String,
      default: () => 'md',
    },
    hasSeparator: {
      type: Boolean,
      default: () => true,
    },
  },

  data() {
    return {
      closeType: null,
    };
  },

  mounted() {
    // this.$refs['edit-global-variant-modal']?.on('hide.bs.modal', (e) => {
    //   eventBus.$emit('resetInputValuesForGlobalVariant');
    // });
    // this.$refs['add-product-modal-product-variant']?.on(
    //   'hide.bs.modal',
    //   (e) => {
    //     eventBus.$emit('resetInputValuesForVariant');
    //   }
    // );
    // $('div.modal').on('shown.bs.modal', function () {
    //   var id = $(this).attr('id');
    //   $(`#${id} .firstInput`).focus();
    // });
  },
};
</script>

//
<style scoped lang="scss">
// *:not(i) {
//   font-size: $base-font-size;
//   font-family: $base-font-family;
//   color: $base-font-color;
// }

// p {
//   margin: 0;
// }

// .modal {
//   overflow-y: auto;
// }

// body.modal-open {
//   overflow: hidden !important;
// }
//
</style>

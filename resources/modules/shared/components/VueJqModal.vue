<template>
  <div
    :id="modalId"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    :aria-labelledby="`${modalId}-label`"
    aria-hidden="true"
    :data-bs-backdrop="data_backdrop"
    :data-bs-keyboard="data_keyboard"
    style="z-index: 2000"
  >
    <div
      class="modal-dialog modal-dialog-centered"
      style="max-width: 800px; margin-top: 0"
      :class="{}"
    >
      <div
        class="modal-content"
        :class="{ 'm-container--small': small }"
      >
        <div
          v-show="showTitle"
          class="m-container__header"
        >
          <p
            :id="`${modalId}-label`"
            class="m-container__header-title h-three"
            :class="{ 'm-container__header-title--long': small }"
            style="text-align: left; width: 100%"
          >
            <slot name="title" />
          </p>

          <slot name="custom-close-btn">
            <span
              :id="`${modalId}-close-button`"
              type="button"
              class="close-button"
              data-bs-dismiss="modal"
              aria-label="Close"
              style="font-size: 20px"
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
            <!-- <div style="min-height: -webkit-fill-available; width: 100%"> -->
            <slot name="body" />
            <!-- </div> -->
          </div>

          <div
            v-show="showFooter"
            class="m-container__footer"
          >
            <slot name="footer" />
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';

export default {
  name: 'VueJqModal',
  mixins: [specialCurrencyCalculationMixin],
  props: {
    modalId: String,

    // manually build modal body
    manual: {
      type: Boolean,
      default: false,
    },

    showTitle: {
      type: Boolean,
      default: true,
    },

    showFooter: {
      type: Boolean,
      default: true,
    },

    isProductReview: {
      type: Boolean,
      default: false,
    },

    scrollable: {
      type: Boolean,
      default: false,
    },

    data_backdrop: String,

    data_keyboard: String || Boolean,

    small: {
      type: Boolean,
      default: false,
    },

    //* to solve issue of v-select collapse into modal body
    isOverflowVisible: {
      type: Boolean,
      default: false,
    },
  },

  mounted() {
    // to focus on first input on modal show
    // $('div.modal').on('shown.bs.modal', function(){
    //   var id = $(this).attr('id');
    //   $(`#${id} .firstInput`).focus()
    // });
  },
};
</script>

<style scoped lang="scss">

*:not(i) {
  font-size: $base-font-size;
  font-family: $base-font-family;
  color: $base-font-color;
}

p {
  margin: 0;
}

.modal {
  padding-right: 0;
  overflow-y: auto;

  .modal-body {
    padding-bottom: 10px;
  }
}

.modal-open {
  padding-right: 0 !important;
}

body:not(.modal-open) {
  padding-right: 0px !important;
}

body.modal-open {
  overflow: hidden !important;
}
</style>

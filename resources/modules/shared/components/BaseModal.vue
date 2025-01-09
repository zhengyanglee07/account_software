<template>
  <div
    :id="modalId"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    :aria-labelledby="`${modalId}-label`"
    :data-bs-backdrop="staticBackdrop ? 'static' : !noBackdrop"
    :data-bs-keyboard="!staticBackdrop"
    :data-bs-focus="modalId !== 'popup-modal'"
    :style="{
      zIndex: zIndex,
    }"
  >
    <div
      class="modal-dialog modal-dialog-centered"
      :class="[
        `modal-${size} ${scrollable ? 'modal-dialog-scrollable' : ''}`,
        {
          'modal-dialog--popup': isPopup,
        },
      ]"
      :style="{
        'min-width': minWidth,
        width: isPopup ? minWidth : 'auto',
      }"
    >
      <div
        class="modal-content"
        :style="{
          height: isImageModal ? '80vh' : height,
          maxHeight:
            isPopup && modalId === 'popup-modal'
              ? 'calc(100vh - 60px)'
              : 'auto',
          borderRadius: `${borderRadius}px`,
        }"
      >
        <div
          v-if="!noHeader"
          class="modal-header"
        >
          <h2
            v-if="title"
            :style="{
              width: isImageModal ? '20%' : 'auto',
            }"
          >
            {{ title }}
          </h2>

          <div
            class="d-flex justify-content-center align-items-center tabs-wrapper"
          >
            <slot name="tabs" />
          </div>

          <div
            class="text-end"
            :style="{
              width: isImageModal ? '20%' : 'auto',
            }"
          >
            <BaseButton
              type="close"
              size="sm"
              data-bs-dismiss="modal"
              aria-label="Close"
            />
          </div>
        </div>

        <template v-if="manual">
          <slot />
        </template>

        <template v-else>
          <div
            class="modal-body row py-10"
            :class="{ 'px-lg-17': !noPadding }"
          >
            <!-- @slot For modal body -->
            <slot />
          </div>
        </template>
        <div
          v-if="!noFooter"
          class="modal-footer flex-center"
        >
          <BaseButton
            v-if="!noDismiss"
            type="light"
            class="me-3"
            data-bs-dismiss="modal"
          >
            Dismiss
          </BaseButton>
          <!-- @slot For buttons in footer -->
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import eventBus from '@services/eventBus.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  /**
   * The unique id of the modal. Will
   * be used by button to trigger this modal via `data-bs-target`
   */
  modalId: {
    type: String,
    required: true,
  },
  /**
   * Determine the z-index of the modal
   */
  zIndex: {
    type: [String, Number],
    default: 1055,
  },
  /**
   * If true, the modal wouldn't be closed when click outside
   */
  staticBackdrop: {
    type: Boolean,
    default: false,
  },
  /**
   * If true, the modal wouldn't have backdrop at background
   */
  noBackdrop: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine the size of the modal
   */
  size: {
    type: String,
    default: 'md',
    validator: (value) => {
      return ['xl', 'lg', 'md'].includes(value);
    },
  },
  /**
   * If true, the modal won't have header section
   */
  noHeader: {
    type: Boolean,
    default: false,
  },
  /**
   * Modal header title
   */
  title: {
    type: String,
    default: null,
  },
  /**
   * If true, you can customize the modal body via default slot
   */
  manual: {
    type: Boolean,
    default: false,
  },
  /**
   * If true, the modal won't have footer section
   */
  noFooter: {
    type: Boolean,
    default: false,
  },
  /**
   * If true, the modal won't have Dismiss button at footer section
   */
  noDismiss: {
    type: Boolean,
    default: false,
  },
  /**
   * If true, will set the height of modal to be 80vh
   */
  isImageModal: {
    type: Boolean,
    default: false,
  },
  isPopup: {
    type: Boolean,
    default: false,
  },
  scrollable: {
    type: Boolean,
    default: false,
  },
  noPadding: {
    type: Boolean,
    default: false,
  },
  /**
   * Used by popup to customize modal width in different devices
   */
  minWidth: {
    type: String,
    default: null,
  },
  height: {
    type: String,
    default: 'auto',
  },
  /**
   * Determine the border radius of modal content
   */
  borderRadius: {
    type: Number,
    default: 6,
  },
});

const modal = ref(null);

onMounted(() => {
  bootstrap?.then(({ Modal }) => {
    if (document.getElementById(props.modalId))
      modal.value = new Modal(document.getElementById(props.modalId));

    eventBus.$emit('base-modal-mounted');
    eventBus.$on(`hide-modal-${props.modalId}`, () => {
      modal.value?.hide();
    });
  });
});
</script>

<style lang="scss" scoped>
.modal-dialog--popup {
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
}

@media only screen and (min-width: 768px) {
  .modal-dialog {
    min-width: 650px;
  }
}

.tabs-wrapper {
  :slotted(button) {
    color: #a1a5b7;
    margin-right: 15px;
  }

  :slotted(button.active) {
    color: black;
    transition: color 0.2s ease;
  }
}
</style>

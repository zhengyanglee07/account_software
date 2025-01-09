<template>
  <div>
    <div
      class="asset-preview-container"
      :class="`asset-preview-container--${isRounded ? 'rounded' : size}`"
      :style="{
        borderRadius: isRounded ? '50%' : '0',
      }"
      @click="triggerModal"
      @mouseover="showDeleteButton = true"
      @mouseleave="showDeleteButton = false"
    >
      <img
        class="container-main"
        :src="imageURL ?? defaultImage"
      >
      <div class="modal-trigger">
        <button
          v-if="showDeleteButton"
          class="btn btn-icon delete-button"
          @click.stop="$emit('delete')"
        >
          <i class="fa-solid fa-trash" />
        </button>
        <div
          v-if="!noHoverMessage"
          class="hover-message-window text-center"
        >
          Choose Image
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

const props = defineProps({
  /**
   * Specify the id of the modal that will be triggered when this component is clicked.
   * It will trigger image modal by default
   */
  modalId: {
    type: String,
    default: 'image-modal',
  },
  /**
   * Determine whether want to show `Choose Image` message at component footer
   */
  noHoverMessage: {
    type: Boolean,
    default: false,
  },
  /**
   * Specify the URL of the image to be showed
   */
  imageURL: {
    type: String,
    default: null,
  },
  /**
   * Specify whether the component is rounded (with border-radius: 50%)
   */
  isRounded: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine the size of the component (`sm`, `md` or `lg`)
   */
  size: {
    type: String,
    default: 'md',
    validator: (value) => {
      return ['lg', 'md', 'sm'].includes(value);
    },
  },
  /**
   * Specify the URL of the default image to be showed when no value is passed to `imageURL`
   */
  defaultImage: {
    type: String,
    default: 'https://cdn.hypershapes.com/assets/placeholder.png',
  },
});

const emit = defineEmits([
  /**
   * Fires when the component is clicked
   */
  'click',
  /**
   * Fires when the delete button is clicked
   */
  'delete',
]);

const showDeleteButton = ref(false);

const triggerModal = () => {
  emit('click');
  nextTick(() => {
    bootstrap?.then(({ Modal }) => {
      new Modal(document.getElementById(props.modalId)).show();
    });
  });
};
</script>

<style lang="scss" scoped>
.container-main {
  object-fit: cover;
  width: 100%;
  max-height: 250px;
  height: 100%;
}

.asset-preview-container {
  position: relative;
  display: block;
  cursor: pointer;
  border: thin solid rgb(119, 136, 153, 0.2);
  max-height: 250px;
  overflow: hidden;

  &--sm {
    width: 70px;
    height: 70px;
  }

  &--md {
    width: 230px;
    height: 98px;
  }

  &--lg {
    width: 100%;
    height: 100%;
  }

  &--rounded {
    width: 94px;
    height: 94px;
  }
}

.modal-trigger {
  position: absolute;
  background: rgb(119, 136, 153, 0.3);
  width: 100%;
  height: 100%;
  align-items: flex-end;
  color: #333;
  font-size: 18px;
  font-family: Helvetica;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  flex-direction: column;
}

.modal-trigger:hover {
  background-color: rgba(164, 175, 183, 0.8);
}

.modal-trigger:hover div {
  visibility: visible;
  /* opacity: 1; */
  transition-delay: 0s;
}

.file-input {
  display: none;
}

.delete-button {
  font-size: 11px;
  width: 18px;
  height: 18px;
  padding: 4px;
  margin: 8px 8px 0 0;
  background-color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  float: right;
  color: gray;
}

.hover-message-window {
  width: 100%;
  height: 30px;
  font-size: 11px;
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  color: lightgray;
  visibility: hidden;
  opacity: 1;
  transition: visibility 1s ease-in 0.33s, opacity 0.33s ease-in;
  background-color: hsla(209, 9%, 47%, 0.9);
}
</style>

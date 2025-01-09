<template>
  <div
    class="asset-preview-container"
    :style="{ borderRadius: shapeType === 'round' ? '50%' : '0' }"
    @click="openModal"
    @mouseover="showDeleteButton"
    @mouseleave="hideDeleteButton"
  >
    <img
      class="container-main"
      :src="value ?? 'https://cdn.hypershapes.com/assets/placeholder.png'"
    >
    <div
      class="modal-trigger"
      @click="chooseImage"
    >
      <button
        v-show="showDelete == true && imageData !== null"
        class="btn btn-icon delete-button"
        @mouseover="showDeleteButton"
        @click.stop="$emit('delete')"
      >
        <img
          src="@shared/assets/media/trash.svg"
          alt=""
          style="transform: scale(4.5); padding-bottom: 0.5px"
        >
      </button>
      <div
        v-if="showHoverMessageWindow"
        class="hover-message-window"
      >
        Choose Image
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, toRefs, nextTick } from 'vue';
import eventBus from '@services/eventBus.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  props: {
    editType: {
      type: String,
      required: false,
      default: 'builderImage',
    },
    imageSettings: {
      type: String,
      required: false,
      default: '/images/placeholder.png',
    },
    type: {
      type: String,
      default: 'default',
    },
    selectedIndex: {
      type: Number,
      default: -1,
    },
    optionIndex: {
      type: Number,
      required: false,
    },
    showHoverMessageWindow: {
      type: Boolean,
      default: true,
    },
    value: {
      type: String,
      default: 'https://cdn.hypershapes.com/assets/placeholder.png',
    },
    shapeType: {
      type: String,
      default: 'square',
    },
  },

  emits: ['update-value', 'delete', 'cancelled'],

  setup(props, { emit }) {
    const { imageSettings, type, selectedIndex } = toRefs(props);

    const showDelete = ref(false);
    const imagePath = ref('');

    const imageData = computed(() => {
      const defaultImg = '@assets/builder/placeholder.png';

      return imageSettings === '' ||
        imageSettings === undefined ||
        imageSettings === null
        ? defaultImg
        : imageSettings;
    });

    const showDeleteButton = () => {
      showDelete.value = true;
    };

    const hideDeleteButton = () => {
      showDelete.value = false;
    };

    const dataSelected = (data) => {
      emit('update-value', data);
    };

    const chooseImage = () => {
      eventBus.$emit('fetchIndex', {
        selectedIndex: '1',
        optionIndex: '1',
      });
      eventBus.$emit('editType', props.editType);
    };

    const openModal = () => {
      const modalId = `${type.value === 'button' ? 'icon' : type.value}-modal${
        selectedIndex.value < 0 ? '' : `-${selectedIndex.value}`
      }`;
      nextTick(() => {
        bootstrap?.then(({ Modal }) => {
          new Modal(document.getElementById(modalId)).show();
        });
      });
    };
    return {
      showDelete,
      imagePath,
      imageData,
      showDeleteButton,
      hideDeleteButton,
      chooseImage,
      dataSelected,
      openModal,
    };
  },
};
</script>

<style scoped lang="scss">
.container-main {
  object-fit: cover;
  width: 100%;
  max-height: 250px;
  height: 100%;
}

.asset-preview-container {
  position: relative;
  display: block;
  width: 100%;
  cursor: pointer;
  border: thin solid rgb(119, 136, 153, 0.2);
  max-height: 250px;
  overflow: hidden;
  height: 100%;
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
  opacity: 1;
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
  opacity: 0;
  transition: visibility 1s ease-in 0.33s, opacity 0.33s ease-in;
  background-color: hsla(209, 9%, 47%, 0.9);
}
</style>

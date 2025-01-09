<template>
  <div
    class="container main-container"
    @click="openModal"
  >
    <Draggable
      :list="
        parsedImages.length
          ? parsedImages
          : ['https://cdn.hypershapes.com/assets/product-default-image.png']
      "
      class="row"
      :class="{
        'h-250':
          imageArray[0] ===
          'https://cdn.hypershapes.com/assets/product-default-image.png',
      }"
      @start="onStartDrag"
      @end="onEndDrag"
    >
      <template #item="{ element, index }">
        <div
          class="imageItem"
          :class="{
            image:
              element ===
              'https://cdn.hypershapes.com/assets/product-default-image.png',
            image__first:
              index === 0 &&
              imageArray.length >= 1 &&
              element !==
              'https://cdn.hypershapes.com/assets/product-default-image.png',
            image__subsequence: imageArray.length > 1 && index !== 0,
            'col-md-3': imageArray.length > 1 && index !== 0,
          }"
          @mouseenter="selectedIndex = index"
          @mouseleave="selectedIndex = null"
        >
          <img
            :src="element"
            alt="product image"
            class="img"
          >
          <div
            class="image-upload-window-placeholder d-flex flex-column"
            @click="chooseImage(index)"
          >
            <button
              v-show="selectedIndex === index && element !== null"
              class="btn btn-icon bg-white delete-button"
              @click.stop.prevent="deleteImage(index)"
            >
              <i class="fa-solid fa-trash" />
            </button>
            <div class="hover-message-window">
              Choose Image
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <div
          v-if="
            imageArray[0] !==
              'https://cdn.hypershapes.com/assets/product-default-image.png' &&
              imageArray[0] !== '/images/product-default-image.png' &&
              !isDragging
          "
          class="image__subsequence col-md-3"
        >
          <img
            class="img"
            src="https://cdn.hypershapes.com/assets/product-default-image.png"
            style="opacity: 0"
          >
          <i
            class="fa fa-plus-circle add-icon"
            aria-hidden="true"
          />
          <div
            class="image-upload-window-placeholder d-flex flex-column"
            @click="chooseImage(imageArray?.length)"
          >
            <div class="hover-message-window p-two">
              Choose Image
            </div>
          </div>
        </div>
      </template>
    </Draggable>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

export default {
  components: {
    Draggable: draggable,
  },
  props: {
    images: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      selectedIndex: null,
      parsedImages: [
        'https://cdn.hypershapes.com/assets/product-default-image.png',
      ],
      onDrag: false,
    };
  },

  computed: {
    imageArray() {
      return this.parsedImages.length
        ? this.parsedImages
        : ['https://cdn.hypershapes.com/assets/product-default-image.png'];
    },

    isDragging() {
      return this.onDrag;
    },
  },

  watch: {
    images(newVal) {
      this.parsedImages = newVal;
    },
  },

  mounted() {
    this.parsedImages = this.images;
  },

  methods: {
    openModal() {
      this.$emit('inputImage');
      this.$nextTick(() => {
        this.imageUploader = new Modal(
          document.getElementById('default-modal')
        );
        this.imageUploader.show();
      });
    },
    chooseImage(index) {
      this.$emit('update-index', index);
    },

    deleteImage(index) {
      eventBus.$emit('deleteMultiProductImage', index);
    },

    onStartDrag(e) {
      this.onDrag = true;
      if (e.item.classList.contains('image__first')) {
        e.item.classList.remove('image__first');
        e.item.classList.add('image__subsequence');
        e.item.classList.add('col-md-3');
      }
    },

    onEndDrag(e) {
      if (e.oldIndex === 0) {
        e.item.classList.remove('image__subsequence');
        e.item.classList.remove('col-md-3');
        e.item.classList.add('image__first');
      }
      this.onDrag = false;
      this.$emit('onProductImageSwapping', this.parsedImages);
    },
  },
};
</script>

<style scoped lang="scss">
*:not(i) {
  padding: 0;
  margin: 0;
}

.main-container {
  width: 100%;
  height: 100%;
  margin: 0 !important;
}

.image {
  position: relative;
  max-height: 250px;
  width: 100%;
  height: 100%;
  display: inline-block;
  cursor: pointer;

  &__first {
    position: relative;
    width: 100%;
    height: 250px;
    max-height: 300px;
    display: inline-block;
    cursor: pointer;
  }

  &__subsequence {
    position: relative;
    //width: 24.5%;
    height: 120px;
    display: inline-block;
    cursor: pointer;

    @media (max-width: 767px) {
      width: 50%;
    }

    &:last-child {
      position: relative;
      //width: 24.5%;
      height: 119px;
      position: relative;
      cursor: pointer;
    }
  }
}

.h-250 {
  height: 250px;
}

.img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.add-image-container {
  display: inline-block;
  position: relative;
  border: 2px dotted grey;
}

.add-icon {
  font-size: 20px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: grey;
}

.delete-icon {
  position: absolute;
  right: 5px;
  top: 5px;
  font-size: 11px;
  width: 20px;
  height: 20px;
  padding: 4px;
  margin: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  float: right;
  color: gray;

  &:hover {
    cursor: pointer;
  }
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

.image-upload-window-placeholder {
  position: absolute;
  //background: rgb(119, 136, 153, 0.3);
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
  padding-bottom: 20px;
  margin-bottom: 20px;
}

.delete-button {
  font-size: 11px;
  width: 20px;
  height: 20px;
  padding: 4px;
  margin: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  float: right;
  color: gray;
}

.image-upload-window-placeholder:hover {
  background-color: rgba(164, 175, 183, 0.8);
}

.image-upload-window-placeholder:hover div {
  visibility: visible;
  opacity: 1;
  transition-delay: 0s;
}
</style>

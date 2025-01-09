<template>
  <div class="media-container h-100">
    <div
      v-if="images.length !== 0 && selectedImage?.id"
      class="media-container__left-bar"
    >
      <p class="left-bar__title">
        Image Details
      </p>

      <!-- Image Preveiew Here -->
      <PictureElement
        img-class="left-bar__image"
        img-container-width="150"
        :img-link="selectedImage?.s3_url ?? selectedImage?.local_path"
        :img-alt="selectedImage?.name"
        img-styles="max-width: 150px; max-height: 150px"
      />

      <!-- Details -->
      <p class="left-bar__text mt-2">
        <span class="left-bar__text--bold">Name : </span>
        {{ selectedImage?.name }}
      </p>

      <p class="left-bar__text">
        <span class="left-bar__text--bold">Size : </span>
        {{ imageSize }}
      </p>

      <p class="left-bar__text">
        <span class="left-bar__text--bold">Dimension : </span>
        {{ `${selectedImage?.width} x ${selectedImage?.height}` }}
      </p>

      <div class="left-bar__button-container">
        <BaseButton
          type="light-primary"
          size="sm"
          @click="deleteImage"
        >
          Delete Permanently
        </BaseButton>
      </div>
    </div>

    <div class="media-container__right-bar">
      <!-- SEARCH BAR -->
      <div class="action-bar">
        <div class="action-bar__count">
          Displaying {{ images.length }} images
        </div>
        <div class="action-bar__search">
          <span class="action-bar__search-icon">
            <i class="fas fa-search" />
          </span>
          <input
            v-model="searchKeywords"
            type="text"
            placeholder="Search by image name"
            class="action-bar__search-input form-control"
          >
        </div>
      </div>

      <!-- Image Preview -->
      <div class="gallery-wrapper">
        <div class="img-container flex-row">
          <div
            v-for="image in filteredImages"
            :key="image.id"
            class="img-container__image"
            @click.stop="chooseImage(image)"
          >
            <!-- Overlay -->
            <div
              v-show="
                images?.length !== 0 ? image.id === selectedImage?.id : true
              "
              class="img-container__overlay"
            >
              <i
                class="fa fa-check img-container__icon"
                aria-hidden="true"
              />
            </div>

            <!-- IMAGE HERE -->
            <PictureElement
              img-class="image-gallery-container"
              img-container-width="200"
              is-from-image-gallery
              :img-alt="image.name"
              :img-link="image?.s3_url ?? image?.local_path"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import imagesAPI from '@shared/api/imagesAPI.js';
import { ref, computed, defineEmits, watch } from 'vue';
import { useStore } from 'vuex';

const emits = defineEmits(['imageSelected']);

const props = defineProps({
  preSelectedImage: {
    type: [Object, String],
    default: null,
  },
});

const store = useStore();
const searchKeywords = ref('');
const selectedImage = ref({});

const images = computed(() => store.state.image.images);

const imageSize = computed(() => {
  const size = Number(selectedImage.value.size);
  return size >= 1024
    ? `${(size / 1024).toFixed(2)} MB`
    : `${size.toFixed(2)} KB`;
});

const filteredImages = computed(() => {
  const imagesInDesc = [...images.value].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  );
  if (searchKeywords.value !== '') {
    return imagesInDesc.filter((img) =>
      img.name.toLowerCase().includes(searchKeywords.value.toLowerCase())
    );
  }
  return imagesInDesc;
});

const chooseImage = (image) => {
  selectedImage.value = image;
  emits('imageSelected', image);
};

const firstImage = computed(() => filteredImages.value[0]);

const preSelectedImageObj = computed(() =>
  filteredImages.value.find((e) => e.s3_url === props.preSelectedImage)
);
chooseImage(preSelectedImageObj.value ?? firstImage.value);

watch(
  () => preSelectedImageObj,
  () => {
    chooseImage(preSelectedImageObj.value ?? firstImage.value);
  },
  { deep: true }
);

const deleteImage = () => {
  // eslint-disable-next-line
  const isConfirmed = confirm('Are you sure want to delete this image ?');
  if (!isConfirmed) return;

  imagesAPI
    .delete(selectedImage.value.id)
    .then(() => {
      store.commit('image/deleteImage', selectedImage.value.id);
      chooseImage({});
    })
    .catch((err) => {
      throw new Error(err);
    });
};
</script>

<style scoped lang="scss">
.media-container {
  display: flex;
  flex-direction: row;
  background-color: $base-background;

  @media (max-width: $md-display) {
    width: 100%;
  }

  &__left-bar {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 32px 16px;
    background-color: #fff;
    height: 100%;
    width: 210px;
    border-right: 1px solid #ced4da;
    word-wrap: break-word;
    @media (max-width: $md-display) {
      width: 35vw;
      display: none;
    }
  }

  &__right-bar {
    display: flex;
    flex-direction: column;
    padding: 32px 24px;
    width: 100%;
    height: 100%;
    @media (max-width: $md-display) {
      padding: 16px;
    }
  }
}

.left-bar {
  &__title {
    font-size: 12px;
    margin-bottom: 0.5rem;
  }

  &__image {
    width: 100%;
    height: 100%;
    max-width: 100%;
    object-fit: contain;
  }

  &__text {
    font-size: 10px;
    width: 100%;
    max-width: 170px;
    margin: 6px 0;

    &--bold {
      font-size: 10px;
      font-weight: bold;
    }
  }

  &__button-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin-top: 5px;
  }

  &__button {
    color: $h-primary;
    border: 1px solid $h-primary;
    border-radius: 10rem;
    padding: 6px 16px;
    text-transform: uppercase;
    font-size: 10px;
    margin-top: 20px;
    background-color: #fff;

    &:focus {
      outline: none;
    }

    &:hover {
      cursor: pointer;
      color: $h-primary-hover;
      border-color: $h-primary-hover;
    }
  }
}

.action-bar {
  display: flex;
  flex-direction: row;
  width: 100%;
  justify-content: space-between;
  align-items: center;
  margin: 0 0 16px 0;

  &__count {
    font-size: 13px;
  }

  &__search {
    display: flex;
    flex-direction: row;

    width: 250px;
  }

  &__search-icon {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 28px;
    padding: 0 8px;
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
    background-color: #fff;
    border: 1px solid #ced4da;

    .fa-search {
      padding: 0 !important;
      color: black;
    }
  }

  &__search-input {
    width: 100%;
    height: 28px;
    font-size: 13px;
    padding: 0 12px;
    border: 1px solid #ced4da;
    border-left: none;
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;

    &:focus {
      outline: none;
    }

    &::placeholder {
      color: #ced4da;
    }
  }
}

.gallery-wrapper {
  overflow: hidden scroll;

  &::-webkit-scrollbar {
    display: none;
  }
}

.img-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, 160px);
  grid-auto-rows: 160px;
  overflow: visible;
  grid-gap: 15px;

  @media (min-width: $md-display) {
    max-width: 100%;
  }

  &__image {
    position: relative;
    // margin: 0 12px 12px 0;
    // width: 141.3px;
    // height: 141.3px;
    background-color: #fff;
    // @media (max-width: $md-display) {
    //   width: 36vw;
    //   height: 36vw;
    //   max-height: 150px;
    //   max-width: 150px;
    // }

    &:hover {
      cursor: pointer;
    }
  }

  &__overlay {
    position: absolute;
    top: 0;
    left: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    background-color: rgba(#202930, 0.8);
    // opacity: 0;

    // &:hover {
    //     opacity: 1;
    //     cursor: pointer;
    // }
  }

  &__icon {
    font-size: 28px;
    color: #fff;
  }
}

.image-overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0.7;
  position: absolute;
  width: 100%;
  height: 100%;
  color: white;
  background-color: grey;
}
</style>

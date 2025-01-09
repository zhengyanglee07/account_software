<template>
  <BaseModal
    :modal-id="modalId"
    size="xl"
    manual
    no-dismiss
    is-image-modal
    title="Insert Media"
    z-index="1056"
    scrollable
  >
    <template #tabs>
      <BaseButton
        type="link"
        :active="isUploadTab"
        @click="isUploadTab = true"
      >
        Upload Files
      </BaseButton>
      <BaseButton
        type="link"
        :active="!isUploadTab"
        @click="isUploadTab = false"
      >
        Media Library
      </BaseButton>
    </template>

    <template v-if="isUploadTab">
      <div class="upload-container">
        <div
          v-if="!isUploading"
          class="upload-container__dropzone"
        >
          <div
            v-if="errorMessage.type"
            class="text-center"
          >
            <img
              src="@shared/assets/media/error.svg"
              alt="Error"
              class="mx-auto mb-3"
              style="width: 250px"
            >
            <p class="text-danger text-center fs-6">
              <template v-if="errorMessage.type === 'exceed_quota_limit'">
                You have reached plan quota of subscription plan on media
                storage. <br>
                Delete old images or
                <a
                  target="_blank"
                  href="/subscription/plan/upgrade?ft=false"
                >
                  subscribe to a higher plan now
                </a>
                to continue upload images.
              </template>
              <template v-else>
                The file size of your {{ errorMessage.message }} has exceed 5MB.
                <br>
                Consider compress your image before upload.
              </template>
            </p>
            <button
              class="primary-small-square-button"
              style="width: 150px"
              @click="resetErrorMessage"
            >
              Upload again
            </button>
          </div>
          <template v-else>
            <input
              type="file"
              class="upload-container__file"
              name="image"
              accept=".jpg, .jpeg, .png, .webp, .gif"
              multiple
              @change="handleUpload($event.target.files)"
            >
            <div class="upload-container__upload">
              <i
                class="fa fa-plus-circle upload-container__icon"
                aria-hidden="true"
              />
              <p class="upload-container__label">
                ADD MEDIA
              </p>
            </div>
            <h3 class="upload-container__text">
              Drag and drop or click here to upload file
            </h3>
            <div class="upload-container__text">
              Maximum upload file size is 5MB
            </div>
          </template>
        </div>
        <div
          v-else
          class="upload-container__title"
        >
          <i class="fas fa-spinner fa-pulse" />
          <h3 class="mt-3">
            Uploading ...
          </h3>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="modal-body p-0">
        <ImageGallery
          :pre-selected-image="preSelectedImage"
          :modal-id="modalId"
          @image-selected="imageSelected"
        />
      </div>
    </template>

    <template #footer>
      <BaseButton
        data-bs-dismiss="modal"
        :disabled="!hasSelectedImage"
        @click="insertImage"
      >
        Insert
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import imagesAPI from '@shared/api/imagesAPI.js';
import ImageGallery from '@shared/components/ImageGallery.vue';
import { ref, computed, toRefs, onBeforeMount, shallowRef, inject } from 'vue';
import { useStore } from 'vuex';

const props = defineProps({
  type: {
    type: String,
    default: 'image',
  },
  index: {
    type: Number,
    default: -1,
  },
  preSelectedImage: {
    type: String,
    default: null,
  },
});

const emits = defineEmits(['update-value']);

const $toast = inject('$toast');
const { type, index } = toRefs(props);
const isUploadTab = ref(true);
const isUploading = ref(false);
const selectedImage = ref({});
const store = useStore();
const errorMessage = ref({
  type: null,
  message: null,
});
const tabs = shallowRef([
  {
    label: 'Upload Files',
    target: 'upload',
  },
  {
    label: 'Image Library',
    target: 'library',
  },
]);

onBeforeMount(() => {
  if (store.state.image.images.length === 0) {
    store.dispatch('image/fetchImages');
  }
});

const hasSelectedImage = computed(
  () => Object.keys(selectedImage.value ?? {}).length !== 0
);

const modalId = computed(
  () => `${type.value}-modal${index.value < 0 ? '' : `-${index.value}`}`
);

const resetErrorMessage = () => {
  errorMessage.value = {
    type: null,
    message: null,
  };
};

const uploadImage = (file) => {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('image', file)
    
    imagesAPI
      .upload(formData)
      .then(({ data: { image } }) => {
        isUploadTab.value = false;
        store.commit('image/addNewImages', [image]);
        resolve();
      })
      .catch((err) => {
        console.log(err);
        $toast.error(
          'Error', 
          `Error uploading file: ${file.name}. ${err.response.data?.message ?? "Please contact support."}`
        );
        reject(err);
      })
  });
};

const uploadImages = (files) => {
  const uploadPromises = Array.from(files).map((file) => uploadImage(file));
  return Promise.all(uploadPromises);
}

const handleUpload = async (files) => {
  if (!files || files?.length <= 0) return;
  isUploading.value = true;

  try {
    await uploadImages(files);
    $toast.success(
      'Success',
      `${files.length > 1 ? "All images " : "Image"} uploaded successfully`
    );
  } catch (err) {
    console.log(err);
  }
  isUploading.value = false;
}

const imageSelected = (image) => {
  selectedImage.value = image;
};

const insertImage = () => {
  const { local_path: localPath, s3_url: s3Url } = selectedImage.value;
  emits('update-value', localPath ?? s3Url);
};
</script>

<style scoped lang="scss">
.header-container {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  background-color: #fff;
  padding: 0 16px;
  width: 100%;
  border-bottom: 1px solid #ced4da;

  @media (max-width: $md-display) {
    justify-content: center;
  }

  &__title {
    font-size: 12px;
    font-weight: bold;
    text-transform: lowercase;
    font-weight: 600 !important;
    font-family: 'Inter', sans-serif !important;

    &::first-letter {
      text-transform: uppercase;
    }
  }

  &__nav-container {
    display: flex;
    flex-direction: row;
    justify-content: center;
  }

  &__nav {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 66px;
    padding: 0 8px;
    font-size: 14px;
    margin: 0 8px;
    border-bottom: 3px solid transparent;
    @media (max-width: $md-display) {
      width: max-content;
      height: 50px;
      font-size: 12px;
    }

    &.active {
      border-bottom: 3px solid $base-font-color;
    }

    &:hover {
      cursor: pointer;
    }
  }

  &__close-button {
    text-align: center;
    width: 86px;
    font-size: 14px;
    @media (max-width: $md-display) {
      position: absolute;
      right: 20px;
    }
  }
}

.upload-container {
  height: 100%;

  &__dropzone {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;

    &:hover {
      background: #f9f6f6;
    }
  }

  &__file {
    opacity: 0;
    width: 100%;
    height: calc(80vh - 70px - 90px);
    position: absolute;
    cursor: pointer;
  }

  &__upload {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 216px;
    height: 146px;
    border-radius: 5px;
    border: 3px dotted #677a8b;
    margin: 0 0 32px 0;
  }

  &__icon {
    font-size: 30px;
    color: #677a8b;
    padding: 0 0 16px 0;
  }

  &__label {
    color: #677a8b;
    font-size: 16px;
    font-weight: bold;
  }

  &__text {
    color: #677a8b;
    font-size: 14px;
    padding: 0 0 16px 0;
  }

  &__title {
    margin-top: 0;
    color: grey;
    text-align: center;
    height: 100%;
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;

    .fas {
      font-size: 2rem;
    }

    h3 {
      font-size: 20px;
    }
  }
}
</style>

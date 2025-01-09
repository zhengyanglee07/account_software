<template>
  <picture>
    <source
      :srcset="imageUrl"
      type="image/webp"
      media="(min-width: 320px)"
    >
    <img
      :class="[
        imgClass,
        {
          'pe-none': mode === 'Builder',
        },
      ]"
      :src="imgLink"
      :alt="imgAlt ?? imageUrl"
      loading="lazy"
      :crossOrigin="previewImageSnapshot.isTaking ? 'anonymous' : null"
      :style="imgStyles"
    >
  </picture>
</template>

<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';

const props = defineProps({
  imgLink: {
    type: String,
    default: 'https://cdn.hypershapes.com/assets/placeholder.png',
  },
  imgClass: {
    type: String,
    default: '',
  },
  imgStyles: {
    type: Object,
    default: () => ({}),
  },
  imgAlt: {
    type: String,
    default: null,
  },
  imgContainerWidth: {
    type: Number,
    default: 760,
  },
  isFromImageGallery: {
    type: Boolean,
    default: false,
  },
});

const store = useStore();

const replaceEmptySpace = (str) => {
  if (!str) return str;
  return str.split(' ').join('%20');
};

const mode = computed(() => store.state.builder.mode);
const previewImageSnapshot = computed(
  () => store.state.builder.previewImageSnapshot
);

const appendQueryString = (image) => {
  const { queryString } = previewImageSnapshot.value;
  return image.includes('?')
    ? `${image}&v=${queryString}`
    : `${image}?v=${queryString}`;
};

const imageUrl = computed(() => {
  const defaultImages = [
    'https://cdn.hypershapes.com/assets/placeholder.png',
    'https://cdn.hypershapes.com/assets/product-default-image.png',
  ];

  if (defaultImages.includes(props.imgLink))
    return appendQueryString(props.imgLink);
  const imageLink = (props.imgLink ?? '').endsWith('.gif')
    ? props.imgLink
    : store.getters['builder/responsiveImageUrl']({
        imgUrl: props.imgLink,
        imgContainerWidth: props.imgContainerWidth,
      });
  const image = replaceEmptySpace(imageLink);
  if (mode.value === 'Builder' && !props.isFromImageGallery) {
    return appendQueryString(image);
  }
  return image;
});
</script>

<style scoped lang="scss">
img {
  max-width: 100%;
}

.image-container {
  width: 100%;
  height: auto;
  min-height: 5px;
  min-width: 20px;
}

.image-container-main {
  display: flex;
  height: auto;
  min-height: 40px;
  min-width: 40px;
  align-items: center;
}
/* for image gallery */
.image-gallery-container {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mini-store-site-logo {
  border-radius: 50%;
  width: 70px;
  height: 70px;
  border: 1px solid lightgray;
  object-fit: cover;
}

/* Product */
.product-image-container {
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  margin-left: 0;
  position: relative;

  img {
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    position: absolute;
  }
}

/* Product Image Hover Effect */
.img-hover-zoom {
  overflow: hidden;

  &:hover {
    img {
      transform: scale(1.5);
    }
  }

  img {
    transition: transform 0.5s ease;
  }
}
</style>

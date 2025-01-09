<template>
  <BaseCard>
    <div class="text-center py-20 my-10">
      <h2 class="fs-2x text-capitalize fw-bolder mb-10">
        {{ title }}
      </h2>
      <p class="text-gray-400 fs-5 fw-bold mb-13">
        <span v-if="!customDescription">
          {{
            `There are no ${title} added yet. Kickstart today by adding your first ${title}.`
          }}
        </span>
        <span v-else>{{ customDescription }}</span>
      </p>
      <slot />
    </div>
    <div
      v-if="!noImage"
      class="text-center px-5"
    >
      <img
        src="@shared/assets/media/base-components/2.png"
        alt=""
        class="mw-100 mh-300px"
      >
    </div>
  </BaseCard>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  customDescription: {
    type: String,
    default: '',
  },
  noImage: {
    type: Boolean,
    default: false,
  },
});
</script>

<style></style>

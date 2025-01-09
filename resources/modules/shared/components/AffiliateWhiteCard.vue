<template>
  <div
    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
  >
    <div class="d-flex align-items-center">
      <div
        class="fs-2 fw-bolder"
        data-kt-countup="true"
        data-kt-countup-value="4500"
        data-kt-countup-prefix="$"
      >
        {{ quantity }}
      </div>
    </div>
    <div class="fw-bold fs-6 text-gray-400">
      {{ text_title }}
    </div>
  </div>
</template>

<script>
export default {
  props: ['quantity', 'text_title', 'image_url'],
};
</script>

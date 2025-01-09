<template>
  <!-- <div class="affiliate-card-content">
    <div
      class="title_bar"
      style="margin-bottom: 10px"
    >
      <span class="affiliate_text_title p-two">{{ text_title }}</span>
    </div>
    <span class="affiliate_card-title h-two">{{ quantity }}</span>
  </div> -->

  <div class="card statistic-card card-xl-stretch">
    <div class="card-body">
      <div class="text-gray-800 fw-bolder fs-3.5 mb-2">
        {{ text_title }}
      </div>
      <div class="fw-bold text-gray-800">
        {{ quantity }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AffiliateCard',
  props: ['quantity', 'text_title', 'image_url'],
};
</script>

<style scoped lang="scss">
.statistic-card {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
}
</style>

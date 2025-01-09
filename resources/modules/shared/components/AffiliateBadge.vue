<template>
  <a
    :href="`https://hypershapes.com?affiliate_id=${affiliateId}`"
    style="position: fixed; right: 0; z-index: 2147483644"
    :class="{
      'affiliate-badge--mobile':
        $page.url.startsWith('/products') &&
        !$page.url.startsWith('/products/all'),
    }"
    target="_blank"
  >
    <img
      src="https://media.hypershapes.com/images/web-affiliate-badge.png"
      alt="Sign Up Hypershapes today"
      width="210"
    >
  </a>
</template>

<script setup>
defineProps({
  affiliateId: {
    type: String,
    default: '',
  },
});
</script>

<style scoped>
a {
  bottom: 0;
}

@media (max-width: 575px) {
  .affiliate-badge--mobile {
    bottom: 70px;
  }
}
</style>

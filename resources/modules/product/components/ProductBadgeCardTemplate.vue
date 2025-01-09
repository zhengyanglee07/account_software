<template>
  <div class="general-card">
    <div class="general-card__body">
      <div class="badge-card__title h-five">
        {{ cardTitle }}
      </div>
      <div class="general-card-content-container">
        <div class="w-100">
          <slot name="content" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductBadgeCardTemplate',
  props: ['cardTitle'],
};
</script>

<style lang="scss" scoped>

.general-card {
  @media (max-width: $lg-display) {
    margin-top: 0 !important;
  }
}
</style>

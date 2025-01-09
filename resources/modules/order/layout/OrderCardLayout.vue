<template>
  <div class="general-card mt-0">
    <div class="general-card__body">
      <div
        class="general-card__section-title"
        :class="customClass"
      >
        {{ cardTitle }}
      </div>
      <slot name="body" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrderCard',
  props: ['custom-class', 'card-title'],
};
</script>

<style lang="scss" scoped>

.general-card__body-row {
  padding: 0 20px;
  margin-bottom: 20px !important;
  @media (max-width: $md-display) {
    padding: 0 $mobile-align-left-padding;
  }
}
</style>

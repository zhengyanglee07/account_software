<template>
  <div class="row sub-filter-container">
    <slot />
  </div>
</template>

<script>
// simple container for condition filter (other than first select)
export default {
  name: 'ConditionFilterContainer',
};
</script>

<style lang="scss" scoped>

.sub-filter-container {
  // max-width: 95%;
  // width: auto;
  width: 76%;

  @media (max-width: $lg-display) {
    width: 95%;

    .pfilter__elem--max-content .dropdown-input.people-dropdown-col,
    .pfilter__elem--max-content :deep(input) {
      border-radius: 2.5px !important;
    }
  }
}
</style>

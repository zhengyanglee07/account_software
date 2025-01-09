<template>
  <div class="row mt-2">
    <div
      class="col-md-4 pt-1"
      :class="{
        'col-md-12 px-0': isOnboarding,
      }"
    >
      <h3
        v-if="title"
        class="text-capitalize mb-4"
        :class="{
          'ps-3': isOnboarding,
        }"
      >
        {{ title }}
      </h3>
      <div class="fs-6 text-gray-600 pb-5 d-block">
        <!-- @slot To put the description of the setting section -->
        <slot name="description" />
      </div>
    </div>

    <div
      class="col-md-8"
      :class="{
        'col-md-12 px-0': isOnboarding,
      }"
    >
      <template v-if="isDatatableOnlyInContent">
        <slot name="content" />
      </template>
      <BaseCard
        v-else
        has-footer
        :no-body-margin="isOnboarding"
      >
        <!-- @slot To put the content inside the card at right container -->
        <slot name="content" />
        <template #footer>
          <!-- @slot To put the action button at the footer of right container card -->
          <slot name="footer" />
        </template>
      </BaseCard>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  /**
   * The title of the setting section
   */
  title: {
    type: String,
    default: null,
  },
  /**
   * Set this to  `true` if the setting content only contains datatable to remove extra spacing on normal card wrapper
   */
  isDatatableOnlyInContent: {
    type: Boolean,
    default: false,
  },
  /**
   * Set this to  `true` if the setting is in onboarding
   */
  isOnboarding: {
    type: Boolean,
    default: false,
  },
});
</script>

<style lang="scss" scoped>
@media (max-width: 480px) {
  .row {
    padding: 10px 0;
  }
}
</style>

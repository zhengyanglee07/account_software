<template>
  <ul
    class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-5"
  >
    <li
      v-for="(tab, $tabIndex) in tabs"
      :key="tab.target"
      class="nav-item"
    >
      <a
        class="nav-link"
        data-bs-toggle="tab"
        :href="`#${tab.target}`"
        :class="{
          active: $tabIndex === defaultIndex,
        }"
        :aria-selected="$tabIndex === defaultIndex"
        @click="$emit('click', tab.target)"
      >
        {{ tab.label }}
      </a>
    </li>
  </ul>

  <div
    id="myTabContent"
    class="tab-content"
  >
    <div
      v-for="(tab, $tabContentIndex) in tabs"
      :id="tab.target"
      :key="tab.target"
      class="tab-pane fade"
      role="tabpanel"
      :class="{
        'show active': $tabContentIndex === defaultIndex,
      }"
    >
      <!-- @slot Put the content of each tab here. When a tab is clicked, the content inside slot of `#tab-{clicked tab's target}` will be displayed. The first tab's content is displayed by default -->
      <slot :name="`tab-${tab.target}`" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  /**
   * An array of `label:target` pairs.
   * Label will be used as the display text for the tab, and
   * target will be used to toggle the tab content according to the slot.
   */
  tabs: {
    type: Array,
    required: true,
  },
  /**
   * To determine default selected tab
   */
  defaultIndex: {
    type: Number,
    default: 0,
  },
});

defineEmits([
  /**
   * Emitted when a tab is clicked.
   * `Payload: tab's target`
   */
  'click',
]);
</script>

<style></style>

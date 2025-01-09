<template>
  <div
    class="card card-flush"
    :class="[
      noCardBottomMargin ? 'mb-0' : 'mb-6',
      isDatatable ? 'py-0' : 'py-3',
      {
        'pb-0': hasFooter,
      },
    ]"
  >
    <div
      v-if="hasHeader"
      class="card-header"
      :class="isDatatable ? 'px-6 pt-4' : 'p-3 pb-0 mx-5'"
    >
      <div
        v-if="title"
        class="card-title"
      >
        <h3>{{ title }}</h3>
      </div>
      <!-- @slot To customize the header of card. Card title will be hidden -->
      <slot name="header" />

      <div
        v-if="hasToolbar"
        class="card-toolbar"
        :style="{ flex: !toolbarAtEnd ? 'auto' : '' }"
      >
        <!-- @slot For the content at the top right of card (usually button) -->
        <slot name="toolbar" />
      </div>
    </div>
    <div
      class="card-body row p-3 px-0"
      :class="noBodyMargin ? 'mx-0' : 'mx-5'"
    >
      <!-- @slot For the content of card body -->
      <slot />
    </div>
    <div
      v-if="hasFooter"
      class="card-footer d-flex justify-content-end p-6 pt-0"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    /**
     * To determine whether the card has header section
     */
    hasHeader: {
      type: Boolean,
      default: false,
    },
    /**
     * To determine whether the card has toolbar section (usually for button)
     */
    hasToolbar: {
      type: Boolean,
      default: false,
    },
    /**
     * To determine whether the card has footer section
     */
    hasFooter: {
      type: Boolean,
      default: false,
    },
    /**
     * To set the title of the card
     */
    title: {
      type: String,
      default: null,
    },
    /**
     * Set this to true if you don't want margin on body
     */
    noBodyMargin: {
      type: Boolean,
      default: false,
    },
    /**
     * Set this to true if you don't want margin on bottom card
     */
    noCardBottomMargin: {
      type: Boolean,
      default: false,
    },
    /**
     * Adjust styles if the card is used as datatable wrapper
     */
    isDatatable: {
      type: Boolean,
      default: false,
    },
    /**
     * Adjust toolbar place
     */
    toolbarAtEnd: {
      type: Boolean,
      default: true,
    },
  },
};
</script>

<style scoped>
.card .card-header {
  min-height: 50px;
}
</style>

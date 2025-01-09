<template>
  <div
    class="form-group"
    :class="[`col-${col}`, { 'mb-6': !noMargin }]"
  >
    <div
      v-if="label || $slots['label-row-end']"
      class="d-flex justify-content-between align-items-center mb-2"
    >
      <label  
        class="form-label d-block mb-0"
        :class="{ required: required }"
        :for="labelFor"
        :description="description"
      >
        {{ label }}
      </label>
      <slot name="label-row-end" />
    </div>

    <slot />

    <div
      v-if="!errorMessage && !$slots['error-message'] && description"
      class="text-muted fs-7 mt-1"
    >
      {{ description }}
    </div>

    <div class="text-danger">
      <template v-if="$slots['error-message']">
        <slot name="error-message" />
      </template>
      <template v-else>
        {{ errorMessage }}
      </template>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: null,
    },
    labelFor: {
      type: String,
      default: null,
    },
    required: {
      type: Boolean,
      default: false,
    },
    description: {
      type: String,
      default: null,
    },
    col: {
      type: [Number, String],
      default: 12,
    },
    errorMessage: {
      type: String,
      default: null,
    },
    noMargin: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style></style>

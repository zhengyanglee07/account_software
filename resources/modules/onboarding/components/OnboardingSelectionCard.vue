<template>
  <div
    class="btn btn-outline btn-outline-default p-7 d-flex align-items-center mb-10 position-relative"
    :for="id"
    :class="{
      active: isActive,
      'btn-outline-dashed': hoverEffect,
    }"
    :style="{
      cursor: cursor,
      '--display': vertical ? 'inline-flex' : 'inline-block',
    }"
    @click="$emit('click')"
  >
    <div
      v-if="channel === 'funnel'"
      class="popular-badge-wrap"
    >
      <span class="popular-badge h-five">Most Popular</span>
    </div>

    <div class="image-container me-5 py-4">
      <!-- slot for image -->
      <slot name="image" />
    </div>
    <div
      class="d-block fw-bold text-start"
      :style="{ '--padding': vertical ? '5px' : '0px' }"
    >
      <!-- slot for text label -->
      <div
        class="text-dark fw-bolder d-block fs-4 mb-2"
        :style="{
          '--align': vertical ? 'left' : 'center',
          '--margin': vertical ? '0 0 10px' : '10px 0',
        }"
      >
        <slot name="title" />
      </div>
      <!-- slot for description -->
      <div
        class="text-muted fw-bold fs-6"
        :style="{
          '--align': vertical ? 'left' : 'center',
          '--padding': vertical ? '5px' : '0px',
        }"
      >
        <slot name="description" />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: null,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  hoverEffect: {
    type: Boolean,
    default: true,
  },
  cursor: {
    type: String,
    default: () => 'pointer',
  },
  vertical: {
    type: Boolean,
    default: () => false,
  },
  channel: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['click']);
</script>

<style lang="scss" scoped>
.btn-check {
  position: absolute;
  clip: rect(0, 0, 0, 0);
  pointer-events: none;
}
.active {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
}

@media (max-width: 450px) {
  .d-block {
    padding-left: var(--padding) !important;
  }
  .btn {
    display: var(--display) !important;
  }

  .text-dark {
    text-align: var(--align) !important;
    margin: var(--margin);
  }

  .text-muted {
    text-align: var(--align) !important;
    padding-left: var(--padding) !important;
  }

  .image-container {
    margin-right: 0 !important;
  }
}

.popular-badge-wrap {
  position: absolute;
  width: 100%;
  height: 188px;
  top: -9px;
  left: 9px;
  overflow: hidden;

  &::before,
  &::after {
    content: '';
    position: absolute;
  }

  &::before {
    width: 40px;
    height: 8px;
    right: 80px;
    background: #400675;
    border-radius: 8px 8px 0px 0px;
  }

  &::after {
    width: 8px;
    height: 40px;
    right: 0px;
    top: 80px;
    background: #400675;
    border-radius: 0px 8px 8px 0px;
  }
}

.popular-badge {
  width: 170px;
  height: 35px;
  line-height: 40px;
  position: absolute;
  top: 30px;
  right: -50px;
  z-index: 2;
  overflow: hidden;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
  border: 1px dashed white;
  box-shadow: 0 0 0 3px, $h-primary 0px 21px 5px -18px rgba(0, 0, 0, 0.6);
  background: $h-primary;
  text-align: center;
  color: white;
  font-size: 12px !important;
  padding-right: 20px;
}
</style>

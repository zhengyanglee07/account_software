<template>
  <button
    class="btn p-0 close-popup"
    type="button"
    @click="emit('click')"
  >
    <i
      class="fa-solid fa-xmark p-0"
      :style="{
        color,
      }"
    />
  </button>
</template>

<script setup>
defineProps({
  color: {
    type: String,
    default: '#000',
  },
});

const emit = defineEmits(['click']);
</script>

<style lang="scss" scoped>
.close-popup {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 999;

  i {
    font-size: 22px;
    vertical-align: baseline;
  }
}
</style>

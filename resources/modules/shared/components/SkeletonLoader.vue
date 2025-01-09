<template>
  <div
    v-if="props.type === 'cart-item'"
    class="container"
  >
    <img class="avatar skeleton">
    <div class="skeleton skeleton-text w-50" />
  </div>

  <div v-if="props.type.includes('list-item')">
    <div
      v-for="i in parseInt(props.type.match(/\d+/)[0])"
      :key="i"
      class="skeleton skeleton-text"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: null,
  },
});
</script>

<style scoped>
.skeleton {
  opacity: 0.7;
  animation: skeleton-loading 1s linear infinite alternate;
}

.skeleton-text {
  width: 70%;
  height: 1.25rem;
  margin-bottom: 0.5rem;
  border-radius: 25px;
}
@keyframes skeleton-loading {
  0% {
    background-color: hsl(200, 20%, 70%);
  }

  100% {
    background-color: hsl(200, 20%, 95%);
  }
}

.container {
  margin: 0;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
}

.avatar {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 100%;
  margin-right: 1rem;
  flex-shrink: 0;
}
</style>

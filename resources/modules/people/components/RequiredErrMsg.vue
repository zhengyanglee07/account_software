<template>
  <span
    v-if="showError"
    class="people-error"
  > Field is required </span>
</template>

<script>
export default {
  name: 'RequiredErrMsg',
  props: {
    showError: Boolean,
  },
};
</script>

<style scoped lang="scss">
</style>

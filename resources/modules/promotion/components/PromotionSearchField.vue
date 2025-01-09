<template>
  <div class="row">
    <div
      class="col-md-12 mb-3"
      style="padding: 0 !important"
    >
      <div class="form-group-prepend">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">
              <i class="fas fa-search gray_icon" />
            </span>
          </div>
          <input
            ref="search"
            v-model="searchInput"
            type="text"
            class="form-control firstInput"
            :placeholder="placetext"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchField',
  props: ['placetext'],
  data() {
    return {
      searchInput: '',
    };
  },
  watch: {
    searchInput(newValue) {
      this.$emit('searchInput', newValue);
    },
  },
};
</script>

<style scoped lang="scss">
</style>

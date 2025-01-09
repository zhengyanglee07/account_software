<template>
  <VueJqModal :modal-id="modalId">
    <template #title>
      Rename Tag
    </template>

    <template #body>
      <div class="form-group d-flex justify-content-center align-items-center">
        <label
          for="add-new-tag-input"
          class="mb-0 me-2"
        >Tag name</label>
        <div class="position-relative">
          <input
            id="add-new-tag-input"
            v-model="tagName"
            type="text"
            class="form-control"
            :class="{
              'error-border': showErr && v$.tagName.$invalid,
            }"
            @input="clearError"
          >
          <span
            v-if="showErr && !v$.tagName.required"
            class="people-error tag-error"
          >
            Field is required
          </span>
        </div>
      </div>
    </template>

    <template #footer>
      <button
        type="button"
        class="cancel-button"
        data-bs-dismiss="modal"
      >
        Cancel
      </button>
      <button
        class="primary-small-square-button"
        @click="renameTag"
      >
        Save
      </button>
    </template>
  </VueJqModal>
</template>

<script>
import { required } from '@vuelidate/validators';
import VueJqModal from '@shared/components/VueJqModal.vue';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'PeopleTagsRenameTagModal',
  components: {
    VueJqModal,
  },
  props: {
    modalId: String,
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      tagName: '',
      showErr: false,
    };
  },
  validations: {
    tagName: {
      required,
    },
  },
  methods: {
    clearError() {
      this.showErr = false;
    },
    renameTag() {
      this.clearError();

      if (this.v$.$invalid) {
        this.showErr = true;
        return;
      }

      this.$emit('rename-tag', this.tagName);
    },
  },
};
</script>

<style scoped lang="scss">

.tag-error {
  position: absolute;
  bottom: -1.6rem;
}
</style>

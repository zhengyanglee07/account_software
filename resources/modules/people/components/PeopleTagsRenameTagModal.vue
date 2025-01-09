<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Rename Tag"
  >
    <BaseFormGroup
      for="add-new-tag-input"
      label="Tag name"
    >
      <BaseFormInput
        id="add-new-tag-input"
        v-model="tagName"
        type="text"
        @input="clearError"
      />

      <template
        v-if="showErr && v$.tagName.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="renameTag">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import eventBus from '@services/eventBus.js';

export default {
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
  mounted() {
    eventBus.$on('get-tag-name', (tagName) => {
      this.tagName = tagName;
      const modal = document.getElementById(this.modalId);
      modal?.addEventListener('hidden.bs.modal', () => {
        this.tagName = tagName;
        this.showErr = false;
      });
    });
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

<style scoped lang="scss"></style>

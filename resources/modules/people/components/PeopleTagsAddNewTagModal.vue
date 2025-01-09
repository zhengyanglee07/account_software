<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Add Tag"
  >
    <BaseFormGroup
      for="add-new-tag-input"
      label="Enter a tag name"
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
      <BaseButton @click="addNewTag">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
/* eslint-disable no-unused-expressions */
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { Modal } from 'bootstrap';
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
    eventBus.$on('base-modal-mounted', () => {
      const modal = document.getElementById(this.modalId);
      modal?.addEventListener('hidden.bs.modal', () => {
        this.tagName = '';
        this.showErr = false;
      });
    });
  },

  methods: {
    clearError() {
      this.showErr = false;
    },
    addNewTag() {
      this.clearError();

      if (this.v$.$invalid) {
        this.showErr = true;
        return;
      }

      const { tagName } = this;
      this.$axios
        .post('/newtag', {
          newTag: tagName,
        })
        .then(({ data: { message, tags } }) => {
          if (!message) {
            this.$emit('update-tags', tags);

            this.$toast.success('Success', 'Successfully created new tag');
            this.tagName = '';
            Modal.getInstance(document.getElementById(this.modalId)).hide();
            return;
          }

          this.$toast.error('Error', message);
        })
        .catch((err) => {
          console.error(err);

          this.$toast.error('Error', 'Failed to add tag');
        });
    },

    handleAddTagModalHidden() {
      this.tagName = '';
    },
  },
};
</script>

<style scoped lang="scss"></style>

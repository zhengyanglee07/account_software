<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Remove Tag"
  >
    <BaseFormGroup
      for="removeTagOption"
      label="Tag Name"
    >
      <BaseFormSelect
        id="removeTagOption"
        v-model="selectedTag"
        @input="errMessage = ''"
      >
        <option
          value=""
          disabled
          selected
        >
          Select a tag
        </option>
        <option
          v-for="tag in tags"
          :key="tag.tagId"
          :value="tag.tagId"
        >
          {{ tag.tagName }}
        </option>
      </BaseFormSelect>
      <template
        v-if="emptyCheckedContacts"
        #error-message
      >
        {{ errMessage }}
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="removingTag"
        @click="handleRemoveTag"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { Modal } from 'bootstrap';

export default {
  props: {
    modalId: String,
  },
  data() {
    return {
      selectedTag: '',
      errMessage: '',
      removingTag: false,
    };
  },
  computed: {
    ...mapState('people', ['tags', 'checkedContactIds', 'contacts']),
    emptyCheckedContacts() {
      return this.checkedContactIds.length === 0;
    },
  },
  methods: {
    ...mapMutations('people', ['clearCheckedContactIds']),
    handleRemoveTag(e) {
      // e.preventDefault();
      this.errMessage = '';

      if (!this.selectedTag) {
        this.errMessage = 'Please select a tag';
        return;
      }

      if (this.emptyCheckedContacts) {
        this.errMessage = 'Please select at least one people';
        return;
      }

      this.removingTag = true;
      this.$axios
        .post('/remove/tag', {
          contact: this.checkedContactIds,
          selectedTag: this.selectedTag,
        })
        .then(() => {
          Modal.getInstance(document.getElementById('removeTagModal')).hide();
          this.clearCheckedContactIds();
          this.$toast.success(
            'Success',
            'Successfully removed tag from contact(s) selected.'
          );
        })
        .catch((err) => {
          console.log(err);

          this.$toast.error('Error', 'Failed to remove tag');
        })
        .finally(() => {
          this.removingTag = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

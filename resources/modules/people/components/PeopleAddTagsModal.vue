<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Add Tag"
  >
    <BaseFormGroup
      for="addTagOption"
      label="Enter a tag name"
    >
      <BaseFormSelect
        id="addTagOption"
        v-model="selectedTagId"
        @input="errMessage = ''"
      >
        <!-- <option value="" disabled selected> -- select a tag -- </option> -->
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
        :disabled="addingTag"
        @click="handleAddTags"
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
      selectedTagId: '',
      errMessage: '',
      addingTag: false,
    };
  },
  computed: {
    ...mapState('people', ['tags', 'contacts', 'checkedContactIds']),
    emptyCheckedContacts() {
      return this.checkedContactIds.length === 0;
    },
  },
  methods: {
    ...mapMutations('people', ['clearCheckedContactIds']),
    handleAddTags() {
      this.errMessage = '';

      if (!this.selectedTagId) {
        this.errMessage = 'Please select a tag';
        return;
      }

      if (this.emptyCheckedContacts) {
        this.errMessage = 'Please select at least one people';
        return;
      }

      this.addingTag = true;
      this.$axios
        .post('/add/tag', {
          contact: this.checkedContactIds,
          selectedTag: this.selectedTagId,
        })
        .then(() => {
          Modal.getInstance(document.getElementById('addTagModal')).hide();
          this.clearCheckedContactIds();
          this.$toast.success('Success', 'Successfully added tag.');
        })
        .catch((err) => {
          console.log(err);

          this.$toast.error('Failed', 'Failed to add the tag.');
        })
        .finally(() => {
          this.addingTag = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

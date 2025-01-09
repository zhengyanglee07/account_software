<template>
  <BaseDatatable
    no-hover
    title="Tags"
    no-edit-action
    :table-headers="tableHeaders"
    :table-datas="tableTagsData"
    :empty-state="emptyState"
    @delete="removeRecord"
  >
    <template #action-button>
      <BaseButton
        id="add-tag-button"
        has-add-icon
        @click="toggleAddTag"
      >
        Add Tag
      </BaseButton>
    </template>

    <template #action-options="{ row: { id } }">
      <BaseDropdownOption
        text="Edit"
        data-bs-toggle="modal"
        :data-bs-target="`#${renameTagModalId}`"
        @click="tagIdToRename = id"
      />
    </template>
  </BaseDatatable>

  <PeopleTagsAddNewTagModal
    modal-id="tags-add-new-tag-modal"
    @update-tags="handleUpdateTags"
  />
  <PeopleTagsRenameTagModal
    :modal-id="renameTagModalId"
    @rename-tag="handleRenameTag"
  />
</template>

<script>
import PeopleTagsAddNewTagModal from '@people/components/PeopleTagsAddNewTagModal.vue';
import PeopleTagsRenameTagModal from '@people/components/PeopleTagsRenameTagModal.vue';
import { Modal } from 'bootstrap';
import eventBus from '@services/eventBus.js';

export default {
  name: 'Tags',
  components: {
    PeopleTagsRenameTagModal,
    PeopleTagsAddNewTagModal,
  },
  props: {
    dbTags: Array,
    isAddTag: Boolean,
  },
  data() {
    return {
      tags: [],
      showLimitModal: false,
      tableHeaders: [
        /**
         * @param name : column header title
         * @param key : data column name in table
         * @param order : order of column data, default => 0
         * @param width : column width in px, default => auto
         */
        { name: 'Name', key: 'tagName', order: 0 },
        {
          name: 'People',
          key: 'people',
        },
      ],

      tagIdToRename: '',
      renameTagModalId: 'tags-rename-tag-modal',
      modalId: 'people-tag-delete-modal',
      selectedId: '', // for scopedid
      emptyState: {
        title: 'tag',
        description: 'tags',
      },
    };
  },
  computed: {
    tableTagsData() {
      return this.tags.map((tag) => ({
        id: tag.tagId,
        tagName: tag.tagName,
        people: tag.people,
      }));
    },
  },

  watch: {
    tagIdToRename(id) {
      const tag = this.tableTagsData.find((e) => e.id === id);
      eventBus.$emit('get-tag-name', tag.tagName);
    },
  },
  beforeMount() {
    this.tags = [...this.dbTags];
  },

  methods: {
    handleUpdateTags(tags) {
      this.tags = tags;
    },
    handleRenameTag(tagName) {
      const tagId = this.tagIdToRename;

      this.$axios
        .post('/rename/tag', {
          oldTagId: tagId,
          renameTag: tagName,
        })
        .then(({ data: { message, newtags } }) => {
          if (!message) {
            this.handleUpdateTags(newtags);
            this.$toast.success('Success', 'Successfully rename tag.');
            Modal.getInstance(
              document.getElementById(this.renameTagModalId)
            ).hide();
            return;
          }

          this.$toast.error(
            'Failed',
            'Name has been used. Please try another name.'
          );
        });
    },

    removeRecord(id) {
      this.$axios
        .post('/delete/tag', {
          oldTagId: id,
        })
        .then(({ data }) => {
          this.tags = data.tags;
          this.$toast.success('Success', 'Successfully deleted tag.');
        })
        .catch((e) => console.log(e));
    },

    toggleAddTag() {
      this.tags_add_new_modal = new Modal(
        document.getElementById('tags-add-new-tag-modal')
      );
      this.tags_add_new_modal.show();
    },
  },
};
</script>

<style lang="scss" scoped></style>

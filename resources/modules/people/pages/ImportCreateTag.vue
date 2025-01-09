<template>
  <BaseCard
    title="Organize your imported people"
    has-header
    has-footer
  >
    <p><b>Successful:</b> {{ successfulImports }}</p>
    <p><b>New Contacts:</b> {{ newContacts }}</p>
    <p><b>Update People Profile:</b> {{ updatePeopleProfile }}</p>
    <p><b>Failed:</b> {{ failedImports }}</p>
    <p><b>Skipped:</b> {{ skippedImports }}</p>
    <BaseFormGroup
      label="Tag all people"
      col="md-4"
    >
      <BaseMultiSelect
        v-model="selectedTagNames"
        multiple
        push-tags
        taggable
        :options="tagNames"
        @input="resetIsSelectedTagNamesEmpty"
      />

      <template
        v-if="isSelectedTagNamesEmpty"
        #error-message
      >
        Please select at least one tag to save
      </template>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        type="link"
        href="/people"
        class="me-3"
        @click="save"
      >
        Skip Action
      </BaseButton>
      <BaseButton
        :disabled="savingTags"
        @click="save"
      >
        Save Tag
      </BaseButton>
    </template>
  </BaseCard>
</template>

<script>
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  name: 'ImportCreateTag',
  props: {
    tags: {
      type: Array,
      default: () => [],
    },
    successfulImports: {
      type: Number,
      required: true,
    },
    newContacts: {
      type: Number,
      required: true,
    },
    updatePeopleProfile: {
      type: Number,
      required: true,
    },
    failedImports: {
      type: Number,
      required: true,
    },
    skippedImports: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      selectedTagNames: [],

      savingTags: false,
      isSelectedTagNamesEmpty: false,
    };
  },
  computed: {
    tagNames() {
      return this.tags.map((t) => t.tagName);
    },
  },
  methods: {
    resetIsSelectedTagNamesEmpty() {
      this.isSelectedTagNamesEmpty = false;
    },

    save() {
      this.isSelectedTagNamesEmpty = this.selectedTagNames.length === 0;
      if (this.isSelectedTagNamesEmpty) {
        return;
      }

      this.savingTags = true;

      this.$axios
        .post('/saveImportTag', {
          tagNames: this.selectedTagNames,
        })
        .then(() => {
          this.$inertia.visit('/people');
        })
        .catch((err) => {
          if (err.response.status !== 422) {
            this.$toast.error(
              'Failed.',
              `Unexpected error occurs. ${err.response.data?.message}`
            );
          }

          validationFailedNotification(err);
        })
        .finally(() => {
          this.savingTags = false;
        });
    },

    // skip() {
    //   this.$inertia.visit('/people');
    // },
  },
};
</script>

<style scoped lang="scss"></style>

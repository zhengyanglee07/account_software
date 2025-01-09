<template>
  <div class="mt-5">
    <BaseFormGroup label="Which tag?">
      <template #label-row-end>
        <BaseButton
          type="link"
          @click="refreshTag"
        >
          {{ refreshStateLib[refreshState].text }}
          <i
            :class="refreshStateLib[refreshState].iconClass"
            style="color: inherit"
          />
        </BaseButton>
      </template>
      <BaseMultiSelect
        v-model="selectedOption"
        label="tagName"
        push-tags
        :options="options"
        placeholder="Select Tag"
        :reduce="reducer"
        @option:selected="optionInput"
      >
        <template #no-options="{ search, searching }">
          <template v-if="searching">
            <div
              type="button"
              @click="createTag(search)"
            >
              <i class="fas fa-plus w-auto" />
              Add {{ search }}
            </div>
          </template>
        </template>
      </BaseMultiSelect>
      <BaseButton
        type="link"
        href="/people/tags"
        is-open-in-new-tab
      >
        Manage tag
        <i
          class="ms-2 fa-solid fa-up-right-from-square"
          style="color: inherit"
        />
      </BaseButton>
    </BaseFormGroup>
  </div>
</template>

<script>
import axios from 'axios';
import { mapState, mapMutations } from 'vuex';
import { transformTags } from '@automation/lib/automations.js';

export default {
  name: 'ActionStepTagSelector',
  props: ['modelValue'],
  emits: ['update:modelValue'],

  data() {
    return {
      refreshState: 'initial',
      refreshStateLib: {
        initial: {
          text: 'Refresh',
          iconClass: 'ms-2 fa-solid fa-rotate-right',
        },
        fetching: {
          text: 'Fetching',
          iconClass: 'ms-2 fa-solid fa-spinner fa-spin-pulse',
        },
        updated: {
          text: 'Updated',
          iconClass: 'ms-2 fa-solid fa-circle-check text-success',
        },
      },
    };
  },
  computed: {
    ...mapState('automations', {
      options: (state) => state.tags,
    }),
    selectedOption: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      },
    },
  },

  methods: {
    ...mapMutations('automations', ['updateTags']),

    // track only tag id
    reducer(tag) {
      return tag?.processed_tag_id;
    },

    createTag(name) {
      axios.post('/newtag', { newTag: name }).then(({ data }) => {
        this.updateTags({ tags: transformTags(data.tags) });
        const selectedTag = data.tags.find((e) => e.tagName === name);
        this.selectedOption = selectedTag.tagId;
      });
    },
    refreshTag() {
      this.refreshState = 'fetching';
      axios
        .post('/refresh/option')
        .then(({ data }) => {
          this.updateTags({ tags: transformTags(data.tags) });
        })
        .finally(() => {
          this.refreshState = 'updated';
          setTimeout(() => {
            this.refreshState = 'initial';
          }, 2000);
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

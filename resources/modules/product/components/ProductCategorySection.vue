<template>
  <BaseCard
    has-header
    title="Categories"
  >
    <BaseFormGroup>
      <BaseMultiSelect
        v-model="defaultCategory"
        push-tags
        multiple
        :options="availability"
        placeholder="Add Categories"
        label="name"
        @input="addCategory"
      >
        <template #no-options="{ search, searching }">
          <template v-if="searching">
            <div
              type="button"
              @click="createCategory(search)"
            >
              <i class="fas fa-plus w-auto" />
              Add {{ search }}
            </div>
          </template>
        </template>
      </BaseMultiSelect>
      <BaseBadge
        v-for="(selected, index) in selectedCategories"
        :key="index"
        :text="selected?.name"
        has-delete-button
        @click="removeTag(selected.id)"
      />
    </BaseFormGroup>
  </BaseCard>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';

export default {
  props: ['allCategories', 'selectedCategories'],

  data() {
    return {
      defaultCategory: null,
      categories: [],
    };
  },

  computed: {
    parsedCategories() {
      return this.categories;
    },
    availability() {
      const selected = this.selectedCategories?.map((e) => e?.name);
      const options = this.parsedCategories?.filter(
        (item) => !selected?.includes(item?.name)
      );
      return options;
    },
  },

  mounted() {
    this.categories = cloneDeep(this.allCategories);
  },

  methods: {
    addCategory(e) {
      this.defaultCategory = null;
      this.$emit('addCategory', e.pop());
    },
    createCategory(eventName) {
      axios
        .post('/product/category/add', {
          name: eventName,
        })
        .then(({ data }) => {
          if (data.status.includes('Error'))
            return this.$toast.error('Error', 'Category Name Exist!');
          this.categories = data.categories;
          this.addCategory(data.category);
          const { searchEl } = this.$refs.vSelect;
          if (searchEl) searchEl.blur();
          return this.$toast.success('Success', 'Category Added!');
        });
    },
    removeTag(id) {
      this.$emit('removeCategory', id);
    },
  },
};
</script>

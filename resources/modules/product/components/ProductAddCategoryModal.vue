<template>
  <BaseModal
    title="Add Category"
    :modal-id="modalId"
  >
    <BaseFormGroup
      label="Select a Category"
      :error-message="errMessage"
    >
      <BaseFormSelect v-model="selectedCategoryId">
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
        >
          {{ category.name }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="addingCategory"
        @click="handleAddCategories"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
// import { mapState, mapMutations } from 'vuex';
import { Modal } from 'bootstrap';

export default {
  props: {
    modalId: String,
    categories: Array,
    checkedProductIds: Array,
  },
  data() {
    return {
      selectedCategoryId: '',
      errMessage: '',
      addingCategory: false,
    };
  },
  computed: {
    // ...mapState('people', ['tags', 'contacts', 'checkedContactIds']),
    emptyCheckedProducts() {
      return this.checkedProductIds?.length === 0;
    },
  },

  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);
      modalEl.addEventListener('show.bs.modal', () => {
        this.selectedCategoryId = '';
        this.errMessage = '';
      });
    }, 1000);
  },

  methods: {
    // ...mapMutations('people', ['clearCheckedContactIds']),
    handleAddCategories() {
      this.errMessage = '';
      if (!this.selectedCategoryId) {
        this.errMessage = 'Please select a category';
        return;
      }

      if (this.emptyCheckedProducts) {
        this.errMessage = 'Please select at least one product';
        return;
      }

      this.addingCategory = true;
      this.$axios
        .post('/bulk-products/category/add', {
          productIds: this.checkedProductIds,
          categoryId: this.selectedCategoryId,
        })
        .then(() => {
          Modal.getInstance(
            document.getElementById('product-add-category-modal')
          ).hide();
          this.selectedCategoryId = '';
          this.$emit('update');
          this.$toast.success('Success', 'Successfully added category.');
        })
        .catch((err) => {
          console.log(err);
          this.$toast.error('Failed', 'Failed to add the category.');
        })
        .finally(() => {
          this.addingCategory = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

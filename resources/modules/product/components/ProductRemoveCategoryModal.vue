<template>
  <BaseModal
    title="Remove Category"
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
        :disabled="removingCategory"
        @click="handleRemoveCategories"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
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
      removingCategory: false,
      inputOptionId: [],
    };
  },
  computed: {
    emptyCheckedProducts() {
      return this.checkedProductIds.length === 0;
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
    handleRemoveCategories() {
      this.errMessage = '';

      if (!this.selectedCategoryId) {
        this.errMessage = 'Please select a category';
        return;
      }

      if (this.emptyCheckedProducts) {
        this.errMessage = 'Please select at least one product';
        return;
      }

      this.removingCategory = true;
      this.$axios
        .post('/bulk-products/category/remove', {
          productIds: this.checkedProductIds,
          categoryId: this.selectedCategoryId,
        })
        .then(() => {
          Modal.getInstance(
            document.getElementById('product-remove-category-modal')
          ).hide();
          this.selectedCategoryId = '';
          this.$emit('update');
          this.$toast.success('Success', 'Successfully removed category.');
        })
        .catch((err) => {
          console.log(err);

          this.$toast.error('Failed', 'Failed to remove the category.');
        })
        .finally(() => {
          this.removingCategory = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

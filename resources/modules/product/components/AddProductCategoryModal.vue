<template>
  <BaseModal
    :small="true"
    :modal-id="modalId"
    :title="title"
  >
    <BaseFormGroup
      label="Category Name"
      :error-message="errorMessage"
    >
      <BaseFormInput
        id="add-category"
        v-model="category"
        type="text"
        @input="clearError"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="modifyCategory">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { Modal } from 'bootstrap';
/* eslint-disable no-unused-expressions */
/* eslint consistent-return: 1 */

export default {
  props: {
    modalId: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: '',
    },
    data: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      category: '',
      selectedId: null,
      errorMessage: '',
      title: 'Add New Category',
    };
  },

  watch: {
    type(newValue) {
      if (newValue === 'add') {
        this.title = 'Add New Category';
        this.category = '';
        this.selectedId = null;
      } else this.title = 'Edit Category';
    },
    data(newValue) {
      if (newValue !== null) {
        this.category = newValue.name;
        this.selectedId = newValue.id;
      }
    },
  },

  methods: {
    clearError() {
      this.errorMessage = '';
    },
    async addNewCategory() {
      if (this.category === '') {
        this.errorMessage = 'Category Name cannot be empty!';
        return this.errorMessage;
      }
      await axios
        .post('/product/category/add', {
          name: this.category,
        })
        .then(({ data }) => {
          if (data.status.includes('Error'))
            return this.$toast.error('Error', 'Category Name Exist!');
          this.$toast.success('Success', 'Category Added!');
          Modal.getInstance(
            document.getElementById('configure-product-category-modal')
          ).hide();
          this.$inertia.visit('/product/category');
        });
      return true;
    },
    editCategory() {
      if (this.category === '') {
        this.errorMessage = 'Category Name cannot be empty!';
        return this.errorMessage;
      }
      axios
        .post(`/product/category/update/${this.selectedId}`, {
          name: this.category,
        })
        .then(({ data }) => {
          if (data.status.includes('Something'))
            return this.$toast.error('Error', 'Something went wrong!');
          if (data.status.includes('Exist'))
            return this.$toast.error('Error', 'Category Name Exist!');
          this.$toast.success('Success', 'Category Updated!');
          Modal.getInstance(
            document.getElementById('configure-product-category-modal')
          ).hide();
          this.$inertia.visit('/product/category');
        });
      return true;
    },
    modifyCategory() {
      if (this.type === 'add') return this.addNewCategory();
      return this.editCategory();
    },
  },
};
</script>

<style lang="scss" scoped>
*:not(i) {
  font-family: $base-font-family;
  color: $base-font-color;
}
</style>

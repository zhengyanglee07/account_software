<template>
  <ModalTemplate
    :id="modalId"
    :title="modalTitle"
    :button_title="buttonTitle"
    @save-button="save"
  >
    <template #body>
      <div class="row">
        <div
          class="col-md-12 mb-3"
          style="padding: 0 !important"
        >
          <div class="form-group-prepend">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <i class="fas fa-search gray_icon" />
                </span>
              </div>
              <input
                ref="search"
                v-model="searchInput"
                type="text"
                class="form-control firstInput"
                :placeholder="'Search Categories'"
              >
            </div>
          </div>
        </div>
      </div>
      <template v-if="allCategories.length > 0">
        <template v-if="!filteredItems.length">
          <div>
            <span class="flex-center">No results found...</span>
          </div>
        </template>
        <template
          v-for="(item, index) in filteredItems"
          :key="index"
        >
          <div class="list-group list-group-item list-group-item-action">
            <div
              class="row"
              style="align-items: center"
              @click="addCategory(item, index)"
            >
              <div class="col-1 px-0">
                <input
                  :id="`${index}-checkbox`"
                  type="checkbox"
                  class="me-1 black-checkbox"
                  :checked="selected.includes(item.id)"
                >
                <span />
              </div>
              <div
                class="col-5 px-0"
                style="text-overflow: ellipsis; cursor: pointer"
              >
                <span style="padding-left: 2px">{{ item.name }}</span>
              </div>
              <div
                v-if="item.count > 0"
                class="col-4"
              >
                <span>{{ item.count }} products</span>
              </div>
            </div>
          </div>
        </template>
      </template>
      <template v-else>
        <div class="wrapper">
          <span
            class="flex-center"
            style="color: grey"
          >No category yet...</span>
          <a
            class="flex-center h_link"
            style="font-size: 1rem"
            href="/product/category"
          >Click here to Create Category</a>
        </div>
      </template>
    </template>
  </ModalTemplate>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'BrowseCategoryModal',

  props: [
    'modalId',
    'modalTitle',
    'buttonTitle',
    'allCategories',
    'currency',
    'selectedCategory',
  ],

  data() {
    return {
      searchInput: '',
      selected: [],
      showSelected: [],
    };
  },

  computed: {
    filteredItems() {
      return this.allCategories.filter(
        function (category) {
          return category.name
            .toString()
            .toLowerCase()
            .includes(this.searchInput.toLowerCase());
        }.bind(this)
      );
    },
  },

  watch: {
    selectedCategory(newValue) {
      if (newValue) {
        this.selected = newValue;
      }
    },
  },

  mounted() {
    this.selected = this.selectedCategory;
  },

  methods: {
    save() {
      this.showSelected = [];
      this.selected.forEach((id) => {
        const selectedItem = this.allCategories.find((category) => {
          return category.id === id;
        });
        this.showSelected.push(selectedItem);
      });
      this.$emit('save', this.selected);
      this.searchInput = '';
      Modal.getInstance(document.getElementById('browseCategoryModal')).hide();
    },

    addCategory(item, index) {
      if (!this.selected.includes(item.id)) {
        // document.getElementById(index + '-checkbox').checked = true;
        this.selected.push(item.id);
      } else {
        const i = this.selected.findIndex((element) => element === item.id);
        this.selected.splice(i, 1);
        // document.getElementById(index + '-checkbox').checked = false;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
</style>

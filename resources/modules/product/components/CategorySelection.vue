<template>
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="row browse-wrapper mb-3">
        <div class="input-group-app-prep p-0">
          <div class="input-group">
            <div class="input-group-prepend browse-wrapper-search">
              <span class="input-group-text-search input-group-text">
                <i class="fas fa-search gray_icon" />
              </span>
            </div>
            <div class="browse-wrapper-input">
              <input
                v-model="searchInput"
                type="text"
                class="form-control browse-input"
                :data-target="'#browseCategoryModal'"
                :placeholder="'Search categories'"
                @keyup="showModal"
              >
            </div>

            <div class="input-group-append browse-wrapper-button">
              <button
                class="browse-btn"
                @click="showModal"
              >
                Browse
              </button>
            </div>
          </div>
        </div>
      </div>

      <ul
        v-if="selectedCategory.length > 0"
        class="_3Z2aM"
      >
        <li
          v-for="(category, index) in showSelectedCategory"
          :key="index"
          class="_19Fmu"
        >
          <div class="_3sTAJ">
            <div class="_3wqBY">
              <div
                class="Polaris-Stack_32wu2 Polaris-Stack--alignmentCenter_1rtaw"
              >
                <div class="Polaris-Stack__Item_yiyol">
                  <div class="badge-card__product-label">
                    {{ category.name }}
                  </div>
                  <div class="badge-card__product-lighttext">
                    {{ category.count }} products
                  </div>
                </div>
              </div>
            </div>
            <div class="_6zSLq">
              <button
                type="button"
                class="tag-button"
                aria-label="Remove category"
                data-banner-dismiss="true"
                @click="removeCategory(category.id)"
              >
                <svg
                  class="icon-svg icon-svg--size-14 icon-svg--block"
                  aria-hidden="true"
                  focusable="false"
                >
                  <use xlink:href="#close">
                    <svg id="close">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                      >
                        <path
                          d="M15.1 2.3L13.7.9 8 6.6 2.3.9.9 2.3 6.6 8 .9 13.7l1.4 1.4L8 9.4l5.7 5.7 1.4-1.4L9.4 8"
                        />
                      </svg>
                    </svg>
                  </use>
                </svg>
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <BrowseCategoryModal
      modal-id="browseCategoryModal"
      :modal-title="'Select Categories'"
      :button-title="selectedCategory.length > 0 ? 'Done' : 'Add'"
      :all-categories="allCategories"
      :currency="currencyDetails.currency"
      :selected-category="selectedCategory"
      @save="updateSelectedCategory"
    />
  </div>
</template>

<script>
import BrowseCategoryModal from '@product/components/BrowseCategoryModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'CategorySelection',

  components: {
    BrowseCategoryModal,
  },

  props: [
    'allProducts',
    'currencyDetails',
    'selectedCategory',
    'allCategories',
  ],

  data() {
    return {
      searchInput: '',
      selectedId: [],
      showSelectedCategory: [],
    };
  },

  watch: {
    selectedCategory(newValue) {
      if (newValue) {
        this.selectedId = newValue;
      }
    },
    selectedId(newValue) {
      if (newValue) {
        this.showSelectedCategory = [];
        this.selectedId.forEach((id) => {
          const selectedItem = this.allCategories.find((category) => {
            return category.id === id;
          });
          this.showSelectedCategory.push(selectedItem);
        });
      }
    },
  },
  mounted() {
    this.selectedId = this.selectedCategory;
  },

  methods: {
    showModal() {
      new Modal(document.getElementById('browseCategoryModal')).show();
    },
    removeCategory(id) {
      const i = this.selectedId.findIndex((element) => element === id);
      this.selectedId.splice(i, 1);
      this.$emit('update', this.selectedId);
    },
    updateSelectedCategory(value) {
      this.selectedId = value;
      this.$emit('update', this.selectedId);
    },
  },
};
</script>

<style lang="scss" scoped>

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type='number'] {
  -moz-appearance: textfield;
}
._19Fmu {
  padding: 10px 0;
  &:last-child {
    padding-bottom: 0;
  }
  &:first-child {
    padding-top: 0;
  }
}
._3Z2aM {
  margin: 0;
  padding: 0;
  list-style: none;
}
._3sTAJ {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
._3wqBY {
  margin-top: 0.4rem;
  flex: 1 1 auto;
}
.Polaris-Button--iconOnly_viazp {
  padding-left: 0.8rem;
  padding-right: 0.8rem;
}
.Polaris-Button--plain_2z97r {
  margin: -0.7rem -0.8rem;
  padding-left: 0.8rem;
  padding-right: 0.8rem;
  background: transparent;
  border: 0;
  box-shadow: none;
  color: var(--p-interactive);
}
.Polaris-Button--plain_2z97r .Polaris-Button__Icon_yj27d {
  margin-left: 0;
  margin-right: 0;
}
.Polaris-Button__Icon_yj27d:last-child {
  margin-right: -0.8rem;
  margin-left: 0.4rem;
}
.Polaris-Button__Icon_yj27d {
  margin-left: -0.4rem;
}

.Polaris-Stack--alignmentCenter_1rtaw {
  align-items: center;
}
.Polaris-Stack_32wu2 {
  margin-top: -1.6rem;
  margin-left: -1.6rem;
  display: flex;
  // flex-wrap: wrap;
  align-items: stretch;
}
.Polaris-Stack_32wu2 > .Polaris-Stack__Item_yiyol {
  margin-top: 1.6rem;
  margin-left: 1.6rem;
  max-width: 100%;
}
.Polaris-Stack__Item_yiyol {
  flex: 0 0 auto;
  min-width: 0;
}
._6zSLq {
  display: flex;
  flex-wrap: row nowrap;
  align-items: center;
  justify-content: flex-end;
  min-width: 11rem;
  flex: 1 0 auto;
  margin-left: 1.6rem;
}

.Polaris-Icon_yj27d {
  display: block;
  height: 2rem;
  width: 2rem;
  max-height: 100%;
  max-width: 100%;
  margin: auto;
}
.Polaris-Button--plain_2z97r.Polaris-Button--iconOnly_viazp svg {
  fill: var(--p-icon);
}
.Polaris-Icon__Img_375hq,
.Polaris-Icon__Svg_375hu {
  position: relative;
  display: block;
  width: 100%;
  max-width: 100%;
  max-height: 100%;
}

.icon-svg--size-12 {
  width: 12px;
  height: 12px;
}

.icon-svg--size-14 {
  width: 14px;
  height: 14px;
}

.icon-svg {
  display: inline-block;
  vertical-align: middle;
  fill: currentColor;
}

.icon-svg--block {
  display: block;
}

.tag-button .icon-svg {
  vertical-align: middle;
  fill: currentColor;
  stroke: rgba(113, 113, 113, 0.9);
}

.tag-button:hover .icon-svg,
.tag-button:focus .icon-svg {
  stroke: #323232;
}

.tag-button {
  margin-left: 0.8571428571em;
  color: inherit;
  font: inherit;
  padding: 0;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  -webkit-font-smoothing: inherit;
  border: none;
  background: transparent;
  line-height: normal;
}
li {
  list-style-type: none;
}

.browse-input {
  height: 36px !important;
}
</style>

<template>
  <div class="right_container_content_inner-page">
    <SettingPageHeader
      :title="badge ? 'Edit Product Label' : 'Add Product Label'"
      :previous-button-u-r-l="'/product/badges'"
      previous-button-link-title="Back to All Product Labels"
    />

    <div class="row">
      <div class="col-lg-8 pr-3">
        <ProductBadgeCard card-title="Product Label Settings">
          <template #content>
            <div class="row p-1">
              <div class="col-md-6">
                <div class="col-left">
                  <div class="">
                    <label
                      for="badgeName"
                      class="badge-card__input-title p-two"
                    >
                      Label Name
                    </label>
                    <div class="input-group">
                      <input
                        v-model="badgeName"
                        type="text"
                        name="badgeName"
                        class="form-control p-two"
                        @input="nameErr = /^ *$/.test(badgeName) ? true : false"
                      >
                    </div>
                  </div>
                  <span
                    v-show="nameErr"
                    class="error-message error-font-size"
                  >Name is required *</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-right">
                  <label
                    for="badgeText"
                    class="badge-card__input-title p-two"
                  >
                    Text
                  </label>
                  <div class="">
                    <div class="input-group">
                      <input
                        v-model="text"
                        type="text"
                        name="badgeText"
                        class="form-control p-two"
                        @input="textErr = /^ *$/.test(text) ? true : false"
                      >
                    </div>
                  </div>
                  <span
                    v-show="textErr"
                    class="error-message error-font-size"
                  >Text is required *</span>
                </div>
              </div>
            </div>

            <div class="row p-1">
              <div class="col-md-6">
                <div class="col-left">
                  <label
                    for="badgeText"
                    class="badge-card__input-title p-two"
                  >
                    Text Color
                  </label>
                  <div class="">
                    <div>
                      <div
                        id="colorpicker"
                        style="height: 38px"
                      >
                        <div class="picker-product row">
                          <div
                            class="px-0"
                            style="height: 38px; width: 38px"
                          >
                            <button
                              class="color-picker-button-product col-3"
                              :style="{ background: textColor }"
                              @click.stop="showTextColor"
                            />
                          </div>
                          <input
                            class="form-control p-two"
                            style="border: none; height: 38px; width: 160px"
                            type="text"
                            maxlength="7"
                            :value="textColor"
                            @input="updateTextColor($event.target.value)"
                          >
                        </div>
                      </div>
                      <ChromeColor
                        v-show="showTextColorPicker"
                        v-click-away="closeTextColorPicker"
                        class="input-color-picker"
                        style="top: auto"
                        :value="textColor"
                        @input="(col) => updateTextColor(col.hex)"
                      />
                    </div>
                  </div>
                  <span class="error-message error-font-size" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-right">
                  <div class="form-group-append">
                    <label
                      for="fontSize"
                      class="badge-card__input-title p-two"
                    >
                      Font size
                    </label>
                    <div class="input-group">
                      <input
                        v-model="fontSize"
                        type="Number"
                        name="fontSize"
                        class="form-control p-two"
                        min="5"
                        max="35"
                        @input="validateFontSize"
                      >
                      <div class="input-group-append">
                        <span
                          style="padding-right: 10px"
                          class="input-group-text"
                        >px</span>
                      </div>
                    </div>
                  </div>
                  <span class="error-message error-font-size" />
                </div>
              </div>
            </div>

            <div class="row p-1">
              <div class="col-md-6">
                <div class="col-left">
                  <label
                    for="badgeText"
                    class="badge-card__input-title p-two"
                  >
                    Background Color
                  </label>
                  <div class="">
                    <div>
                      <div
                        id="colorpicker"
                        style="height: 38px"
                      >
                        <div class="picker-product row">
                          <div
                            class="px-0"
                            style="height: 38px; width: 38px"
                          >
                            <button
                              class="color-picker-button-product col-3"
                              :style="{ background: backgroundColor }"
                              @click.stop="showBackgroundColor"
                            />
                          </div>
                          <input
                            class="form-control p-two"
                            style="border: none; height: 38px; width: 160px"
                            type="text"
                            maxlength="7"
                            :value="backgroundColor"
                            @input="updateBackgroundColor($event.target.value)"
                          >
                        </div>
                      </div>
                      <ChromeColor
                        v-show="showBackgroundColorPicker"
                        v-click-away="closeBackgroundColorPicker"
                        class="input-color-picker"
                        style="top: auto"
                        :value="backgroundColor"
                        @input="(col) => updateBackgroundColor(col.hex)"
                      />
                    </div>
                  </div>
                  <span class="error-message error-font-size" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="col-right">
                  <div class="">
                    <label
                      for="fontFamily"
                      class="badge-card__input-title p-two"
                    >
                      Font Family
                    </label>
                    <VSelect
                      v-model="fontFamily"
                      label="label"
                      :options="fontFamilies"
                      :reduce="(label) => label.value"
                    >
                      <template #option="option">
                        <span :class="[option.value]"> {{ option.label }}</span>
                      </template>
                    </VSelect>
                  </div>
                  <span class="error-message error-font-size" />
                </div>
              </div>
            </div>

            <div class="row p-1">
              <div class="col-md-6">
                <div class="col-left">
                  <div class="form-group-append">
                    <label
                      for="fontSize"
                      class="badge-card__input-title p-two"
                    >
                      Margin Size
                    </label>
                    <div class="input-group">
                      <input
                        v-model="marginSize"
                        type="Number"
                        name="fontSize"
                        class="form-control p-two"
                        min="0"
                        @input="validateMarginSize"
                      >
                      <div class="input-group-append">
                        <span
                          style="padding-right: 10px"
                          class="input-group-text"
                        >px</span>
                      </div>
                    </div>
                  </div>
                  <span class="error-message error-font-size" />
                </div>
              </div>
            </div>

            <div class="row p-1">
              <div class="col-md-6">
                <div class="col-left">
                  <div class="">
                    <label
                      for="position"
                      class="badge-card__input-title p-two"
                    >
                      Position
                    </label>
                    <div
                      id="badge-position"
                      class="input-group"
                    >
                      <label>
                        <input
                          id="top_left"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="top-left"
                          class="d-none"
                        >
                        <span v-show="position === 'top-left'" />
                      </label>
                      <label>
                        <input
                          id="top_middle"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="top-middle"
                          class="d-none"
                        >
                        <span v-show="position === 'top-middle'" />
                      </label>
                      <label>
                        <input
                          id="top_right"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="top-right"
                          class="d-none"
                        >
                        <span v-show="position === 'top-right'" />
                      </label>
                      <label>
                        <input
                          id="middle_left"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="middle-left"
                          class="d-none"
                        >
                        <span v-show="position === 'middle-left'" />
                      </label>
                      <label>
                        <input
                          id="middle"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="middle"
                          class="d-none"
                        >
                        <span v-show="position === 'middle'" />
                      </label>
                      <label>
                        <input
                          id="middle_right"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="middle-right"
                          class="d-none"
                        >
                        <span v-show="position === 'middle-right'" />
                      </label>
                      <label>
                        <input
                          id="bottom_left"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="bottom-left"
                          class="d-none"
                        >
                        <span v-show="position === 'bottom-left'" />
                      </label>
                      <label>
                        <input
                          id="bottom_middle"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="bottom-middle"
                          class="d-none"
                        >
                        <span v-show="position === 'bottom-middle'" />
                      </label>
                      <label>
                        <input
                          id="bottom_right"
                          v-model="position"
                          type="radio"
                          name="badge_position"
                          value="bottom-right"
                          class="d-none"
                        >
                        <span v-show="position === 'bottom-right'" />
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row p-1">
              <div class="col">
                <div class="col">
                  <div class="">
                    <label
                      for="badge_design"
                      class="badge-card__input-title p-two"
                    >
                      Label Design
                    </label>
                    <div
                      id="badge-shape"
                      class="input-group"
                    >
                      <label class="p-4">
                        <input
                          v-model="badgeDesign"
                          type="radio"
                          name="badge_design"
                          value="circle"
                          class="d-none"
                        >
                        <span
                          class="product-badge badge-circle"
                          :style="{
                            'background-color': backgroundColor,
                            color: backgroundColor,
                            'font-size': fontSize + 'px',
                            opacity: badgeDesign === 'circle' ? 1 : 0.15,
                          }"
                        >
                          <span
                            class="product-badge-text"
                            :style="{ color: textColor }"
                          >Circle</span>
                        </span>
                      </label>
                      <label class="p-4">
                        <input
                          v-model="badgeDesign"
                          type="radio"
                          name="badge_design"
                          value="rectangular"
                          class="d-none"
                        >
                        <span
                          class="product-badge badge-rectangular"
                          :style="{
                            'background-color': backgroundColor,
                            color: backgroundColor,
                            'font-size': fontSize + 'px',
                            opacity: badgeDesign === 'rectangular' ? 1 : 0.15,
                          }"
                        >
                          <span
                            class="product-badge-text"
                            :style="{ color: textColor }"
                          >Rectangular
                          </span>
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </ProductBadgeCard>
      </div>

      <div class="col-lg-4 right-layout-lg">
        <ProductBadgeCard card-title="Product Label Preview">
          <template #content>
            <div class="container">
              <img
                class="border border-3"
                src="https://cdn.hypershapes.com/assets/product-default-image.png"
                alt=""
              >
              <ProductBadgeLabel
                :badge-design="badgeDesign"
                :text="text"
                :text-color="textColor"
                :background-color="backgroundColor"
                :font-size="fontSize"
                :font-family="fontFamily"
                :position="position"
                :margin-size="marginSize"
              />
            </div>
          </template>
        </ProductBadgeCard>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 pr-3">
        <ProductBadgeCard card-title="Product Selections">
          <template #content>
            <div class="col">
              <label
                for="selectedProducts"
                class="badge-card__input-title p-two"
              >
                Products
              </label>
              <div class="">
                <VSelect
                  v-model="productSelect"
                  label="name"
                  :options="productSelections"
                  :reduce="(name) => name.value"
                />
              </div>
              <ProductSelection
                v-if="productSelect === 'specific'"
                :all-products="allProducts"
                :currency-details="currencyDetails"
                :selected-product="selectedProduct"
                @update="updateSelectedProduct"
              />
              <CategorySelection
                v-if="productSelect === 'category'"
                :all-products="allProducts"
                :all-categories="allCategories"
                :currency-details="currencyDetails"
                :selected-category="selectedCategory"
                @update="updateSelectedCategory"
              />
            </div>
          </template>
        </ProductBadgeCard>
      </div>
    </div>

    <div class="row">
      <div
        class="col-md-12"
        style="padding: 0 !important"
      >
        <div class="footer-container">
          <Link
            class="cancel-button"
            style="
              padding: 0;
              padding-top: 8px;
              color: #808285;
              margin-right: 20px;
            "
            href="/product/badges"
          >
            Cancel
          </Link>

          <button
            class="footer-container__button primary-small-square-button"
            @click="saveProductBadge"
          >
            <span
              v-show="!loading"
              style="color: #fff; font-size: 12px"
            >Save</span>
            <span v-show="loading">
              <div class="spinner--white-small">
                <div class="loading-animation loading-container my-0">
                  <div class="shape shape1" />
                  <div class="shape shape2" />
                  <div class="shape shape3" />
                  <div class="shape shape4" />
                </div>
              </div>
            </span>
          </button>
        </div>
        <!-- <button class="blueBaseButton" type="button">Save</button> -->
      </div>
    </div>
  </div>
</template>

<script>
import ProductBadgeMixin from '@product/mixins/ProductBadgeMixin.js';
import ProductBadgeCard from '@product/components/ProductBadgeCardTemplate.vue';
import ProductBadgeLabel from '@product/components/ProductBadgeLabel.vue';
import ProductSelection from '@product/components/ProductSelection.vue';
import CategorySelection from '@product/components/CategorySelection.vue';
import VueColor from '@ckpack/vue-color';
import googleFontFamilies from '@shared/assets/js/fonts.js';

import vSelect from 'vue-select';

export default {
  components: {
    ProductBadgeCard,
    ProductBadgeLabel,
    ChromeColor: VueColor.Chrome,
    ProductSelection,
    CategorySelection,

    vSelect,
  },

  mixins: [ProductBadgeMixin],
  props: ['badge', 'allProducts', 'currencyDetails', 'allCategories'],

  computed: {
    fontFamilies() {
      return googleFontFamilies;
    },
  },

  async mounted() {
    if (this.badge) {
      this.badgeName = this.badge.badge_name;
      this.nameErr = /^ *$/.test(this.badge.badge_name);
      this.badgeDesign = this.badge.badge_design;
      this.text = this.badge.text;
      this.fontSize = this.badge.font_size;
      this.textColor = this.badge.text_color;
      this.fontFamily = this.badge.font_family;
      this.backgroundColor = this.badge.background_color;
      this.marginSize = this.badge.margin_size;
      this.position = this.badge.position;
      this.productSelect = this.badge.select_products;
      this.selectedProduct = this.badge.selected_product;
      this.selectedCategory = this.badge.selected_categories ?? [];
    }
  },

  methods: {
    updateSelectedProduct(value) {
      this.selectedProduct = value;
    },
    updateSelectedCategory(value) {
      this.selectedCategory = value;
    },
  },
};
</script>
<style>
.v-select .vs__dropdown-menu {
  overflow-x: hidden;
}
</style>
<style lang="scss" scoped>
img {
  display: block;
  width: -webkit-fill-available;
  height: auto;
}

.right_container_content_inner-page {
  //padding-top: 12px !important;

  @media (max-width: $md-display) {
    padding-top: 0px !important;

    .back-to-previous {
      padding-left: 20px;
    }
  }
}

.footer-container {
  padding: 20px 0;
  margin: 0 auto;
  border-top: 0.1rem solid var(--p-border-subdued, #dfe3e8);
  display: flex;
  justify-content: flex-end;

  @media (max-width: $sm-display) {
    flex-direction: column-reverse;

    .primary-small-square-button {
      width: 100%;
    }

    .cancel-button {
      text-align: center;
      padding-top: 10px !important;
      margin-right: 0px !important;
    }
  }

  &__cancel-btn {
    color: #6c757d;
    text-decoration: underline;
    display: flex;
    justify-content: center;
    align-items: center;

    &:hover {
      cursor: pointer;
    }
  }

  //   &__button {
  //     // margin-right: 25px;
  //   }
}

.summary-ul::after {
  content: '';
  position: absolute;
  top: 9px;
  left: 0;
  height: 4px;
  width: 4px;
  background: #000;
  border-radius: 50%;
}

.summary-li {
  display: block;
  padding-left: 20px;
  position: relative;
}

.input-group {
  position: relative;
  display: flex;
  width: 100%;
}

#badge-position {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  width: 100%;
  max-width: 150px;
}

#badge-position label {
  width: 33.333%;
  padding-bottom: 30%;
  background-color: #f5f5f7;
  border: 1px solid #ccc;
  cursor: pointer;
  position: relative;
}
#badge-position span {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: $h-primary;
}

#badge-shape .product-badge {
  position: relative;
  margin: 0;
}

.product-badge.badge-rectangular {
  border-radius: 0px;
  width: auto;
  height: auto;
  padding: 0.5em 1.5em;
  margin: 12px 0;
}

.product-badge.badge-circle {
  border-radius: 50%;
  width: 5em;
  height: 5em;
}

.product-badge-text {
  background-color: transparent !important;
}

.product-badge {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  text-align: center;
  font-size: 10px;
  line-height: 1.1;
  z-index: 10;
  max-width: 100%;
}

.container {
  position: relative;
  padding: 0;
  width: 60%;
}
</style>

<template>
  <div class="display-product">
    <div
      class="display-product-container"
      :style="{ 'min-width': min_width }"
    >
      <div
        v-for="(item, index) in items"
        :key="item.hasOwnProperty('id') ? item.id : index"
        class="row-product"
        style="padding-left: 0 20px"
      >
        <div
          class="product-container"
          style="align-items: flex-start; width: 100%"
        >
          <div class="col-2 d-flex px-0">
            <img
              :src="item.image_url || productDefaultImages"
              class="order_image"
              style="
                object-fit: contain;
                height: 80px;
                width: 80px;
                justify-content: flex-start;
              "
            >
          </div>

          <div
            :class="custom_class !== undefined ? custom_class : 'col-6'"
            style="padding-left: 10px !important"
          >
            <div class="mt-1 me-3">
              <div style="text-align: left; overflow-wrap: anywhere">
                <span class="p-two">
                  {{ item.product_name }}
                  <span
                    v-if="item.is_taxable"
                    style="color: red"
                  >*</span>
                </span>
                <template v-if="item.SKU">
                  <li class="order-display p-two">
                    SKU : {{ item.SKU }}
                  </li>
                </template>
                <template
                  v-if="item.variant != null && item.variant.length > 0"
                >
                  <li
                    v-for="variant in JSON.parse(item.variant)"
                    :key="variant.id"
                    class="order-display p-two"
                  >
                    {{ variant.label }} : {{ variant.value }}
                  </li>
                </template>
              </div>

              <template
                v-if="
                  item.customization != null && item.customization.length > 0
                "
              >
                <li
                  v-for="customization in JSON.parse(item.customization)"
                  :key="customization.id"
                  class="order-display p-two"
                >
                  <template v-if="customization.values.length != 0">
                    <div style="text-align: left">
                      <span class="p-two label-style">
                        {{ customization.label }}</span>
                      :
                      <span
                        v-for="(value, index) in customization.values"
                        :key="index"
                        class="order-display p-two"
                      >
                        {{ value.value_label }}

                        <span
                          v-if="parseFloat(value.single_charge) !== 0"
                          class="p-two"
                          style="color: #808285"
                        >
                          ( + {{ currency }} {{ value.single_charge }} )
                        </span>
                        <span class="p-two">{{
                          index + 1 >= customization.values.length ? '' : ', '
                        }}</span>
                        <span
                          v-if="
                            customization.is_total_charge &&
                              customization.total_charge_amount != 0
                          "
                          class="p-two"
                          style="color: #808285"
                        >
                          ( + {{ currency }}
                          {{ customization.total_charge_amount }} )
                        </span>
                      </span>
                    </div>
                  </template>
                </li>
              </template>

              <slot
                name="expander"
                :item="item"
                :index="index"
              />
            </div>
          </div>

          <div class="col-4">
            <slot
              name="right"
              :item="item"
              :index="index"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import productDefaultImages from '@shared/assets/media/product-default-image.png';

export default {
  name: 'DisplayProduct',
  props: ['items', 'currency', 'custom_class', 'min_width'],
  data() {
    return {
      productDefaultImages,
    };
  },
};
</script>

<style lang="scss" scoped>
// .row-product {
//      border-top: 1px solid lightgray;
//  }

.row-product:first-child {
  border-top: 0;
}

// New design
p,
span {
  color: $base-font-color;
  font-family: $base-font-family;
  font-size: $base-font-size;
}

.product-price {
  font-weight: bold;
}

.label-style {
  // margin-left: 20px;
  color: #808285;
  overflow-wrap: anywhere;
  word-break: break-word;
  display: inline-block;
}

.order-display {
  list-style: none;
  font-size: $base-font-size;
  color: $h-secondary-color;
  font-family: $base-font-family;
}

// .display-product{
//     // overflow: auto;
//     &-container{
//         min-width: 500px;
//     }
// }

// .display-product-container
// {
//     padding-left: 20px;
//     padding-right: 20px;
// }

.product-container {
  border-bottom: 1px solid lightgrey;
  display: flex;
  //   margin-left: 20px;
  //   margin-right: 20px;
  padding-bottom: 10px;
}

.row-product:last-child .product-container {
  border-bottom: none !important;
}

li {
  list-style: none;
}
</style>

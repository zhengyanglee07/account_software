<!-- eslint-disable vue/no-useless-template-attributes -->
<template>
  <template
    v-for="(item, countIndex) in variantOptions"
    :key="countIndex"
  >
    <BaseFormGroup
      label="Display Name"
      required
      :error-message="
        item.errorMessage.includes('name') ? item.errorMessage : ''
      "
    >
      <template #label-row-end>
        <BaseButton
          size="sm"
          type="link"
          color="danger"
          @click="removeVariant(countIndex, item.id, item.is_shared)"
        >
          Delete Variant
        </BaseButton>
      </template>
      <BaseFormInput
        :id="`display-name-${countIndex}`"
        placeholder="Size, Color, etc."
        type="text"
        :model-value="item.display_name || item.name"
        :disabled="item.is_shared"
        @input="updateVariantDetails(countIndex, 'name', $event.target.value)"
        @keyup="updateVariantDetails(countIndex, 'errorMessage', '')"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Type">
      <BaseFormSelect
        :model-value="item.type"
        :disabled="item.is_shared"
        label-key="name"
        value-key="value"
        :options="[
          { name: 'Image', value: 'image' },
          { name: 'Color', value: 'colour' },
          { name: 'Button', value: 'button' },
          { name: 'Dropdown', value: 'dropdown' },
        ]"
        @change="updateVariantDetails(countIndex, 'type', $event.target.value)"
      />
    </BaseFormGroup>

    <!-- <Draggable
      :list="item.valueArray"
      draggable=".variantValue"
      :disabled="!enabled"
      @change="reoderValues(countIndex)"
    > -->
    <!-- <template #item="{ element: option, index: optionIndex }"> -->
    <template
      v-for="(option, optionIndex) in item.valueArray"
      :key="optionIndex"
    >
      <!-- <template v-if="true"> -->
      <BaseFormGroup
        :col="6"
        :label="!optionIndex ? 'Values' : ''"
        :error-message="
          option?.errorMessage?.toLowerCase()?.includes('value')
            ? option.errorMessage
            : ''
        "
        :required="!optionIndex"
      >
        <BaseFormInput
          :id="`value-${countIndex}-${optionIndex}`"
          type="text"
          :disabled="item.is_shared"
          :model-value="option.variant_value"
          :placeholder="
            item.type === 'colour' ? 'Black, White, etc.' : 'XL, L, etc.'
          "
          @input="
            updateVariantValueDetails(
              countIndex,
              optionIndex,
              'variant_value',
              $event.target.value
            )
          "
          @keyup="
            updateVariantValueDetails(
              countIndex,
              optionIndex,
              'errorMessage',
              ''
            );
            nextInput($event, countIndex, item?.valueArray?.length);
          "
          @keypress.enter="addVariantOptions(countIndex)"
        />
      </BaseFormGroup>

      <BaseFormGroup
        :col="2"
        :label="!optionIndex ? 'Default' : ''"
        class="row"
      >
        <BaseFormCheckBox
          :id="`checkbox-${countIndex}-${optionIndex}`"
          :model-value="option.default"
          :value="true"
          class="justify-content-center align-items-center ps-3"
          @change="
            updateVariantValueDetails(
              countIndex,
              optionIndex,
              'default',
              $event.target.checked
            )
          "
          @click="
            checkDefault({
              count: countIndex,
              index: optionIndex,
            })
          "
        />
      </BaseFormGroup>
      <BaseFormGroup
        :col="2"
        :label="!optionIndex ? '&nbsp' : ''"
      >
        <BaseButton
          size="sm"
          type="link"
          color="danger"
          :disabled="item.is_shared || item?.valueArray?.length === 1"
          @click="deleteVariant(countIndex, optionIndex, option.id)"
        >
          Remove
        </BaseButton>
      </BaseFormGroup>
      <BaseFormGroup v-show="item.type === 'colour'">
        <BaseColorPicker
          :model-value="option.color"
          @update:modelValue="
            updateVariantValueDetails(countIndex, optionIndex, 'color', $event)
          "
          @focus="isOpen = false"
        />
      </BaseFormGroup>
      <BaseFormGroup v-show="item.type === 'image'">
        <div
          v-if="item.type === 'image'"
          style="position: relative; width: 70px; height: 65px"
          @click="
            item.is_shared
              ? () => {}
              : selectedValueIndex(countIndex, optionIndex)
          "
        >
          <template
            v-if="item.is_shared"
            style="width: 100%; height: 100%; position: relative"
          >
            <img
              :src="
                option.image_url === null
                  ? 'https://cdn.hypershapes.com/assets/product-default-image.png'
                  : option.image_url
              "
              style="object-fit: cover; width: 100%; height: 100%"
            >
          </template>

          <!-- image upload  -->
          <BaseImagePreview
            v-else
            modal-id="variantOption-modal"
            no-hover-message
            size="sm"
            :default-image="
              option.image_url ??
                'https://cdn.hypershapes.com/assets/product-default-image.png'
            "
            @delete="removeVariantImage(countIndex, optionIndex)"
          />
        </div>
      </BaseFormGroup>
    </template>
    <!-- </template> -->
    <!-- </Draggable> -->

    <BaseFormGroup>
      <BaseButton
        v-if="item.is_shared !== 1"
        size="sm"
        type="link"
        @click="addVariantOptions(countIndex)"
      >
        + Add Value
      </BaseButton>
    </BaseFormGroup>
    <div class="separator mb-10" />
  </template>
  <BaseFormGroup :col="3">
    <BaseButton
      size="sm"
      @click="addNewVariant({ type: 'new', variant: null })"
    >
      Add Variation
    </BaseButton>
  </BaseFormGroup>
</template>

<script>
/* eslint-disable camelcase */
import { mapState, mapMutations } from 'vuex';
// import draggable from 'vuedraggable';
import cloneDeep from 'lodash/cloneDeep';
import eventBus from '@services/eventBus.js';
import { Modal } from 'bootstrap';

export default {
  directives: {
    focus: {
      inserted(el) {
        if (!el.classList.contains('first_value')) el.focus();
      },
    },
  },

  components: {
    // ChromeColor: Chrome,
    // Draggable: draggable,
  },
  props: {
    currentVariants: {
      type: [Object, Array],
      default: () => [],
    },
  },

  data() {
    return {
      open_color_picker: false,
      openSelectedColorPicker: '',
      selectedVariantIndex: 0,
      selectedVariantValueIndex: 0,
      isOpen: false,
      enabled: true,
      variantOptions: [],
    };
  },

  computed: {
    ...mapState('product', ['variantOptionArray']),
  },
  watch: {
    variantOptionArray: {
      deep: true,
      handler(newValue) {
        this.variantOptions = cloneDeep(newValue);
      },
    },
  },

  mounted() {
    eventBus.$on('productVariantValueImageLocal', (imageData) => {
      this.updateVariantValueDetails(
        this.selectedVariantIndex,
        this.selectedVariantValueIndex,
        'image_url',
        imageData
      );
    });
    this.variantOptions = cloneDeep(this.variantOptionArray);
    if (window.screen.width <= 1024) this.enabled = false;
  },

  methods: {
    ...mapMutations('product', [
      'clearAllVariantErrors',
      'checkDefault',
      'deleteVariantValueByIndex',
      'deleteVariantByIndex',
      'addVariantOptions',
      'addNewVariant',
      'updateVariant',
      'updateVariantValue',
      'addDeletedVariant',
      'swapVariantValue',
      'reorderVariantValues',
    ]),
    nextInput(e, countIndex, latestIndex) {
      if (e?.key === 'Enter') {
        document
          .getElementById(`value-${countIndex}-${latestIndex - 1}`)
          .focus();
      }
    },
    reoderValues(i) {
      const data = { index: i, values: this.variantOptions[i].valueArray };
      this.reorderVariantValues(data);
    },
    mouseDown() {
      if (window.screen.width <= 1024) this.enabled = true;
    },
    mouseUp() {
      if (window.screen.width <= 1024) this.enabled = false;
    },
    onEndDragValue(e, index) {
      this.swapVariantValue({ index, values: e });
    },

    updateVariantDetails(index, type, value) {
      this.updateVariant({
        index,
        type,
        value,
      });
    },

    updateVariantValueDetails(index, valueIndex, type, value) {
      this.updateVariantValue({
        index,
        valueIndex,
        type,
        value,
      });
    },

    removeVariant(index, id, is_shared) {
      if (id !== null && this.currentVariants.includes(id)) {
        this.addDeletedVariant({ type: 3, value: id });
      }
      if (id !== null && is_shared !== 1) {
        this.addDeletedVariant({ type: 1, value: id });
      }
      this.deleteVariantByIndex(index);
      this.clearAllVariantErrors(); // reset the error message
    },

    deleteVariant(index, optionIndex, id) {
      if (id !== null) {
        this.addDeletedVariant({ type: 2, value: id });
      }
      this.deleteVariantValueByIndex({ index, valueIndex: optionIndex });
      this.clearAllVariantErrors(); // reset the error message
    },

    closeColorPicker() {
      this.openSelectedColorPicker = '';
    },

    /**
     * Set selected index when user selected an image
     * NOTE: only use this methods for updating image
     */
    selectedValueIndex(variantIndex, valueIndex) {
      this.selectedVariantIndex = variantIndex;
      this.selectedVariantValueIndex = valueIndex;
    },

    removeVariantImage(variantIndex, valueIndex) {
      this.updateVariantValueDetails(
        variantIndex,
        valueIndex,
        'image_url',
        'https://cdn.hypershapes.com/assets/product-default-image.png'
      );
    },
  },
};
</script>

//
<style scoped lang="scss">
// input[type='checkbox']:enabled.checkbox-size + span:before {
//   height: 17px !important;
//   width: 17px !important;
// }

// @media (max-width: 767px) {
//   .default-margin {
//     margin-left: 0 !important;
//   }
// }

// .section-col-left {
//   padding-right: 18px !important;
// }

// @media (max-width: 415px) {
//   .variant-button {
//     display: flex;
//     justify-content: center;
//   }
//   .primary-long-square-button {
//     margin-left: 10px;
//     height: auto;
//   }
// }

// .color-container {
//   width: 70% !important;

//   @media (min-width: $md-display) {
//     width: 50% !important;
//   }
// }

// .row > * {
//   padding-left: 0;
//   padding-right: 0;
// }

// .form-control {
//   &::placeholder {
//     font-size: 12px;
//   }
//   &:hover,
//   &:active,
//   &:focus {
//     outline: none !important;
//   }
// }

// .section-title {
//   padding-bottom: 8px;
//   color: $h-secondary-color;
// }

// @media (max-width: 376px) {
//   .primary-square-button {
//     width: 45% !important;
//   }

//   .primary-long-square-button {
//     width: 45% !important;
//   }
// }

// .col-left {
//   padding-bottom: 0px !important;
// }

// .product-configure-container__section {
//   margin-bottom: 20px !important;
//   @media (max-width: 415px) {
//     display: block !important;
//   }
// }

// .delete-icon {
//   position: absolute;
//   right: 5px;
//   top: 5px;

//   &:hover {
//     cursor: pointer;
//   }
// }

// .variant-default {
//   width: 50%;
//   @media (max-width: $md-display) {
//     width: 30%;
//   }
// }

// .variant-value-input {
//   width: 50%;
//   @media (max-width: $md-display) {
//     width: 70%;
//   }
// }

// ::placeholder {
//   font-size: 1rem;
//   color: lightslategray;
// }

// input {
//   color: $base-font-color;
//   font-family: $base-font-family;
//   border-color: #ced4da;
//   padding: 0 12px;
//   height: 36px;
//   &::placeholder {
//     color: #ced4da;
//   }
// }
// input:focus {
//   outline: none;
// }
// input::-webkit-outer-spin-button,
// input::-webkit-inner-spin-button {
//   -webkit-appearance: none;
//   margin: 0;
// }

// .black-checkbox + span:before {
//   margin-top: 0 !important;
// }
//
</style>

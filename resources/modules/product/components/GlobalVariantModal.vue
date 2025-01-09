<template>
  <template v-if="true">
    <BaseFormGroup
      label="Display Name"
      required
      :error-message="inputDisplayNameError"
      description="Visible to customers on storefront"
    >
      <BaseFormInput
        id="display-name"
        v-model="inputDisplayName"
        placeholder="Size, Color, etc."
        type="text"
        @keyup="inputDisplayNameError = ''"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Unique Name"
      required
      :error-message="inputNameError"
      description="Unique identifier for managing variations"
    >
      <BaseFormInput
        id="unique-name"
        v-model="inputName"
        placeholder="Variant 1, Variant 2, etc."
        type="text"
        @keyup="inputNameError = ''"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Type">
      <BaseFormSelect
        v-model="inputType"
        label-key="name"
        value-key="value"
        :options="[
          { name: 'Image', value: 'image' },
          { name: 'Color', value: 'colour' },
          { name: 'Button', value: 'button' },
          { name: 'Dropdown', value: 'dropdown' },
        ]"
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
      v-for="(option, optionIndex) in inputValues"
      :key="optionIndex"
    >
      <!-- <template v-if="true"> -->
      <BaseFormGroup
        :col="6"
        :label="!optionIndex ? 'Values' : ''"
        :error-message="option.errorMessage"
        :required="!optionIndex"
      >
        <BaseFormInput
          :id="`value-${optionIndex}`"
          v-model="option.variant_value"
          type="text"
          :placeholder="
            inputType === 'colour' ? 'Black, White, etc.' : 'XL, L, etc.'
          "
          @keyup="
            clearValueError(optionIndex);
            nextInput($event, inputValues.length);
          "
          @keypress.enter="addValue"
        />
      </BaseFormGroup>
      <BaseFormGroup
        :col="2"
        :label="!optionIndex ? 'Default' : ''"
      >
        <BaseFormCheckBox
          :id="`checkbox-${optionIndex}`"
          v-model="option.default"
          :value="true"
          @change="updateDefault(optionIndex)"
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
          :disabled="inputValues?.length === 1"
          @click="deleteVariantValue(optionIndex)"
        >
          Remove
        </BaseButton>
      </BaseFormGroup>
      <BaseFormGroup v-show="inputType === 'colour'">
        <BaseColorPicker
          v-model="option.color"
          @focus="isOpen = false"
        />
      </BaseFormGroup>
      <BaseFormGroup v-show="inputType === 'image'">
        <!-- image upload  -->
        <BaseImagePreview
          modal-id="globalVariant-modal"
          no-hover-message
          size="sm"
          :default-image="
            option.image_url ??
              'https://cdn.hypershapes.com/assets/product-default-image.png'
          "
          @delete="removeVariantImage(optionIndex)"
        />
      </BaseFormGroup>
    </template>
    <!-- </template> -->
    <!-- </Draggable> -->
    <BaseFormGroup>
      <BaseButton
        size="sm"
        type="link"
        @click="addValue"
      >
        + Add Value
      </BaseButton>
    </BaseFormGroup>
  </template>

  <div v-if="false">
    <div class="w-100 product-configure-container">
      <div class="product-section-border">
        <div class="product-configure-container__section row">
          <div class="col-md-6">
            <div class="col-left">
              <p class="section-title p-two">
                Display Name <span class="error-message">*</span>
              </p>
              <input
                id="variant-display-name"
                v-model="inputDisplayName"
                type="text"
                class="form-control p-three"
                placeholder="Size, Color, etc..."
                @keyup="inputDisplayNameError = ''"
              >

              <!-- Error -->
              <p
                v-if="inputDisplayNameError !== ''"
                class="error-message error-font-size"
              >
                {{ inputDisplayNameError }}
              </p>

              <!-- Description -->
              <p
                class="p-three mb-0 description-container"
                style="color: #808285"
              >
                Visible to customers on storefront
              </p>
            </div>
          </div>

          <div class="col-md-6">
            <div class="col-right">
              <p
                class="section-title p-two"
                style="padding-top: 20px"
              >
                Unique Name <span class="error-message">*</span>
              </p>
              <input
                v-model="inputName"
                type="text"
                class="form-control p-three"
                @keyup="inputNameError = ''"
              >

              <!-- Error -->
              <p
                v-if="inputNameError !== ''"
                class="error-message error-font-size"
              >
                {{ inputNameError }}
              </p>

              <!-- Description -->
              <p
                style="color: #808285"
                class="mb-0 p-three description-container"
              >
                A unique identifier for managing options, not visible to
                customers
              </p>
            </div>
          </div>
        </div>

        <div
          class="product-configure-container__section row"
          style="margin-bottom: 12px !important"
        >
          <div class="col-md-6">
            <div class="col-left">
              <p class="section-title p-two">
                Type
              </p>
              <select
                id="type"
                v-model="inputType"
                class="form-control"
                name="type"
              >
                <option value="dropdown">
                  Dropdown
                </option>
                <option value="button">
                  Button
                </option>
                <option value="image">
                  Image
                </option>
                <option value="colour">
                  Color
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="type-container-scroll">
          <div class="type-container">
            <div class="row w-100">
              <div class="variant-value-input">
                <div class="section-col-left">
                  <p
                    class="section-title option-value-header ps-0 p-two"
                    style="padding-bottom: 0 !important"
                  >
                    Values
                  </p>
                </div>
              </div>

              <div class="variant-default">
                <div class="row">
                  <div class="col-6">
                    <div class="">
                      <p
                        class="section-title option-value-header text-center p-two"
                        style="padding-bottom: 0 !important"
                      >
                        Default
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <Draggable
              :list="inputValues"
              draggable=".variantValue"
              tag="div"
            >
              <template #item="{ element: value, index }">
                <div
                  class="product-configure-container__section variantValue"
                  style="text-align: center; margin-bottom: 0px !important"
                >
                  <div class="w-100 d-flex">
                    <div style="cursor: grab; padding: 10px 15px 0 0">
                      <i
                        class="fas fa-ellipsis-v"
                        style="color: #e0e0e0"
                      />
                    </div>
                    <div class="w-100">
                      <div class="row w-100">
                        <!-- Variant Value -->
                        <div class="variant-value-input">
                          <div class="section-col-left">
                            <input
                              v-model="value.variant_value"
                              v-focus="false"
                              type="text"
                              class="form-control"
                              :class="{ first_value: index === 0 }"
                              style="margin-top: 10px"
                              @keyup="clearValueError(index)"
                              @keypress.enter="addValue"
                            >
                            <span
                              v-if="value.errorMessage !== ''"
                              class="error-message d-flex error-font-size"
                              style="justify-content: left"
                            >
                              {{ value.errorMessage }}
                            </span>
                          </div>
                        </div>

                        <div class="variant-default">
                          <div class="row">
                            <!-- Variant Default Checkbox-->
                            <div class="col-6 flex-center">
                              <label class="w-100">
                                <input
                                  style="
                                    height: 20px;
                                    color: #808285;
                                    width: 18px;
                                  "
                                  type="checkbox"
                                  :value="value.default"
                                  :checked="value.default"
                                  class="black-checkbox checkbox-size"
                                  @click="updateDefault(index)"
                                >
                                <span />
                              </label>
                            </div>

                            <!-- remove value  -->
                            <div class="col-1">
                              <button
                                type="button"
                                class="datatableEditButton m-0"
                                data-bs-toggle="tooltip"
                                aria-haspopup="true"
                                aria-expanded="false"
                                data-bs-placement="top"
                                title="Delete This Value"
                                @click="deleteVariantValue(index)"
                              >
                                <i
                                  class="fas fa-minus-circle padding-delete"
                                  aria-hidden="true"
                                  style="
                                    color: red;
                                    font-size: 18px;
                                    margin-top: 5px;
                                  "
                                />
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        v-if="inputType === 'colour' || inputType === 'image'"
                      >
                        <!-- Variant Color -->
                        <div
                          v-if="inputType === 'colour'"
                          class="col-6 color-container mt-1"
                          style="padding-bottom: 10px"
                        >
                          <div id="colorpicker">
                            <div class="picker-product row">
                              <div
                                class="col-3 px-0"
                                style="height: 38px; width: 38px"
                              >
                                <button
                                  class="color-picker-button-product col-3 d-flex"
                                  :style="{ 'background-color': value.color }"
                                  @click.stop="openColorPicker(index)"
                                />
                              </div>
                              <div
                                class="col-9 px-0 h-100"
                                style="align-self: center"
                              >
                                <input
                                  v-model="value.color"
                                  style="
                                    border: none;
                                    outline: none;
                                    height: 38px;
                                  "
                                  class="col-9 form-control"
                                  type="text"
                                  maxlength="7"
                                  @focus="isOpen = false"
                                >
                              </div>
                            </div>
                            <ChromeColor
                              v-if="
                                toggleColorPicker &&
                                  selectedColorPicker === index
                              "
                              :key="index"
                              v-click-away="closeColorPicker"
                              class="input-color-picker"
                              :model-value="value.color"
                              style="top: auto"
                              @update:modelValue="
                                (col) => {
                                  updateColor(col.hex, index);
                                }
                              "
                            />
                          </div>
                        </div>
                        <!-- end of variant color picker  -->

                        <!-- Variant Swatch Image -->
                        <div
                          v-if="inputType === 'image'"
                          class="mt-1"
                          style="padding-bottom: 10px"
                          @click="selectedValueIndex(index)"
                        >
                          <div class="image-uploader">
                            <ImagePreview
                              type="inputType"
                              :value="
                                value.image_url ??
                                  'https://cdn.hypershapes.com/assets/product-default-image.png'
                              "
                              :index-number="index"
                              :show-hover-message-window="false"
                              @delete="removeVariantImage(index)"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- end of variant image  -->
                  </div>
                </div>
              </template>
            </Draggable>
          </div>
        </div>

        <div class="addValue">
          <button
            class="h_link mt-3"
            @click="addValue"
          >
            <span class="h_link p-two">
              <i class="fas fa-plus" />&nbsp;&nbsp; Add Value
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <ImageUploader
    type="globalVariant"
    @update-value="chooseImage"
  />
</template>

<script>
/* eslint no-plusplus: ["error", { "allowForLoopAfterthoughts": true }] */
/* eslint no-param-reassign: ["error", { "props": false }] */
/* eslint-disable consistent-return */
import { Chrome } from '@ckpack/vue-color';
import cloneDeep from 'lodash/cloneDeep';
import draggable from 'vuedraggable';
import { Modal } from 'bootstrap';
import ImagePreview from '@shared/components/ImagePreview.vue';
import ImageUploader from '@shared/components/ImageUploader.vue';

export default {
  components: {
    ChromeColor: Chrome,
    Draggable: draggable,
    ImagePreview,
    ImageUploader,
  },

  directives: {
    focus: {
      inserted(el) {
        if (!el.classList.contains('first_value')) el.focus();
      },
    },
  },
  inject: ['variants'],

  data() {
    return {
      inputName: '',
      inputType: 'button',
      defaultArray: [],
      inputValues: [],
      inputDisplayName: '',
      inputNameError: '',
      inputDisplayNameError: '',
      inputsBuffer: {
        inputName: '',
        inputType: 'button',
        defaultArray: [],
        inputValues: [],
        inputDisplayName: '',
        inputNameError: '',
        inputDisplayNameError: '',
      },
      toggleColorPicker: false,
      selectedColorPicker: '',
      selectedIndex: '',
      deletedVariantValues: [],
      selectedVariantDetail: null,
      selectedVariantId: null,
    };
  },

  watch: {
    selectedVariantId: {
      immediate: true,
      deep: true,
      handler(newVal) {
        this.inputNameError = '';
        this.inputDisplayNameError = '';
        this.inputValues = [];
        this.inputDisplayName = '';
        this.inputName = '';
        this.inputType = 'button';
        this.inputsBuffer.inputValues = [];
        this.inputsBuffer.inputDisplayName = '';
        this.inputsBuffer.inputName = '';
        this.inputsBuffer.inputType = 'button';

        if (newVal === 'add') {
          this.addValue();
        } else if (this.variants !== null) {
          this.variants.forEach((item) => {
            if (item.id === this.selectedVariantId) {
              this.selectedVariantDetail = item;
              this.inputDisplayName = item.display_name;
              this.inputName = item.variant_name;
              this.inputType = item.type;
              this.inputsBuffer.inputDisplayName = item.display_name;
              this.inputsBuffer.inputName = item.variant_name;
              this.inputsBuffer.inputType = item.type;

              this.inputValues = item.values.map((el) => ({
                ...el,
                errorMessage: '',
                default: Boolean(el.default),
              }));
              item.values.forEach((i) => {
                this.inputsBuffer.inputValues.push({
                  id: i.id,
                  variant_id: i.variant_id,
                  variant_value: i.variant_value,
                  default: Boolean(i.default),
                  errorMessage: '',
                  color: i.color,
                  image_url: i.image_url,
                });
              });
            }
          });
        }
      },
    },
  },

  created() {
    // axios
    //   .get('/variants/modal')
    //   .then(({ data }) => {
    //     if (data.variants.length !== 0) {
    //       this.parsedVariant = data.variants;
    //     }
    //   })
    //   .catch((e) => console.log(e));
  },

  async mounted() {
    eventBus.$on('shared-variant-type-modal', (data) => {
      if (data === 'add') {
        this.inputValues = [];
        this.inputDisplayName = '';
        this.inputName = '';
        this.inputType = 'button';
        this.addValue();
      }
      this.selectedVariantId = data;
    });
    eventBus.$on('resetInputValuesForGlobalVariant', () => {
      if (this.selectedVariantId !== 'add') {
        this.resetInputs();
      }
    });
    setTimeout(() => {
      const varModal = document.getElementById('add-global-variation-modal');

      varModal.addEventListener('show.bs.modal', () => {
        eventBus.$on('activate-save-shared-variant-function', () => {
          this.saveVariant();
        });
      });
      varModal.addEventListener('hidden.bs.modal', () => {
        this.inputNameError = '';
        this.inputDisplayNameError = '';

        eventBus.$off('activate-save-shared-variant-function');
      });
    }, 1000);
  },

  methods: {
    nextInput(e, latestIndex) {
      if (e?.key === 'Enter') {
        document.getElementById(`value-${latestIndex - 1}`).focus();
      }
    },
    chooseImage(imageData) {
      this.inputValues[this.selectedIndex].image_url = imageData;
    },
    closeModal() {
      Modal.getInstance(
        document.getElementById('add-global-variation-modal')
      ).hide();
    },
    addValue() {
      this.inputValues.push({
        id: null,
        variant_id: null,
        variant_value: '',
        default: false,
        errorMessage: '',
        color: '#000000',
        image_url:
          'https://cdn.hypershapes.com/assets/product-default-image.png',
      });
    },

    clearValueError(index) {
      this.inputValues[index].errorMessage = '';
    },

    updateDefault(index) {
      for (let i = 0; i < this.inputValues.length; i++) {
        if (i !== index) {
          this.inputValues[i].default = false;
        }
      }
    },

    updateColor(color, index) {
      this.inputValues[index].color = color;
    },

    openColorPicker(value) {
      this.toggleColorPicker = true;
      this.selectedColorPicker = value;
    },

    closeColorPicker() {
      this.toggleColorPicker = false;
      this.selectedColorPicker = '';
    },

    selectedValueIndex(index) {
      this.selectedIndex = index;
    },

    deleteVariantValue(index) {
      if (this.inputValues[index].id !== null) {
        this.deletedVariantValues.push(this.inputValues[index].id);
      }
      this.inputValues.splice(index, 1);
    },

    async saveVariant() {
      const noNullValue = true;
      let noDuplicate = true;
      let noErrorMessage = true;

      if (this.inputName.trim() === '' || this.inputDisplayName.trim() === '') {
        if (this.inputName.trim() === '') {
          noErrorMessage = false;
          this.inputNameError = 'Non duplicate unique name is required';
        }
        if (this.inputDisplayName.trim() === '') {
          noErrorMessage = false;
          this.inputDisplayNameError = 'Display name is required';
        }
      } else {
        this.inputDisplayNameError = '';
        this.inputNameError = '';
      }
      if (this.inputValues.length === 0) {
        noErrorMessage = false;
        return this.$toast.error('Error', 'Variant value is required');
      }

      this.inputValues.forEach((item) => {
        // if (usedNames.includes(item.variant_value)) {
        //   noDuplicate = false;
        //   item.errorMessage = 'Name cannot be same!';
        // } else {
        //   usedNames.push(item.variant_value);
        // }
        if (item.variant_value.trim() === '') {
          item.errorMessage = 'Value is required';
        }
        if (item.errorMessage !== '') {
          noErrorMessage = false;
        }
      });

      if (!noErrorMessage || !noNullValue) {
        return;
      }

      if (this.selectedVariantId === 'add') {
        axios
          .post('/addSharedVariant', {
            variant_name: this.inputName,
            type: this.inputType,
            values: this.inputValues,
            display_name: this.inputDisplayName,
          })
          .then(({ data }) => {
            if (data.status.includes('Error')) {
              noDuplicate = false;
              this.inputNameError = 'Non duplicate unique name is required';
              return this.$toast.error(
                'Error',
                'Variant unique name is existed.'
              );
            }
            this.closeModal();
            this.inputNameError = '';
            this.$toast.success('Success', 'Global Variant Created!');
            this.$inertia.visit('/product/options');
          })
          .catch((e) => console.log(e));
      } else {
        const updatedVariants = {
          id: this.selectedVariantId,
          variant_name: this.inputName,
          type: this.inputType,
          values: this.inputValues,
          display_name: this.inputDisplayName,
        };

        if (noNullValue) {
          axios
            .post('/updateSharedVariant', {
              updatedVariants,
              deletedVariantValues: this.deletedVariantValues,
            })
            .then(({ data }) => {
              if (data.status.includes('Error')) {
                this.inputNameError = 'Duplicated Name!';
                return this.$toast.error('Error', 'Variant Name Exist!');
              }
              this.closeModal();
              this.inputNameError = '';
              this.$toast.success('Success', 'Global Variant Updated!');
              this.$inertia.visit('/product/options');
            })
            .catch((e) => console.log(e));
        }
      }
      return true;
    },

    resetInputs() {
      this.inputValues = cloneDeep(this.inputsBuffer.inputValues);
      this.inputDisplayName = this.inputsBuffer.inputDisplayName;
      this.inputName = this.inputsBuffer.inputName;
      this.inputType = this.inputsBuffer.inputType;
      this.deletedVariantValues = [];
    },

    removeVariantImage(index) {
      this.inputValues[index].image_url =
        'https://cdn.hypershapes.com/assets/product-default-image.png';
    },
  },
};
</script>

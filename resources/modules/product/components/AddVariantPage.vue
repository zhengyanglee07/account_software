<template>
  <div class="">
    <div class="back-container">
      <a
        href="/product/options"
        class="back-to-previous"
      >
        <i
          class="fa fa-chevron-left back-to-previous__back-icon"
          aria-hidden="true"
        />
        Back To Product Options
      </a>
    </div>
    <h1 class="header mb-3">
      Add Global Variation
    </h1>

    <div class="container">
      <p class="container__main-title">
        Basic Information
      </p>

      <div class="container__information mb-4">
        <div class="container__col pe-4">
          <p class="container__sub-title">
            Display Name
          </p>
          <input
            v-model="inputDisplayName"
            type="text"
            class="form-control"
            placeholder="Size, Color, etc."
          >
          <p style="color: #b8b8b8">
            Visible to customers on storefront
          </p>
        </div>

        <div class="container__col px-0">
          <p class="container__sub-title">
            Unique Name
          </p>
          <input
            v-model="inputName"
            type="text"
            class="form-control"
          >
          <p style="color: #b8b8b8">
            A unique identifier for managing options, not visible to customers
          </p>
        </div>
      </div>

      <div class="container__information mb-4">
        <div class="container__col pe-4">
          <p class="container__sub-title">
            Type
          </p>
          <select
            id="type"
            v-model="inputType"
            name="type"
            class="form-control"
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
              Colour
            </option>
          </select>
        </div>
      </div>

      <div class="container__information mb-4">
        <!-- no data wrapper  -->
        <template v-if="inputValues.length === 0">
          <EmptyDataContainer />
        </template>
        <!-- end no data wrapper  -->

        <div
          v-else
          class="w-100"
        >
          <div
            v-for="(value, index) in inputValues"
            :key="index"
            class="container__values p-0 row pt-2"
            style="text-align: center"
          >
            <div class="row w-100 mb-4">
              <!-- Variant Value -->
              <div class="col-6 ps-0 pe-4">
                <p style="margin-bottom: 12px; text-align: left">
                  Value
                </p>

                <input
                  v-model="value.variant_value"
                  type="text"
                  class="form-control"
                  @keyup="checkDuplicate(value.variant_value, index)"
                >
                <span
                  v-if="value.errorMessage !== ''"
                  class="error-message error-font-size"
                >{{ value.errorMessage }}</span>
              </div>

              <!-- Variant Default -->
              <div class="col-1">
                <p style="margin-bottom: 12px; text-align: left">
                  Default
                </p>

                <div
                  class="row d-flex"
                  style="align-items: center; height: 2rem"
                >
                  <div
                    class="col-2 d-flex"
                    style="align-items: center"
                  >
                    <input
                      v-model="value.default"
                      class="default-checkbox form-control"
                      type="checkbox"
                      :value="value.variant_value"
                      @click="updateDefault(index)"
                    >
                  </div>
                </div>
              </div>

              <!-- Variant Value Delete -->
              <div
                class="col-1 d-flex"
                style="align-items: left"
              >
                <!-- <span class="delete-btn" @click="deleteVariantValue(index)">
                <i class="fa fa-trash" aria-hidden="true"></i>
              </span> -->
                <button
                  type="button"
                  class="datatableEditButton"
                  data-bs-toggle="tooltip"
                  aria-haspopup="true"
                  aria-expanded="false"
                  data-bs-placement="top"
                  title="Delete This Value"
                  @click="deleteVariantValue(index)"
                >
                  <i
                    class="fas fa-minus-circle"
                    aria-hidden="true"
                    style="color: red"
                  />
                  <!-- <i class="fas fa-trash-alt" /> -->
                </button>
              </div>
            </div>

            <div
              v-if="inputType === 'colour' || inputType === 'image'"
              class="row w-100 pb-4"
            >
              <!-- Variant Color -->
              <div v-if="inputType === 'colour'">
                <p style="margin-bottom: 12px; text-align: left">
                  Color
                </p>

                <div id="colorpicker">
                  <div class="picker row">
                    <button
                      class="color-picker-button-product col-3"
                      :style="{ 'background-color': value.color }"
                      @click.stop="openColorPicker(value.variant_value)"
                    />

                    <input
                      v-model="value.color"
                      style="border: none"
                      class="form-control col-9"
                      type="text"
                      maxlength="7"
                      @focus="isOpen = false"
                    >
                  </div>
                  <ChromeColor
                    v-if="
                      toggleColorPicker &&
                        selectedColorPicker === value.variant_value
                    "
                    :key="index"
                    v-on-clickaway="closeColorPicker"
                    class="background-color-picker"
                    :value="value.color"
                    @input="
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
                @click="selectedValueIndex(index)"
              >
                <p style="margin-bottom: 12px; text-align: left">
                  Image
                </p>
                <div class="image-uploader">
                  <img
                    class="image-uploader__img"
                    :src="
                      value.image_url === null
                        ? '/images/product-default-image.png'
                        : value.image_url
                    "
                    style="object-fit: cover"
                  >
                </div>
                <!-- <product-image-uploader
					:editType="'addVariantValueImage'"
                    :imageSettings="value.image_url"
                    @delete="(() => {})"
				/> -->
              </div>
              <!-- end of variant image  -->
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <button
          class="btn-link"
          @click="addValue"
        >
          <span class="btn-link">
            <i class="fas fa-plus" />&nbsp;&nbsp; Add Value
          </span>
        </button>
      </div>
    </div>

    <div class="footer-container">
      <a
        class="cancel-button"
        href="/product/options"
      > Cancel </a>
      <button
        class="primary-square-button"
        @click="saveVariant()"
      >
        SAVE
      </button>
    </div>

    <!-- Error Message Modal -->
    <VueJqModal
      :modal-id="modalId"
      :scrollable="false"
      :small="true"
    >
      <template #title>
        {{ errorTitle }}
      </template>

      <template #body>
        <p
          v-if="errorTitle === 'Error'"
          class="m-container__text"
        >
          Please remove the error to add new variant value.
        </p>
        <p
          v-if="errorTitle === 'Default Missing'"
          class="m-container__text"
        >
          Please choose any variant value as default.
        </p>
        <p
          v-if="errorTitle === 'Null Value'"
          class="m-container__text"
        >
          Variant value cannot be empty.
        </p>
        <p
          v-if="errorTitle === 'Warning'"
          class="m-container__text"
        >
          Some of the product's details might be affected if the variant value
          had deleted. Are you sure you want to delete?
        </p>
        <p
          v-if="errorTitle === 'Variant Exist'"
          class="m-container__text"
        >
          There is an existing Global Shared Variant with the same name. Please
          change the variant name to proceed.
        </p>
      </template>

      <template #footer>
        <span
          class="cancel-button"
          data-bs-dismiss="modal"
        >Cancel</span>
        <button
          v-if="errorTitle === 'Warning'"
          id="deleteConfirmationButton"
          class="primary-small-square-button"
          @click="removeValue"
        >
          Save
        </button>
        <button
          v-else
          data-bs-dismiss="modal"
          class="primary-small-square-button"
        >
          Save
        </button>
      </template>
    </VueJqModal>
  </div>
</template>

<script>
/* eslint no-plusplus: ["error", { "allowForLoopAfterthoughts": true }] */
import VueColor from '@ckpack/vue-color';
import { Modal } from 'bootstrap';

export default {
  components: {
    ChromeColor: VueColor.Chrome,
  },
  props: ['variant', 'values', 'default', 'safetodelete'],

  data() {
    return {
      inputName: '',
      inputType: 'dropdown',
      inputDefault: '.',
      inputValues: [],
      inputDisplayName: '',
      newVariant: false,
      duplicateError: '',
      toggleColorPicker: false,
      selectedColorPicker: '',
      selectedIndex: '',
      selectedDeleteIndex: '',
      modalId: 'delete-variant-error-modal',
      errorTitle: '',
      deletedVariantValues: [],
    };
  },

  computed: {
    parsedVariant() {
      if (this.variant.length === 0) {
        this.addValue();
        return null;
      }
      return this.variant;
    },

    parsedValues() {
      if (this.values.length === 0) {
        return [];
      }
      return JSON.parse(this.values);
    },

    selectedType() {
      return this.parsedVariant.type;
    },
  },

  watch: {
    variant: {
      deep: true,
      handler(newValue) {
        if (newValue.length === 0) this.newVariant = true;
      },
    },
  },

  mounted() {
    if (this.parsedVariant !== null) {
      this.inputName = this.parsedVariant.variant_name;
      this.inputType = this.parsedVariant.type;
      this.inputDefault = this.default;
      this.inputDisplayName = this.parsedVariant.display_name;
      this.newVariant = false;
      this.parsedValues.forEach((item) => {
        this.inputValues.push({
          id: item.id,
          variant_id: item.variant_id,
          variant_value: item.variant_value,
          default: item.default,
          errorMessage: '',
          color: item.color,
          image_url: item.image_url,
        });
      });
    }
    eventBus.$on('productVariantValueImage', (imageData) => {
      this.inputValues[this.selectedIndex].image_url = imageData;
      eventBus.$emit(
        'emitProductImage',
        this.inputValues[this.selectedIndex].image_url
      );
    });
  },

  methods: {
    addValue() {
      let noError = true;

      for (let i = 0; i < this.inputValues.length; i++) {
        if (this.inputValues[i].variant_value === '') {
          this.inputValues[i].errorMessage = 'Value cannot be empty';
        }
        if (this.inputValues[i].errorMessage !== '') {
          noError = false;
          this.errorTitle = 'Error';
          this.delete_variant_error_modal = new Modal(
            document.getElementById('delete-variant-error-modal')
          );
          this.delete_variant_error_modal.show();
          break;
        }
      }

      if (noError) {
        this.inputValues.push({
          id: null,
          variant_value: '',
          default: 0,
          errorMessage: '',
          color: '#fff',
          image_url: null,
        });
      }
    },

    checkDuplicate(value, index) {
      if (this.inputValues.length === 0) {
        return;
      }

      for (let i = 0; i < this.inputValues.length; i++) {
        const length = index;

        if (i === length) this.inputValues[i].errorMessage = '';
        else if (this.inputValues[i].variant_value === value) {
          this.inputValues[i].errorMessage = 'Duplicate name detected';
          this.inputValues[length].errorMessage = 'Duplicate name detected';
          break;
        } else {
          this.inputValues[length].errorMessage = '';
          this.inputValues[i].errorMessage = '';
        }
      }
    },

    updateDefault(index) {
      this.inputValues[index].default = 1;

      for (let i = 0; i < this.inputValues.length; i++) {
        if (i !== index) {
          this.inputValues[i].default = 0;
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
      this.$modal.show('product-upload');
      eventBus.$emit('editType', 'addVariantValueImage');
    },

    deleteVariantValue(index) {
      if (!this.safetodelete && !this.newVariant) {
        this.errorTitle = 'Warning';
        this.selectedDeleteIndex = index;
        this.delete_variant_error_modal = new Modal(
          document.getElementById('delete-variant-error-modal')
        );
        this.delete_variant_error_modal.show();
      } else {
        if (this.inputValues[index].id !== null) {
          this.deletedVariantValues.push(this.inputValues[index].id);
        }
        this.inputValues.splice(index, 1);
      }
    },

    removeValue() {
      if (this.inputValues[this.selectedDeleteIndex].id !== null) {
        this.deletedVariantValues.push(
          this.inputValues[this.selectedDeleteIndex].id
        );
      }
      this.inputValues.splice(this.selectedDeleteIndex, 1);
      Modal.getInstance(
        document.getElementById('delete-variant-error-modal')
      ).hide();
    },

    saveVariant() {
      let noNullValue = true;
      let noDuplicate = true;

      if (this.inputValues.length === 0) {
        return this.$toast.error('Error', 'Variant Value Is Empty.');
      }

      this.inputValues.forEach((item) => {
        if (item.variant_value === '') {
          noNullValue = false;
        }
      });
      if (this.newVariant === true) {
        if (noNullValue) {
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
                this.errorTitle = 'Variant Exist';
                this.delete_variant_error_modal = new Modal(
                  document.getElementById('delete-variant-error-modal')
                );
                this.delete_variant_error_modal.show();
              } else {
                this.$inertia.visit('/product/options');
              }
            })
            .catch((e) => console.log(e));
        } else {
          if (noNullValue === false) {
            this.errorTitle = 'Null Value';
          } else {
            this.errorTitle = 'Default Missing';
          }
          this.delete_variant_error_modal = new Modal(
            document.getElementById('delete-variant-error-modal')
          );
          this.delete_variant_error_modal.show();
        }
      } else {
        const updatedVariants = {
          id: this.parsedVariant.id,
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
                this.errorTitle = 'Variant Exist';
                this.delete_variant_error_modal = new Modal(
                  document.getElementById('delete-variant-error-modal')
                );
                this.delete_variant_error_modal.show();
              } else {
                this.$inertia.visit('/product/options');
              }
            })
            .catch((e) => console.log(e));
        } else {
          if (noNullValue === false) {
            this.errorTitle = 'Null Value';
          }
          this.delete_variant_error_modal = new Modal(
            document.getElementById('delete-variant-error-modal')
          );
          this.delete_variant_error_modal.show();
        }
      }
      return true;
    },
  },
};
</script>

<style lang="scss" scoped>
*:not(i) {
  font-family: $base-font-family;
  color: $base-font-color;
  margin: 0;
}

.back-container {
  width: 100%;
  margin-bottom: 16px;
}

.main {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.container {
  //   max-width: 700px;
  width: 100%;
  margin-bottom: 16px;
  padding: 36px 24px;
  background-color: #fff;
  border-radius: 5px;
  box-shadow: 0 0 2px grey;

  &__main-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 1rem;
  }

  &__sub-title {
    margin-bottom: 12px;
  }

  &__grey-text {
    color: rgb(204, 197, 197);
  }

  &__information {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
  }

  &__col {
    display: flex;
    flex-direction: column;
    width: 50%;
  }

  &__value-header {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    margin: 0;
    background-color: rgb(245, 243, 243);
  }

  &__value-col-big {
    width: 90%;
  }

  &__value-col-small {
    width: 10%;
  }

  &__values {
    position: relative;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;

    margin-bottom: 2rem;
    border-bottom: 1px solid #e0e0e0;

    &::hover {
      background-color: rgb(235, 235, 235);
    }
  }

  &__footer {
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
  }
}

.color-picker {
  position: relative;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  padding-left: 12px;
  width: 150px;
  height: 30px;
  background-color: #fff;
  box-shadow: 0 0 1px grey;

  &__color {
    width: 30px;
    height: 30px;
  }

  &::hover {
    cursor: pointer;
  }
}

.chrome-color {
  z-index: 10;
  position: absolute;
  right: -230px;
}

.image-uploader {
  width: 60px;
  height: 50px;

  &__img {
    width: 100%;
    height: 100%;

    &::hover {
      cursor: pointer;
    }
  }

  &::hover {
    cursor: pointer;
  }
}

.default-checkbox {
  margin-right: 32px;
}

.add-btn {
  margin: 16px 0 0 0;
}

.delete-btn {
  position: absolute;
  right: 15px;

  &::hover {
    cursor: pointer;
  }

  &__icon {
    &::hover {
      cursor: pointer;
    }
  }
}

.footer-container {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
  margin-top: 32px;
}

.btn-link {
  font-weight: 400;
  color: #007bff;
  text-decoration: none;
  border: none;
  background-color: transparent;
  &:active,
  &:focus,
  &:hover {
    outline: none;
  }
}
</style>

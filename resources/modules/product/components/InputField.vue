<template>
  <template
    v-if="
      type === 'Text Field' || type === 'Number Field' || type === 'Text Area'
    "
  >
    <BaseFormGroup
      label="Default Value"
      :col="8"
    >
      <BaseFormInput
        :id="`default-value-${optionIndex}-${selectedIndex}`"
        :type="type === 'Number Field' ? 'number' : 'text'"
        :model-value="input.label"
        :disabled="disable"
        :placeholder="
          type !== 'Number Field'
            ? 'Enter greeting quote, etc.'
            : 'Enter a number'
        "
        @update:modelValue="inputValue('label', $event)"
      />
    </BaseFormGroup>
    <BaseFormGroup
      :label="`Price ( ${currency === 'MYR' ? 'RM' : currency} )`"
      :col="4"
      required
      :error-message="input?.priceError ? 'Price is required' : ''"
    >
      <BaseFormInput
        :id="`price-${optionIndex}-${selectedIndex}`"
        :step="1"
        :min="0.0"
        type="number"
        :model-value="input.single_charge"
        :disabled="disable"
        @update:modelValue="
          inputValue('single_charge', ('' + $event).replace('-', ''))
        "
      />
    </BaseFormGroup>
    <BaseFormGroup>
      <BaseFormCheckBox
        :id="`set-maximum-${optionIndex}-${selectedIndex}`"
        :model-value="input.is_default"
        :value="true"
        :disabled="disable"
        @change="inputValue('is_default', $event.target.checked)"
      >
        Set Maximum
      </BaseFormCheckBox>
    </BaseFormGroup>
    <BaseFormGroup
      v-if="input.is_default"
      :label="type === 'Number Field' ? 'Maximum' : 'Maximum Characters'"
      required
      :error-message="input?.valueError ? 'Maximum value is required' : ''"
    >
      <BaseFormInput
        :id="`maximum-${optionIndex}-${selectedIndex}`"
        :step="1"
        :min="0.0"
        type="number"
        :model-value="input.value_1"
        :disabled="disable"
        @update:modelValue="
          inputValue('value_1', ('' + $event).replace('-', ''))
        "
      />
    </BaseFormGroup>
  </template>
  <template v-else>
    <BaseFormGroup
      :label="!selectedIndex ? 'Values' : ''"
      :col="5"
      :error-message="input?.labelError ? 'Value is required' : ''"
      :required="!selectedIndex"
    >
      <BaseFormInput
        :id="`label-${optionIndex}-${selectedIndex}`"
        type="text"
        :model-value="input.label"
        :disabled="disable"
        :placeholder="
          type === 'Color' ? 'Black, White, etc.' : 'Option 1, Option 2, etc.'
        "
        @update:modelValue="inputValue('label', $event)"
      />
      <!-- @keyup.enter="addInputOption" -->
    </BaseFormGroup>
    <BaseFormGroup
      v-if="!inputOptions[optionIndex].is_total_Charge || type !== 'Checkbox'"
      :label="
        !selectedIndex
          ? `Price ( ${currency === 'MYR' ? 'RM' : currency} )`
          : ''
      "
      :col="3"
      :required="!selectedIndex"
      :error-message="input?.priceError ? 'Price is required' : ''"
    >
      <BaseFormInput
        :id="`price-${optionIndex}-${selectedIndex}`"
        :step="1"
        :min="0.0"
        type="number"
        :model-value="input.single_charge"
        :disabled="disable"
        @update:modelValue="
          inputValue('single_charge', ('' + $event).replace('-', ''))
        "
      />
    </BaseFormGroup>
    <BaseFormGroup
      :label="!selectedIndex ? 'Default' : ''"
      :col="2"
    >
      <BaseFormCheckBox
        :id="`is-default-${optionIndex}-${selectedIndex}`"
        :model-value="input.is_default"
        :value="true"
        :disabled="disable"
        @change="inputValue('is_default', $event.target.checked)"
      />
    </BaseFormGroup>
    <BaseFormGroup
      :col="2"
      :label="!selectedIndex ? '&nbsp' : ''"
    >
      <BaseButton
        size="sm"
        type="link"
        color="danger"
        :disabled="disable || inputOptions[optionIndex]?.inputs.length === 1"
        @click="deleteInputOption(selectedIndex, input.id)"
      >
        Remove
      </BaseButton>
    </BaseFormGroup>
    <BaseFormGroup
      v-show="type === 'Color'"
      :error-message="input?.valueError ? 'Color is required' : ''"
    >
      <BaseColorPicker
        :model-value="input.value_1"
        @update:modelValue="updateInputOptionColor($event)"
        @focus="isOpen = false"
      />
    </BaseFormGroup>
    <BaseFormGroup
      v-show="type === 'Image'"
      :error-message="input?.valueError ? 'Image is required' : ''"
    >
      <div style="position: relative; width: 70px; height: 65px">
        <section
          v-if="disable"
          style="width: 100%; height: 100%; position: relative"
        >
          <img
            :src="
              input.value_1 === null
                ? 'https://cdn.hypershapes.com/assets/product-default-image.png'
                : input.value_1
            "
            style="object-fit: cover; width: 100%; height: 100%"
          >
        </section>

        <!-- image upload  -->
        <BaseImagePreview
          v-else
          :modal-id="
            optionType !== 'shared'
              ? 'customOption-modal'
              : 'globalCustom-modal'
          "
          no-hover-message
          size="sm"
          :default-image="
            inputOptions[optionIndex].inputs[selectedIndex].value_1 ||
              'https://cdn.hypershapes.com/assets/product-default-image.png'
          "
          @delete="deleteImage()"
          @click="
            assignOptionIndex({
              optionIndex,
              optionValueIndex: selectedIndex,
            })
          "
        />
      </div>
    </BaseFormGroup>
  </template>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
// import { Chrome } from '@ckpack/vue-color';
import { Tooltip } from 'bootstrap';
import eventBus from '@services/eventBus.js';

export default {
  name: 'InputField',
  directives: {
    focus: {
      inserted(el) {
        if (!el.classList.contains('first_value')) el.focus();
      },
    },
  },
  props: [
    'optionType',
    'input',
    'selectedIndex',
    'optionIndex',
    'type',
    'currency',
    'dragOption',
  ],

  data() {
    return {
      // picker
      checked: this.input.is_default,
      selected: '',
      open_color_picker: false,
      isOpen: false,
      imagePath: '',
      typeOfSingleCharge: ['Default', 'Create Charge'],
    };
  },

  computed: {
    ...mapState('product', ['inputOptions', 'customizationIsValid']),
    disable() {
      return (
        this.inputOptions[this.optionIndex].is_shared === 1 &&
        this.optionType !== 'shared'
      );
    },
  },
  mounted() {
    eventBus.$on(
      `fetchCustomizationImage-${this.optionIndex}-${this.selectedIndex}`,
      (imageData) => {
        this.inputValue('value_1', imageData);
      }
    );
  },
  created() {
    this.$nextTick(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
      );

      const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl, {
          trigger: 'manual',
        });
      });
    });
  },

  methods: {
    ...mapMutations('product', [
      'pushOptionArray',
      'deleteOptionArray',
      'inputOptionValue',
      'assignOptionIndex',
    ]),
    addInputOption() {
      this.$emit('addInputOptionArray', this.type);
    },
    deleteInputOption(index, id) {
      this.deleteOptionArray({
        type: 'inputs',
        optionIndex: this.optionIndex,
        index,
        id,
      });
    },
    inputValue(object, value) {
      this.inputOptionValue({
        type: 'inputs',
        object,
        optionIndex: this.optionIndex,
        index: this.selectedIndex,
        value,
      });
    },
    updateInputOptionColor(value) {
      this.inputValue('value_1', value.hex ?? value);
    },
    deleteImage() {
      this.inputValue(
        'value_1',
        'https://cdn.hypershapes.com/assets/product-default-image.png'
      );
    },
    // checked(value){
    //     console.log(this.input.is_default === 1)
    //     if(this.input.is_default === 1){
    //         document.querySelector('.default').checked = false
    //     }
    // }
  },
};
</script>

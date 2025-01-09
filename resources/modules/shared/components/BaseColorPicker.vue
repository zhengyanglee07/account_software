<template>
  <div
    class="col-6 color-container mt-1"
    style="padding-bottom: 10px"
  >
    <div id="colorpicker">
      <div class="picker row">
        <div
          class="col-3 px-0"
          style="height: 38px; width: 38px"
        >
          <button
            class="color-picker-button col-3 d-flex"
            style="height: 38px; width: 38px"
            :style="{ 'background-color': value }"
            @click.stop="openColorPicker(index)"
          />
        </div>
        <div
          class="col-9 px-0 h-100"
          style="align-self: center"
        >
          <input
            v-model="value"
            style="height: 38px"
            class="col-9 form-control"
            type="text"
            maxlength="7"
            @focus="isOpen = false"
          >
        </div>
      </div>
      <ChromeColor
        v-if="toggleColorPicker && selectedColorPicker === index"
        :key="index"
        v-click-away="closeColorPicker"
        class="input-color-picker"
        :model-value="value"
        style="top: auto"
        @update:modelValue="
          (col) => {
            updateColor(col.hex, index);
          }
        "
      />
    </div>
  </div>
</template>

<script>
import { Chrome } from '@ckpack/vue-color';

export default {
  components: {
    ChromeColor: Chrome,
  },
  props: {
    index: {
      type: Number,
      default: 0,
    },
    modelValue: {
      type: String,
      default: '#000000',
    },
  },

  data() {
    return {
      toggleColorPicker: true,
      selectedColorPicker: '',
    };
  },

  computed: {
    value: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      },
    },
  },

  methods: {
    updateColor(color) {
      this.value = color;
    },

    openColorPicker(value) {
      this.toggleColorPicker = true;
      this.selectedColorPicker = value;
    },

    closeColorPicker() {
      this.toggleColorPicker = false;
      this.selectedColorPicker = '';
    },
  },
};
</script>

<style>
.input-color-picker {
  position: absolute;
  z-index: 2;
}
</style>

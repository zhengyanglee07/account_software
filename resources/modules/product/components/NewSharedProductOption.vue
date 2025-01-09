<template>
  <ProductCustomization
    :option-type="optionType"
    :currency="currency"
    :hide-add-global-button="true"
    :no-seperator="true"
  />
  <ImageUploader
    type="globalCustom"
    @update-value="chooseImage"
  />
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import ProductCustomization from '@product/components/ProductCustomization.vue';
import { Modal } from 'bootstrap';
import ImageUploader from '@shared/components/ImageUploader.vue';
import eventBus from '@services/eventBus.js';

export default {
  components: {
    ProductCustomization,
    ImageUploader,
  },

  props: ['type', 'optionType', 'currency'],

  data() {
    return {
      hasProductOption: false,
      disabled: false,
      loading: false,
      selected: '',
      oriArray: '',
    };
  },
  computed: {
    ...mapState('product', ['optionIndex', 'optionValueIndex']),
  },
  mounted() {
    eventBus.$on('updateSharedCustomization', () => {
      axios.get('/get/product/option').then((response) => {
        this.$store.dispatch('product/fetchProductOption', {
          productOptionArray: response.data.filter(
            (data) => data.id === this.type
          ),
        });
      });
    });
  },
  methods: {
    ...mapMutations('product', ['pushOptionArray', 'currentOriginalArray']),
    chooseImage(e) {
      eventBus.$emit(
        `fetchCustomizationImage-${this.optionIndex}-${this.optionValueIndex}`,
        e
      );
    },
    deleteSharedOption() {
      this.isSaveStatus(false);
      axios.get(`/delete/option/${this.type}`).then((response) => {
        this.$toast.success('Success', 'Product Option Deleted');
        Modal.getInstance(
          document.getElementById('product-page-delete-modal')
        ).hide();
        this.$inertia.visit('/product/customizations');
      });
    },
  },
};
</script>

<style scoped lang="scss">
*:not(i) {
  font-family: $base-font-family;
  font-size: $base-font-size;
  color: $base-font-color;
}
</style>

<template>
  <BaseCard
    :has-header="!onboarding"
    title="Pricing"
  >
    <BaseFormGroup
      col="md-6"
      :label="
        taxable ? 'Selling Price ( Price not include tax)' : 'Selling Price'
      "
    >
      <!-- <slot name="exceptDefault" /> -->
      <BaseFormInput
        ref="price_input"
        v-model.number="product.productPrice"
        v-bind="args"
        @input="$emit('inputPrice', product.productPrice)"
      >
        <template #prepend>
          {{ currency === 'MYR' ? 'RM' : currency }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>

    <slot name="comparePrice" />
    <div>
      <slot name="isTaxable" />
    </div>
  </BaseCard>
</template>
<script>
export default {
  props: ['currency', 'price', 'onboarding', 'taxable'],
  setup(props) {
    return {
      args: {
        placeholder: '0.00',
        min: '0.00',
        max: '10000.00',
        step: '1',
        type: 'number',
      },
    };
  },
  data() {
    return {
      product: {
        productPrice: 0,
      },
    };
  },
  watch: {
    price(newValue) {
      if (newValue) {
        this.product.productPrice = newValue;
      }
    },
  },
  mounted() {
    if (this.price) {
      this.product.productPrice = this.price;
    }
  },
};
</script>
<style scoped lang="scss">
.general-card {
  padding: 20px 0;
}

@media (max-width: $md-display) {
  .general-card .general-card__body:nth-child(2) {
    padding-top: 0 !important;
  }
}

.general-card-section {
  margin-bottom: 0rem !important;
}

.input-group {
  border-color: $table-border-color;
  flex-wrap: unset;
  .input-group-text {
    // border-left: 1px solid !important;
    background-color: white;
    height: 100%;
    border-radius: 3px 0 0 3px;
    border-color: $table-border-color !important;
  }

  .form-control {
    border-left: none;
    height: 100%;
    border-color: $table-border-color;
  }
}
.input-group-prepend {
  height: 36px;
}

.prepand-no-left-border {
  border-left: 0px;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  padding-right: 12px;
}

.append-no-right-border {
  border-right: 0px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

input[type='checkbox'].purple-checkbox {
  position: absolute;
  opacity: 0;
  z-index: -1;
}

input[type='checkbox'].purple-checkbox + span:before {
  content: '';
  border: 1px solid grey;
  border-radius: 3px;
  display: inline-block;
  width: 16px;
  height: 16px;
  margin-right: 0.5em;
  margin-top: 0.5em;
  vertical-align: -2px;
  cursor: pointer;
}

input[type='checkbox'].purple-checkbox:checked + span:before {
  background-image: url('/FontAwesomeSVG/check-white.svg');
  background-repeat: no-repeat;
  background-position: center;
  background-size: 12px;
  border-radius: 2px;
  background-color: #7766f7;
  color: white;
  cursor: pointer;
}

.general-card__body {
  padding: 0 20px !important;
}

@media (max-width: 767px) {
  .input-content {
    font-size: 15px !important;
    padding: 8px 12px !important;
  }
  .input-group {
    width: 100% !important;
  }
}
</style>

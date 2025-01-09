<template>
  <BaseCard
    :has-header="!onboarding"
    :title="onboarding ? '' : 'General'"
  >
    <BaseFormGroup
      v-bind="args"
      :error-message="
        productTitle?.trim() === '' && submit ? `* Title cannot empty` : ''
      "
    >
      <BaseFormInput
        id="product-title"
        ref="title_input"
        v-model="productTitle"
        type="text"
        name="productTitle"
        @update:modelValue="$emit('inputTitle', productTitle)"
      />
    </BaseFormGroup>
    <slot name="description" />
  </BaseCard>
</template>
<script>
export default {
  props: ['submit', 'title', 'onboarding'],
  setup() {
    return {
      args: {
        label: 'Title',
        required: true,
      },
    };
  },
  data() {
    return {
      productTitle: '',
    };
  },
  watch: {
    title(newValue) {
      if (newValue) {
        this.productTitle = newValue;
      }
    },
  },
  mounted() {
    this.productTitle = this.title;
  },
};
</script>

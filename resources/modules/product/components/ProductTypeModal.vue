<template>
  <BaseModal
    :small="true"
    modal-id="product-type-modal"
    title="Choose a product type"
  >
    <BaseFormGroup
      v-for="(productType, index) in productTypes"
      :key="index"
      col="6"
    >
      <BaseButton
        type="outline btn-outline-dashed btn-outline-default"
        class="d-flex justify-content-start me-5 w-100 h-100"
        :active="selectedType === productType.type"
        @click="selectedType = productType.type"
      >
        <i :class="productType.icon" class="me-2" style="font-size: 30px" />
        <div class="ms-3 text-start">
          <p class="m-0 fw-bolder fs-5 text-dark">{{ productType.name }}</p>
          <p class="m-0 text-muted fw-bold fs-7">
            {{ productType.description }}
          </p>
        </div>
      </BaseButton>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="saveProductType"> Next </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue';

const selectedType = ref('course');
const productTypes = [
  {
    icon: 'fa-solid fa-chalkboard-user',
    type: 'course',
    name: 'Course',
    description:
      'To grow your business and reputation by sharing your knowledge online',
  },
  {
    icon: 'fa-solid fa-box',
    type: 'physical',
    name: 'Physical Product',
    description:
      'To sell t-shirts, shoes, electronics or other deliverable products',
  },
  {
    icon: 'fa-solid fa-cloud-arrow-down',
    type: 'virtual',
    name: 'Virtual Product',
    description: 'To sell ebook, tickets, templates, or other digital assets',
  },
];
const saveProductType = () => {
  localStorage.productType = selectedType.value;
  window.location.href = '/product/create';
};
</script>
<style lang="scss" scoped>
.type-container {
  color: #009ef7;
  border-color: #009ef7;
  background-color: #f1faff !important;
  border-width: 1px;
  border-style: dashed;
}
</style>

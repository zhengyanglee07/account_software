<template>
  <div>
    <BaseFormGroup label="Which product?">
      <BaseMultiSelect
        v-model="properties"
        :options="options"
        label="productTitle"
        :reduce="reducer"
      >
        <template #option="{ option }">
          {{ option.productTitle }}
        </template>
      </BaseMultiSelect>
    </BaseFormGroup>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import propertiesSelectionMixin from '@automation/mixins/propertiesSelectionMixin.js';

export default {
  name: 'TriggerModalPurchaseProductContent',
  mixins: [propertiesSelectionMixin],
  computed: {
    ...mapState('automations', {
      options: (state) => state.usersProducts,
    }),
  },
  methods: {
    reducer(props) {
      return { users_product_id: props?.users_product_id };
    },
  },
};
</script>

<style lang="scss" scoped></style>

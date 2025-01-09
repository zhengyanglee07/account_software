<template>
  <div>
    <BaseFormGroup
      v-if="filters.length > 0"
      label="Trigger Filters"
    >
      <BaseFormGroup
        v-for="(filter, i) in filters"
        :key="i"
        label=""
        class="row mt-2"
      >
        <BaseFormGroup col="md-5">
          <BaseMultiSelect
            v-model="filter.type"
            :options="typeOptions"
            label="display"
            :reduce="(o) => o.type"
            placeholder="Product name"
            @option:selected="resetConditionAndValue(i, filter.type)"
          />
        </BaseFormGroup>
        <BaseFormGroup col="md-3">
          <BaseMultiSelect
            v-model="filter.condition"
            :options="
              hasEqualityCondition(filter.type)
                ? equalityConditions
                : comparisonConditions
            "
            placeholder="Please select a condition"
            @option:selected="handlePropertiesChange"
          />
        </BaseFormGroup>
        <BaseFormGroup col="md-4">
          <BaseFormInput
            v-if="filter.type === 'total_sales'"
            v-model="filter.value"
            type="number"
            placeholder="0"
            required
            @input="handlePropertiesChange"
          />
          <BaseMultiSelect
            v-else
            v-model="filter.value"
            label="label"
            :options="getFilterValuesOptions(filter.type)"
            :reduce="getFilterValuesReducer(filter.type)"
            :placeholder="getFilterValuesPlaceholder(filter.type)"
            @option:selected="handlePropertiesChange"
          />
        </BaseFormGroup>
        <template #label-row-end>
          <span
            style="font-size: 1.5rem; color: grey; cursor: pointer"
            @click="removeFilter(i)"
          >
            &times;
          </span>
        </template>
      </BaseFormGroup>
    </BaseFormGroup>

    <BaseButton
      has-add-icon
      type="light-primary"
      @click="addFilter"
    >
      Add Filter
    </BaseButton>
  </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { mapState } from 'vuex';

export default {
  name: 'TriggerModalPlaceAnOrderContent',
  props: ['modelValue'],
  data() {
    return {
      currency: '',
      filters: [],
      categories: [],

      typeOptions: [
        {
          type: 'product_name',
          display: 'Product Name',
        },
        {
          type: 'product_category',
          display: 'Product Category',
        },
        {
          type: 'total_sales',
          display: 'Total Sales',
        },
        {
          type: 'sales_channel',
          display: 'Sales Channel',
        },
        {
          type: 'payment_status',
          display: 'Payment Status',
        },
        {
          type: 'fulfillment_status',
          display: 'Fulfillment Status',
        },
      ],

      equalityConditions: ['is', 'is not'],
      comparisonConditions: [
        'greater than or equal to',
        'less than or equal to',
        'between',
        'equal',
      ],

      salesChannelValues: ['Online Store', 'Mini Store', 'Funnel'],
      paymentStatusValues: ['Paid', 'Unpaid'],
      fulfillmentStatusValues: ['Fulfilled', 'Unfulfilled'],
    };
  },

  computed: {
    ...mapState('automations', ['usersProducts']),
  },

  mounted() {
    const properties = this.modelValue;

    // load categories from user acct
    this.$axios.get('/product/getallcategory').then((res) => {
      this.categories = res.data;
    });

    if (!properties) {
      this.emitProperties();
      return;
    }

    const { filters } = properties;
    this.filters = cloneDeep(
      // just a precautionary step if laravel doesn't cast string -> array
      typeof filters === 'string' ? JSON.parse(filters) : filters
    );
  },

  methods: {
    handlePropertiesChange() {
      this.emitProperties();
    },

    emitProperties() {
      this.$emit('update:modelValue', {
        filters: this.filters,
      });
    },

    addFilter() {
      this.filters.push({
        type: 'product_name',
      });
    },

    removeFilter(i) {
      this.filters = this.filters.filter((_, fIdx) => fIdx !== i);
      this.emitProperties();
    },

    resetConditionAndValue(i, type) {
      const defaultVal = {
        sales_channel: 'Online Store',
        payment_status: 'Paid',
        fulfillment_status: 'Fulfilled',
      };

      this.filters = this.filters.map((f, fIdx) => ({
        ...f,
        condition: fIdx === i ? '' : f.condition,
        value: fIdx === i ? defaultVal[type] ?? '' : f.value,
      }));

      this.emitProperties();
    },

    hasEqualityCondition(type) {
      return [
        'product_name',
        'product_category',
        'sales_channel',
        'payment_status',
        'fulfillment_status',
      ].includes(type);
    },

    hasFilterValues(type) {
      return ['sales_channel', 'payment_status', 'fulfillment_status'].includes(
        type
      );
    },

    getFilterValuesOptions(type) {
      if (type === 'product_name') {
        return this.usersProducts
          .filter((p) => p.id) // remove Any Product option
          .map((p) => ({
            ...p,
            label: p.productTitle,
          }));
      }

      if (type === 'product_category') {
        return this.categories.map((c) => ({
          ...c,
          label: c.name,
        }));
      }

      if (type === 'sales_channel') {
        return this.salesChannelValues;
      }

      if (type === 'payment_status') {
        return this.paymentStatusValues;
      }

      if (type === 'fulfillment_status') {
        return this.fulfillmentStatusValues;
      }

      return [];
    },

    getFilterValuesReducer(type) {
      if (type === 'product_name' || type === 'product_category') {
        return (v) => v.id;
      }

      return (v) => v;
    },

    getFilterValuesPlaceholder(type) {
      if (type === 'product_name') {
        return 'Choose a product...';
      }

      if (type === 'product_category') {
        return 'Choose a category...';
      }

      return '';
    },
  },
};
</script>

<style scoped></style>

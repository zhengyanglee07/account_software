<template>
  <div>
    <div class="text-left">
      <label class="font-weight-bold"> What should be the condition? </label>

      <div class="input-wrapper row">
        <div class="col-md-6 px-1 mb-2">
          <select
            v-model="operator"
            class="dropdown-input"
            @change="handlePropertiesChange"
          >
            <option>greater than or equal to</option>
            <option>less than or equal to</option>
            <option>equal to</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-center px-2 mb-2">
          <label>{{ currency }}</label>
          <input
            v-model="spent"
            type="number"
            class="form-control ms-2"
            style="height: 38px"
            @input="handlePropertiesChange"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TriggerModalOrderSpentContent',
  props: ['value'],
  data() {
    return {
      currency: '',
      operator: 'greater than or equal to',
      spent: 0,
    };
  },

  mounted() {
    this.$axios
      .get('/get/default/currency')
      .then((res) => {
        this.currency = res.data.currency;
      })
      .catch(() => {
        this.$toast.error('Error', 'Failed to load currency');
      });

    const properties = this.value;

    if (!properties) {
      this.emitProperties();
      return;
    }

    this.operator = properties.operator;
    this.spent = properties.spent;
  },

  methods: {
    handlePropertiesChange() {
      this.emitProperties();
    },

    emitProperties() {
      this.$emit('input', {
        operator: this.operator,
        spent: this.spent,
      });
    },
  },
};
</script>

<style scoped></style>

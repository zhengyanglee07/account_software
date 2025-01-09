<template>
  <VSelect
    v-model="selectedTimezone"
    class="account-timezone-setting-select"
    :options="timezones"
    style="width: 100%"
  >
    <template #no-options>
      <span style="color: darkgray">No results</span>
    </template>
  </VSelect>
</template>

<script>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { timezones } from '@shared/lib/timezone.js';

export default {
  name: 'AccountTimezoneSetting',
  components: {
    vSelect,
  },
  props: {
    value: String,
  },
  data() {
    return {
      timezones,
    };
  },
  computed: {
    selectedTimezone: {
      get() {
        return this.value;
      },
      set(value) {
        this.$emit('input', value);
      },
    },
  },
};
</script>

<style lang="scss">
.account-timezone-setting-select {
  width: 280px;

  .vs__dropdown-option {
    padding: 10px 15px;
  }
}
</style>

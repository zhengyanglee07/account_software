<template>
  <BaseButton
    style="margin-right: 10px"
    type="white"
    @click="filterContacts"
  >
    Filter
  </BaseButton>
  <BaseButton
    style="margin-right: 10px"
    type="white"
    @click="clearFilter"
  >
    Clear Filter
  </BaseButton>
</template>

<script>
import { validateConditionFilters } from '@people/lib/conditionFilters.js';
import { mapMutations, mapState } from 'vuex';

export default {
  name: 'PeopleFilterConditionsButtons',
  data() {
    return {
      showFilterSpinner: false,
    };
  },
  computed: mapState('people', ['conditionFilters']),
  methods: {
    ...mapMutations('people', [
      'updateShowPeopleDesc',
      'updateContacts',
      'clearCheckedContactIds',

      'updateConditionFiltersShowErrors',
      'resetConditionFilters',
    ]),
    showSpinner() {
      this.showFilterSpinner = true;
    },
    removeSpinner() {
      this.showFilterSpinner = false;
    },
    refreshDatatableWithContacts(contacts) {
      this.updateContacts({ contacts });
      this.clearCheckedContactIds();
    },
    filterContacts() {
      const { conditionFilters } = this;
      if (!validateConditionFilters(conditionFilters)) {
        // show validation error styles in all inputs/selects
        this.updateConditionFiltersShowErrors({ show: true });
        return;
      }

      this.showSpinner();

      this.$axios
        .post('/people/filter', {
          conditionFilters,
        })
        .then(({ data }) => {
          this.updateShowPeopleDesc(false);
          this.refreshDatatableWithContacts(data.data);
        })
        .catch((err) => {
          console.error(err);
        })
        .finally(() => {
          this.removeSpinner();
        });
    },
    clearFilter() {
      this.showSpinner();

      this.resetConditionFilters();

      // directly refresh page to prevent issue on pagination
      window.location.reload();
    },
  },
};
</script>

<style scoped lang="scss"></style>

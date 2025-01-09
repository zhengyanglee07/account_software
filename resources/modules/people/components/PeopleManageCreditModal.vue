<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Manage Store Credit"
  >
    <!-- CREDIT -->
    <BaseFormGroup
      for="addTagOption"
      label="Store Credit"
    >
      <BaseFormSelect v-model="selectedCreditType">
        <option
          v-for="(type, index) in creditTypes"
          :key="index"
        >
          {{ type }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <BaseFormGroup>
      <BaseFormInput
        id="credit"
        v-model.number="credit"
        type="number"
        min="0.00"
        @blur="resetInputOnBlur"
      >
        <template #prepend>
          RM
        </template>
      </BaseFormInput>
    </BaseFormGroup>

    <!-- EXPIRE DATE -->
    <BaseFormGroup
      v-if="selectedCreditType !== 'Deduct'"
      for="addTagOption"
      label="Expire Date (months)"
    >
      <BaseFormInput
        v-model.number="expireMonthCount"
        type="number"
        min="1"
        @blur="resetInputOnBlur"
      />
    </BaseFormGroup>

    <BaseFormGroup
      for="addTagOption"
      label="Reason"
    >
      <BaseFormSelect v-model="selectedReason">
        <option
          v-for="(reason, index) in reasonSelections"
          :key="index"
        >
          {{ reason }}
        </option>
      </BaseFormSelect>
    </BaseFormGroup>

    <BaseFormGroup
      for="addTagOption"
      label="Notes"
    >
      <BaseFormTextarea
        v-model="notes"
        rows="1"
        style="height: auto"
      />
    </BaseFormGroup>

    <BaseFormGroup>
      <template #error-message>
        {{ errMessage }}
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="updatingCredit"
        @click="updateStoreCredits"
      >
        Update Store Credit
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
/* eslint no-return-assign: 1 */
/* eslint consistent-return: 1 */
import { mapState, mapMutations } from 'vuex';
import { Modal } from 'bootstrap';
import eventBus from '@shared/services/eventBus.js';

export default {
  props: {
    modalId: String,
    currency: String,
    exchangeRate: String,
    isPeopleProfile: Boolean,
  },

  data() {
    return {
      errMessage: '',
      credit: 0,
      creditTypes: ['Add', 'Deduct', 'Set'],
      reasonSelections: ['Refund', 'Reward', 'Other'],
      selectedCreditType: 'Add',
      selectedReason: 'Refund',
      expireMonthCount: 1,
      notes: '',
      updatingCredit: false,
    };
  },

  computed: {
    ...mapState('people', ['checkedContactIds']),

    isCheckedContactsEmpty() {
      return this.checkedContactIds.length === 0;
    },
  },

  watch: {
    credit(newVal) {
      this.errMessage = '';
    },
    checkedContactIds(newVal) {
      this.errMessage = '';
    },
  },

  methods: {
    ...mapMutations('people', ['clearCheckedContactIds', 'updateContacts']),

    resetStoreCredit() {
      this.selectedCreditType = 'Add';
      this.selectedReason = 'Refund';
      this.expireMonthCount = 1;
      this.credit = 0;
      this.notes = '';
    },
    updateStoreCredits() {
      // Validate the input, return if not valide
      this.errMessage = '';
      if (this.credit === 0 && this.selectedCreditType !== 'Set') {
        return (this.errMessage = 'Please set a credit amount.');
      }
      if (this.isCheckedContactsEmpty) {
        return (this.errMessage = 'Please select at least one people.');
      }

      this.updatingCredit = true;
      axios
        .post('/people/addStoreCredit', {
          contacts: this.checkedContactIds,
          credit: this.credit * 100 * parseFloat(this.exchangeRate),
          type: this.selectedCreditType,
          reason: this.selectedReason,
          notes: this.notes,
          expireMonths: this.expireMonthCount,
        })
        .then(({ data }) => {
          this.updateContacts({ contacts: data });
          this.$toast.success('Success', 'Successfully update store credit.');
          if (!this.isPeopleProfile) {
            this.$inertia.visit('/people');
            this.clearCheckedContactIds();
          } else {
            this.resetStoreCredit();
            eventBus.$emit('processedContactUpdated', data);
          }
          Modal.getInstance(
            document.getElementById('manageCreditModal')
          ).hide();
        })
        .catch((e) => {
          this.$toast.error('Error', 'Failed to update store credit.');
        })
        .finally(() => {
          this.updatingCredit = false;
        });
    },

    resetInputOnBlur() {
      if (this.credit < 0 || this.credit === '') {
        this.credit = 0;
      }
      if (this.expireMonthCount <= 0 || this.expireMonthCount === '') {
        this.expireMonthCount = 1;
      }
    },

    closeModal() {
      Modal.getInstance(document.getElementById('manageCreditModal')).hide();
    },
  },
};
</script>

<style scoped lang="scss"></style>

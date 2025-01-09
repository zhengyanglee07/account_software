<template>
  <BaseModal
    :modal-id="modalId"
    title="Create Payout Request"
  >
    <BaseFormGroup
      label="Payout amount"
      :description="
        'Total approved commissions available for payout: ' +
          defaultCurrency +
          ' ' +
          parseFloat(availableCommissions, 10).toFixed(2) +
          '.' +
          ' After this payout request, the remaining approved commissions will be ' +
          defaultCurrency +
          ' ' +
          parseFloat(availableCommissions - (amountToPay || 0), 10).toFixed(2)
      "
      :error-message="minValueErr || maxValueErr"
    >
      <BaseFormInput
        v-model="amountToPay"
        type="number"
        :min="minimumPayout"
        :max="availableCommissions"
      >
        <template #prepend>
          {{ defaultCurrency }}
        </template>
      </BaseFormInput>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="requesting"
        @click="requestPayout"
      >
        <span v-if="!requesting">Create</span>
        <div v-else>
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { minValue, maxValue, required } from '@vuelidate/validators';
import requestPayouts from '@affiliate/mixins/requestPayouts';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'CreatePayoutRequestModal',
  mixins: [requestPayouts],
  props: {
    modalId: {
      type: String,
      required: true,
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      amountToPay: 0,
      requesting: false,
    };
  },
  validations() {
    return {
      amountToPay: {
        minValue: minValue(this.minimumPayout),
        maxValue: maxValue(this.availableCommissions),
        required,
      },
    };
  },
  computed: {
    minValueErr() {
      if (this.v$.amountToPay.minValue.$invalid || !this.amountToPay)
        return `This amount should be greater than minimum payout ${this.defaultCurrency} ${this.minimumPayout}`;
      return '';
    },
    maxValueErr() {
      if (this.v$.amountToPay.maxValue.$invalid)
        return `This amount should not be greater than ${this.defaultCurrency} ${this.availableCommissions}`;
      return '';
    },
  },
  mounted() {
    this.setDefault();
  },
  methods: {
    setDefault() {
      this.amountToPay = this.availableCommissions;
    },

    hideModal() {
      this.setDefault();
      this.$emit('hide');
    },

    async requestPayout() {
      this.requesting = !this.v$.$invalid;

      if (!this.requesting) {
        return this.$toast.error(
          'Error',
          'Please clear all your errors before proceeding'
        );
      }

      try {
        await this.$axios.post('/affiliates/payouts', {
          amount: this.amountToPay,
        });

        this.$toast.success('Success', 'Successfully requested payouts.');

        // lazy do dynamic update, so just reload directly
        // If who is seeing this now is free, then you can do dynamic, just take
        // good care of availableCommissions
        setTimeout(() => {
          window.location.reload();
        }, 500);
      } catch (err) {
        this.$toast.error('Error', 'Failed to request payouts');
      } finally {
        this.requesting = false;
      }
      return !this.requesting;
    },
  },
};
</script>

<style scoped lang="scss"></style>

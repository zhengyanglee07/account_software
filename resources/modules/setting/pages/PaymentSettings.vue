<template>
  <BasePageLayout
    page-name="Payment Settings"
    back-to="/settings/all"
    is-setting
  >
    <template #action-button>
      <BaseButton
        data-bs-toggle="modal"
        :data-bs-target="`#${addModalName}`"
        has-add-icon
      >
        Add Payment Method
      </BaseButton>
    </template>

    <BaseSettingLayout
      title="Payment Method"
      is-datatable-only-in-content
    >
      <template #description>
        <p>Choose a payment method</p>
      </template>
      <template #content>
        <BaseDatatable
          title="payment method"
          :table-headers="tableHeaders"
          :table-datas="resetPaymentMethods"
          no-header
          no-sorting
          no-delete-action
        >
          <template #cell-switch="{ row: { id, enabled_at } }">
            <BaseFormGroup>
              <BaseFormSwitch
                :model-value="!!enabled_at"
                @input="enablePaymentMethod(id)"
              />
            </BaseFormGroup>
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>

  <BaseModal
    title="Add Payment Method"
    :modal-id="addModalName"
  >
    {{
      computedAddPaymentMethod.length === 0
        ? 'No Payment Methods Available'
        : ''
    }}
    <BaseFormGroup
      v-for="(paymentMethod, index) in computedAddPaymentMethod"
      :key="index"
      col="6"
    >
      <BaseButton
        type="outline btn-outline-dashed btn-outline-default"
        :href="`/payment/settings/new/${paymentMethod.link}`"
        class="d-flex justify-content-start me-5 w-100 h-100"
        @click="closeModal"
      >
        <img
          :src="paymentMethod.image"
          :width="90"
        >
        <div class="ms-3 text-start">
          <p class="m-0 fw-bolder fs-5">
            {{ paymentMethod.name }}
          </p>
          <p class="m-0 text-muted fw-bold fs-7">
            {{ paymentMethod.description }}
          </p>
        </div>
      </BaseButton>
    </BaseFormGroup>
  </BaseModal>
</template>

<script>
import paymentAPI from '@setting/api/paymentAPI.js';
import stripeIcon from '@setting/assets/media/stripe-logo.jpg';
import manualPaymentIcon from '@setting/assets/media/manual-payment-icon.jpg';
import ipay88Icon from '@setting/assets/media/ipay88-logo.png';
import eventBus from '@services/eventBus.js';

export default {
  name: 'PaymentSettings',
  props: {
    paymentMethods: {
      type: Array,
      required: true,
    },
    env: {
      type: String,
      default: 'production',
    },
  },
  data() {
    return {
      addModalName: 'addPaymentMethodModal',
      resetPaymentMethods: [],
      addPaymentMethods: [
        {
          name: 'Stripe',
          link: 'stripe',
          description: '',
          image: stripeIcon,
        },
        {
          name: 'SenangPay',
          link: 'senangPay',
          description: '',
          image:
            'https://senangpay.my/wp-content/uploads/2022/09/regpage2x.png',
        },
        {
          name: 'Manual Payment',
          link: 'manual payment',
          description: '',
          image: manualPaymentIcon,
        },
        {
          name: 'iPay88',
          link: 'ipay88',
          description: '',
          image: ipay88Icon,
        },
      ],
      tableHeaders: [
        { name: 'Display Name', key: 'display_name' },
        { name: 'Payment Method', key: 'payment_methods' },
        { name: 'Enable/Disable', key: 'switch', custom: true },
      ],
    };
  },
  computed: {
    computedAddPaymentMethod() {
      const paymentMethods = this.paymentMethods.map(
        (paymentMethod) => paymentMethod.payment_methods
      );
      return this.addPaymentMethods.filter(({ name, link }) => {
        return link === 'manual payment' || !paymentMethods.includes(link);
      });
    },
  },
  mounted() {
    this.resetPaymentMethods = this.paymentMethods.map((m) => ({
      ...m,
      payment_methods:
        m.payment_methods === 'ipay88' ? 'iPay88' : m.payment_methods,
      editLink: `/payment/settings/${m.urlId}/${m.payment_methods}`,
    }));
  },
  methods: {
    closeModal() {
      eventBus.$emit(`hide-modal-${this.addModalName}`);
    },
    enablePaymentMethod(id) {
      this.resetPaymentMethods.forEach((item) => {
        const paymentMethod = item;
        if (paymentMethod.id !== id) return;
        paymentMethod.enabled_at = !paymentMethod.enabled_at;
        paymentAPI
          .update(paymentMethod)
          .then((response) => {
            this.$toast.success('Success', 'Successfully updated');
          })
          .catch((error) => {
            this.$toast.error('Error', 'Failed to update');
          });
      });
    },
    editPayment(data) {
      // console.log(`payment/settings/${data.item.urlId}/${data.item.payment_methods}`);
      this.$inertia.visit(
        `/payment/settings/${data.item.urlId}/${data.item.payment_methods}`
      );
    },
  },
};
</script>

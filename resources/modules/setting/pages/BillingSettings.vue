<template>
  <BasePageLayout
    page-name="Billing Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Subscription Status">
      <template #description>
        <p>Manage your plan details.</p>
        <p v-if="planName !== 'Circle'">
          <BaseButton
            type="link"
            href="/subscription/plan/upgrade"
          >
            Compare plans
          </BaseButton>
          with different features and rates.
        </p>
      </template>
      <template #content>
        <BaseFormGroup
          label="Current Plan"
          col="6"
        >
          <BaseFormInput
            id="billing-plan-name"
            :model-value="props.planName"
            type="text"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Status"
          col="6"
        >
          <BaseFormInput
            id="billing-status"
            :model-value="subscriptionDetail.status.replace('_', ' ')"
            type="text"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Plan Start"
          col="6"
        >
          <BaseFormInput
            id="billing-planstart"
            :model-value="dateStart ? dateStart[0] : ''"
            type="text"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          v-if="subscriptionDetail.current_plan_end !== '1970-01-01 07:30:00'"
          label="Plan End"
          col="6"
        >
          <BaseFormInput
            id="billing-plan-end"
            :model-value="dateEnd[0]"
            type="text"
            disabled
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton
          v-if="props.planName !== 'Free'"
          type="secondary"
          class="me-3"
          @click="triggerModal"
        >
          {{ terminateButtonText }}
        </BaseButton>

        <BaseButton
          v-if="
            subscriptionDetail.cancel_at === null ||
              subscriptionDetail.cancel_at === '1970-01-01 07:30:00'
          "
          :disabled="props.planName === 'Circle'"
          type="light-primary"
          href="/subscription/plan/upgrade"
        >
          {{ updateButtonText }}
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout
      v-if="props.creditCard"
      title="Credit Card Settings"
    >
      <template #description>
        <p>Manage your Credit Card Details.</p>
      </template>
      <template #content>
        <BaseFormGroup
          label="Card Holder Name"
          col="6"
        >
          <BaseFormInput
            id="billing-card-holder-name"
            type="text"
            :model-value="props.creditCard.card_holder_name"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Card Type"
          col="6"
        >
          <BaseFormInput
            id="billing-card-type"
            type="text"
            :model-value="props.creditCard.card_types"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Card No"
          col="6"
        >
          <BaseFormInput
            id="billing-card-no"
            type="text"
            :model-value="'**** **** **** ' + props.creditCard.last_4_digit"
            disabled
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Expire Date"
          col="6"
        >
          <BaseFormInput
            id="billing-card-expiry"
            type="text"
            :model-value="props.creditCard.expire_date"
            disabled
          />
        </BaseFormGroup>

        <SubscriptionPlanSummary
          v-if="props.creditCard != ''"
          :type="'update'"
          :production="props.production"
          :selected-plan="{ id: null, plan: null }"
        />
        <TerminateConfirmationModal
          :modal-id="state.modalId"
          title="subscription"
          :date="dateEnd[0]"
          @cancel="closeDeleteModal"
          @save="terminateSubscription"
        />
      </template>
      <template #footer>
        <BaseButton
          v-if="
            subscriptionDetail.cancel_at === null ||
              subscriptionDetail.cancel_at === '1970-01-01 07:30:00'
          "
          data-bs-toggle="modal"
          data-bs-target="#plan-summary"
        >
          Change Card
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout title="Billing Information">
      <template #content>
        <BaseFormGroup
          label="First Name"
          :error-message="
            state.showError && $v.data.firstName.required.$invalid
              ? 'First name is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-first-name"
            v-model="state.data.firstName"
            type="text"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Last Name"
          :error-message="
            state.showError && $v.data.lastName.required.$invalid
              ? 'Last name is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-last-name"
            v-model="state.data.lastName"
            type="text"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Company Name"
          :error-message="
            state.showError && $v.data.companyName.required.$invalid
              ? 'Comapny name is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-company-name"
            v-model="state.data.companyName"
            type="text"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Full Address"
          :error-message="
            state.showError && $v.data.address.required.$invalid
              ? 'Full address is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-full-address"
            v-model="state.data.address"
            type="text"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Country"
          col="6"
        >
          <BaseFormCountrySelect
            v-model="state.data.country"
            :country="state.data.country"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="State"
          col="6"
        >
          <BaseFormRegionSelect
            v-model="state.data.state"
            :region="state.data.state"
            :country="state.data.country"
            placeholder="Select State"
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="City"
          :error-message="
            state.showError && $v.data.city.required.$invalid
              ? 'City is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-city"
            v-model="state.data.city"
            type="text"
            required
          />
        </BaseFormGroup>

        <BaseFormGroup
          label="Zipcode"
          :error-message="
            state.showError && $v.data.zipCode.required.$invalid
              ? 'Zip code is required'
              : ''
          "
          col="6"
          required
        >
          <BaseFormInput
            id="billing-zipCode"
            v-model="state.data.zipCode"
            type="number"
            required
          />
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="submitdata">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <BaseSettingLayout
      v-if="props.subscriptionInvoices.length > 0"
      title="Invoices"
      is-datatable-only-in-content
    >
      <template #description>
        <p>Manage your subscription invoices.</p>
      </template>
      <template #content>
        <BaseDatatable
          v-if="props.subscriptionInvoices.length > 0"
          title="subscription invoice"
          :table-headers="tableHeaders"
          :table-datas="tableDatas"
          no-header
          no-edit-action
          no-delete-action
        >
          <template #action-options="{ row: { viewLink } }">
            <BaseDropdownOption
              text="View"
              :link="viewLink"
              is-open-in-new-tab
            />
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script setup>
import SubscriptionPlanSummary from '@subscription/components/SubscriptionPlanSummary.vue';
import TerminateConfirmationModal from '@shared/components/SubscriptionTerminateModal.vue';
import billingAPI from '@setting/api/billingAPI.js';
import { Modal } from 'bootstrap';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { computed, onBeforeMount, reactive, inject } from 'vue';
import { router } from '@inertiajs/vue3';

const $toast = inject('$toast');

const props = defineProps({
  subscription: { type: Object, default: () => {} },
  planName: { type: String, default: '' },
  creditCard: { type: Object, default: () => {} },
  production: { type: String, default: '' },
  userDetail: { type: Object, default: () => {} },
  subscriptionInvoices: { type: Array, default: () => [] },
});

const tableHeaders = [
  { name: 'Date', key: 'date' },
  { name: 'Plan', key: 'plan_name' },
];

const state = reactive({
  modalId: 'subscription-confirmation-modal',
  subscriptionConfirmationModal: null,
  showError: false,
  data: {},
});

const rules = reactive({
  data: {
    city: { required },
    companyName: { required },
    firstName: { required },
    address: { required },
    lastName: { required },
    zipCode: { required },
  },
});

const $v = useVuelidate(rules, state);

const subscriptionDetail = computed(() => props.subscription);
const dateStart = computed(() =>
  subscriptionDetail.value.current_plan_start?.split(' ')
);
const dateEnd = computed(() =>
  subscriptionDetail.value.current_plan_end?.split(' ')
);
const terminateButtonText = computed(() => {
  const { cancel_at: cancelDate, current_plan_end: endDate } =
    subscriptionDetail.value;
  const cancelAt = new Date(cancelDate);
  const isValidDate = cancelAt instanceof Date && !Number.isNaN(cancelAt);
  const isTrialAccount = subscriptionDetail.value.status === 'trialing';
  const isTrialActive =
    !isValidDate || new Date(subscriptionDetail.value.cancel_at) <= new Date();
  const isSubscriptionActive =
    (!isValidDate || cancelAt < new Date()) && new Date(endDate) >= new Date();
  const isActive = isTrialAccount ? isTrialActive : isSubscriptionActive;
  return isActive ? 'Terminate' : 'Reactive Subscription';
});
const updateButtonText = computed(() =>
  props.planName === 'Circle' ? 'Reached the highest plan' : 'Upgrade'
);

const tableDatas = computed(() =>
  props.subscriptionInvoices.map((m) => ({
    ...m,
    viewLink: `/subscription/invoices/${m.reference_key}`,
  }))
);

const submitdata = () => {
  state.showError = true;
  if ($v.value.$invalid) return;

  billingAPI
    .update(state.data)
    .then(() => {
      $toast.success('Success', 'Successfully save billing setting');
    })
    .catch((error) => {
      $toast.error('Error', 'Failed to save billing setting');
    });
};

const terminateSubscription = () => {
  billingAPI
    .terminate()
    .then((response) => {
      $toast.success('Success', 'Cancellation Sucessful');
      setTimeout(() => {
        window.location.href = '/billing/setting';
      }, 1500);
    })
    .catch((error) => {
      $toast.error('Error', 'Cancellation Fail');
    });
};

const reactiveSubscription = () => {
  billingAPI
    .reactiveSubscription()
    .then((response) => {
      $toast.success('Successful', 'Successful Reactive Subscription');
      setTimeout(() => router.visit('/billing/setting'), 1500);
    })
    .catch((error) => {
      $toast.error('Error', 'Fail Reactive Subscription');
    });
};

const closeDeleteModal = () => state.subscriptionConfirmationModal.hide();

const triggerModal = () => {
  state.subscriptionConfirmationModal = new Modal(
    document.getElementById(state.modalId)
  );
  if (terminateButtonText.value === 'Terminate') {
    state.subscriptionConfirmationModal.show();
    return;
  }
  reactiveSubscription();
};

onBeforeMount(() => {
  state.data = props.userDetail;

  //* append stripe script to body
  const script = document.createElement('script');
  script.src = 'https://js.stripe.com/v3/';
  document.body.appendChild(script);
});
</script>

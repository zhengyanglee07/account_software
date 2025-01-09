<template>
  <BaseModal
    :title="modalTitle == '' ? permissionData.modalTitle : modalTitle"
    modal-id="limit-modal"
    no-dismiss
  >
    <p v-if="customContext">
      {{ customContext }}
    </p>
    <p v-else-if="Object.keys(subscriptionDetail).length > 0">
      You have reached the maximum number of
      {{
        `${limitContext == '' ? context : limitContext}${
          subscriptionDetail['isInteger'] ? 's' : ''
        }`
      }}
      in {{ subscriptionDetail['planName'] }} plan ({{
        subscriptionDetail['quotaLimit'][subscriptionDetail['planName']]
      }}).
      <span
        v-if="subscriptionDetail['isUncontrollable']"
      >To access the admin panel, you need to
        <strong>Upgrade Plan</strong> now to increase the quota limit</span>
      <span
        v-else
      >You need to remove one before you can add another or
        <strong>Upgrade Plan</strong> now to increase the quota limit.</span>
    </p>
    <ol
      v-if="!customContext && Object.keys(subscriptionDetail).length > 0"
      class="ms-7"
    >
      <li
        v-for="(plan, index) in ['Free', 'Square', 'Triangle', 'Circle']"
        :key="index"
      >
        {{
          `${plan} Plan - ${
            subscriptionDetail['isInteger'] &&
            subscriptionDetail['quotaLimit'][plan] === 0 &&
            limitContext === 'form submissions'
              ? 'unlimited'
              : subscriptionDetail['quotaLimit'][plan]
          } ${limitContext == '' ? permissionData.context : limitContext}${
            subscriptionDetail['isInteger'] ? 's' : ''
          }`
        }}
      </li>
    </ol>

    <template #footer>
      <BaseButton
        v-if="permissionData.isSubscriptionPage"
        @click="close"
      >
        Choose a plan now
      </BaseButton>
      <template v-else-if="upgradeButton">
        <BaseButton
          type="secondary"
          @click="close"
        >
          Cancel
        </BaseButton>
        <BaseButton @click="upgradePlan">
          Upgrade Plan
        </BaseButton>
      </template>
      <BaseButton
        v-else
        @click="close"
      >
        Close
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import eventBus from '@services/eventBus.js';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

export default {
  name: 'LimitModal',

  props: {
    permissionData: { type: Object, default: () => {} },
  },

  data() {
    return {
      showModal: false,
      modalTitle: '',
      limitContext: '',
      customContext: null,
      upgradeButton: true,
      subscriptionDetail: null,
      modal: null,
    };
  },
  watch: {
    showModal(val) {
      if (val) {
        this.modal?.show();
      }
    },
  },
  created() {
    if (!this.permissionData) return;
    this.subscriptionDetail = this.permissionData.subscription;
    this.limitContext = this.permissionData.context;
    eventBus.$on('open-limit-modal', (data) => {
      this.setupLimitModalData(data);
    });
    eventBus.$on('trigger-paid-plan-modal', () => {
      this.triggerPaidPlanModal();
    });
    eventBus.$on('base-modal-mounted', () => {
      const elem = document.getElementById('limit-modal');
      if (!elem || this.modal) return;
      elem.addEventListener('hidden.bs.modal', () => {
        this.showModal = false;
      });

      bootstrap?.then(({ Modal }) => {
        this.modal = new Modal(elem);
      });
      this.showModal = this.permissionData.showLimitModal;
    });
  },
  methods: {
    setupLimitModalData(data) {
      this.showModal = data.showModal;
      this.limitContext = data.context;
      this.modalTitle = data.modalTitle;
      this.customContext = data.customContext;
      this.upgradeButton = data.upgradeButton ?? true;
      this.subscriptionDetail = data.subscriptionDetail;
    },
    triggerPaidPlanModal() {
      this.showModal = true;
      this.modalTitle = 'Only For Paid Plan';
      this.customContext =
        'Subscribe to one of our paid plan to use this feature.';
    },
    upgradePlan() {
      window.location.href = '/subscription/plan/upgrade';
    },

    close() {
      this.$emit('close');
      this.showModal = false;
      this.modal.hide();
      eventBus.$emit('hide-modal-limit-modal');
    },
  },
};
</script>

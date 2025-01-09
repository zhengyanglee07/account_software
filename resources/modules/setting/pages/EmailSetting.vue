<template>
  <BasePageLayout
    page-name="Email Settings"
    back-to="/settings/all"
    is-setting
  >
    <BaseSettingLayout title="Affiliate Badge">
      <template #description>
        Determine whether display affiliate badge in emails.
      </template>
      <template #content>
        <BaseFormGroup
          label="Affiliate Badge"
          label-for="showAffiliate"
        >
          <BaseFormCheckBox
            id="showAffiliate"
            v-model.number="hasAffiliateBadge"
            :value="true"
            :disabled="!canDisableBadges"
          >
            Show affiliate badge at the bottom of emails
          </BaseFormCheckBox>
        </BaseFormGroup>
      </template>
      <template #footer>
        <BaseButton @click="updateAffiliateSetting">
          Save
        </BaseButton>
      </template>
    </BaseSettingLayout>

    <div class="setting-page__dividor" />

    <BaseSettingLayout title="Sender Domain">
      <template #description>
        Add email to manage your store.
      </template>

      <template #content>
        <BaseFormGroup
          label="Sender Email"
          label-for="newSenderEmail"
        >
          <BaseFormInput
            id="newSenderEmail"
            v-model="senderEmail"
            type="text"
            placeholder="Enter Email"
          />
          <span
            v-if="showError"
            class="text-danger"
          > Invalid email </span>
        </BaseFormGroup>
      </template>

      <template #footer>
        <BaseButton @click="verify">
          Verify
        </BaseButton>
      </template>
    </BaseSettingLayout>
    <BaseSettingLayout>
      <template #content>
        <BaseDatatable
          title="sender email"
          no-edit-action
          no-header
          no-hover
          :table-headers="sendersTableHeaders"
          :table-datas="sendersTableData"
          @delete="deleteSender"
        >
          <template #cell-status="{ row: { status } }">
            <span
              class="badge"
              :class="{
                'badge-warning': status === 'pending',
                'badge-success': status === 'verified',
              }"
            >
              {{ status }}
            </span>
          </template>
          <template #action-options="{ row: { id, email, status } }">
            <BaseDropdownOption
              text="Refresh"
              @click="refreshSenderStatus({ id, email, status })"
            />
          </template>
        </BaseDatatable>
      </template>
    </BaseSettingLayout>
    <BaseSettingLayout title="Suppression List">
      <template #description>
        Our system will automatically exclude the email addresses in this list
        from receiving your marketing emails to maintain the high reputation and
        delivery rate of your sender email address.
      </template>
      <template #content>
        <BaseDatatable
          no-edit-action
          no-delete-action
          title="suppression email"
          :table-headers="suppressionListTableHeaders"
          :table-datas="suppressionListTableDatas"
          custom-description="&nbsp;"
        >
          <template #action-button>
            <BaseButton
              id="add-suppression-email-button"
              has-add-icon
              data-bs-target="#new-suppression-email-modal"
              data-bs-toggle="modal"
            >
              Add Email
            </BaseButton>
          </template>
          <template #action-options="{ row: { id, type, emailAddress } }">
            <BaseDropdownOption
              text="Delete"
              @click="deleteSuppressionEmail({ id, type, emailAddress })"
            />
          </template>
        </BaseDatatable>
        <CreateNewSuppressionEmailModal
          :suppression-email-addresses="suppressionEmailAddresses"
          @update-suppression-list="
            suppressionListTableDatas = [
              ...suppressionListTableDatas,
              ...$event,
            ]
          "
        />
      </template>
    </BaseSettingLayout>
  </BasePageLayout>
</template>

<script>
import useVuelidate from '@vuelidate/core';
import { required, email } from '@vuelidate/validators';
import verifySenderDomainMixin from '@setting/mixins/verifySenderDomainMixin.js';
import CreateNewSuppressionEmailModal from '@setting/components/CreateNewSuppressionEmailModal.vue';
import { Modal } from 'bootstrap';

export default {
  name: 'EmailSettings',
  components: {
    CreateNewSuppressionEmailModal,
  },
  mixins: [verifySenderDomainMixin],
  props: {
    dbSenders: Array,
    hasEmailAffiliateBadge: {
      type: Boolean,
      default: true,
    },
    suppressionEmailAddresses: {
      type: [Array, Object],
      default: () => [],
    },
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      senders: [],
      sendersTableHeaders: [
        { name: 'Email', key: 'email_address' },
        { name: 'Status', key: 'status', custom: true },
      ],
      senderEmail: '',
      showError: false,
      suppressionListTableHeaders: [
        { name: 'Email Address', key: 'emailAddress' },
        { name: 'Date', key: 'createdAt', isDateTime: true },
        { name: 'Reason', key: 'type' },
      ],
      suppressionListTableDatas: [],

      refreshingSender: false,
      refreshingSenderId: null,

      hasAffiliateBadge: true,
      selectedSenderId: '',
      deleteSenderModal: null,
      deleteSenderModalId: 'delete-sender-modal',
    };
  },
  validations: {
    senderEmail: {
      required,
      email,
    },
  },
  computed: {
    canDisableBadges() {
      return this.$page.props.permissionData.featureForPaidPlan.includes(
        'can-disabled-badge'
      );
    },

    sendersTableData() {
      return this.senders.map((sender) => ({
        id: sender.id,
        email_address: sender.email_address,
        status: sender.status,
      }));
    },

    showEmailError() {
      return this.showError;
    },
  },
  mounted() {
    this.senders = [...this.dbSenders];
    this.hasAffiliateBadge = this.hasEmailAffiliateBadge;
    this.suppressionListTableDatas = [...this.suppressionEmailAddresses];
  },
  methods: {
    areCurrentSendersVerified() {
      return (
        this.senders.filter((sender) => sender.status !== 'verified').length ===
        0
      );
    },

    /**
     * Add some logic on top of verifySenderDomain method in
     * verifySenderDomainMixin, such as clear input
     *
     * @returns {Promise<void>}
     */
    async verify() {
      this.showError = false;

      if (this.v$.$invalid) {
        this.showError = true;
        return;
      }

      // prevent user to verify new sender before current senders
      // are all verified
      if (!this.areCurrentSendersVerified()) {
        this.$toast.warning(
          'Warning',
          'Seems like you have one or more emails pending for verification. Please verify them before verifying new email.'
        );
        return;
      }

      const verifiedSender = await this.verifySenderDomain(this.senderEmail);

      if (verifiedSender) {
        this.senders.push(verifiedSender);
        this.senderEmail = '';
      }
    },

    async refreshSenderStatus(sender) {
      this.refreshingSender = true;
      this.refreshingSenderId = sender.id;

      try {
        const res = await this.$axios.get(`/senders/${sender.id}/refresh`);
        const { senderStatus } = res.data;

        this.senders = this.senders.map((localSender) => ({
          ...localSender,
          status:
            localSender.id === sender.id ? senderStatus : localSender.status,
        }));

        this.$toast.success('Success', 'Successfully refreshed sender status');
      } catch (e) {
        console.error(e);
        this.$toast.error('Error', 'Failed to refresh sender');
      } finally {
        this.refreshingSender = false;
        this.refreshingSenderId = null;
      }
    },

    async deleteSender(id) {
      try {
        await this.$axios.delete(`/senders/${id}`);

        this.senders = this.senders.filter((sender) => sender.id !== id);
        this.$toast.success('Success', 'Successfully deleted sender');
      } catch (e) {
        this.$toast.error('Error', 'Failed to delete sender');
      }
    },

    updateAffiliateSetting() {
      axios
        .put('/account/affiliate', {
          canDisableBadges: this.canDisableBadges,
          hasAffiliateBadge: this.hasAffiliateBadge,
        })
        .then((res) => {
          this.hasAffiliateBadge = res.data;
          this.$toast.success(
            'Success',
            'Successfully updated affiliate setting'
          );
        })
        .catch((err) => {
          console.error(err);
          this.$toast.error('Error', 'Failed to update affiliate setting');
        });
    },

    async deleteSuppressionEmail(suppression) {
      // eslint-disable-next-line no-restricted-globals, no-alert
      const answer = confirm('Are you sure want to delete this record?');
      if (answer) {
        try {
          await this.$axios.delete(
            `/emails/suppression/${suppression.id}/${suppression.type}`,
            this.$toast.success(
              'Success',
              'Suppression email is deleted successfully.'
            )
          );

          this.suppressionListTableDatas =
            this.suppressionListTableDatas.filter(
              (s) => s.emailAddress !== suppression.emailAddress
            );

          // this.localCampaigns = this.localCampaigns.filter((c) => c.id !== id);
        } catch (err) {
          this.$toast.error('Error', 'Failed to delete suppression email');
        }
      }
    },
  },
};
</script>

<style scoped lang="scss">
// New Design
// *:not(i) {
//   font-family: $base-font-family;
//   font-size: $base-font-size;
//   color: $base-font-color;
//   margin: 0;
//   padding: 0;
// }

.setting-page__form-footer {
  @media (max-width: 576px) {
    padding-right: 1rem;
  }
}
</style>

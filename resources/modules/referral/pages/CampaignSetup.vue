<template>
  <BasePageLayout
    :page-name="isEdit ? 'Edit Referral Campaign' : 'Add Referral Campaign'"
    back-to="/referral/campaigns"
  >
    <template #left>
      <BaseCard
        has-header
        title="General"
      >
        <BaseFormGroup
          label="Title"
          :error-message="
            showValidationErrors && errors?.title ? errors.title[0] : ''
          "
          required
        >
          <BaseFormInput
            id="title"
            v-model="title"
            type="text"
            placeholder="Enter Title"
            @input="clearValidationError"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Sales Channel"
          :error-message="
            showValidationErrors && errors?.salesChannel
              ? errors?.salesChannel[0]
              : ''
          "
          required
        >
          <BaseFormSelect
            id="sales-channel"
            v-model="salesChannel"
            :disabled="isEdit"
            placeholder="Select Sales Channel"
            @change="clearValidationError"
          >
            <option
              value=""
              disabled
            >
              Select sales channel
            </option>
            <option
              v-for="(s, i) in salesChannels"
              :key="`sales-channel-${i}`"
              :value="{ id: s.id, type: s.type }"
            >
              {{ s.name }}
            </option>
          </BaseFormSelect>
        </BaseFormGroup>
        <BaseFormGroup
          v-if="salesChannel.type === 'funnel'"
          label="Which Funnel"
          :error-message="
            showValidationErrors && errors?.salesChannel
              ? errors?.salesChannel[0]
              : ''
          "
          required
          description="Note: One funnel only can be targeted by one referral campaign"
        >
          <template #label-row-end>
            <BaseButton
              v-if="funnel"
              id="redirect-funnel-button"
              size="sm"
              type="link"
              is-open-in-new-tab
              :href="
                '/funnel/' + funnels.find((e) => e.id === funnel)?.reference_key
              "
            >
              Set up Funnel
              <i
                class="fa-solid fa-square-up-right mx-1"
                style="color: #009ef7"
              />
            </BaseButton>
          </template>
          <BaseFormSelect
            id="funnel"
            v-model="funnel"
            :disabled="isEdit"
            placeholder="Select funnel"
            @change="clearValidationError"
          >
            <option
              value="null"
              disabled
            >
              Select funnel
            </option>
            <option
              v-for="(f, i) in funnels"
              :key="`funnel-${i}`"
              :value="f.id"
            >
              {{ f.funnel_name }}
            </option>
          </BaseFormSelect>
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        has-toolbar
        title="Points for Inviter"
      >
        <template #toolbar>
          <BaseButton
            id="add-inviter-action-button"
            size="sm"
            has-add-icon
            data-bs-toggle="modal"
            :data-bs-target="`#add-${actionType}-action-modal`"
            @click="selectedAction = {}"
            @mouseenter="actionType = 'inviter'"
          >
            Add Action
          </BaseButton>
        </template>
        <BaseFormGroup
          :label="
            actions.length
              ? 'Point Per Action'
              : 'A participant in this campaign will earn points whenever they perform the actions you defined here'
          "
          :error-message="
            showValidationErrors && errors?.actions ? errors?.actions[0] : ''
          "
          required
        >
          <CampaignActionDatatable
            v-if="actions.length"
            :no-hover="true"
            :actions="actions"
            :selected-channel="salesChannel?.type || 'funnel'"
            type="inviter"
            @trigger-action-modal="triggerActionModal"
            @delete-action="deleteAction"
            @mouseenter="actionType = 'inviter'"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        has-toolbar
        title="Points for Invitee"
      >
        <template #toolbar>
          <BaseButton
            id="add-invitee-action-button"
            size="sm"
            has-add-icon
            data-bs-toggle="modal"
            :data-bs-target="`#add-${actionType}-action-modal`"
            @click="selectedInviteeAction = {}"
            @mouseenter="actionType = 'invitee'"
          >
            Add Action
          </BaseButton>
        </template>
        <BaseFormGroup
          :label="
            inviteeActions.length
              ? 'Point per Action'
              : `A person will earn points whenever they visit your ${storeName(
                salesChannel?.type
              )} through an inviter\'s referral link and perform the actions you defined here`
          "
        >
          <CampaignActionDatatable
            v-if="inviteeActions.length"
            :no-hover="true"
            :actions="inviteeActions"
            :selected-channel="salesChannel?.type || 'funnel'"
            type="invitee"
            @trigger-invitee-action-modal="triggerInviteeActionModal"
            @delete-invitee-action="deleteInviteeAction"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        has-toolbar
        title="Grand Prize"
      >
        <template #toolbar>
          <BaseButton
            id="add-prize-button"
            size="sm"
            :has-add-icon="!Object.keys(prizes ?? {}).length"
            :has-edit-icon="Object.keys(prizes ?? {}).length !== 0"
            data-bs-toggle="modal"
            :data-bs-target="`#add-prize-modal`"
          >
            {{ Object.keys(prizes ?? {}).length ? 'Edit' : 'Add' }} Prize
          </BaseButton>
        </template>
        <BaseFormGroup
          :label="
            Object.keys(prizes).length
              ? ''
              : 'Offer a grand prize to your campaign\'\s participants. The higher the points a participant earns, the higher the chance he will be selected as the winner.'
          "
        >
          <CampaignPrizeDatatable
            v-if="Object.keys(prizes).length"
            :no-hover="true"
            :prize="prizes"
            @delete-prize="
              $nextTick(() => {
                prizes = {};
              })
            "
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        has-toolbar
        title="Rewards"
      >
        <template #toolbar>
          <BaseButton
            id="add-reward-button"
            size="sm"
            has-add-icon
            data-bs-toggle="modal"
            data-bs-target="#add-reward-modal"
            @click="selectedReward = {}"
          >
            Add Reward
          </BaseButton>
        </template>
        <BaseFormGroup
          :error-message="
            showValidationErrors && errors?.rewards ? errors?.rewards[0] : ''
          "
          :label="
            rewards.length
              ? ''
              : 'Offer rewards for your campaign\'\s participants to unlock.'
          "
        >
          <!-- <span
            v-if="!rewards.length && !showValidationErrors"
            class="text-danger"
          >
            This field is required *
          </span> -->
          <CampaignRewardDatatable
            v-if="rewards.length"
            :no-hover="true"
            :rewards="rewards"
            @trigger-reward-modal="triggerRewardModal"
            @delete-reward="deleteReward"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Emails"
      >
        <BaseFormGroup
          v-for="(emailType, index) in emailTypes"
          :key="index"
          :label="emailType.label"
        >
          <BaseFormSwitch v-model="emailStatus[emailType.value]" />
          <template #label-row-end>
            <BaseButton
              id="edit-email-button"
              type="link"
              @click="showEditEmailModal(emailType)"
            >
              Edit Email
            </BaseButton>
          </template>
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Social Sharing"
      >
        <BaseFormGroup>
          <BaseFormCheckBox
            id="social-networks-enabled"
            v-model="socialNetworkEnabled"
            :value="true"
          >
            Social Networks
          </BaseFormCheckBox>
          <BaseButton
            v-if="socialNetworkEnabled"
            type="link"
            data-bs-toggle="modal"
            :data-bs-target="`#social-preview-modal`"
            @click="socialType = 'social'"
          >
            Preview
          </BaseButton>
        </BaseFormGroup>
        <section v-if="socialNetworkEnabled">
          <BaseFormGroup label="Message">
            <BaseFormTextarea
              id="social-message"
              v-model="socialMessage"
            />
          </BaseFormGroup>
          <BaseFormGroup label="Networks">
            <div class="d-flex flex-wrap">
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('facebook')"
                @click="selectedSocial = 'facebook'"
                @update:modelValue="updateSoicalNetworks"
              >
                Facebook
              </BaseFormSwitch>
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('twitter')"
                @click="selectedSocial = 'twitter'"
                @update:modelValue="updateSoicalNetworks"
              >
                Twitter
              </BaseFormSwitch>
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('linkedin')"
                @click="selectedSocial = 'linkedin'"
                @update:modelValue="updateSoicalNetworks"
              >
                Linkedin
              </BaseFormSwitch>
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('whatsapp')"
                @click="selectedSocial = 'whatsapp'"
                @update:modelValue="updateSoicalNetworks"
              >
                Whatsapp
              </BaseFormSwitch>
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('messenger')"
                @click="selectedSocial = 'messenger'"
                @update:modelValue="updateSoicalNetworks"
              >
                Messenger
              </BaseFormSwitch>
              <BaseFormSwitch
                class="p-5"
                :model-value="referralSocialNetworks.includes('telegram')"
                @click="selectedSocial = 'telegram'"
                @update:modelValue="updateSoicalNetworks"
              >
                Telegram
              </BaseFormSwitch>
            </div>
          </BaseFormGroup>
        </section>
        <BaseFormGroup>
          <BaseFormCheckBox
            id="share-email-enabled"
            v-model="shareEmailEnabled"
            :value="true"
          >
            Email
          </BaseFormCheckBox>
          <BaseButton
            v-if="shareEmailEnabled"
            type="link"
            data-bs-toggle="modal"
            :data-bs-target="`#social-preview-modal`"
            @click="socialType = 'email'"
          >
            Preview
          </BaseButton>
        </BaseFormGroup>
        <section v-if="shareEmailEnabled">
          <BaseFormGroup label="Subject">
            <BaseFormInput
              id="email-subject"
              v-model="emailSubject"
              type="text"
              name="email-subject"
            />
          </BaseFormGroup>
          <BaseFormGroup label="Message">
            <BaseFormTextarea
              id="email-message"
              v-model="emailMessage"
            />
          </BaseFormGroup>
        </section>
      </BaseCard>
      <BaseCard
        has-header
        title="Advanced"
      >
        <BaseFormGroup
          label="Active Dates"
          :error-message="
            showValidationErrors && errors?.activeDate
              ? errors?.activeDate[0]
              : ''
          "
        >
          <BaseDatePicker
            v-model="activeDate"
            :clearable="false"
            type="datetime"
            format="YYYY-MM-DD hh:mm a"
            value-type="format"
            :show-second="false"
            :editable="false"
            :disabled-date="notBeforeToday"
          />
        </BaseFormGroup>
        <BaseFormGroup>
          <BaseFormCheckBox
            id="is-expeiry"
            v-model="isExpiry"
            :value="true"
          >
            Set end date
          </BaseFormCheckBox>
        </BaseFormGroup>
        <BaseFormGroup
          v-if="isExpiry"
          label="End Dates"
          :error-message="
            showValidationErrors && errors?.endDate
              ? 'End date is required when Set end date is checked'
              : ''
          "
          required
        >
          <BaseDatePicker
            v-model="endDate"
            :clearable="false"
            type="datetime"
            format="YYYY-MM-DD hh:mm a"
            value-type="format"
            :show-second="false"
            :editable="false"
            :disabled-date="notBeforeStartDate"
            :disabled-time="notBeforeStartTime"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        v-if="isEdit"
        has-header
        has-toolbar
        :title="`All ${participants.length} Participants`"
      >
        <template #toolbar>
          <BaseButton
            v-if="processedContacts.length"
            id="export-participant-button"
            class="mx-2"
            type="secondary"
            :disabled="exportingContacts"
            @click="exportContacts"
          >
            <i class="fa-solid fa-download me-2" /><span> Export to CSV </span>
            <span v-if="exportingContacts">
              <i
                class="fas fa-spinner fa-pulse"
                style="margin-left: 5px"
              />
            </span>
          </BaseButton>
          <BaseButton
            v-if="processedContacts.length && Object.keys(prizes ?? {}).length"
            id="select-winner-button"
            data-bs-toggle="modal"
            data-bs-target="#select-winner-modal"
          >
            Select Winners
          </BaseButton>
        </template>
        <BaseDatatable
          no-edit-action
          no-delete-action
          max-height="300px"
          :table-headers="tableHeaders"
          :table-datas="participants"
          title="participant"
          custom-description="This campaign has no participants yet."
        >
          <template #action-options="{ row: item }">
            <BaseDropdownOption
              text="View"
              :link="
                '/people/profile/' +
                  item.contactRandomId +
                  '/?tab=referral&campaign=' +
                  campaign.id
              "
            />
          </template>
          <template #cell-email="{ row: item }">
            <span>{{ item?.email }}</span>
            <span v-if="item?.isWinner">
              &nbsp;
              <BaseBadge
                class="text-capitalize"
                text="prize winner"
                type="success"
              />
            </span>
          </template>
        </BaseDatatable>
      </BaseCard>
    </template>
    <template #right>
      <BaseCard
        has-header
        title="Summary"
      >
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">Target:</span>
          {{ salesChannel?.type || 'Empty' }}
        </div>
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">{{
            'Inviter Actions :'
          }}</span>
          <span v-if="!actions.length"> Empty</span>
          <li
            v-for="(action, index) in actions.filter((e) =>
              salesChannel?.type === 'funnel' || !salesChannel?.type
                ? e
                : e.actionType !== 'sign-up'
            )"
            :key="index"
          >
            {{ action.title }}: {{ action.point }} points
          </li>
        </div>
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">{{
            'Invitee Actions :'
          }}</span>
          <span v-if="!inviteeActions.length"> Empty</span>
          <li
            v-for="(action, index) in inviteeActions.filter((e) =>
              salesChannel?.type === 'funnel' || !salesChannel?.type
                ? e
                : e.actionType !== 'sign-up'
            )"
            :key="index"
          >
            {{ action.title }}: {{ action.point }} points
          </li>
        </div>
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">Grand Prize: </span>
          <span v-if="Object.keys(prizes ?? {}).length">
            {{ prizes.prizeTitle }}: {{ prizes?.noOfWinner }} winners
          </span>
          <span v-else> Empty </span>
        </div>
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">{{
            'Rewards :'
          }}</span>
          <span v-if="!rewards.length"> Empty</span>
          <li
            v-for="(reward, index) in rewards"
            :key="index"
          >
            {{
              reward.rewardType === 'promo-code'
                ? reward.promoCode
                : reward.rewardValue
            }}: {{ reward?.pointToUnlock }} points to unlock
          </li>
        </div>
        <div>
          <span class="text-gray-800 fw-bolder fs-6 mb-1">Active from </span>
          <i>{{ activeDate }}</i>
          <span v-if="isExpiry">
            <span> to </span>
            <i>{{ endDate }}</i>
          </span>
        </div>
      </BaseCard>
    </template>
    <template #footer>
      <BaseButton
        type="link"
        class="me-5"
        href="/referral/campaigns"
      >
        Cancel
      </BaseButton>
      <BaseButton
        :disabled="savingCampaign"
        @click="saveCampaign"
      >
        <span> Save </span>
        <span v-if="savingCampaign">
          <i
            class="fas fa-spinner fa-pulse"
            style="margin-left: 5px"
          />
        </span>
      </BaseButton>
    </template>
  </BasePageLayout>
  <CampaignActionModal
    :modal-id="`add-${actionType}-action-modal`"
    :actions="actionType === 'inviter' ? actions : inviteeActions"
    :selected-action="
      actionType === 'inviter' ? selectedAction : selectedInviteeAction
    "
    :selected-channel="salesChannel?.type || 'funnel'"
    :type="actionType"
    @edit-action="editAction"
    @edit-invitee-action="editInviteeAction"
  />
  <CampaignRewardModal
    modal-id="add-reward-modal"
    :promotions="promotions"
    :selected-reward="selectedReward"
    @edit-reward="editReward"
  />
  <CampaignEmailModal
    modal-id="edit-email-modal"
    :selected-type="selectedEmailType"
    :email-content="tempEmailTemplates"
    @email-template="saveEmailTemplate"
    @open-test-email-modal="showTestEmailModal"
  />
  <SendTestEmailModal
    modal-id="referral-test-email-modal"
    is-referral
    :referral-email-data="testEmailContent"
  />
  <CampaignPrizeModal
    modal-id="add-prize-modal"
    :prize="prizes ?? {}"
    @edit-prize="editPrize"
  />
  <CampaignSelectWinnerModal
    modal-id="select-winner-modal"
    :is-ended="isEnded"
    :campaign="campaign"
    :prizes="prizes"
    :participants="processedContacts ?? []"
    :winners="winners ?? []"
    :no-of-winner="prizes?.noOfWinner ?? 0"
    @save-winner="
      $nextTick(() => {
        winners = $event;
      })
    "
  />
  <CampaignSocialPreviewModal
    modal-id="social-preview-modal"
    :social-networks="referralSocialNetworks"
    :type="socialType"
    :social-message="socialMessage"
    :email-subject="emailSubject"
    :email-message="emailMessage"
    :funnel-id="funnel"
    :sale-channel="salesChannel?.type"
    :meta-info="metaInfo"
  />
</template>

<script>
import CampaignSocialPreviewModal from '@referral/components/CampaignSocialPreviewModal.vue';
import CampaignPrizeModal from '@referral/components/CampaignPrizeModal.vue';
import CampaignPrizeDatatable from '@referral/components/CampaignPrizeDatatable.vue';
import CampaignSelectWinnerModal from '@referral/components/CampaignSelectWinnerModal.vue';
import CampaignActionModal from '@referral/components/CampaignActionModal.vue';
import CampaignActionDatatable from '@referral/components/CampaignActionDatatable.vue';
import CampaignRewardModal from '@referral/components/CampaignRewardModal.vue';
import CampaignEmailModal from '@referral/components/CampaignEmailModal.vue';
import CampaignRewardDatatable from '@referral/components/CampaignRewardDatatable.vue';
import SendTestEmailModal from '@email/components/SendTestEmailModal.vue';
import { Modal } from 'bootstrap';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import cloneDeep from 'lodash/cloneDeep';
import { nanoid } from 'nanoid';

dayjs.extend(timezone);

export default {
  components: {
    CampaignActionModal,
    CampaignActionDatatable,
    CampaignRewardModal,
    CampaignEmailModal,
    CampaignRewardDatatable,
    SendTestEmailModal,
    CampaignPrizeModal,
    CampaignPrizeDatatable,
    CampaignSelectWinnerModal,
    CampaignSocialPreviewModal,
  },
  props: {
    campaign: {
      type: Object,
      default: () => ({}),
    },
    salesChannels: {
      type: Array,
      default: () => [],
    },
    funnels: {
      type: Array,
      default: () => [],
    },
    promotions: {
      type: Array,
      default: () => [],
    },
    processedContacts: {
      type: Array,
      default: () => [],
    },
    emailTemplates: {
      type: Object,
      default: () => ({}),
    },
    metaInfo: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      title: '',
      savingCampaign: false,
      salesChannel: '',
      funnel: null,
      activeDate: dayjs(new Date(new Date()))
        .tz(this.timezone)
        .format('YYYY-MM-DD hh:mm a'),
      endDate: dayjs(new Date(new Date().setMonth(new Date().getMonth() + 1)))
        .tz(this.timezone)
        .format('YYYY-MM-DD hh:mm a'),
      isExpiry: false,
      actions: [],
      inviteeActions: [],
      rewards: [],
      errors: {},
      selectedAction: {},
      selectedInviteeAction: {},
      selectedReward: {},
      showValidationErrors: false,
      isEdit: false,
      count: -1,
      tableHeaders: [
        {
          name: 'Email',
          key: 'email',
          custom: true,
        },
        { name: 'Points', key: 'point' },
      ],
      emailTypes: [
        {
          label: 'Successful registration',
          value: 'register-success',
        },
        {
          label: 'Successful referral',
          value: 'referral-success',
        },
        {
          label: 'A reward is unlocked',
          value: 'reward-unlocked',
        },
        {
          label: 'Grand prize winner',
          value: 'grand-prize',
        },
      ],
      tempEmailTemplates: [
        {
          id: nanoid(),
          template: `<p>Hi {{name}},</p><p><br></p><p>You've successfully joined our referral campaign!</p><p><br></p><p>You can unlock exciting rewards with the points that you earn.</p><p><br></p><p>Earning points is easy — you earn points when someone clicks through your referral link and performs the action set out for the campaign.</p><p><br></p><p>Here's your unique referral link: {{referral_url}}</p><p><br></p><p>Just grab your link and start inviting all your friends and family.</p><p><br></p><p>You can check your points, rewards and more info anytime here: {{share_page_url}}</p><p><br></p><p>Start unlocking rewards with more points.</p><p><br></p><p>Here’s to a rewarding journey! :)</p>`,
          type: 'register-success',
          subject: `You're in {{name}}!`,
        },
        {
          id: nanoid(),
          template: `<p>Hi {{name}},</p><p><br></p><p>Great news! Someone just performed an action through your referral link.</p><p><br></p><p>You now have more points. Here you can see your latest points: {{share_page_url}}.</p><p><br></p><p>People seem to love what you’ve shared with them.</p><p><br></p><p>So, keep sharing your unique referral URL: {{referral_url}}</p><p><br></p><p>Share this link with your friends and family whom you think will benefit.</p><p><br></p><p>Happy Sharing :)</p>`,
          type: 'referral-success',
          subject: `{{name}}, someone just performed an action through your referral link!`,
        },
        {
          id: nanoid(),
          template: `<p>Nicely done, {{name}}!</p><p><br></p><p>You’ve earned enough points to unlock a well-deserved reward.</p><p><br></p><p>Redeem your reward here: {{share_page_url}}</p><p><br></p><p>Continue to unlock more rewards — your unique referral URL:</p><p>{{referral_url}}</p><p><br></p><p>Keep up the great work :)</p>`,
          type: 'reward-unlocked',
          subject: `{{name}}, you just unlocked a reward!`,
        },
        {
          id: nanoid(),
          template: `<p>Hi {{name}},</p><p><br></p><p>I have some amazing news for you...</p><p><br></p><p>The campaign has come to an end... and you won!</p><p><br></p><p>Congratulations on winning the grand prize!</p><p><br></p><p>We'll reach out to you in the next 48 hours with all the details. For now, we just wanted to congratulate you, and thank you for being so active throughout the campaign.</p><p><br></p><p>Talk soon!</p>`,
          type: 'grand-prize',
          subject: `{{name}}, congratulations! You’re the winner!`,
        },
      ],
      selectedEmailType: {
        label: 'Successful registration',
        value: 'register-success',
      },
      emailStatus: {
        'register-success': true,
        'referral-success': true,
        'reward-unlocked': true,
        'grand-prize': true,
      },
      testEmailContent: { template: '', type: '', subject: '' },
      actionType: 'inviter',
      prizes: {},
      winners: [],
      emailedWinners: [],
      socialNetworkEnabled: true,
      shareEmailEnabled: true,
      selectedSocial: null,
      referralSocialNetworks: ['facebook', 'twitter'],
      socialMessage: '',
      emailMessage: '',
      emailSubject: '',
      socialType: 'social',
      exportingContacts: false,
    };
  },
  computed: {
    isEnded() {
      const today = new Date(new Date());
      const convertToday = dayjs(today)
        .tz(this.timezone)
        .format('YYYY-MM-DD hh:mm a');
      return this.isExpiry ? this.endDate < convertToday : this.isExpiry;
    },
    participants() {
      return this.processedContacts.map((contact) => ({
        ...contact,
        isWinner:
          this.winners?.includes(contact.id) &&
          Object.keys(this.prizes ?? {}).length,
      }));
    },
  },
  watch: {
    activeDate(newValue) {
      if (newValue) {
        this.count += 1;
        if (this.count) {
          const date = new Date(newValue);
          this.endDate = dayjs(new Date(date.setMonth(date.getMonth() + 1)))
            .tz(this.timezone)
            .format('YYYY-MM-DD hh:mm a');
        }
      }
    },
    campaign: {
      immediate: true,
      deep: true,
      handler(newVal) {
        if (newVal) this.isEdit = true;
      },
    },
  },

  mounted() {
    if (this.campaign) {
      this.isEdit = true;
      this.title = this.campaign.title;
      this.salesChannel = Object.values(this.salesChannels)?.filter(
        (el) => el.id === this.campaign.sale_channel?.id
      )?.length
        ? this.campaign.sale_channel
        : '';
      this.funnel = this.campaign.funnel_id;
      this.isExpiry = Boolean(this.campaign.is_expiry);
      this.actions = cloneDeep(this.campaign.actions);
      this.inviteeActions = cloneDeep(this.campaign.inviteeActions ?? []);
      this.rewards = cloneDeep(this.campaign.rewards) ?? [];
      this.prizes = cloneDeep(this.campaign.prizes ?? {});
      this.winners = cloneDeep(
        Object.values(this.campaign?.prizes?.winners ?? {}) ?? []
      );
      this.activeDate = this.campaign.active_date;
      this.endDate = this.campaign?.end_date
        ? this.campaign.end_date
        : this.endDate;
      this.tempEmailTemplates = cloneDeep(
        this.emailTemplates.length
          ? this.emailTemplates
          : this.tempEmailTemplates
      );
      this.emailTemplates.forEach((et) => {
        this.emailStatus[et.type] = !!et.is_enabled;
      });
      this.emailedWinners = cloneDeep(
        this.processedContacts
          .filter((el) => {
            return this.campaign?.emailedWinners?.includes(el.contactRandomId);
          })
          ?.map((p) => p?.id)
      );
      this.socialMessage = this.campaign.social_message
        ? JSON.parse(this.campaign.social_message)
        : '';
      this.emailSubject = this.campaign.email_subject;
      this.emailMessage = this.campaign.email_message
        ? JSON.parse(this.campaign.email_message)
        : '';
      this.socialNetworkEnabled = Boolean(this.campaign.social_network_enabled);
      this.shareEmailEnabled = Boolean(this.campaign.share_email_enabled);
      this.referralSocialNetworks = this.campaign.referralSocialNetworks;
      this.emailedWinners = cloneDeep(this.winners);
    }
    localStorage.setItem(
      'emailTemplates',
      JSON.stringify(this.tempEmailTemplates)
    );
  },
  methods: {
    updateSoicalNetworks(e) {
      if (e) this.referralSocialNetworks.push(this.selectedSocial);
      else
        this.referralSocialNetworks = this.referralSocialNetworks.filter(
          (el) => el !== this.selectedSocial
        );
      this.referralSocialNetworks = [...new Set(this.referralSocialNetworks)];
    },
    storeName(e) {
      switch (e) {
        case 'online-store':
          return 'Onlin Store';
        case 'funnel':
          return 'Funnel';
        case 'mini-store':
          return 'Mini Store';
        default:
          return 'store';
      }
    },
    triggerActionModal(e) {
      this.actionType = 'inviter';
      this.$nextTick(() => {
        this.selectedAction = e;
        new Modal(document.getElementById('add-inviter-action-modal')).show();
      });
    },
    triggerInviteeActionModal(e) {
      this.actionType = 'invitee';
      this.$nextTick(() => {
        this.selectedInviteeAction = e;
        new Modal(document.getElementById('add-invitee-action-modal')).show();
      });
    },
    deleteAction(data) {
      this.$nextTick(() => {
        this.actions = this.actions.filter((el) => el?.id !== data.id);
      });
    },
    deleteInviteeAction(data) {
      this.$nextTick(() => {
        this.inviteeActions = this.inviteeActions.filter(
          (el) => el?.id !== data.id
        );
      });
    },
    triggerRewardModal(e) {
      this.$nextTick(() => {
        this.selectedReward = e;
        new Modal(document.getElementById('add-reward-modal')).show();
      });
    },
    deleteReward(data) {
      this.$nextTick(() => {
        this.rewards = this.rewards.filter((el) => el?.id !== data.id);
      });
    },
    editAction(data) {
      const index = this.actions.findIndex((el) => el?.id === data.id);
      if (index >= 0) this.actions[index] = data;
      else this.actions.push(data);
    },
    editInviteeAction(data) {
      const index = this.inviteeActions.findIndex((el) => el?.id === data.id);
      if (index >= 0) this.inviteeActions[index] = data;
      else this.inviteeActions.push(data);
    },
    editReward(data) {
      const index = this.rewards.findIndex((el) => el?.id === data.id);
      if (index >= 0) this.rewards[index] = data;
      else this.rewards.push(data);
    },
    showTestEmailModal(e) {
      this.testEmailContent = e;
      this.$nextTick(() => {
        new Modal(document.getElementById('referral-test-email-modal')).show();
      });
    },
    showEditEmailModal(e) {
      this.selectedEmailType = e;
      this.$nextTick(() => {
        new Modal(document.getElementById('edit-email-modal')).show();
      });
    },
    editPrize(data) {
      this.prizes = data;
    },
    notBeforeToday(date) {
      const datepicker = dayjs(date).format('YYYY-MM-DD hh:mm a');
      const today = new Date(new Date().setHours(0, 0, 0, 0));
      const convertToday = dayjs(today)
        .tz(this.timezone)
        .format('YYYY-MM-DD hh:mm a');
      return this.activeDate !== datepicker && datepicker < convertToday;
    },
    notBeforeStartDate(date) {
      return date < new Date(new Date(this.activeDate).setHours(0, 0, 0, 0));
    },
    notBeforeStartTime(date) {
      const activeDate = new Date(this.activeDate);
      const afterAdded = new Date(
        activeDate.setMinutes(activeDate.getMinutes() + 1)
      );
      return date <= new Date(afterAdded);
    },
    clearValidationError() {
      this.showValidationErrors = false;
    },
    saveEmailTemplate(e) {
      this.tempEmailTemplates = cloneDeep(e.emailType);
      localStorage.setItem(
        'emailTemplates',
        JSON.stringify(this.tempEmailTemplates)
      );
    },
    async saveCampaign() {
      let res = true;
      const nonEmailedWinners = this.winners.filter(
        (id) => !this.emailedWinners.includes(id)
      );
      if (Object.keys(this.prizes).length && nonEmailedWinners.length) {
        // eslint-disable-next-line no-restricted-globals
        res = confirm(
          'Send notification email to the winners after campaign being saved?'
        );
      }
      if (res) {
        this.savingCampaign = true;
        try {
          await this.$axios({
            method: this.isEdit ? 'put' : 'post',
            url: this.isEdit ? 'update' : 'create',
            data: {
              id: this.campaign?.id || null,
              title: this.title || null,
              salesChannel: this.salesChannel.type || null,
              funnel: this.funnel || null,
              activeDate: this.activeDate,
              endDate: this.isExpiry ? this.endDate : null,
              isExpiry: this.isExpiry,
              actions: this.actions.filter((e) =>
                this.salesChannel?.type === 'funnel'
                  ? e
                  : e.actionType !== 'sign-up'
              ),
              inviteeActions: this.inviteeActions.filter((e) =>
                this.salesChannel?.type === 'funnel'
                  ? e
                  : e.actionType !== 'sign-up'
              ),
              rewards: this.rewards,
              prizes: Object.keys(this.prizes).length ? [this.prizes] : [],
              suggestedWinners:
                Object.values(this.campaign?.prizes?.winners ?? {})?.filter(
                  (id) => {
                    return !this.winners?.includes(id);
                  }
                ) ?? [],
              acceptedWinners: this.winners,
              emailTemplates:
                JSON.parse(localStorage.getItem('emailTemplates')) ??
                this.tempEmailTemplates,
              emailStatus: this.emailStatus,
              socialMessage: this.socialMessage,
              emailSubject: this.emailSubject,
              emailMessage: this.emailMessage,
              socialNetworkEnabled: this.socialNetworkEnabled,
              shareEmailEnabled: this.shareEmailEnabled,
              referralSocialNetworks: this.referralSocialNetworks,
            },
          }).then(() => {
            this.savingCampaign = false;
            this.$toast.success('Success', 'Referral Campaign has been saved');
            this.$inertia.visit(`/referral/campaigns`);
          });
        } catch (error) {
          const { data } = error.response;
          const { errors } = data;
          this.errors = errors;
          this.showValidationErrors = true;
          this.savingCampaign = false;
          if (!data.message.includes('exceed_limit')) {
            this.$toast.warning('Warning', 'Check the required field');
          }
        }
      }
    },
    async exportContacts() {
      this.exportingContacts = true;
      try {
        await this.$axios({
          method: 'post',
          url: 'participants/export',
          data: {
            participants: this.processedContacts.map((contact) => ({
              email: contact.email,
              point: contact.point,
              phone: contact.phone_number,
              fname: contact.fname,
              lname: contact.lname,
              joinDate: contact.joinDate,
              joinTime: contact.joinTime,
              isWinner:
                this.winners?.includes(contact.id) &&
                Object.keys(this.prizes ?? {}).length,
            })),
          },
        }).then((res) => {
          // this.$toast.success('Success', 'Exported successfully');
          const { data } = res;
          const encodedUri = encodeURI(`data:text/csv;charset=utf-8,${data}`);
          this.exportingContacts = false;

          const link = document.createElement('a');
          link.setAttribute('href', encodedUri);
          link.setAttribute('download', 'participant_data.csv');
          document.body.appendChild(link);

          link.click();
        });
      } catch (error) {
        const { data } = error.response;
        const { errors } = data;
        this.errors = errors;
        if (!data.message.includes('exceed_limit')) {
          this.$toast.warning('Warning', 'Please try again later!');
        }
      }
    },
  },
};
</script>

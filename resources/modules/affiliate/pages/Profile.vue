<template>
  <BasePageLayout
    :page-name="memberName + `&nbsp&nbsp | &nbsp&nbsp` + memberData.email"
    back-to="/affiliate/members"
  >
    <template #left>
      <BaseCard
        has-header
        has-toolbar
        title="General"
      >
        <template #toolbar>
          <div class="d-flex flex-column">
            <BaseDatePicker
              v-model="dateRange"
              placeholder="Select date range"
              range
              value-type="date"
              :disabled-date="disableAfterToday"
              @change="handleDatesChange"
              @clear="clearDates"
            />
            <!-- <BaseButton
              class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
              type="link"
              :href="`/people/profile/${peopleProfileInfo?.contactRandomId}`"
            >
              <span class="svg-icon svg-icon-4 me-1"> View People Profile </span>
            </BaseButton> -->
          </div>
        </template>
        <!-- <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
          <BaseButton
            class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            type="link"
            :href="`/people/profile/${peopleProfileInfo?.contactRandomId}`"
          >
            <span class="svg-icon svg-icon-4 me-1"> View People Profile </span>
          </BaseButton>
          <BaseButton
            class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            type="link"
            data-bs-toggle="modal"
            data-bs-target="#edit-affiliate-profile-modal"
          >
            <span class="svg-icon svg-icon-4 me-1"> Edit Profile </span>
          </BaseButton>
        </div> -->
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(uniqueViews)?.length ?? 0"
            text_title="Clicks"
            class="h-100"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(Object.values(contacts))?.length ?? 0"
            text_title="People"
            class="h-100"
          />
        </div>

        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="getFilteredData(orders)?.length"
            text_title="Orders"
            class="h-100"
          />
        </div>
        <div class="col-xl-3 my-0 mb-6">
          <AffiliateCard
            :quantity="
              getFilteredData(orders)
                ?.map((e) => e.sale)
                .reduce((p, c) => +(p ?? 0) + +(c ?? 0), 0)
                ?.toFixed(2)
            "
            :text_title="`Sales (${currency})`"
            class="h-100"
          />
        </div>
      </BaseCard>
      <BaseCard
        has-header
        title="Commissions"
      >
        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(totalCommissions)"
            :text_title="`Total Commissions (${currency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(pendingCommissions)"
            :text_title="`Pending Commissions (${currency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(approvedCommissions)"
            :text_title="`Approved Commissions (${currency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(paidCommissions)"
            :text_title="`Withdrawn Commissions (${currency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(disapprovedCommissions)"
            :text_title="`Disapproved Commissions (${currency})`"
            class="h-100"
          />
        </div>

        <div class="col-xl-4 my-0 mb-6">
          <AffiliateCard
            :quantity="specialCurrencyCalculation(disapprovedPayouts)"
            :text_title="`Disapproved Payouts (${currency})`"
            class="h-100"
          />
        </div>
      </BaseCard>
      <BaseCard>
        <BaseTab
          :tabs="tabs"
          @click="tabType = $event"
        />
        <AffiliateProfileOrder
          v-if="tabType === 'orders'"
          :orders="orders"
          :currency="currency"
          :all-customers="allCustomers"
        />

        <AffiliateProfilePeople
          v-if="tabType === 'people'"
          :people="contacts"
          :currency="currency"
        />
      </BaseCard>
    </template>
    <template #right>
      <BaseCard
        has-header
        has-toolbar
        title="Contact Info"
      >
        <template #toolbar>
          <BaseButton
            class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            type="link"
            data-bs-toggle="modal"
            data-bs-target="#edit-affiliate-profile-modal"
          >
            <span class="svg-icon svg-icon-4 me-1">Edit </span>
          </BaseButton>
        </template>
        <BaseFormGroup>
          <div class="text-gray-600">
            {{ `Name: ${memberName ?? '-'}` }}
          </div>
          <div class="text-gray-600">
            {{ `Email: ${memberData.email ?? '-'}` }}
          </div>
          <div class="text-gray-600">
            {{ `Affiliate ID: ${memberData.affiliate_id ?? '-'}` }}
          </div>
          <div class="text-gray-600">
            {{ `Promo Code: ` }}

            <BaseBadge
              :text="`${memberData.promo_code || '-'}`"
              type="secondary"
            />
          </div>
          <div class="text-gray-600">
            Affiliate Group:
            <span v-if="!memberData.groups.length">-</span>
            <BaseBadge
              v-for="(group, index) in memberData.groups"
              :key="index"
              :text="group"
            />
          </div>
          <div class="text-gray-600">
            {{ `Email: ${memberData.email ?? '-'}` }}
          </div>
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Campaign Info"
      >
        <BaseFormGroup
          v-for="(campaign, index) in campaigns"
          :key="index"
        >
          <h4>
            {{ `${campaign.title ?? '-'}` }}
          </h4>
          <div class="text-gray-600">
            {{
              `Commission: ${
                currency + ' ' + specialCurrencyCalculation(campaign.commission)
              }`
            }}
          </div>
          <div class="text-gray-600">
            {{ `Joined Date: ${campaign.joined_date ?? '-'}` }}
          </div>
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        title="Sublines Overview"
        has-header
      >
        <section v-if="levels?.length > 0">
          <div
            v-for="(el, i) in levels"
            :key="i"
          >
            <div class="d-flex flex-stack m-2">
              <span class="text-muted fw-bold fs-6"> Level {{ i + 1 }} </span>
              <span class="text-muted fw-bold fs-6">
                {{ el?.length }} sublines
              </span>
            </div>
            <div
              v-if="(levels?.length || 1) - 1 !== i"
              class="separator separator-dashed my-3"
            />
          </div>
        </section>
        <section v-else>
          <span class="fw-bold fs-6 text-muted">
            No have any sublines yet
          </span>
        </section>
      </BaseCard>
    </template>
    <template #footer>
      <!-- <BaseButton
        type="link"
        :href="`/affiliate/members`"
      >
        Cancel
      </BaseButton> -->
    </template>
  </BasePageLayout>
  <EditAffiliateProfileModal
    modal-id="edit-affiliate-profile-modal"
    :affiliate-profile="affiliateProfile"
    :address="address"
    :available-promotions="Object.values(availablePromotions)"
    :saving="savingEditedProfile"
    @update-affiliate-profile="handleUpdateAffiliateProfile"
  />
</template>

<script>
import dayjs from 'dayjs';
import 'vue-datepicker-next/index.css';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin';
import EditAffiliateProfileModal from '@affiliate/components/EditAffiliateProfileModal.vue';
import AffiliateProfileOrder from '@affiliate/components/AffiliateProfileOrder.vue';
import AffiliateProfilePeople from '@affiliate/components/AffiliateProfilePeople.vue';
import eventBus from '@services/eventBus.js';
import { Modal } from 'bootstrap';
import AffiliateCard from '@shared/components/AffiliateCard.vue';

import {
  UPDATE_ADDRESS,
  UPDATE_PROFILE,
} from '@affiliate/lib/affiliateMemberProfileBus.js';

const startDate = '2019-01-01';
const endDate = dayjs().$d;

export default {
  components: {
    EditAffiliateProfileModal,
    AffiliateProfileOrder,
    AffiliateProfilePeople,
    AffiliateCard,
  },
  mixins: [specialCurrencyCalculationMixin],
  props: {
    availablePromotions: {
      type: Object,
      required: true,
    },
    allCustomers: {
      type: Array,
      required: true,
    },
    memberData: {
      type: Object,
      required: true,
    },
    campaigns: {
      type: Array,
      required: false,
      default: () => [],
    },
    people: {
      type: Number,
      default: 0,
    },
    contacts: {
      type: Object,
      default: () => {},
    },
    uniqueViews: {
      type: Array,
      default: () => [],
    },
    orders: {
      type: Array,
      required: true,
      default: () => [],
    },
    currency: {
      type: String,
      required: true,
    },
    totalCommissions: {
      type: Number,
      required: true,
    },
    pendingCommissions: {
      type: Number,
      required: true,
    },
    approvedCommissions: {
      type: Number,
      required: true,
    },
    paidCommissions: {
      type: Number,
      required: true,
    },
    pendingPayouts: {
      type: Number,
      required: true,
    },
    disapprovedPayouts: {
      type: Number,
      required: true,
    },
    disapprovedCommissions: {
      type: Number,
      required: true,
    },
    levels: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      dateRange: [],
      tab: 'showOrderTab',
      affiliateGroupsLoading: false,
      savingEditedProfile: false,
      affiliateProfile: {},
      address: {},
      busEvents: [
        {
          event: UPDATE_PROFILE,
          cb: (newProfile) => {
            this.affiliateProfile = {
              ...this.affiliateProfile,
              ...newProfile,
            };
          },
        },
        {
          event: UPDATE_ADDRESS,
          cb: (newAddress) => {
            this.address = {
              ...this.address,
              ...newAddress,
            };
          },
        },
      ],
      tabs: [
        { label: 'People', target: 'people' },
        { label: 'Orders', target: 'orders' },
      ],
      tabType: 'people',
    };
  },

  computed: {
    memberName() {
      return `${this.memberData.first_name ?? ''} ${
        this.memberData.last_name ?? ''
      }`;
    },

    peopleProfileInfo() {
      return this.allCustomers.find(
        (cust) => cust.email === this.memberData?.email
      );
    },

    datesBetweenRange() {
      let dates = [];
      // const [startDate, endDate] = this.dateRange;
      let theDate = dayjs(this.dateRange[0] ?? '2019-01-01').format(
        'YYYY-MM-DD'
      );
      while (
        theDate < dayjs(this.dateRange[1] ?? dayjs()).format('YYYY-MM-DD')
      ) {
        dates = [theDate, ...dates];
        theDate = dayjs(theDate).add(1, 'days').format('YYYY-MM-DD');
      }
      return [dayjs(endDate).format('YYYY-MM-DD'), ...dates];
    },
  },

  mounted() {
    this.affiliateProfile = {
      ...this.memberData,
      phone_number: '',
      // delete above line if phone_num is added in db
    };
    this.address = {
      address: this.memberData.address,
      city: this.memberData.city,
      state: this.memberData.state,
      zipcode: this.memberData.zipcode,
      country: this.memberData.country,
    };

    this.registerAffiliateProfileEventBus();
  },

  methods: {
    clearDates() {
      this.dateRange = [];
    },

    registerAffiliateProfileEventBus() {
      this.busEvents.forEach(({ event, cb }) => {
        eventBus.$on(event, cb);
      });
    },

    disableAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0));
    },

    handleDatesChange(newDates) {
      const startingDate = newDates[0];
      const endingDate = newDates[1];
      this.dateRange = [startingDate, endingDate];
    },

    getFilteredData(data) {
      const filteredData = data.filter((d) =>
        this.datesBetweenRange.includes(
          new Date(d?.created_at).toLocaleDateString('sv-SE')
        )
      );
      return filteredData;
    },
    handleUpdateAffiliateProfile({ newProfile }) {
      try {
        this.$axios.put('/affiliate/member/profile', newProfile).then(() => {
          Modal.getInstance(
            document.getElementById('edit-affiliate-profile-modal')
          ).hide();
          this.$inertia.visit(window.location.pathname, { replace: true });
          this.$toast.success('Success', 'Successfully update profile');
        });
      } catch (err) {
        this.$toast.error('Error', 'Failed to update profile');
        this.savingEditedProfile = false;
      }
    },
  },
};
</script>

<template>
  <BasePageLayout page-name="People Profile" back-to="/people">
    <template #left>
      <div class="card mb-6 pt-3">
        <div class="card-body pt-11 pb-0 row">
          <div class="d-flex align-items-center mb-2">
            <h2 class="text-gray-900 fs-2 fw-bolder me-1">
              {{ `${contact.fname ?? ''} ${contact.lname ?? ''}` }}
            </h2>
          </div>

          <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
            <a
              :href="'mailto:' + contact.email"
              class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            >
              <span class="svg-icon svg-icon-4 me-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <path
                    opacity="0.3"
                    d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z"
                    fill="black"
                  />
                  <path
                    d="M21 5H2.99999C2.69999 5 2.49999 5.10005 2.29999 5.30005L11.2 13.3C11.7 13.7 12.4 13.7 12.8 13.3L21.7 5.30005C21.5 5.10005 21.3 5 21 5Z"
                    fill="black"
                  />
                </svg>
              </span>
              {{ contact.email }}
            </a>

            <a
              :href="'https://api.whatsapp.com/send?phone=' + formatPhoneNumber"
              target="_blank"
              class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            >
              <span class="svg-icon svg-icon-4 me-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <path
                    opacity="0.3"
                    d="M22 12C22 17.5 17.5 22 12 22C6.5 22 2 17.5 2 12C2 6.5 6.5 2 12 2C17.5 2 22 6.5 22 12ZM12 7C10.3 7 9 8.3 9 10C9 11.7 10.3 13 12 13C13.7 13 15 11.7 15 10C15 8.3 13.7 7 12 7Z"
                    fill="black"
                  />
                  <path
                    d="M12 22C14.6 22 17 21 18.7 19.4C17.9 16.9 15.2 15 12 15C8.8 15 6.09999 16.9 5.29999 19.4C6.99999 21 9.4 22 12 22Z"
                    fill="black"
                  />
                </svg>
              </span>
              Whatsapp
            </a>

            <a
              :href="`tel:${contact.phone_number}`"
              class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
            >
              <span class="svg-icon svg-icon-4 me-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                >
                  <path
                    opacity="0.3"
                    d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z"
                    fill="black"
                  />
                  <path
                    d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z"
                    fill="black"
                  />
                </svg>
              </span>
              Call
            </a>
          </div>

          <div class="d-flex flex-wrap">
            <div
              class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
            >
              <div class="d-flex align-items-center">
                <div
                  class="fs-2 fw-bolder"
                  data-kt-countup="true"
                  data-kt-countup-value="4500"
                  data-kt-countup-prefix="$"
                >
                  {{ specialCurrencyCalculation(totalSales) }}
                </div>
              </div>
              <div class="fw-bold fs-6 text-gray-400">
                Total Sales ({{ currency }})
              </div>
            </div>
            <div
              class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
            >
              <div class="d-flex align-items-center">
                <div
                  class="fs-2 fw-bolder"
                  data-kt-countup="true"
                  data-kt-countup-value="80"
                >
                  {{ specialCurrencyCalculation(averageOrdervalue) }}
                </div>
              </div>
              <div class="fw-bold fs-6 text-gray-400">
                Average Order Value ({{ currency }})
              </div>
            </div>
            <div
              class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
            >
              <div class="d-flex align-items-center">
                <div
                  class="fs-2 fw-bolder"
                  data-kt-countup="true"
                  data-kt-countup-value="60"
                  data-kt-countup-prefix="%"
                >
                  {{ contact.salesOrdersCount }}
                </div>
              </div>
              <div class="fw-bold fs-6 text-gray-400">Total Orders</div>
            </div>
            <div
              class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
            >
              <div class="d-flex align-items-center">
                <div
                  class="fs-2 fw-bolder"
                  data-kt-countup="true"
                  data-kt-countup-value="60"
                  data-kt-countup-prefix="%"
                >
                  {{
                    specialCurrencyCalculation(
                      parseFloat(latestProcessedContact.credit_balance) / 100
                    )
                  }}
                </div>
              </div>
              <div class="fw-bold fs-6 text-gray-400">
                Credits ({{ currency }})
              </div>
            </div>
          </div>

          <!-- Profile nav -->
          <ul
            class="nav nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder px-4"
          >
            <li
              v-for="profileTab in profileTabs"
              :key="profileTab"
              class="nav-item mt-2"
              @click="tab = profileTab.tab"
            >
              <div
                class="tabs ms-0 me-10 py-5 cursor-pointer"
                :class="{ tabUnderline: tab == profileTab.tab }"
              >
                {{ profileTab.name }}
              </div>
            </li>
          </ul>
        </div>
      </div>

      <BaseCard>
        <div class="justify-content-end">
          <!-- timeline -->
          <div
            v-if="tab === 'showActivityLog'"
            id="timeline"
            class="tab-pane showActivityLog"
            role="tabpanel"
            aria-expanded="true"
          >
            <PeopleProfileTimeline
              :tracking-logs="trackingActivitiesInDate"
              :funnel-domains="funnelDomains"
            />
          </div>

          <div
            v-if="tab === 'order'"
            id="timeline"
            class="tab-pane order w-100"
            role="tabpanel"
            aria-expanded="true"
          >
            <PeopleProfileOrder :orders="orders" />
          </div>

          <div
            v-if="tab === 'topVisited'"
            id="timeline"
            class="tab-pane topVisited w-100"
            role="tabpanel"
            aria-expanded="true"
          >
            <PeopleProfileTopVisited
              :tracking-activities="trackingActivities"
              :domain="domain"
            />
          </div>

          <div
            v-if="tab === 'interest'"
            id="timeline"
            class="tab-pane interest w-100"
            role="tabpanel"
            aria-expanded="true"
          >
            <PeopleProfileInterest
              :tracking-activities="trackingActivities"
              :products="products"
              :order-details="orderDetails"
            />
          </div>

          <div
            v-if="tab === 'purchased'"
            id="timeline"
            class="tab-pane purchased w-100"
            role="tabpanel"
            aria-expanded="true"
          >
            <PeopleProfilePurchased :orders="orderDetails" />
          </div>

          <div
            v-if="tab === 'courses'"
            class="tab-pane course w-100"
            style="padding-bottom: 20px"
          >
            <PeopleProfileCourses :students="courseStudents" />
          </div>

          <div
            v-if="tab === 'credits'"
            class="tab-pane credits w-100"
            style="padding-bottom: 20px"
          >
            <PeopleProfileCredit
              :processed-contact="latestProcessedContact"
              :exchange-rate="exchangeRate"
              :credit-records="latestProcessedContact.store_credits"
              :currency="currency"
              :currencies="currencies"
            />
          </div>

          <div
            v-if="tab === 'showReferral'"
            class="tab-pane showReferral w-100"
            style="
              width: 100%;
              padding-bottom: 20px !important;
              padding-top: 10px !important;
            "
          >
            <PeopleProfileReferral
              :referral-campaigns="referralCampaigns"
              :refer="processedContact?.refer ?? []"
              :refer-by="processedContact?.referBy ?? []"
            />
          </div>
          <div
            v-if="tab === 'showNotes'"
            class="row showNotes"
            style="
              width: 100%;
              padding-bottom: 20px !important;
              padding-top: 10px !important;
            "
          >
            <PeopleProfileNotes
              :notes="notes"
              :contact-id="processedContact.id"
            />
          </div>
        </div>
      </BaseCard>
    </template>

    <template #right>
      <BaseCard class="py-4">
        <div class="fw-bolder fs-4 pb-3">Tags</div>
        <div>
          <BaseMultiSelect
            v-model="selectTag"
            push-tags
            taggable
            :options="filterSelectedTags"
            placeholder="Add Tags"
            label="tagName"
          >
            <template #option="{ option }">
              {{ option.tagName }}
            </template>
          </BaseMultiSelect>
        </div>

        <div class="mt-3">
          <BaseBadge
            v-for="(tag, index) in contact.processed_tags"
            :key="tag.id"
            :text="tag.tagName"
            has-delete-button
            @click="deleteTag(tag.id, index)"
          />
        </div>
      </BaseCard>

      <BaseCard class="py-4">
        <div class="fw-bolder fs-4 pb-3">Segment</div>
        <div>
          <BaseBadge
            v-for="segmentName in segmentNames"
            :key="segmentName"
            :text="segmentName"
          />
        </div>
      </BaseCard>

      <BaseCard>
        <div class="d-flex flex-stack fs-4 py-3">
          <div
            class="fw-bolder"
            data-bs-toggle="collapse"
            href="#kt_customer_view_details"
            role="button"
            aria-expanded="false"
            aria-controls="kt_customer_view_details"
          >
            Profile
          </div>
          <BaseButton
            type="light-primary"
            data-bs-toggle="modal"
            data-bs-target="#edit-profile-modal"
          >
            Edit
          </BaseButton>
        </div>
        <div class="separator separator-dashed my-3" />

        <PeopleProfileProfile
          :contact="contact"
          :address="address"
          :custom-fields="customFields"
          :marketing-email-status="marketingEmailStatus"
        />
      </BaseCard>
    </template>
  </BasePageLayout>
  <EditProfileModal
    modal-id="edit-profile-modal"
    :contact="contact"
    :address="address"
    :custom-fields="customFields"
    :saving="savingEditedProfile"
    @update-people-profile="handleUpdatePeopleProfile"
  />
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import eventBus from '@services/eventBus.js';
import {
  UPDATE_ADDRESS,
  UPDATE_CONTACT,
  UPDATE_CUSTOM_FIELDS,
  UPDATE_NOTES,
} from '@people/lib/peopleProfileBus.js';
import PeopleProfileProfile from '@people/components/PeopleProfileProfile.vue';
import PeopleProfileNotes from '@people/components/PeopleProfileNotes.vue';
import PeopleProfileReferral from '@people/components/PeopleProfileReferral.vue';
import PeopleProfileCredit from '@people/components/PeopleProfileCredit.vue';
import PeopleProfileCourses from '@people/components/PeopleProfileCourses.vue';
import PeopleProfileTimeline from '@people/components/PeopleProfileTimeline.vue';
import PeopleProfileTopVisited from '@people/components/PeopleProfileTopVisited.vue';
import PeopleProfileInterest from '@people/components/PeopleProfileInterest.vue';
import PeopleProfileOrder from '@people/components/PeopleProfileOrder.vue';
import PeopleProfilePurchased from '@people/components/PeopleProfilePurchased.vue';
import peopleProfileQueryStringsMixin from '@people/mixins/peopleProfileQueryStringsMixins.js';
import EditProfileModal from '@people/components/EditProfileModal.vue';
import { validationFailedNotification } from '@shared/lib/validations.js';
import specialCurrencyCalculationMixin from '@shared/mixins/specialCurrencyCalculationMixin.js';
import { Modal } from 'bootstrap';

export default {
  components: {
    PeopleProfileProfile,
    PeopleProfileNotes,
    PeopleProfileReferral,
    PeopleProfileTimeline,
    EditProfileModal,
    PeopleProfileTopVisited,
    PeopleProfileInterest,
    PeopleProfilePurchased,
    PeopleProfileCredit,
    PeopleProfileCourses,
    PeopleProfileOrder,
  },

  mixins: [peopleProfileQueryStringsMixin, specialCurrencyCalculationMixin],
  props: {
    accountRandomId: {
      type: [Number, String],
      default: () => '',
    },
    currency: {
      type: String,
      default: () => '',
    },
    orders: {
      type: [Array, Object],
      default: () => [],
    },
    orderDetails: {
      type: [Array, Object],
      default: () => [],
    },
    groupFormContents: {
      type: [Array, Object],
      default: () => [],
    },
    forms: {
      type: [Object],
      default: () => ({}),
    },
    processedContact: {
      type: Object,
      default: () => ({}),
    },
    allAddress: {
      type: Object,
      default: () => ({}),
    },
    customFieldNames: {
      type: Array,
      default: () => [],
    },
    allNotes: {
      type: [Array, Object],
      default: () => [],
    },
    marketingEmailStatus: {
      type: String,
      default: () => '',
    },
    tags: {
      type: Array,
      default: () => [],
    },
    taggable: {
      type: String,
      default: () => '',
    },
    trackingActivities: {
      type: [Object, Array],
      default: () => [],
    },
    trackingActivitiesInDate: {
      type: [Object, Array],
      default: () => ({}),
    },
    products: {
      type: Array,
      default: () => [],
    },
    domain: {
      type: Array,
      default: () => [],
    },
    funnelDomains: {
      type: Array,
      default: () => [],
    },
    exchangeRate: {
      type: [Number, String],
      default: () => '',
    },
    currencies: {
      type: Object,
      default: () => ({}),
    },
    totalSales: {
      type: [String],
      default: () => '',
    },
    referralCampaigns: {
      type: Array,
      default: () => [],
    },
    courseStudents: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      contact: {},
      address: {},
      notes: [],
      customFields: [],
      segmentNames: [],
      segmentNamesLoading: false,
      showPurchases: false,
      showActivityLog: false,
      showReferral: false,
      showNotes: false,
      selectTag: [],
      tab: 'showActivityLog',
      savingEditedProfile: false,
      latestProcessedContact: {},

      // people profile bus events
      //
      // put all events to be registered in array below
      // Event structure: {
      //    event: Event name,
      //    cb   : callback function for the event
      // }
      busEvents: [
        {
          event: UPDATE_CONTACT,
          cb: (newContact) => {
            this.contact = {
              ...this.contact,
              ...newContact,
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
        {
          event: UPDATE_CUSTOM_FIELDS,
          cb: (newCustomFields) => {
            this.customFields = newCustomFields.map((cf) => ({
              ...cf,
              customFieldContent:
                cf.customFieldContent === null ? '-' : cf.customFieldContent,
            }));
          },
        },
        {
          event: UPDATE_NOTES,
          cb: (newNotes) => {
            console.log(newNotes);
            this.notes = newNotes;
          },
        },
      ],
      profileTabs: [
        {
          name: 'Activity',
          tab: 'showActivityLog',
        },
        {
          name: 'Order',
          tab: 'order',
        },
        {
          name: 'Top visited',
          tab: 'topVisited',
        },
        // {
        //   name: 'Interest',
        //   tab: 'interest',
        // },
        {
          name: 'Purchased',
          tab: 'purchased',
        },
        {
          name: 'Courses',
          tab: 'courses',
        },
        {
          name: 'Credits',
          tab: 'credits',
        },
        // {
        //   name: 'Referral',
        //   tab: 'showReferral',
        // },
        {
          name: 'Notes',
          tab: 'showNotes',
        },
      ],
    };
  },
  computed: {
    filterSelectedTags() {
      const tags = this.contact.processed_tags ?? [];
      const selectedTags = tags.map((e) => e.tagName);
      return this.tags.filter((e) => !selectedTags.includes(e.tagName));
    },

    /**
     * For the reason why this link is generated like this, refer to
     * the controller method responsible for /people/view/{contactRandomId} route
     *
     * @returns {string}
     */
    backToLink() {
      return this.from === 'segment' ? `/segments/${this.refKey}` : `/people`;
    },

    averageOrdervalue() {
      return (
        parseFloat(this.totalSales) / (this.contact.salesOrdersCount || 1)
      ).toFixed(2);
    },

    formatPhoneNumber() {
      const { phone_number: phoneNumber } = this.contact;
      return phoneNumber ? phoneNumber.replace(/(\s|\D)+/g, '') : null;
    },
  },
  watch: {
    selectTag(newValue) {
      if (!newValue) return;

      axios
        .post('/selectTag', {
          selectTag: newValue.id,
          currentContact: this.contact.id,
          newTag: typeof newValue === 'object' ? newValue.tagName : newValue,
        })
        .then((response) => {
          this.contact.processed_tags = response.data.processedTagArray;
          this.$toast.success('Success', 'Successfully added the Tag.');
          this.selectTag = null;
        })
        .catch((error) => {
          this.$toast.error('Failed', 'Failed to add the tag.');
        });
    },
  },
  mounted() {
    this.latestProcessedContact = this.processedContact;
    eventBus.$on('processedContactUpdated', (contacts) => {
      this.latestProcessedContact = contacts.find(
        (e) => e.id === this.latestProcessedContact.id
      );
    });

    this.contact = {
      ...this.processedContact,
      phone_number: this.processedContact.phone_number ?? '',
      salesOrdersCount: this.processedContact.orders.length,
    };
    this.address = { ...this.allAddress };
    this.notes = cloneDeep(this.allNotes);

    // get a "dev" friendly structure to use in frontend
    this.customFields = [
      ...this.processedContact.people_custom_fields.map((customField) => ({
        customFieldName:
          customField.people_custom_field_name?.custom_field_name,
        type: customField.people_custom_field_name?.type || 'text',
        customFieldContent: customField.custom_field_content,
      })),

      // empty custom fields
      ...this.customFieldNames.map((c) => ({
        customFieldName: c.custom_field_name,
        type: c.type || 'text',
        customFieldContent: '-',
      })),
    ];

    /**
     * register people profile event bus
     *
     * Note: use Vuex if you feel this bus is growing large gradually
     */
    this.registerPeopleProfileEventBus();

    this.loadSegmentNames();

    this.showTabFromQuery();
  },
  methods: {
    showTabFromQuery() {
      const urlSearchParams = new URLSearchParams(window.location.search);
      const params = Object.fromEntries(urlSearchParams.entries());
      if (params?.tab) {
        switch (params.tab) {
          case 'top-visited':
            this.tab = 'topVisited';
            break;
          case 'order':
            this.tab = 'order';
            break;
          case 'referral':
            this.tab = 'showReferral';
            break;
          case 'notes':
            this.tab = 'showNotes';
            break;
          case 'purchased':
            this.tab = 'purchased';
            break;
          case 'courses':
            this.tab = 'courses';
            break;
          case 'credits':
            this.tab = 'credits';
            break;
          case 'interest':
            this.tab = 'interest';
            break;
          default:
            this.tab = 'showActivityLog';
        }
      }
    },
    handleUpdateTags(tags) {
      this.tag = tags;
    },
    registerPeopleProfileEventBus() {
      this.busEvents.forEach(({ event, cb }) => {
        eventBus.$on(event, cb);
      });
    },
    loadSegmentNames() {
      this.segmentNamesLoading = true;

      this.$axios
        .get(
          `/people/profile/${this.processedContact.contactRandomId}/segments`
        )
        .then(({ data: { segmentNames } }) => {
          this.segmentNames = segmentNames;
        })
        .catch((err) => console.error(err))
        .finally(() => {
          this.segmentNamesLoading = false;
        });
    },

    handleUpdatePeopleProfile({ newContact, newAddress, newCustomFields }) {
      const reqs = [
        {
          url: `/people/profile/${this.contact.id}`,
          data: newContact,
        },
        {
          url: `/people/profile/address`,
          data: {
            id: this.address.id || null,
            processed_contact_id: this.contact.id,
            ...newAddress,
          },
        },
        {
          url: `/people/profile/${this.contact.id}/customFields`,
          data: {
            // don't sync untouched new custom field
            customFields: newCustomFields.filter(
              (cf) => cf.customFieldContent !== null
            ),
          },
        },
      ];

      this.savingEditedProfile = true;
      Promise.allSettled(
        reqs.map((req) =>
          this.$axios.put(req.url, {
            ...req.data,
          })
        )
      )
        .then((results) => {
          results.forEach((result, idx) => {
            if (
              result?.status === 'rejected' &&
              result?.reason?.response?.status === 422
            ) {
              eventBus.$emit(
                'get-taken-email',
                result?.reason?.response?.data?.errors?.email[0]
              );
            }

            switch (idx) {
              case 0: {
                eventBus.$emit(UPDATE_CONTACT, newContact);
                break;
              }

              case 1: {
                eventBus.$emit(UPDATE_ADDRESS, newAddress);
                break;
              }

              case 2: {
                eventBus.$emit(UPDATE_CUSTOM_FIELDS, newCustomFields);
                break;
              }
              default:
                break;
            }
          });

          const resultsStatus = results.map((result) => result.status);

          // if all promises passed without any errors
          if (!resultsStatus.includes('rejected')) {
            this.$toast.success('Success', 'Successfully updated profile.');
            Modal.getInstance(
              document.getElementById('edit-profile-modal')
            ).hide();
          }
        })
        .finally(() => {
          this.savingEditedProfile = false;
        });
    },
    deleteTag(currentValue, index) {
      axios
        .post('/deleteSelectTag', {
          selectTag: currentValue,
          currentContact: this.contact.id,
        })
        .then((response) => {
          this.contact.processed_tags.splice(index, 1);
          this.$toast.success('Success', 'Successfully deleted the Tag.');
          this.selectTag = null;
        })
        .catch((error) => {
          this.$toast.error('Failed', 'Failed to delete the tag');
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.separator {
  display: block;
  height: 0;
  border-bottom: 1px solid #eff2f5;
}
.separator.separator-dashed {
  border-bottom-style: dashed;
  border-bottom-color: #e4e6ef;
}

.tabs {
  color: #a1a5b7;
  width: max-content;
}

.tabUnderline,
.tabs:hover {
  color: #009ef7;
  border-bottom: 2px solid #009ef7;
}

a {
  cursor: pointer;
}
</style>

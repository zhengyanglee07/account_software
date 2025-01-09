<template>
  <div class="people-profile-timeline-div pb-0">
    <div class="timeline">
      <!-- for the tracking record key (dates) -->
      <div
        v-for="(date, $dateIndex) in Object.keys(trackingLogs)"
        :key="$dateIndex"
        class="timeline-outer-items"
      >
        <h6 class="timeline-inner-item-title-date p-two grey-text mt-3">
          {{ formatDate(date) }}
        </h6>
        <!-- for the tracking record of that date -->
        <div
          v-for="(session, sessionIndex) in trackingLogs[date]"
          id="accordion-page-settings"
          :key="sessionIndex"
          class="timeline-inner-item"
        >
          <div
            id="headingOne"
            class="timeline-inner-item-title"
          >
            <span class="bullet" />
            <div class="row w-100 py-2">
              <div class="col-sm-3">
                <div>
                  <span class="time-content">
                    {{ formatTime(session.created_at) }}
                  </span>
                </div>
              </div>
              <div class="col-sm-9">
                <div class="row w-100">
                  <button
                    class="row w-100 font-weight-normal border-0 bg-white"
                    data-bs-toggle="collapse"
                    :data-bs-target="`#collapseTimeline${$dateIndex}-${sessionIndex}`"
                    aria-expanded="false"
                    aria-controls="collapseTimeline"
                    @click="isOpen = !isOpen"
                  >
                    <div class="p-0 text-start">
                      <template v-if="session.status === 'ongoing'">
                        Ongoing session
                      </template>
                      <template v-else-if="session.status === 'abandoned-cart'">
                        Abandoned cart
                      </template>
                      <template v-else-if="session.status === 'completed'">
                        {{
                          session?.lastActivity?.type === 'order'
                            ? 'Place an order'
                            : 'Submitted form'
                        }}
                        <a
                          target="_blank"
                          :href="session?.lastActivity?.url"
                          @click.stop
                        >
                          {{ session?.lastActivity?.name }}
                        </a>
                      </template>
                      <template v-else>
                        Bounced
                      </template>
                      <span>
                        at {{ salesChannel[session.sales_channel] }}
                        <a
                          v-if="session.sales_channel === 'funnel'"
                          target="_blank"
                          :href="`/funnel/${
                            funnelDetails[session.funnel_id]?.reference_key
                          }`"
                          @click.stop
                        >
                          {{ funnelDetails[session.funnel_id]?.funnel_name }}
                        </a>
                      </span>
                      <span>
                        <i
                          :class="`fas fa-caret-${
                            isOpen ? 'down' : 'right'
                          } ms-3 accordion-icons`"
                        />
                      </span>
                    </div>
                  </button>
                  <div
                    :id="'collapseTimeline' + $dateIndex + '-' + sessionIndex"
                    class="row collapse"
                    aria-labelledby="headingOne"
                    data-parent="#accordion-page-settings"
                  >
                    <table class="trackingLogsTable">
                      <tr style="border-bottom: 1px solid #e0e0e0">
                        <td class="w-100px">
                          Time
                        </td>
                        <td>Activity</td>
                      </tr>
                      <tr
                        v-for="(
                          activity, $activityIndex
                        ) in reversedTrackingLogs(session.activity_logs)"
                        :key="$activityIndex"
                      >
                        <td class="w-100px">
                          {{ formatTime(activity.created_at) }}
                        </td>
                        <td v-if="activity.type !== 'form'">
                          <Component
                            :is="activity.type === 'order' ? 'strong' : 'span'"
                          >
                            {{ activityDescription(activity.type) }}
                            <template v-if="activity.url">
                              <a
                                :href="activity.url"
                                target="_blank"
                              >
                                {{ activity.name }}
                              </a>
                              <template v-if="activity.type.includes('cart')">
                                {{
                                  activity.type === 'remove-from-cart'
                                    ? ' from'
                                    : ' to'
                                }}
                                cart
                              </template>
                              <template
                                v-else-if="activity.type.includes('page')"
                              >
                                page
                              </template>
                              <template v-else-if="activity.type === 'product'">
                                product
                              </template>
                            </template>
                            <span v-else>
                              {{ activity.name }}
                            </span>
                          </Component>
                        </td>
                        <td v-else>
                          <strong>
                            {{ activityDescription(activity.type) }}
                            <a
                              :href="activity.url"
                              target="_blank"
                            >
                              {{ activity.name }}
                            </a>
                          </strong>
                          <table
                            class="form-content-table"
                            style=""
                          >
                            <tr style="border-bottom: 1px solid #e0e0e0">
                              <td style="width: 40%">
                                Label
                              </td>
                              <td style="width: 60%">
                                Value
                              </td>
                            </tr>
                            <tr
                              v-for="(
                                { label, landing_page_form_content },
                                $submissionIndex
                              ) in activity.submission"
                              :key="$submissionIndex"
                            >
                              <td>
                                {{ label }}
                              </td>
                              <td>
                                {{ landing_page_form_content }}
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint consistent-return: 1 */
import dayjs from 'dayjs';

export default {
  props: {
    trackingLogs: {
      type: Object,
      default: () => ({}),
    },
    funnelDomains: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      isOpen: false,
      salesChannel: {
        'mini-store': 'Mini Store',
        'online-store': 'Online Store',
        funnel: 'Funnel',
      },
    };
  },

  computed: {
    funnelDetails() {
      return this.funnelDomains.reduce((acc, curr) => {
        acc[curr.id] = curr;
        return acc;
      }, {});
    },
  },

  methods: {
    formatDate(date) {
      return dayjs(date).format('DD MMMM YYYY');
    },

    formatTime(time) {
      return dayjs(time).format('hh:mm A');
    },

    activityDescription(type) {
      const detail = {
        page: 'Visited',
        product: 'Viewed',
        'store-page': 'Visited',
        'builder-page': 'Visited',
        'abandoned-cart': 'Abandoned cart',
        cart: 'Add',
        'add-to-cart': 'Add',
        'remove-from-cart': 'Remove',
        checkout: 'Initiate checkout',
        order: 'Placed an order',
        form: 'Submitted form',
      };

      return detail[type];
    },

    reversedTrackingLogs(logs) {
      return logs.sort((a, b) => {
        return (
          new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
      });
    },
  },
};
</script>

<style scoped lang="scss">
@mixin focus {
  &:focus,
  &:active {
    border: 1px solid #0e8dc7;
    outline: #0e8dc7;
  }
}

.form-content-table {
  border: 1px solid #ced4da;
  border-radius: 5px;
  width: 100%;
  color: #212529;
  opacity: 0.8;
  margin: 10px 0 10px 0;

  &:last-child {
    border-bottom: 1px solid #ced4da;
  }
}

td {
  padding: 0.5rem;
  font-size: 12px;
  border: 0;
  text-align: left !important;
}
</style>

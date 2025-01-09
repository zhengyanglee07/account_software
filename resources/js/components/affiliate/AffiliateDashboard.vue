<template>
  <div class="container-fluid px-0">
    <div class="row">
      <div class="col-md-12">
        <div class="affiliate-share">
          <div class="card p-3">
            <p class="card-text text-start">
              Share this affiliate link to your friends and followers.
            </p>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" style="cursor: context-menu"
                  >Your link is:</span
                >
              </div>
              <!-- <input type="text" :value="'https://my.hypershapes.com/register?affiliate_id='+ affiliate_user.affiliate_unique_id " class="form-control"> -->
              <input
                id="affiliate_url"
                type="text"
                style="cursor: text; background-color: white"
                :value="
                  (environment === 'staging'
                    ? 'https://staging.hypershapes.com/register'
                    : 'https://my.hypershapes.com/register') +
                  '?affiliate_id=' +
                  affiliate_user.affiliate_unique_id
                "
                readonly
                class="form-control"
              />
              <div class="input-group-append">
                <button
                  id="button-clipboard"
                  class="btn btn-secondary"
                  data-clipboard-target="#affiliate_url"
                  style="
                    width: 40px;
                    border-color: rgb(206, 212, 218);
                    background-color: white;
                    color: #202930;
                  "
                  data-bs-toggle="custom-tooltip"
                  data-bs-placement="bottom"
                  title="Copied to Clipboard!"
                >
                  <i class="fas fa-link" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3 affiliate-individual-card">
        <AffiliateCard
          :quantity="affiliate_detail.total_click_for_link"
          text_title="Unique Clicks"
          image_url="images\link.svg"
        />
      </div>

      <div class="col-md-3 affiliate-individual-card">
        <AffiliateCard
          :quantity="affiliate_detail.total_referrals"
          text_title="Signups"
          image_url="images\referral.svg"
        />
      </div>

      <div class="col-md-3 affiliate-individual-card">
        <AffiliateCard
          :quantity="affiliate_detail.current_free_user"
          text_title="Free Accounts"
          image_url="images\free.svg"
        />
      </div>

      <div class="col-md-3 affiliate-individual-card">
        <AffiliateCard
          :quantity="affiliate_detail.current_paid_account"
          text_title="Paid Accounts"
          image_url="images\payment.svg"
        />
      </div>
    </div>

    <CommissionCard v-if="!loading" :datas="details" />
    <!-- end second row -->
    <div class="row mb-5">
      <div class="col-md-12">
        <div class="row">
          <div class="col-md-12 row">
            <div
              class="card affiliate-datatable w-100 referral-container"
              style="padding: 0 20px"
            >
              <div class="card-body referral-container-content px-0">
                <ul
                  class="nav nav-pills nav-pills-sm nav-pills-label nav-pills-bold"
                  role="tablist"
                >
                  <li class="nav-item me-2">
                    <a
                      class="nav-link active"
                      data-bs-toggle="tab"
                      role="tab"
                      @click="tableName = 'history'"
                      >Referral Payment History</a
                    >
                  </li>
                  <li class="nav-item">
                    <a
                      class="nav-link"
                      data-bs-toggle="tab"
                      role="tab"
                      @click="tableName = 'status'"
                      >Referral Status</a
                    >
                  </li>
                </ul>
                <div class="tab-content">
                  <div
                    v-show="tableName === 'history'"
                    class="tab-pane active"
                    role="tabpanel"
                  >
                    <Datatable
                      :headers="historyHeaders"
                      :datas="referralHistory"
                    />
                  </div>
                  <div
                    v-show="tableName == 'status'"
                    class="tab-pane active"
                    role="tabpanel"
                  >
                    <Datatable
                      :headers="statusHeaders"
                      :datas="referralStatus"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- end container fluid -->
</template>

<script>
/* eslint-disable no-nested-ternary */
import affiliateCard from '@components/affiliate/component/AffiliateCard';
import * as ClipboardJS from 'clipboard';
import commissionCard from '@components/affiliate/component/CommissionCard';
import { Tooltip } from 'bootstrap';
import * as dayjs from 'dayjs';

export default {
  components: { affiliateCard, commissionCard },
  props: {
    affiliate_user: Object,
    affiliate_log: Array,
    affiliate_detail: Object,
    referral_history: Array,
    domain_url: String,
    affiliate_log_all: Array,
    environment: String,
    detailCards: Array,
  },
  data() {
    return {
      baseURL: process.env.MIX_APP_URL,
      historyHeaders: [
        { text: 'Date', value: 'date', order: 0 },
        { text: 'Referral No', value: 'referral_no', order: 0 },
        { text: 'Name', value: 'name', order: 0 },
        { text: 'Commission', value: 'commission', order: 0 },
        { text: 'Commission status', value: 'commission_status', order: 0 },
        { text: 'Refer By', value: 'refer_from', order: 0 },
        { text: 'Payout Countdown', value: 'countdown', order: 0 },
      ],
      statusHeaders: [
        { text: 'Referral No', value: 'referral_no', order: 0 },
        { text: 'Name', value: 'name', order: 0 },
        { text: 'Plan', value: 'plan', order: 0 },
        { text: 'Status', value: 'status', order: 0 },
      ],
      details: this.detailCards,
      isActive: false,
      tableName: 'history',
      loading: false,
    };
  },

  computed: {
    referralHistory() {
      return this.referral_history.map((item) => {
        const countdown = dayjs(item.credited_date).diff(
          dayjs(Date.now()),
          'day'
        );
        return {
          date: dayjs(item.paid_date).format('DD/MM/YYYY'),
          refer_from:
            item.affiliate_referral.refer_from_affiliate.id ===
            this.affiliate_user.id
              ? 'Me'
              : item.affiliate_referral.refer_from_affiliate.fullName,
          referral_no: item.affiliate_referral.referral_unique_id,
          name: item.affiliate_referral.referral_name,
          commission: `$ ${item.commission}`,
          commission_status: item.commission_status,
          countdown:
            countdown <= 0 ? 'Available for payout' : `${countdown} day(s)`,
        };
      });
    },

    referralStatus() {
      return this.affiliate_log_all.map((item) => {
        const referralSubscription = this.affiliate_log.find((data) => {
          return data.referral_unique_id === item.referral_unique_id;
        });
        return {
          referral_no: item.referral_unique_id,
          name: item.referral_name,
          plan:
            item.subscription_id && referralSubscription !== undefined
              ? referralSubscription.plan.includes('Free')
                ? `${referralSubscription.plan}`
                : `${referralSubscription.plan} ( ${this.capitalize(
                    item.subscription.subscription_plan_type
                      .subscription_plan_type
                  )} )`
              : 'No plan',
          status: item.referral_status,
          isActive: item.status !== 'cancelled',
        };
      });
    },
  },

  mounted() {
    // this.checkDetail();

    // tooltip for clipboard success message
    this.$nextTick(function () {
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="custom-tooltip"]')
      );

      const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl, {
          trigger: 'manual',
        });
      });
    });

    // clipboard
    const clipboard = new ClipboardJS('.btn-secondary');
    clipboard.on('success', function (e) {
      // Show success message in tooltip
      const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="custom-tooltip"]')
      );

      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl, {
          trigger: 'manual',
        });
      });

      e.clearSelection();
    });
  },

  methods: {
    capitalize(s) {
      if (typeof s !== 'string') return '';
      return s.charAt(0).toUpperCase() + s.slice(1);
    },
  },
};
</script>

<style scoped lang="scss">
// @import 'public\assets\dist\snow.css';

.modal-body {
  padding: 1.25rem;
}

.paypal-container {
  margin-top: 20px;
  @media (max-width: $md-display) {
    margin-top: 10px;
  }
}

.form-control {
  height: calc(1.5em + 1.3rem + 2px);
}

.form-control:focus {
  border-color: lightgrey;
}

li {
  list-style: none;
}

.actived {
  color: green;
}

.cancel {
  color: red;
}

.nav-pills.nav-pills-label .nav-item .nav-link:active,
.nav-pills.nav-pills-label .nav-item .nav-link.active,
.nav-pills.nav-pills-label .nav-item .nav-link.active:hover {
  background-color: rgba(230, 233, 236, 0);
  color: $base-font-color;
  border-radius: 0;
  border-bottom: 2px solid #202930;
}
.nav-pills .nav-link,
.nav-link {
  background-color: rgba(230, 233, 236, 0);
  color: $base-font-color;
  border-radius: 0;
  cursor: pointer;
  font-size: $base-font-size;
  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
    padding: 0.5rem 0.25rem;
  }
}

.modal-header {
  padding: 10px 20px;
  border-bottom: none;
  //   background-color: #1668ab;
  border-radius: 3px 3px 0 0;
  //   color: #fff;
  padding-top: 10px;
}

.modal-content {
  border-radius: 4px !important;
  -webkit-box-shadow: 0 1px 10px 4px rgba(0, 0, 0, 0.3) !important;
  -moz-box-shadow: 0 1px 10px 4px rgba(0, 0, 0, 0.3) !important;
  box-shadow: 0 1px 10px 4px rgba(0, 0, 0, 0.3) !important;
  border: none !important;
}

.modal-header h5 {
  font-size: 18px;
  text-align: left;
  text-transform: uppercase;
  margin: 0;
  line-height: 30px;
  font-weight: 600;
}

.modal-header .close {
  opacity: 0.3 !important;
}

.modal .modal-content .modal-header .close {
  color: white;
}

.close:hover {
  color: white;
}

.modal-dialog {
  margin: 100px auto !important;
}

.card {
  box-shadow: 0 0 13px 0 rgba(82, 63, 105, 0.05);
}

.input-group > .form-control:not(:last-child),
.input-group > .form-select:not(:last-child) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  cursor: text;
}

.affiliate-card {
  margin: 0 5px;
}

.affiliate-datatable {
  margin: 0 5px;
  @media (max-width: $md-display) {
    margin: 0;
  }
}

.affiliate_user_email {
  font-size: $base-font-size;
  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
  }
}

// .tab-content {
//   margin: 0 -20px;
// }

:deep .profile-label-class {
  font-size: $base-font-size;
  font-weight: normal;
  font-family: $base-font-family;
  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size;
  }
}

:deep .form-control {
  font-size: $base-font-size !important;
  font-weight: normal !important;
  font-family: $base-font-family !important;
  @media (max-width: $md-display) {
    font-size: $responsive-base-font-size !important;
  }
}

.affiliate-share {
  padding: 0 5px 10px;
  @media (max-width: $md-display) {
    padding: 0 0 20px;
  }
}

.input-group-text {
  font-size: 14px;
  height: 100%;
  background-color: white;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

@media (max-width: 768px) {
  .col-md-3 {
    flex: 0 0 auto;
    width: 50%;
    margin-bottom: 20px;
  }

  .row .affiliate-individual-card:nth-child(odd) {
    .affiliate-card {
      margin-right: 10px;
    }
  }

  .row .affiliate-individual-card:nth-child(even) {
    .affiliate-card {
      margin-left: 10px;
    }
  }
}

.nav-pills.nav-pills-label .nav-item .nav-link {
  &:hover,
  &.active,
  &.active:hover {
    border-radius: 0;
    border-bottom: 2px solid #7766f7;
  }
}
</style>

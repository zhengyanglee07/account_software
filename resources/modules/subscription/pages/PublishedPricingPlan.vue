<template>
  <div class="container text-center">
    <SubscriptionSignup
      is-publish
      :subscription-plan="subscriptionPlan"
    />
  </div>
</template>

<script>
import PublishLayout from '@builder/layout/PublishLayout.vue';
import SubscriptionSignup from '@subscription/components/SubscriptionSignup.vue';

export default {
  name: 'SubscriptionTable',

  components: {
    SubscriptionSignup,
  },

  layout: PublishLayout,

  data() {
    return {
      subscriptionPlan: [
        {
          plan: 'Free',
          subscription_plan_price: [
            {
              id: 1,
              subscription_plan_id: 1,
              subscription_plan_type: 'monthly',
              price: `0.00`,
            },
            {
              id: 2,
              subscription_plan_id: 1,
              subscription_plan_type: 'yearly',
              price: `0.00`,
            },
          ],
        },
        {
          plan: 'Square',
          subscription_plan_price: [
            {
              id: 3,
              subscription_plan_id: 2,
              subscription_plan_type: 'monthly',
              price: `29.00`,
            },
            {
              id: 4,
              subscription_plan_id: 2,
              subscription_plan_type: 'yearly',
              price: `290.00`,
            },
          ],
        },
        {
          plan: 'Triangle',
          subscription_plan_price: [
            {
              id: 5,
              subscription_plan_id: 3,
              subscription_plan_type: 'monthly',
              price: `49.00`,
            },
            {
              id: 6,
              subscription_plan_id: 3,
              subscription_plan_type: 'yearly',
              price: `490.00`,
            },
          ],
        },
        {
          plan: 'Circle',
          subscription_plan_price: [
            {
              id: 7,
              subscription_plan_id: 4,
              subscription_plan_type: 'monthly',
              price: `99.00`,
            },
            {
              id: 8,
              subscription_plan_id: 4,
              subscription_plan_type: 'yearly',
              price: `990.00`,
            },
          ],
        },
      ],
    };
  },
};
</script>

<style lang="scss" scoped>
.desktop-version {
  display: inline-block;
}
.mobile-mode {
  display: none;
}

.plan-table {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 1.25rem;
  border-radius: 0.475rem;
  box-shadow: 0 0.5rem 1.5rem 0.5rem rgb(0 0 0 / 8%);
}

table {
  margin-top: 2.5rem;
}

thead tr:nth-child(2) {
  display: none;
}

thead th:not(:first-child) {
  padding-left: 0;
  padding-right: 0;
}

tbody {
  overflow: auto;
  background-color: #fff;
}

th {
  vertical-align: top;
  padding: 50px 0 15px 0;
}

th:first-child,
td:first-child {
  vertical-align: middle;
  text-align: start;
  padding-left: 1rem;
}

th,
td {
  border-left: 2px solid #fff;
  border-right: 2px solid #fff;
}

th:not(:first-child):not(:first-child) {
  text-align: center;
  text-transform: uppercase;
}

table .sticky-top {
  top: 70px;
}
.plan-interval-nav {
  height: 70px;
}

@media screen and (max-width: 415px) {
  .desktop-version {
    display: none;
  }
  .mobile-mode {
    display: block;
  }
  .plan-card {
    padding-right: 2.5rem;
    padding-left: 2.5rem;
  }

  thead {
    position: initial;
  }

  thead tr:nth-child(1) {
    display: none;
  }
  thead tr:nth-child(2) {
    display: table-row;
  }

  th,
  td {
    border: none;
    padding: 0.75rem 0.25rem;
  }

  th:not(:first-child):not(:first-child) {
    text-transform: none;
  }

  .plan-table {
    padding: 0;
    box-shadow: none;
  }

  .ribbon {
    display: none;
  }
}

.ribbon {
  position: absolute;
  top: 0;
  width: 100%;
  font-size: 11px;
  color: white;
  background-color: #009ef7;
  padding: 5px 21px;
}

.popular-badge-wrap {
  position: absolute;
  width: 100%;
  height: 188px;
  top: -9px;
  left: 11px;
  overflow: hidden;

  &::before,
  &::after {
    content: '';
    position: absolute;
  }

  &::before {
    width: 40px;
    height: 8px;
    right: 75px;
    background: #400675;
    border-radius: 8px 8px 0px 0px;
  }

  &::after {
    width: 8px;
    height: 40px;
    right: 0px;
    top: 75px;
    background: #400675;
    border-radius: 0px 8px 8px 0px;
  }
}

.popular-badge {
  width: 170px;
  height: 30px;
  line-height: 30px;
  position: absolute;
  top: 30px;
  right: -50px;
  z-index: 2;
  overflow: hidden;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
  border: 1px dashed white;
  box-shadow: 0 0 0 3px, $h-primary 0px 21px 5px -18px rgba(0, 0, 0, 0.6);
  background: $h-primary;
  text-align: center;
  color: white;
  font-size: 9px !important;
  padding-right: 15px;
}
</style>

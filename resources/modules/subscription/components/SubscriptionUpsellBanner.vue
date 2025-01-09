<template>
  <div
    v-if="isShow && showBanner"
    class="alert alert-light alert-dismissible fade show mb-0 py-3"
  >
    <div class="container">
      <div class="row">
        <div class="col-lg-6 col-md-7 col-sm-7">
          <div class="panel-label h-100 d-flex align-items-center">
            <p v-if="props.subscriptionPage">
              {{ discountRate }}% discount on yearly plan applied. Subscribe
              Now!
            </p>
            <p v-else>
              Limited Time Offer: {{ discountRate }}% off for yearly
              subscription!
            </p>
          </div>
        </div>
        <div class="col col-lg-2 col-md-3 col-sm-3 timer">
          <div class="countdown-timer">
            <template
              v-for="(interval, index) in [
                'days',
                'hours',
                'minutes',
                'seconds',
              ]"
              :key="index"
            >
              <div :class="`countdown-timer-${interval} me-3`">
                <div :class="`countdown-timer-${interval}-number`">
                  <p class="countdown-timer-number">
                    {{ remainingTime[interval] }}
                  </p>
                </div>
                <div :class="`countdown-timer-${interval}-label`">
                  <p class="countdown-timer-label">
                    {{ interval }}
                  </p>
                </div>
              </div>
            </template>
          </div>
        </div>
        <div
          v-if="!isSubscriptionPage"
          class="col col-lg-2 col-md-2-half col-sm-2-half text-center"
        >
          <BaseButton
            class="subscription-upgrade-button"
            @click="redirectToSubscription"
          >
            Upgrade Now
          </BaseButton>
        </div>
      </div>
    </div>
    <button
      type="button"
      class="btn-close"
      data-bs-dismiss="alert"
      data-v-79ac3d06=""
      @click="closeBanner"
    />
  </div>
</template>

<script setup>
import subscriptionAPI from '@subscription/api/subscriptionAPI.js';
import { computed, onMounted, ref } from 'vue';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';
import eventBus from '@services/eventBus.js';
import { router } from '@inertiajs/vue3';
import Cookies from 'js-cookie';

dayjs.extend(duration);

const props = defineProps({
  subscriptionPage: { type: Boolean, default: false },
});

const isShow = ref(false);
const showBanner = ref(true);
const isSubscriptionPage = ref(false);
const discountRate = ref(0);
const diffInSeconds = ref(0);
// const discount

const remainingTime = computed(() => {
  const discountDuration = dayjs.duration(diffInSeconds.value);
  return {
    days: discountDuration.days(),
    hours: discountDuration.hours(),
    minutes: discountDuration.minutes(),
    seconds: discountDuration.seconds(),
  };
});

const redirectToSubscription = () => {
  router.visit('/subscription/plan/upgrade');
};

subscriptionAPI.getYearlySubscriptionCoupon().then(({ data }) => {
  eventBus.$emit('subscription-coupon', data.coupon);
  isShow.value = data.coupon?.valid;
  discountRate.value = data.discountRate;
  const discountStart = dayjs(data.discountStartedAt);
  const discountEnd = dayjs(discountStart).add(7, 'day');

  setInterval(() => {
    diffInSeconds.value = discountEnd.diff(dayjs());
  }, 1000);
});

const closeBanner = () => {
  Cookies.set('showBanner', false, { expires: 1 });
};

onMounted(() => {
  isSubscriptionPage.value =
    window.location.pathname.includes('/subscription/plan');
});

const isShowBanner = Cookies.get('showBanner');
showBanner.value = isShowBanner !== 'false';
</script>

<style>
@media screen and (max-width: 716px) {
  .subscription-upgrade-button {
    padding: calc(0.55rem + 1px) calc(1.25rem + 1px) !important;
    margin-bottom: 1rem;
  }
}
</style>
<style scoped lang="scss">
p {
  margin-bottom: 0px;
}
.row {
  justify-content: space-evenly;
}
.alert {
  border-bottom: 1px solid rgb(219, 216, 216);
  align-items: center;
  width: 100%;
  top: 0px;
  background-color: #e8e8f7;
  z-index: 999;
  border-radius: 0;
}

.alert-dismissible {
  padding-left: 3rem;
  border: 0;
}

.panel-label {
  font-size: 18px;
  font-weight: 500;
  text-align: center;
}

.countdown-timer {
  display: flex;
  cursor: pointer;
  justify-content: center;
}

.countdown-timer-number {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 0;
  text-align: center;
  color: rgb(79, 99, 248);
}

.countdown-timer-label {
  font-size: 11px;
  max-width: 46px;
  color: rgba(103, 97, 124, 0.67);
  text-transform: capitalize;
}

.countdown-timer-minutes {
  justify-content: center;
}

.btn {
  background-color: rgb(79, 99, 248);
  color: white;
  font-weight: 700;
  font-size: 13px;
  padding: 10px 30px;
}

/* Responsive styles */
@media screen and (max-width: 1399px) {
  .panel-label {
    font-size: 19px;
  }

  .countdown-timer-number {
    font-size: 17px;
  }

  .countdown-timer-label {
    font-size: 9px;
  }

  .btn {
    font-size: 12px;
    padding: 8px 30px;
  }
}

@media screen and (max-width: 1233px) {
  .panel-label {
    font-size: 16px;
  }

  .countdown-timer-number {
    font-size: 15px;
  }

  .countdown-timer-label {
    font-size: 9px;
  }

  .btn {
    font-size: 11px;
    padding: 7px 25px;
  }
}

@media screen and (max-width: 1199px) {
  .panel-label {
    font-size: 15px;
    text-align: left;
  }
}

@media screen and (max-width: 1017px) {
  .panel-label {
    font-size: 14px;
  }

  .countdown-timer-number {
    font-size: 14px;
  }

  .countdown-timer-label {
    font-size: 9px;
  }
}

@media screen and (max-width: 991px) {
  .panel-label {
    font-size: 14px;
  }

  .countdown-timer-number {
    font-size: 14px;
  }

  .countdown-timer-label {
    font-size: 9px;
  }

  .btn {
    font-size: 9px;
  }
}

@media screen and (max-width: 805px) {
  .panel-label {
    font-size: 13px;
  }

  .countdown-timer-number {
    font-size: 13px;
  }

  .countdown-timer-label {
    font-size: 8px;
  }

  .btn {
    font-size: 8.3px;
    padding: 7px 20px;
  }
}

@media screen and (max-width: 767px) {
  .panel-label {
    font-size: 10.5px;
    text-align: left;
  }

  .countdown-timer-number {
    font-size: 10px;
  }

  .countdown-timer-label {
    font-size: 7px;
  }

  .btn {
    font-size: 8px;
    padding: 7px 15px;
  }
}

@media screen and (max-width: 633px) {
  .panel-label {
    font-size: 10px;
  }

  .countdown-timer-number {
    font-size: 10px;
  }

  .countdown-timer-label {
    font-size: 6px;
  }

  .btn {
    font-size: 7.5px;
    padding: 7px 15px;
  }
}

@media screen and (max-width: 611px) {
  .panel-label {
    font-size: 9.5px;
  }

  .countdown-timer-number {
    font-size: 9.5px;
  }

  .countdown-timer-label {
    font-size: 6px;
  }
  .btn {
    font-size: 7px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 589px) {
  .alert {
    padding-top: 0.5rem;
  }

  .panel-label {
    font-size: 9px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 6.5px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 575px) {
  .panel-label {
    font-size: 18px;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 17px;
  }

  .countdown-timer-label {
    font-size: 10px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 14px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 525px) {
  .panel-label {
    font-size: 16.5px;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 16px;
  }

  .countdown-timer-label {
    font-size: 10px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 13px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 497px) {
  .panel-label {
    font-size: 15px;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 15px;
  }

  .countdown-timer-label {
    font-size: 9px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 13px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 480px) {
  .panel-label {
    font-size: 14px;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 15px;
  }

  .countdown-timer-label {
    font-size: 9px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 13px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 463px) {
  .panel-label {
    font-size: 13px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 14px;
  }

  .countdown-timer-label {
    font-size: 9px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 12px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 432px) {
  .panel-label {
    font-size: 12.5px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 13.5px;
  }

  .countdown-timer-label {
    font-size: 8px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 11.5px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 410px) {
  .panel-label {
    font-size: 12px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 13px;
  }

  .countdown-timer-label {
    font-size: 8px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 11px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 391px) {
  .panel-label {
    font-size: 11px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 12.5px;
  }

  .countdown-timer-label {
    font-size: 8px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 11px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 367px) {
  .panel-label {
    font-size: 10px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 11px;
  }

  .countdown-timer-label {
    font-size: 8px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 10px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 342px) {
  .panel-label {
    font-size: 9px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 10px;
  }

  .countdown-timer-label {
    font-size: 7px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 9px;
    padding: 7px 14px;
  }
}

@media screen and (max-width: 318px) {
  .panel-label {
    font-size: 8.5px;
    text-align: left;
    margin-bottom: 0.5rem;
  }

  .alert-dismissible {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .countdown-timer {
    justify-content: center;
  }

  .countdown-timer-number {
    font-size: 9.5px;
  }

  .countdown-timer-label {
    font-size: 7px;
    margin-bottom: 0.5rem;
  }

  .btn {
    font-size: 8px;
    padding: 7px 10px;
  }
}

/* End of responsive styles */
</style>

<template>
  <div style="white-space: break-spaces">
    <div
      id="card-element"
      class="MyCardElement"
    >
      <!-- Elements will create input elements here -->
    </div>
    <!-- We'll put the error messages in this element -->
    <div
      id="card-errors"
      role="alert"
    />
    <!-- <button type="submit" class="mt-2">
              <div class="spinner hidden" id="spinner"></div>
              <span id="button-text">Subscribe</span>
          </button> -->
  </div>
</template>

<script setup>
import { onMounted, reactive, inject } from 'vue';
import subscriptionAPI from '@subscription/api/subscriptionAPI.js';
import eventBus from '@services/eventBus.js';

const $toast = inject('$toast');

const props = defineProps({
  type: { type: String, default: '' },
  cardHolder: { type: String, default: '' },
  selectedPlan: { type: Object, default: () => {} },
  promoCode: { type: String, default: '' },
  promoCodeType: { type: String, default: '' },
  production: { type: String, default: 'production' },
});

const state = reactive({
  csrf_token: document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content'),
  stripe: '',
  subscriptionDetail: '',
  paymentIntent: '',
  paymentMethod: '',
  lastestInvoice: {},
  isEligibleForNewUserPromo: false,
});

const hideSubscriptionModal = () => {
  eventBus.$emit('hide-modal-plan-summary');
};

const loading = (isLoading) => {
  eventBus.$emit('stripe-payment-processing', isLoading);
  // TODO ask tommy to take a look (jianloong)
  // if (isLoading) {
  //   // Disable the button and show a spinner
  //   document.querySelector('#submit-button').disabled = true;
  //   document.querySelector('#spinner').classList.remove('hidden');
  //   document.querySelector('#button-text').classList.add('hidden');
  // } else {
  //   document.querySelector('#submit-button').disabled = false;
  //   document.querySelector('#spinner').classList.add('hidden');
  //   document.querySelector('#button-text').classList.remove('hidden');
  // }
};

const showError = (result) => {
  if (result.error) {
    let declineCode = '';
    loading(false);
    // The card had an error when trying to attach it to a customer.
    if (result.error.decline_code) {
      declineCode =
        result.error.decline_code.includes('_') === true
          ? result.error.decline_code.replace(/_/g, ' ')
          : result.error.decline_code;
    } else {
      declineCode =
        result.error.code.includes('_') === true
          ? result.error.code.replace(/_/g, ' ')
          : result.error.code;
    }
    $toast.error('Error', result.error.message);
    loading(false);
    throw result;
  }
  return result;
};

const changePaymentMethods = (cardElement) => {
  loading(true);
  state.stripe
    .createPaymentMethod({
      type: 'card',
      card: cardElement,
    })
    .catch((error) => {
      loading(false);
      $toast.error('Error', 'Subscription not successful');
    })
    .then((result) => {
      showError(result);
      subscriptionAPI
        .changePaymentMethod(result.paymentMethod.id)
        .then((response) => {
          if (response.data.error) {
            showError(response);
            return;
          }
          subscriptionAPI.saveCardDetail(response.data).then(() => {
            loading(false);
            $toast.success('Success', 'Successful Change Payment Methods');
            hideSubscriptionModal();
            window.location.href = '/billing/setting';
          });
        });
    });
};

const showCardError = (event) => {
  console.log(event, 'event');
  let declineCode = '';
  const displayError = document.getElementById('card-errors');
  loading(false);
  if (event.error) {
    if (event.error.decline_code) {
      declineCode =
        event.error.decline_code.includes('_') === true
          ? event.error.decline_code.replace(/_/g, ' ')
          : event.error.decline_code;
    } else {
      declineCode =
        event.error.code.includes('_') === true
          ? event.error.code.replace(/_/g, ' ')
          : event.error.code;
    }
    displayError.textContent = declineCode;
    $toast.error('Error', event.error.message);
  } else {
    displayError.textContent = '';
  }
};

const deleteRetryInvoice = () => {
  subscriptionAPI.deleteRetryInvoice();
};

const handlePaymentThatRequiresCustomerAction = ({
  subscription,
  payment,
  priceId,
  paymentMethodId,
  isRetry,
  planId,
  invoice,
}) => {
  if (subscription !== undefined) {
    if (
      (subscription && subscription.status === 'active') ||
      subscription.status === 'trialing'
    ) {
      // Subscription is active, no customer actions required.
      return { subscription, priceId, paymentMethodId, planId };
    }
  }
  // If it's a first payment attempt, the payment intent is on the subscription latest invoice.
  // If it's a retry, the payment intent will be on the invoice itself.
  const paymentIntent =
    subscription === undefined
      ? payment
      : subscription.latest_invoice.payment_intent;
  if (
    paymentIntent.status === 'requires_action' ||
    (isRetry === true && paymentIntent.status === 'requires_payment_method')
  ) {
    return state.stripe
      .confirmCardPayment(paymentIntent.client_secret, {
        payment_method: paymentMethodId,
      })
      .then((result) => {
        if (result.error) {
          // Start code flow to handle updating the payment details.
          // Display error message in your UI.
          // The card was declined (i.e. insufficient funds, card has expired, etc).
          showCardError(result);
          throw result;
        } else if (result.paymentIntent.status === 'succeeded') {
          deleteRetryInvoice();
          subscriptionAPI
            .getSubscription(invoice)
            .then((response) => {
              subscriptionAPI
                .saveSubscriptionDetail({
                  subscriptionDetail: response.data.subscription,
                  selectedPlan: props.selectedPlan,
                })
                .then(() => {
                  subscriptionAPI
                    .saveCardDetail(state.paymentMethod, props.cardHolder)
                    .then((res) => {
                      $toast.success('Success', 'Subscription successful');
                      loading(false);
                      hideSubscriptionModal();
                      window.location.href = '/dashboard';
                    });
                })
                .catch((error) => {});
            })
            .catch((error) => {});
          // Show a success message to your customer.
          // There's a risk of the customer closing the window before the callback.
          // We recommend setting up webhook endpoints later in this guide.
          return {
            subscription,
            paymentMethodId,
            priceId,
            planId,
          };
        }
        return null;
      })
      .catch((error) => {
        showCardError(error);
      });
  }
  // No customer action needed.
  return { subscription, priceId, paymentMethodId, planId };
};

const handleRequiresPaymentMethod = ({
  subscription,
  priceId,
  paymentMethodId,
  planId,
}) => {
  if (subscription.status === 'active' || subscription.status === 'trialing') {
    // subscription is active, no customer actions required.
    return { subscription, priceId, paymentMethodId, planId };
  }
  if (
    subscription.latest_invoice.payment_intent.status ===
    'requires_payment_method'
  ) {
    // Using localStorage to manage the state of the retry here,
    // feel free to replace with what you prefer.
    // Store the latest invoice ID and status.
    subscriptionAPI
      .saveRetryInvoice({
        invoiceId: subscription.latest_invoice.id,
        status: subscription.latest_invoice.payment_intent.status,
      })
      .then(() => {
        loading(false);
        $toast.error('Error', 'Your card was declined.');
      });
  }
  return { subscription, priceId, paymentMethodId, planId };
};

const onSubscriptionComplete = ({
  subscription,
  priceId,
  paymentMethodId,
  planId,
}) => {
  // Payment was successful.
  if (subscription.status === 'active' || subscription.status === 'trialing') {
    subscriptionAPI
      .saveSubscriptionDetail({
        subscriptionDetail: subscription,
        selectedPlan: props.selectedPlan,
        status: 'active',
      })
      .then((response) => {
        subscriptionAPI
          .saveCardDetail(state.paymentMethod, props.cardHolder)
          .then((res) => {
            $toast.success('Success', 'Subscription successful');
            loading(false);
            document.getElementById('plan-summary-close-button')?.click();
            hideSubscriptionModal();
            window.location.href = '/';
          });
      })
      .catch((error) => {});
    // Change your UI to show a success message to your customer.
    // Call your backend to grant access to your service based on
    // `result.subscription.items.data[0].price.product` the customer subscribed to.
  }
};

const retryInvoiceWithNewPaymentMethod = ({
  csrf_token: csrfToken,
  paymentMethodId,
  invoiceId,
  priceId,
}) => {
  loading(true);
  fetch('/retryInvoice', {
    method: 'POST',
    headers: {
      'Content-type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
      paymentMethodId,
      invoiceId,
    }),
  })
    .then((response) => {
      return response.json();
    })
    // If the card is declined, display an error to the user.
    .then((result) => {
      if (result.error) {
        console.dir(result.error);
        // The card had an error when trying to attach it to a customer.
        throw result;
      }
      return result;
    })
    .then((result) => {
      return {
        // Use the Stripe 'object' property on the
        // returned result to understand what object is returned.
        payment: result.paymentIntent,
        paymentMethodId,
        priceId,
        isRetry: true,
        invoice: result.invoice,
      };
    })
    // Some payment methods require a customer to be on session
    // to complete the payment process. Check the status of the
    // payment intent to handle these actions.
    .then(handlePaymentThatRequiresCustomerAction)

    .then(onSubscriptionComplete)
    .catch((error) => {
      showCardError(error);
    });
};

const createSubscription = ({
  csrf_token: csrfToken,
  paymentMethodId,
  priceId,
  selectedPlan,
}) => {
  loading(true);
  fetch('/createSubscription', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
      paymentMethodId,
      priceId,
      promoCode: props.promoCode,
      promoCodeType: props.promoCodeType,
      selectedPlan,
      newUserPromo: state.isEligibleForNewUserPromo,
    }),
  })
    .then((response) => {
      return response.json();
    })
    .then((result) => {
      if (result.error) {
        throw result;
      }
      return result;
    })
    .then((result) => {
      return {
        paymentMethodId,
        priceId,
        subscription: result.subscriptionDetail,
        planId: result.planId,
      };
    })
    // Some payment methods require a customer to be on session
    // to complete the payment process. Check the status of the
    // payment intent to handle these actions.
    .then(handlePaymentThatRequiresCustomerAction)
    // If attaching this card to a Customer object succeeds,
    // but attempts to charge the customer fail, you
    // get a requires_payment_method error.
    .then(handleRequiresPaymentMethod)
    // No more actions required. Provision your service for the user.
    .then(onSubscriptionComplete)
    .catch((error) => {
      // An error has happened. Display the failure to the user here.
      // We utilize the HTML element we created.
      showCardError(error);
    });
};

const createPaymentMethod = ({
  cardElement,
  isPaymentRetry = false,
  invoiceId,
}) => {
  loading(true);
  // Set up payment method for recurring usage
  state.stripe
    .createPaymentMethod({
      type: 'card',
      card: cardElement,
    })
    .then((result) => {
      if (result.error) {
        showCardError(result);
      } else {
        state.paymentMethod = result.paymentMethod;
        if (isPaymentRetry) {
          // Update the payment method and retry invoice payment
          retryInvoiceWithNewPaymentMethod({
            csrf_token: state.csrf_token,
            paymentMethodId: result.paymentMethod.id,
            invoiceId,
            priceId: props.selectedPlan.price_id,
          });
        } else {
          // Create the subscription
          createSubscription({
            csrf_token: state.csrf_token,
            paymentMethodId: result.paymentMethod.id,
            priceId: props.selectedPlan.price_id,
            selectedPlan: props.selectedPlan,
          });
        }
      }
    });
};

const initialSubscription = () => {
  if (props.production === 'local') {
    state.stripe = Stripe(
      // 'pk_test_51Hh9jlFeI4XEtTz43EgiK80JCpbh7BJqh487MRgx0dABW7iNl8SHr05t0r3s3QLNbS25X41ZMZPqJK35JFNUjoDy00hW3VpCCa'
      'pk_test_51IoNpTFXu7UEcy2x62wRLyRWcHyhShRNofl0ushuRAgFHOSc1rR3pJWYOKPo7CpVzdsCtwMhKlbuZGwIO4iKSf0G00zEawcSl3'
    );
  } else if (props.production === 'staging') {
    state.stripe = Stripe('pk_test_Xn0BfLQ7cDD5GlIJl9BOZAVy00VPFHAyEF');
  } else if (props.production === 'production') {
    state.stripe = Stripe(
      'pk_live_51IoNpTFXu7UEcy2xCQjDhprm1rRTnGMniLgHpKi3YYf5iddovzcJuw1g9IvpfDMjT2juLaspq6z3feH222Gt0Y6Q00ouo7LeXm'
    );
  }
  const elements = state.stripe.elements();
  const style = {
    base: {
      color: '#202930',
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '1rem',
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a',
    },
  };
  const cardElement = elements.create('card', {
    style,
    hidePostalCode: true,
  });
  cardElement.mount('#card-element');
  cardElement.on('change', (event) => {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
  });
  const form = document.getElementById('subscription-form');
  form.addEventListener('submit', (ev) => {
    ev.preventDefault();
    localStorage.removeItem('latestInvoiceId');
    localStorage.removeItem('latestInvoicePaymentIntentStatus');
    if (props.promoCode === '' || props.promoCodeType !== 'fail') {
      if (props.cardHolder !== '') {
        subscriptionAPI.getRetryInvoice().then(({ data }) => {
          if (Object.keys(data).length !== 0) {
            if (
              data.latest_invoice_payment_intent_status ===
              'requires_payment_method'
            ) {
              createPaymentMethod({
                cardElement,
                isPaymentRetry: true,
                invoiceId: data.latest_invoice_id,
              });
            }
          }
          createPaymentMethod({ cardElement });
        });
        // If a previous payment was attempted, get the latest invoice
      }
    } else {
      alert('Please verify the promo code before subscribe');
    }
    if (props.type === 'update') {
      changePaymentMethods(cardElement);
    }
  });
};

const loadScript = (url) => {
  const script = document.createElement('script');
  script.src = url;
  document.body.appendChild(script);

  return new Promise((res, rej) => {
    script.onload = () => {
      res();
    };
    script.onerror = () => {
      rej();
    };
  });
};

onMounted(async () => {
  await loadScript('https://js.stripe.com/v3/').then(() => {
    initialSubscription();
  });

  try {
    const res = await subscriptionAPI.getPromoEligibility(props.type);
    state.isEligibleForNewUserPromo = res.data.isEligible;
  } catch (err) {
    /**/
  }
});
</script>

<style scoped>
* {
  box-sizing: border-box;
}
body {
  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  display: flex;
  justify-content: center;
  align-content: center;
  height: 100vh;
  width: 100vw;
}
/* form { */
/* width: 30vw;
min-width: 500px;
align-self: center;
/* box-shadow: 0px 0px 0px 0.5px rgba(50, 50, 93, 0.1),
    0px 2px 5px 0px rgba(50, 50, 93, 0.1), 0px 1px 1.5px 0px rgba(0, 0, 0, 0.07);
border-radius: 7px; */
/* padding: 40px; */
/* } */
input {
  border-radius: 6px;
  margin-bottom: 6px;
  padding: 12px;
  border: 1px solid rgba(50, 50, 93, 0.1);
  height: 44px;
  font-size: 16px;
  width: 100%;
  background: white;
}
.result-message {
  line-height: 22px;
  font-size: 16px;
}
.result-message a {
  color: rgb(89, 111, 214);
  font-weight: 600;
  text-decoration: none;
}
.hidden {
  display: none;
}

#card-errors {
  color: red;
  text-align: left;
  font-size: 13px;
  line-height: 17px;
  margin-top: -12px;
  margin-bottom: 1rem;
}

#card-element {
  border-radius: 0.475rem;
  padding: 12px;
  height: calc(1.5em + 1.3rem + 2px);
  /* height: 44px; */
  width: 100%;
  border: 1px solid #e4e6ef;
  background-color: #fff;
  margin: 1rem auto;
}

/* @media(max-width: 768px){
  #card-element{
    margin: 0 !important;
  }
} */

#payment-request-button {
  margin-bottom: 32px;
}
/* Buttons and links */
button {
  background-color: #1a73e8;
  color: #ffffff;
  font-family: Arial, sans-serif;
  border-radius: 0.25rem;
  border: 1px solid transparent;
  padding: 0.65rem 1rem;
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  display: block;
  transition: all 0.2s ease;
  box-shadow: 0px 4px 5.5px 0px rgba(0, 0, 0, 0.07);
  margin-top: 3rem !important;
  /* width: 100%; */
}
button:hover {
  filter: contrast(115%);
  /* background: #1a65c8; */
}
button:disabled {
  opacity: 0.5;
  cursor: default;
}
/* spinner/processing state, errors */
.spinner,
.spinner:before,
.spinner:after {
  border-radius: 50%;
}
.spinner {
  color: #ffffff;
  font-size: 22px;
  text-indent: -99999px;
  margin: 0px auto;
  position: relative;
  width: 20px;
  height: 20px;
  box-shadow: inset 0 0 0 2px;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
.spinner:before,
.spinner:after {
  position: absolute;
  content: '';
}
.spinner:before {
  width: 10.4px;
  height: 20.4px;
  background: #1a73e8;
  border-radius: 20.4px 0 0 20.4px;
  top: -0.2px;
  left: -0.2px;
  -webkit-transform-origin: 10.4px 10.2px;
  transform-origin: 10.4px 10.2px;
  -webkit-animation: loading 2s infinite ease 1.5s;
  animation: loading 2s infinite ease 1.5s;
}
.spinner:after {
  width: 10.4px;
  height: 10.2px;
  background: #1a73e8;
  border-radius: 0 10.2px 10.2px 0;
  top: -0.1px;
  left: 10.2px;
  -webkit-transform-origin: 0px 10.2px;
  transform-origin: 0px 10.2px;
  -webkit-animation: loading 2s infinite ease;
  animation: loading 2s infinite ease;
}
@-webkit-keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes loading {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@media only screen and (max-width: 600px) {
  form {
    width: 80vw;
  }
}
</style>

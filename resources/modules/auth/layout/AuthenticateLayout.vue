<template>
  <div
    class="row w-100 mx-0 bg-white"
    style="height: 100vh"
  >
    <div class="d-flex flex-column flex-root px-0">
      <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div>
          <div
            class="d-flex flex-column flex-lg-row-auto w-xl-600px positon-xl-relative h-100"
          >
            <div
              class="d-flex flex-column position-xl-fixed top-0 bottom-0 w-xl-500px h-100"
              style="background: #f0f9ff"
            >
              <div class="d-flex flex-column text-center pt-10">
                <a
                  href="https://hypershapes.com"
                  class="pb-9 mb-5"
                  target="__blank"
                >
                  <img
                    alt="Logo"
                    src="@shared/assets/media/hypershapes-logo.png"
                    class="onboarding-logo h-60px"
                  >
                </a>
                <h1 class="fs-1 pb-2 mt-md-15 fw-bold mx-auto">
                  Quicky Multiply Your Sales
                </h1>
                <h1 class="fs-1 fw-bold mx-auto d-flex">
                  With
                  <span class="fw-boldest d-inline-block ms-2 flex-row">
                    <div id="text" />
                    <div id="cursor" />
                  </span>
                </h1>
              </div>
              <div class="min-h-100px min-h-lg-350px text-center mt-5 mb-10">
                <img
                  src="@auth/assets/media/auth-hero-image.png"
                  style="width: 70%; margin: auto"
                >
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-column flex-lg-row-fluid">
          <div
            class="d-flex flex-center flex-column flex-column-fluid p-10 pb-20 mx-md-auto w-md-550px"
          >
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const highlightedFeatures = [
  'Affiliate Marketing',
  'Referral Marketing',
  // 'Online Store',
  // 'Sales Funnels',
  // 'Landing Pages',
  // 'Promotions',
  // 'CRM',
  // 'Email Marketing',
  // 'Automation',
  // 'Cashback',
  // 'Social Proof',
];

let interval;
let featureIndex = 0;
let characterIndex = 0;

onMounted(() => {
  const highlighedTextElement = document.querySelector('#text');
  const cursorElement = document.querySelector('#cursor');

  const typeWords = () => {
    const text = highlightedFeatures[featureIndex].substring(
      0,
      characterIndex + 1
    );
    highlighedTextElement.innerHTML = text;
    characterIndex += 1;

    if (text === highlightedFeatures[featureIndex]) {
      clearInterval(interval);

      setTimeout(() => {
        // eslint-disable-next-line no-use-before-define
        interval = setInterval(deleteWords, 50);
      }, 1000);
    }
  };

  const deleteWords = () => {
    const text = highlightedFeatures[featureIndex].substring(
      0,
      characterIndex - 1
    );
    highlighedTextElement.innerHTML = text;
    characterIndex -= 1;

    if (text === '') {
      clearInterval(interval);

      if (featureIndex === highlightedFeatures.length - 1) {
        featureIndex = 0;
      } else {
        featureIndex += 1;
      }

      characterIndex = 0;

      setTimeout(() => {
        cursorElement.style.display = 'inline-block';
        interval = setInterval(typeWords, 100);
      }, 200);
    }
  };

  interval = setInterval(typeWords, 100);
});
</script>

<style lang="scss" scoped>
.hero-image {
  width: 250px;
  margin-left: -20px;
  margin-top: -10px;

  @media (max-width: $sm-display) {
    width: 200px;
    margin-left: 0;
  }
}

#text {
  display: inline-block;
  color: #009df6;
  letter-spacing: 1px;
}

#cursor {
  display: inline-block;
  vertical-align: middle;
  width: 3px;
  height: 28px;
  background-color: #009df6;
  animation: blink 0.75s step-end infinite;
}

@keyframes blink {
  from,
  to {
    background-color: transparent;
  }
  50% {
    background-color: #009df6;
  }
}

@media (max-width: 992px) {
  .row {
    height: 100% !important;
  }
}
</style>

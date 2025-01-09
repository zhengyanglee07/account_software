<template>
  <div></div>
</template>
<script>
// import MiniStorePage from '@miniStore/pages/MiniStorePage.vue';
import OnboardingLayout from '@shared/layout/OnboardingLayout.vue';

export default {
  layout: OnboardingLayout,
  props: {
    miniStore: { type: Object, default: () => {} },
    account: { type: Object, default: () => {} },
    onboarding: { type: Boolean, default: false },
  },
  data() {
    return {
      emit: 0,
    };
  },
};
</script>

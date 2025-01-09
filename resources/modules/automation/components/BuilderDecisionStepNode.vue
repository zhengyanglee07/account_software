<template>
  <div class="d-flex justify-content-around decision_container">
    <div
      class="d-flex flex-column align-items-center"
      style="min-width: 320px"
    >
      <p>yes</p>
      <AddStepButton
        class="mt-5"
        :index="0"
        :parent="parent"
        :config="getDecisionRouteConfig('yes')"
      />
      <StepNode
        v-if="yesRoutes.length !== 0"
        :steps="yesRoutes"
        :parent="parent"
        :config="getDecisionRouteConfig('yes')"
      />
    </div>

    <div class="decision-nodes-container">
      <p>no</p>
      <AddStepButton
        class="mt-5"
        :index="0"
        :parent="parent"
        :config="getDecisionRouteConfig('no')"
      />
      <StepNode
        v-if="noRoutes.length !== 0"
        :steps="noRoutes"
        :parent="parent"
        :config="getDecisionRouteConfig('no')"
      />
    </div>
  </div>
</template>

<script>
import AddStepButton from '@automation/components/BuilderAddStepButton.vue';
import eventBus from '@services/eventBus.js';

export default {
  name: 'DecisionStepNode',
  components: {
    AddStepButton,
  },
  props: {
    yesRoutes: Array,
    noRoutes: Array,
    lastBtnIdx: Number,
    parent: {
      type: Object,
      default: () => ({}),
    },
  },
  mounted() {
    const decisionStepNode = document.querySelector('#decisionStepNode');
    if (!decisionStepNode) return;
    const observer = new MutationObserver(() => {
      eventBus.$emit('connect-line');
    });
    observer.observe(decisionStepNode, { subtree: true, childList: true });
  },
  methods: {
    getDecisionRouteConfig(yesNo) {
      return {
        route: yesNo,
      };
    },
  },
};
</script>

<style scoped>
.decision_container {
  min-width: 250px;
}

.decision-nodes-container {
  min-width: 350px !important;
  align-items: center !important;
  flex-direction: column !important;
  display: flex !important;
  padding: 0;
  margin: 0;
}
</style>

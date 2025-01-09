<template>
  <div class="d-flex flex-column align-items-center">
    <TriggerNode v-if="automationTriggers?.length === 0" />
    <div class="d-flex align-items-center">
      <TriggerNode
        v-for="(automationTrigger, idx) in automationTriggers"
        :key="automationTrigger.id"
        :automation-trigger="automationTrigger"
        :index="idx"
      />
    </div>
    <AddStepButton :index="0" />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import TriggerNode from '@automation/components/BuilderTriggerNode.vue';
import AddStepButton from '@automation/components/BuilderAddStepButton.vue';

export default {
  name: 'TriggerNodes',
  components: {
    TriggerNode,
    AddStepButton,
  },
  computed: {
    ...mapState('automations', ['automationTriggers']),
  },
};
</script>

<style scoped></style>

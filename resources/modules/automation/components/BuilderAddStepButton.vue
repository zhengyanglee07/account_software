<template>
  <button
    :id="generateAddStepBtnId(index, parent, config)"
    class="add_step_button"
    @click="showAddStepModal"
  >
    <i class="fas fa-plus p-0" />
  </button>
</template>

<script>
import { mapMutations } from 'vuex';
import { Modal } from 'bootstrap';
import { generateAddStepBtnId } from '@automation/lib/automations';

export default {
  name: 'AddStepButton',
  props: {
    index: {
      type: Number,
      default: 0,
    },
    parent: {
      type: Object,
      default: () => ({}),
    },
    config: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      addStepBtnElem: null,
      currentY: 0,
    };
  },
  methods: {
    ...mapMutations('automations', ['updateModal']),

    generateAddStepBtnId,

    showAddStepModal() {
      this.updateModal({
        modal: {
          type: 'step',
          data: {
            index: this.index, // placeholder
            parent: this.parent,
            config: this.config,
          },
        },
      });

      new Modal(document.getElementById('automation-add-step-modal'), {
        backdrop: 'static',
      }).show();
    },
  },
};
</script>

<style lang="scss" scoped>
.add_step_button {
  width: 20px;
  height: 20px;
  padding: 0;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  background-color: #6c757d;
  border: none;

  i {
    font-size: 10px;
  }

  .fa-plus {
    color: white;
  }
}
</style>

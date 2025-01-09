<template>
  <div
    v-if="!automationTrigger"
    id="empty-trigger-node"
    class="empty_trigger spacing"
    @click="showTriggerModal()"
  >
    <div class="empty_trigger_wrapper d-flex align-items-center">
      <p class="add_trigger_text">
        <img
          class="trigger_plus_icon"
          src="@automation/assets/media/plus.svg"
          alt="plus"
        >
        Add an entry point
      </p>
    </div>
  </div>
  <div
    v-else
    :id="`trigger-node-${automationTrigger?.id}`"
    class="trigger_node_div"
    @click="showTriggerModal(automationTrigger?.id)"
  >
    <button class="node_button trigger_border">
      <span class="btn_left_border" />
      <img
        class="container_image"
        :src="getTriggerNodeImageSrc(automationTrigger.trigger.type)"
        alt="img"
      >

      <span class="divider" />

      <span class="text_wrapper">
        <span class="font-weight-bold">
          {{ automationTrigger.trigger.name }}
        </span>
        <span>{{ automationTrigger.triggerKind.description }}</span>
      </span>

      <span class="action_button">
        <span
          class="me-5"
          @click.stop="deleteAutomationTrigger({ id: automationTrigger.id })"
        >
          <span>
            <i
              class="fas fa-trash-alt action_icon"
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              title="Delete"
            />
          </span>
        </span>
      </span>
    </button>
  </div>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
import { getTriggerNodeImageSrc } from '@automation/lib/automations.js';
import { Modal } from 'bootstrap';

export default {
  name: 'TriggerNode',
  props: {
    automationTrigger: {
      type: Object,
      required: false,
      default: null,
    },
    index: Number,
  },
  methods: {
    ...mapActions('automations', ['deleteAutomationTrigger']),
    ...mapMutations('automations', ['updateModal']),
    getTriggerNodeImageSrc,

    showModal() {
      new Modal(document.getElementById('automation-trigger-modal'), {
        backdrop: 'static',
      }).show();
    },

    showTriggerModal(id = null) {
      this.updateModal({
        modal: {
          type: 'trigger',
          data: {
            id,
            index: this.index,
          },
        },
      });

      this.showModal();
    },
  },
};
</script>

<style scoped lang="scss">
.spacing {
  margin: 3rem;
}

.trigger_border {
  .btn_left_border {
    background: $h-secondary-buttercup;
  }

  &:hover {
    border-color: lighten(#ced4da, 10%);
  }
}

.trigger_node_div {
  margin: 1.2rem;
  min-height: 70px;

  &:hover {
    .action_button {
      display: inline-block;
    }
  }
}

.node_button {
  position: relative;
  display: inline-flex;
  align-items: center;
  max-height: 68px;
  padding: 10px 10px;
  margin: 0;
  line-height: 20px;
  border: 1px solid #dbd9d2;
  border-left-color: transparent;
  background: #fff;
  cursor: pointer;
  // width: 100%;
  width: 340px;
  user-select: none;

  .btn_left_border {
    position: absolute;
    left: 0;
    width: 4px;
    height: 100%;
  }

  /* for image inside wrapper */
  .container_image {
    width: 68px;
    height: 68px;
  }

  .divider {
    margin: 0 12px;
    height: 48px;
    border-left: 1px solid rgba(36, 28, 21, 0.15);
  }

  .text_wrapper {
    display: flex;
    flex-direction: column;
    font-size: 14px;

    span {
      text-align: left;

      &:first-child {
        font-weight: 700;
      }

      &:last-child {
        color: rgba(36, 28, 21, 0.65);
      }
    }
  }

  .action_button {
    position: absolute;
    right: -6rem;
    display: none;
    padding: 2.5rem;

    &:hover {
      display: 'inline-block';
    }
  }

  &:hover {
    border-width: 1px;
    border-style: solid;
    border-left-color: transparent;
    box-shadow: 0 4px 12px rgba(36, 28, 21, 0.12);
  }
}

.empty_trigger_wrapper {
  position: relative;
  height: 48px;
  width: 200px;
  border-width: 1px;
  border-style: dashed;
  border-color: grey;
  border-radius: 5px;
  box-sizing: border-box;
  margin: 0 auto;
  background-color: #fafafa;

  .add_trigger_text {
    width: 100%;
    font-size: 14px;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;

    .trigger_plus_icon {
      width: 16px;
      height: 16px;
      margin-right: 0.5rem;
    }
  }

  &:hover {
    cursor: pointer;
  }
}

.action_icon {
  font-size: 18px;
  padding-left: 0.5rem;
}

.fa-trash-alt:hover {
  color: #dc3545;
}
</style>

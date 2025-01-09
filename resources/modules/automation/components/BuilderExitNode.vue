<template>
  <div class="exit_node_div">
    <button class="node_button delay-border">
      <span class="btn_left_border" />

      <img
        class="container_image"
        alt="img"
        src="@automation/assets/media/exit-automation.png"
      >

      <span
        class="divider"
        style="position: absolute; left: 50px; height: 30px"
      />

      <span class="text_wrapper">
        <span class="font-weight-bold">Exit</span>
      </span>

      <span class="action_button">
        <span @click.stop="deleteAutomationStep({ id: step.id })">
          <span class="me-5">
            <i
              class="fas fa-trash-alt action-icon"
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
import { mapMutations } from 'vuex';
import { Modal } from 'bootstrap';

export default {
  name: 'ExitNode',
};
</script>

<style lang="scss" scoped>
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

  width: 150px;
  height: 40px;

  img {
    position: relative;
    left: -9px;
    height: 48px;
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
}

.dividor {
  left: 32px !important;
  position: absolute !important;
  height: 30px;
}

.btn_left_border {
  background: rgb(119, 102, 247);
}

.exit_node_div {
  // @include node_margin;
  margin: 3rem;
  margin-top: 1.2rem;
  // margin: 1.2rem;

  &:hover {
    .action_button {
      display: inline-block;
    }
  }
}
.action-icon {
  font-size: 18px;
  padding-left: 0.5rem;
}

.fa-trash-alt:hover {
  color: #dc3545;
}
</style>

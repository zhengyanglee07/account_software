<template>
  <div class="email-setup-section">
    <div class="email-setup-section-main">
      <div class="section-title">
        <span class="d-block">
          <i
            class="far fa-check-square icon-style"
            :class="{
              'completed-icon-color': complete,
            }"
          />
        </span>

        <div class="p-two mt-1">
          {{ title }}
          <div class="content-style p-two mt-3 icon-color">
            {{ subtitle }}
          </div>
        </div>
      </div>

      <slot name="custom-main-btn">
        <button
          v-show="!showCollapse"
          class="primary-white-button"
          style="margin-left: 1.3rem"
          @click="toggleCollapse"
        >
          {{ mainSectionBtnContent }}
        </button>
      </slot>
    </div>

    <div
      v-show="showCollapse"
      class="w-100 email-setup-section-form"
    >
      <slot name="collapse" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseEmailSetupSection',
  props: {
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      required: true,
    },
    mainSectionBtnContent: String,
    showCollapse: Boolean,
    complete: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    toggleCollapse() {
      this.$emit('toggle-collapse');
    },
  },
};
</script>

<style scoped lang="scss">
.completed-icon-color {
  color: #7766f7 !important;
}

.icon-color {
  color: $h-secondary-color;
}

// .secondary-button {
//   font-family: $base-font-family;
//   font-size: 14px;
//   font-weight: 500;
//   color: #1a73e8;
//   width: 7.5rem;
//   padding: 5px 0;
//   border-radius: 10rem;
//   border: 1px solid #1a73e8;
//   background-color: #fff;
//   text-transform: uppercase;
//   margin-left: 1.3rem;

//   &:focus {
//     outline: none;
//   }

//   &:hover {
//     color: #1a65c8;
//     border-color: #1a65c8;
//   }
// }

.email-setup-section {
  display: flex;
  flex-direction: column;
  padding: 16px;

  @media screen and (max-width: $md-display) {
    align-items: center;
  }

  &:not(:last-child) {
    border-bottom: 1px solid #ced4da;
  }

  .section-title {
    font-weight: 600;
    line-height: 20px;
    display: flex;

    .icon-style {
      font-size: 20px;
      vertical-align: top;
      display: inline-block;
      margin: 4px 8px 0px 0px;
      color: rgb(220, 220, 220);
    }

    .content-style {
      //color: #4a4a4a;
      font-weight: lighter;
      //margin-top: 10px;
    }
  }

  .email-setup-section-main {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;

    @media (max-width: $md-display) {
      flex-direction: column;
      width: 100%;
      justify-content: left;
      align-items: flex-start;
    }

    button,
    a {
      width: 175px;
    }
  }

  .email-setup-section-form {
    margin-top: 12px;
    padding-left: 24px;

    @media (max-width: $md-display) {
      width: 90% !important;
      justify-content: left;
      align-items: flex-start;
    }

    @media (max-width: $sm-display) {
      & > form > div {
        flex-direction: column;
        width: 100%;
      }
      div {
        align-items: flex-start !important;
      }
      select {
        margin: 0 !important;
        font-size: 14px;
      }
      .button-group {
        .primary-small-square-button,
        .cancel-button {
          width: 100%;
          padding-left: 0;
          padding-right: 0;
        }
      }
    }

    input {
      width: 350px;
      @media (max-width: $sm-display) {
        width: 100% !important;
      }
    }
  }
}
</style>

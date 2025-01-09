<template>
  <div>
    <div class="flex-center">
      <div class="container col-md-12">
        <br>
        <div
          class="sub-title-container"
          :class="{ 'schedule-container': isSchedule }"
        >
          <div class="sub-title-container__box px-0 pt-0">
            <h4
              class="sub-title-container__title h-two"
              style="font-size: 19.5px !important; text-align: left"
            >
              {{ title }}
            </h4>
            <p
              class="sub-title-container__sub-title p-two p-0"
              style="
                font-size: 13.975px;
                color: #a1a5b7;
                text-align: left;
                font-weight: 500;
              "
            >
              <slot name="description" />
            </p>
            <slot name="toggle-button" />
          </div>
        </div>

        <div class="container m-auto">
          <div
            id="content"
            class="form-container__row"
            style="display: initial"
          >
            <slot name="content" />
          </div>

          <!-- Required for shipping methods setup -->
          <slot name="footer-content" />

          <div
            class="form-container__footer"
            style="margin-bottom: 15px"
          >
            <div
              v-if="backUrl"
              class="left-footer"
            >
              <button
                class="skipThisStepLink"
                @click="back"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-arrow-left-short"
                  viewBox="0 0 16 16"
                >
                  <path
                    fill-rule="evenodd"
                    d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z"
                  />
                </svg>
                <span>Back</span>
              </button>
            </div>
            <div class="right-footer">
              <button
                v-if="skipUrl"
                class="skipThisStepLink"
                @click="skip"
              >
                <span style="margin-right: 20px">Skip</span>
              </button>
              <slot name="submit-button" />
              <!-- <button class="primary-square-button" style="margin-left: 15px">
                Submit
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="#fff"
                  class="bi bi-arrow-right-short"
                  viewBox="0 0 16 16"
                  style="vertical-align: text-top"
                >
                  <path
                    fill-rule="evenodd"
                    d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"
                  />
                </svg>
              </button> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormLayout',

  props: {
    title: String,
    isSchedule: {
      type: Boolean,
      default: false,
    },
    backUrl: {
      type: String,
      default: null,
    },
    skipUrl: {
      type: String,
      default: null,
    },
  },

  methods: {
    back() {
      this.$inertia.visit(this.backUrl, { replace: true });
    },
    skip() {
      this.$inertia.visit(this.skipUrl, { replace: true });
    },
  },
};
</script>

<style lang="scss" scoped>
// New Design
* {
  font-family: $base-font-family;
  font-size: 14px;
  color: $base-font-color;
  margin: 0;
  padding: 0;
}

label {
  padding: 0 0 8px 0 !important;
  margin: 8px 0 0 0;
}

.main-title {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 100%;

  &__title {
    height: 50px;
    // padding-left: 50px;
  }
}

.onboarding__box-container {
  background-color: #ffffff;
  box-shadow: 1px 2px 7px 2px rgb(147 149 152 / 40%);
  max-width: 700px;
  margin: 20px auto 0;
  border-radius: 5px;

  @media (max-width: 767px) {
    width: 100% !important;
  }
}

.sub-title-container {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: left;
  // margin-bottom: 36px;
  // padding-bottom: 10px;

  &__box {
    // background-color: $base-font-color;
    padding: 24px 8px;
    width: 100%;
    max-width: 700px;
    text-align: center;
    // max-height: 116px;

    // @media(max-width: $sm-display){
    //   width:75%
    // }
  }

  &__title {
    color: $base-font-color;
    font-size: 18px;
    padding-bottom: 10px;
    text-transform: capitalize;
  }

  &__sub-title {
    color: $base-font-color;
    padding: 0 0.5rem;
  }
}

.form-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding-bottom: 20px;
  padding-left: 25px;
  padding-right: 25px;

  &__row {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    // max-width: 700px;
    // padding: 0 20px;
  }

  &__section {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    flex-wrap: wrap;
    width: 50%;
    padding: 0 10px;
  }

  &__section-title {
    font-size: 14px;
    font-weight: 700;
    padding-bottom: 6px !important;
    margin: 0;
  }

  &__section-input {
    padding: 0 3%;
    height: 40px;
    width: 100%;
    border-radius: 2.5px;
    border: 1px solid;
    font-family: 'Roboto', sans-serif;
    font-weight: 400;
  }

  &__section-input--select {
    padding: 0 5px;
    height: 40px;
    width: 100%;
    border-radius: 2.5px;
    border: 1px solid;
    appearance: none;
    font-size: 14px !important;
    font-family: 'Roboto', sans-serif;
    @media (max-width: $sm-display) {
      font-size: 12px !important;
    }
  }

  &__section-input--select-nb {
    padding: 0 12px;
    height: 40px;
    width: 100%;
  }

  &__footer {
    width: 100%;
    padding: 0 30px;
    margin-top: 15px;
  }
}

@media (max-width: $sm-display) {
  .form-container {
    width: 100% !important;
    // margin: 0;
    margin-bottom: 50px;
    // padding-top: 20px;
    padding: 20px 25px;

    &__row {
      display: block;
      width: 100% !important;
    }

    &__section {
      width: 100%;
    }
  }

  .sub-title-container {
    margin-bottom: 0;
    margin: 0 30px;
    width: calc(100% - 60px);
  }

  .primary-square-button {
    width: 100%;
  }
}

@media (max-width: 767px) {
  .sub-title-container {
    margin: 0 !important;
    width: 100% !important;

    &__box {
      margin-bottom: -20px !important;
      padding: 24px 0 !important;
    }
  }
}

.error {
  padding: 0 0 8px;
  color: #e3342f;

  @media (max-width: $sm-display) {
    font-size: 12px !important;
  }
}

select {
  background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' fill ='rgba(60,60,60,.5)' width='14' height='10' role='presentation' class='vs__open-indicator'><path d='M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z'></path></svg>")
    no-repeat;
  appearance: none;
  padding-left: 7px !important;
  background-position: right 5px top 50%;
}

.right-footer {
  float: right;

  @media screen and (max-width: 576px) {
    display: inline-flex;
    align-items: baseline;
  }
}

.left-footer {
  float: left;
  padding: 6px 0px;

  @media screen and (max-width: 576px) {
    padding: 6px 0px;
  }
}

.skipThisStepLink {
  background: transparent;
  border: 0;
  text-align: center;
  font-size: 14px;
  color: #202930;
}

.skipThisStepLink span:hover {
  text-decoration: underline !important;
}

input:focus {
  outline: none !important;
  border: 1px solid #ced4da;
}

.schedule-container {
  border-bottom: none;
  padding-bottom: 0px;
  margin-bottom: 0px;
}

.form-container__footer {
  padding: 0 !important;
}
</style>

<style lang="scss" scoped></style>

<template>
  <BaseModal
    :modal-id="modalId"
    no-header
    no-footer
    no-padding
  >
    <slot name="body">
      <div style="height: 100%">
        <span
          type="button"
          class="close-button"
          data-bs-dismiss="modal"
          aria-label="Close"
          style="
            font-size: 20px;
            margin-left: auto;
            width: fit-content;
            margin-right: 10px;
            margin-top: -20px;
            right: 20px;
            position: absolute;
          "
        >
          &times;
        </span>

        <div class="row modal-style">
          <div
            v-show="image !== null"
            class="col-md-7"
          >
            <div class="container">
              <img
                class=""
                :src="image"
              >
            </div>
          </div>
          <div :class="image !== null ? 'col-md-5' : 'col-12'">
            <div class="customer-review-info">
              <div class="input-oneline">
                <div style="display: flex; flex-direction: row">
                  <div @click="ratingErr = false">
                    <input
                      id="1-star"
                      v-model="rating"
                      type="radio"
                      value="1"
                      class="d-none"
                      disabled
                    >
                    <label for="1-star"><i :class="oneStarClass" /></label>
                    <input
                      id="2-star"
                      v-model="rating"
                      type="radio"
                      value="2"
                      class="d-none"
                      disabled
                    >
                    <label for="2-star"><i :class="twoStarClass" /></label>
                    <input
                      id="3-star"
                      v-model="rating"
                      type="radio"
                      value="3"
                      class="d-none"
                      disabled
                    >
                    <label for="3-star"><i :class="threeStarClass" /></label>
                    <input
                      id="4-star"
                      v-model="rating"
                      type="radio"
                      value="4"
                      class="d-none"
                      disabled
                    >
                    <label for="4-star"><i :class="fourStarClass" /></label>
                    <input
                      id="5-star"
                      v-model="rating"
                      type="radio"
                      value="5"
                      class="d-none"
                      disabled
                    >
                    <label for="5-star"><i :class="fiveStarClass" /></label>
                  </div>
                </div>

                <div class="customer-name">
                  {{ name }}
                </div>
                <div
                  v-if="review"
                  class="customer-review"
                >
                  <p
                    v-for="(reviewText, i) in review?.split('\n')"
                    :key="i"
                  >
                    {{ reviewText }}
                  </p>
                </div>

                <div class="customer-date">
                  {{ date }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </slot>
  </BaseModal>
</template>
<script>
export default {
  name: 'ProductReviewDetailModal',

  props: {
    productReview: Object,
    modalId: String,
  },
  data() {
    return {
      rating: this.productReview.star_rating,
      review: this.productReview.review,
      name: this.productReview.name,
      image: this.productReview.image_collection,
      date: this.productReview.createdAt,
    };
  },

  computed: {
    oneStarClass() {
      return parseInt(this.rating, 10) === 0
        ? 'far fa-star fa-2x'
        : 'fa fa-star fa-2x checked';
    },
    twoStarClass() {
      return parseInt(this.rating, 10) < 2
        ? 'far fa-star fa-2x'
        : 'fa fa-star fa-2x checked';
    },
    threeStarClass() {
      return parseInt(this.rating, 10) < 3
        ? 'far fa-star fa-2x'
        : 'fa fa-star fa-2x checked';
    },
    fourStarClass() {
      return parseInt(this.rating, 10) < 4
        ? 'far fa-star fa-2x'
        : 'fa fa-star fa-2x checked';
    },
    fiveStarClass() {
      return parseInt(this.rating, 10) < 5
        ? 'far fa-star fa-2x'
        : 'fa fa-star fa-2x checked';
    },
  },

  watch: {
    productReview(newValue) {
      this.rating = newValue.star_rating;
      this.review = newValue.review;
      this.name = newValue.name;
      this.image = newValue.image_collection;
      this.date = newValue.createdAt;
    },
  },

  methods: {},
};
</script>
<style lang="scss" scoped>
img {
  height: auto;
  max-width: 300px;
  width: 100%;
}

.checked,
.far {
  color: #ff9900;
}

.fa-2x {
  font-size: 14px;
}

.container {
  padding: 0px;
  height: 100%;
  max-height: 780px;
  text-align: center;
  border-right: none;

  @media (min-width: $md-display) {
    border-right: 1px solid #ced4da;
    padding: 0 20px;
  }
}

.customer-name {
  font-size: 14px !important;
  font-weight: 700 !important;
  font-family: 'Inter', sans-serif !important;
  padding-top: 10px;
}

.customer-review {
  font-size: 14px !important;
  font-weight: 400 !important;
  font-family: 'Inter', sans-serif !important;
  white-space: normal;
  word-break: break-word;
  padding-top: 15px;
}

.customer-date {
  color: #6c757d;
  text-align: right;
  font-size: 12px !important;
  font-weight: 700 !important;
  padding-top: 20px;
}

.col-12 .customer-date {
  @media (max-width: $md-display) {
    padding-bottom: 20px;
  }
}

.customer-review-info {
  padding: 0px;

  @media (min-width: $md-display) {
    padding: 0 0 0 20px;
  }
}

.input-oneline {
  padding-top: 20px;

  @media (min-width: $md-display) {
    padding-top: 0px;
  }
}

.col-12 .customer-review-info .input-oneline {
  padding-top: 0px;
  padding-bottom: 0px !important;
}

.modal-style {
  padding: 0px 20px !important;
  height: 100%;

  @media (min-width: $md-display) {
    padding: 0px 20px 20px 0px !important;
  }
}
</style>

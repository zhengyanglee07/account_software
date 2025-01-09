<template>
  <BaseModal
    :title="modalTitle"
    modal-id="add-review-modal"
    static-backdrop
    :no-dismiss="reviewSuccess === false && customerInfo === null"
  >
    <slot name="body">
      <template v-if="reviewSuccess === false && customerInfo === null">
        <form @submit.prevent="accountLogin()">
          <BaseFormGroup
            class="mx-6"
            label="Email Address"
          >
            <BaseFormInput
              v-model="login.email"
              name="email"
              type="email"
              required="required"
              maxlength="255"
              placeholder="Email Address"
            />
          </BaseFormGroup>
          <BaseFormGroup
            class="mx-6"
            label="Password"
          >
            <BaseFormInput
              v-model="login.password"
              name="password"
              type="password"
              required="required"
              minlength="8"
              placeholder="Password"
            />
          </BaseFormGroup>
          <BaseFormGroup
            class="mx-6"
            style="text-align: center"
          >
            <BaseButton
              id="submitButton"
              is-submit
            >
              Login
            </BaseButton>
          </BaseFormGroup>
        </form>
      </template>

      <template v-if="reviewSuccess === false && customerInfo !== null">
        <BaseFormGroup
          class="mx-6"
          label="Your Rating"
          :error-message="ratingErr ? 'This field is required.*' : ''"
        >
          <div
            style="display: inline-table"
            @click="ratingErr = false"
          >
            <input
              id="1-star"
              v-model="rating"
              type="radio"
              value="1"
              class="d-none"
            >
            <label for="1-star"><i :class="oneStarClass" /></label>
            <input
              id="2-star"
              v-model="rating"
              type="radio"
              value="2"
              class="d-none"
            >
            <label for="2-star"><i :class="twoStarClass" /></label>
            <input
              id="3-star"
              v-model="rating"
              type="radio"
              value="3"
              class="d-none"
            >
            <label for="3-star"><i :class="threeStarClass" /></label>
            <input
              id="4-star"
              v-model="rating"
              type="radio"
              value="4"
              class="d-none"
            >
            <label for="4-star"><i :class="fourStarClass" /></label>
            <input
              id="5-star"
              v-model="rating"
              type="radio"
              value="5"
              class="d-none"
            >
            <label for="5-star"><i :class="fiveStarClass" /></label>
          </div>
        </BaseFormGroup>

        <BaseFormGroup
          class="mx-6"
          label="Your Name"
          :error-message="
            nameErr
              ? 'This field is required. Name with symbol is not acceptable*'
              : ''
          "
        >
          <BaseFormInput
            id="name"
            v-model="name"
            type="text"
            name="name"
            @keypress="nameErr = false"
          />
        </BaseFormGroup>

        <!-- <div class="input-oneline" hidden>
					<label for="visitorEmail" class="p-two font-style m-container__text">Email</label>
					<input
					type="text"
					class="form-control name m-container__input"
					id="visitorEmail"
					name='visitorEmail'
					v-model="visitorEmail"
					/>
				</div> -->

        <BaseFormGroup
          class="mx-6"
          label="Your Review"
        >
          <BaseFormTextarea
            v-model="review"
            rows="4"
            cols="60"
            placeholder="What do you think of this product? Not more than 2000 characters"
            class="form-control"
            maxlength="2000"
            @keypress="reviewErr = false"
          />
          <span
            v-show="reviewErr === true"
            class="text-danger"
          >This field is required.*</span>
          <span
            v-show="reviewLengthErr === true"
            class="text-danger"
          >The review must not more than 2000 word!*</span>
        </BaseFormGroup>

        <BaseFormGroup
          class="mx-6"
          label="Add A Photo"
        >
          <input
            id="imageFiles"
            ref="file"
            accept="image/png, image/jpeg, image/jpg"
            type="file"
            class="form-control"
            @change="onFileChanged"
          >
          <span
            v-show="imageErr === true"
            class="text-danger"
          >This field is required!*</span>
          <div
            v-show="images.length === 0"
            class="image-area mt-4"
          >
            <p
              class="text-center mb-0"
              style="color: #808285"
            >
              Image Uploaded Preview
            </p>
          </div>
          <div
            v-show="images.length !== 0"
            id="fileList"
            class="image-area mt-4"
          />
        </BaseFormGroup>
      </template>

      <template v-if="reviewSuccess === true">
        <h1
          class="text-center"
          style="font-size: 25px"
        >
          Your review has been submitted!
        </h1>
        <p
          v-show="promotionCode !== ''"
          class="text-center"
          style="font-size: 20px; padding-top: 20px"
        >
          Use the following discount code for your next purchase:
        </p>
        <div
          class="text-center"
          style="color: red"
        >
          {{ promotionCode }}
        </div>
      </template>
    </slot>

    <template #footer>
      <div
        v-show="customerInfo === null"
        class="input-section"
        style="
          padding-left: 0;
          padding-right: 0;
          margin: auto;
          white-space: nowrap;
        "
      >
        <span
          class="d-flex flex-center w-100 switch-form customer-account-p-three"
        >Need a customer account?&nbsp;&nbsp;
          <a
            href="/signup"
            class="h_link customer-account-p-three"
            target="_blank"
          >Create an account</a>
        </span>
      </div>

      <BaseButton
        v-if="customerInfo !== null"
        :style="{ display: reviewSuccess === true ? 'none' : 'inline' }"
        :disabled="submitting"
        @click="addReview"
      >
        Submit Review
        <span v-show="submitting">
          &nbsp; <i class="fas fa-spinner fa-pulse" />
        </span>
      </BaseButton>
    </template>
  </BaseModal>
</template>
<script>
/* eslint prefer-destructuring: "off" */

export default {
  props: {
    product: Object,
    customer: Object,
    settings: Object,
    customerName: String,
    purchased: Boolean,
  },

  data() {
    return {
      checkPurchased: false,
      customerInfo: {},
      rating: '0',
      name: '',
      review: '',
      images: [],
      //   files: [],
      isUploading: false,
      login: {
        email: '',
        password: '',
      },
      status: 'pending',
      ratingErr: false,
      nameErr: false,
      reviewErr: false,
      reviewLengthErr: false,
      imageErr: false,
      reviewSuccess: false,
      promotionCode: '',
      submitting: false,
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
    modalTitle() {
      let title = '';
      if (this.customerInfo !== null && !this.reviewSuccess)
        title = 'Write your review';
      if (this.customerInfo !== null && this.reviewSuccess)
        title = 'Thank you !';
      if (this.customerInfo === null && !this.reviewSuccess)
        title = 'Login to write your review';
      return title;
    },
  },
  watch: {
    customerName(newValue) {
      this.name = newValue;
    },
    customer(newValue) {
      this.customerInfo = newValue;
    },
    purchased(newValue) {
      this.checkPurchased = newValue;
    },
  },
  mounted() {
    this.name = this.customerName;
    this.customerInfo = this.customer;
    this.checkPurchased = this.purchased;

    setTimeout(() => {
      const modal = document.getElementById('add-review-modal');

      modal.addEventListener('hidden.bs.modal', () => {
        this.closeAddModal();
      });
    }, 1000);
  },

  methods: {
    closeAddModal() {
      this.rating = '0';
      this.review = '';
      this.images = [];
      //   this.files = [];
      this.status = 'pending';
      this.ratingErr = false;
      this.nameErr = false;
      this.reviewErr = false;
      this.reviewLengthErr = false;
      this.imageErr = false;
      this.reviewSuccess = false;
      this.promotionCode = '';
      this.isUploading = false;
      document.getElementById('fileList').innerHTML = '';
      document.getElementById('imageFiles').value = '';
    },

    validate() {
      const regex = /^[A-Za-z0-9 ]+$/;

      const isValid = regex.test(this.name);
      if (!isValid) {
        this.nameErr = true;
      } else {
        this.nameErr = /^ *$/.test(this.name);
      }
      this.reviewErr = /^ *$/.test(this.review);
      this.reviewLengthErr = this.review.length > 2000;
      this.ratingErr = this.rating === '0';
      if (this.settings.image_option === 'required') {
        this.imageErr = this.images.length === 0;
      }
    },

    onFileChanged(event) {
      this.images = this.$refs.file.files[0];
      if (!event.target.files.length) {
        alert('Unexpected error happen!');
      } else {
        this.imageErr = false;
        document.getElementById('fileList').innerHTML = '';
        const div = document.createElement('div');
        div.classList.add('container');
        div.setAttribute(
          'style',
          'display: flex;flex-wrap: wrap;justify-content: center;align-items: center;'
        );
        document.getElementById('fileList').appendChild(div);
        for (let i = 0; i < event.target.files.length; i += 1) {
          const img = document.createElement('img');
          img.classList.add('img-fluid');
          img.classList.add('img-thumbnail');
          img.setAttribute('style', 'max-height: 200px;');
          img.src = URL.createObjectURL(event.target.files[i]);
          img.onload = function () {
            URL.revokeObjectURL(this.src);
          };
          div.appendChild(img);
        }
      }
    },

    accountLogin() {
      axios
        .post('/login', {
          email: this.login.email,
          password: this.login.password,
          isManuallyLogin: true,
        })
        .then(async ({ data }) => {
          await axios
            .get(`/get-customer-purchase-info/${this.product.id}/purchased`)
            .then((response) => {
              this.checkPurchased = response.data.purchased;
            });
          if (data.status === 'success') {
            this.customerInfo = data.user;
            this.name = data.user.processed_contact.fname;
            alert('Successful login');
          } else {
            alert('These credentials do not match our records');
          }
        });
    },

    async addReview() {
      if (!this.checkPurchased) {
        // alert('To able to review this item. You need to purshase it first.');
        this.$toast.warning(
          'Warning',
          'To able to review this item. You need to purshase it first.'
        );
        return;
      }
      this.validate();
      if (this.reviewLengthErr) {
        // alert('The review should not more than 2000 characters');
        this.$toast.warning(
          'Warning',
          'The review should not more than 2000 characters'
        );
        return;
      }
      if (this.nameErr || this.ratingErr || this.imageErr || this.reviewErr) {
        // alert('Check the required filed!');
        this.$toast.warning('Warning', 'Check the required field!');
        return;
      }

      const formData = new FormData();
      formData.append('file', this.images);
      if (this.settings.auto_approve_type === 'never') {
        this.status = 'pending';
      } else if (this.settings.auto_approve_type === 'all') {
        this.status = 'published';
      } else if (
        this.rating <
        parseInt(this.settings.auto_approve_type.split('-')[0], 10)
      ) {
        this.status = 'pending';
      } else {
        this.status = 'published';
      }
      this.submitting = true;
      await axios
        .post(`/review/image/store`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        .then(async (res) => {
          if (res.data.status === 'fail') {
            // alert('Upload Failed!, Make sure the uploaded file is an image!');
            this.$toast.error(
              'Upload Failed!',
              'Make sure the uploaded file is an image!'
            );
            this.submitting = false;
            return;
          }
          const { images } = res.data;
          await axios
            .post(
              `/send-product-review/product/${this.product.account_id}/product-review`,
              {
                ecommerceAccountId: this.customerInfo.id,
                productId: this.product.id,
                rating: this.rating,
                name: this.name,
                review: this.review,
                images,
                status: this.status,
              }
            )
            .then(({ data }) => {
              if (data.message === 'fail') {
                // alert(
                //   'To able to review this item. You need to purshase it first.'
                // );
                this.$toast.warning(
                  'Warning',
                  'To able to review this item. You need to purshase it first.'
                );
                this.submitting = false;
              }
              if (data.message === 'reviewed') {
                // alert('You already submitted your review!');
                this.$toast.error(
                  'Warning',
                  'You already submited your review!.'
                );
              }
              if (data.message === 'success') {
                if (data.promotion_code !== null)
                  this.promotionCode = data.promotion_code;
                this.reviewSuccess = true;
                this.submitting = false;
              }
            })
            .catch((err) => {
              this.submitting = false;
              console.error(err);
            });
        })
        .catch((err) => {
          this.submitting = false;
          console.error(err);
        });

      this.$emit('save');
    },
  },
};
</script>
<style lang="scss" scoped>
.checked,
.far {
  color: #ff9900;
}

.image-area {
  border: 2px dashed rgba(155, 151, 151, 0.7);
  padding: 1rem;
  position: relative;
  height: auto;
  max-width: auto;
}
.mx-6 {
  margin-right: 1.5rem !important;
  margin-left: 1.5rem !important;

  @media (max-width: 400px) {
    margin-right: 0.7rem !important;
    margin-left: 0.7rem !important;
  }
}
</style>

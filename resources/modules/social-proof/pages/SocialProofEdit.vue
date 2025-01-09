<template>
  <BasePageLayout
    :page-name="'Social Proof Notification - ' + editData.name"
    back-to="/marketing/social-proof"
  >
    <template #left>
      <BaseCard
        has-header
        title="General"
      >
        <BaseFormGroup
          label="Name"
          required
          :error-message="
            editData.name.trim() === '' ? `* Title cannot empty` : ''
          "
          description="Only for internal reference"
        >
          <BaseFormInput
            id="title"
            ref="title_input"
            v-model="editData.name"
            type="text"
            name="title"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Data Source"
      >
        <BaseFormGroup label="Events">
          <BaseFormSelect
            id="select-event"
            v-model="editData.event_type"
            label-key="name"
            value-key="value"
            :options="[
              { name: 'Form Submission', value: 'form' },
              { name: 'Product Purchase', value: 'product' },
            ]"
          />
        </BaseFormGroup>
        <BaseFormGroup
          :label="
            editData.event_type === 'product' ? 'Which Products' : 'Which Forms'
          "
        >
          <BaseFormRadio
            id="radio-1"
            v-model="editData.is_all_selected"
            value="1"
          >
            All
          </BaseFormRadio>
          <BaseFormRadio
            id="radio-2"
            v-model="editData.is_all_selected"
            value="0"
          >
            {{
              editData.event_type === 'product'
                ? 'Specific Products'
                : 'Specific Forms'
            }}
          </BaseFormRadio>
          <BaseMultiSelect
            v-show="
              editData.event_type === 'product' &&
                !Boolean(parseInt(editData.is_all_selected))
            "
            id="select-product"
            v-model="checkedProductList"
            multiple
            :options="productsName"
            placeholder="Select Products"
            label="name"
          />
          <BaseMultiSelect
            v-show="
              editData.event_type === 'form' &&
                !Boolean(parseInt(editData.is_all_selected))
            "
            id="select-product"
            v-model="checkedFormList"
            multiple
            :options="formsName"
            placeholder="Select Forms"
            label="name"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Display"
      >
        <BaseFormGroup label="Where to Display">
          <BaseFormSelect
            id="select-display"
            v-model="editData.display_type"
            label-key="name"
            value-key="value"
            :options="[
              {
                name: 'All pages in online store and mini store',
                value: 'online-store',
              },
              { name: 'Funnels', value: 'funnel' },
            ]"
          />
        </BaseFormGroup>
        <BaseFormGroup
          v-show="editData.display_type === 'funnel'"
          label="Which Funnels"
        >
          <BaseMultiSelect
            id="select-funnel"
            v-model="checkedFunnelList"
            multiple
            :options="funnelsName"
            placeholder="Select funnels"
            label="name"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Design"
      >
        <BaseFormGroup
          label="Layout"
          class="col-lg-6 col-md-12"
        >
          <BaseFormRadio
            id="select-layout-squared"
            v-model="editData.layout_type"
            class="align-items-center"
            value="squared"
          >
            <div
              id="squared"
              class="preview-container"
            >
              <div class="d-flex">
                <div class="preview__image">
                  <div class="preview__image-mask" />
                  <img
                    src="https://cdn.hypershapes.com/assets/product-default-image.png"
                  >
                </div>
                <div class="preview__details">
                  <b
                    class="preview__details-title"
                  >XXX from (City), (Country)...</b>
                  <p class="preview__details-action">
                    just submitted a form
                  </p>
                  <p class="preview__details-time">
                    5 mins ago
                  </p>
                  <span class="verified-text">
                    <i class="fas fa-check-circle" />
                    <span>Verified by Hypershapes</span>
                  </span>
                </div>
              </div>
            </div>
          </BaseFormRadio>
        </BaseFormGroup>
        <BaseFormGroup
          label="&nbsp"
          class="col-lg-6 col-md-12"
        >
          <BaseFormRadio
            id="select-layout-rounded"
            v-model="editData.layout_type"
            class="align-items-center"
            value="rounded"
          >
            <div
              id="rounded"
              class="preview-container notification--rounded"
            >
              <div class="d-flex">
                <div class="preview__image">
                  <div class="preview__image-mask" />
                  <img
                    src="https://cdn.hypershapes.com/assets/product-default-image.png"
                  >
                </div>
                <div class="preview__details">
                  <span
                    class="preview__details-title"
                  >Customer from (City), (Country)... </span><br>
                  <span class="preview__details-action"> just purchased </span>
                  <span class="preview__details-title">Product ABC</span>
                  <p class="preview__details-time">
                    5 mins ago
                  </p>
                  <span
                    class="verified-text"
                    style=""
                  >
                    <i class="fas fa-check-circle" />
                    <span>Verified by Hypershapes</span>
                  </span>
                </div>
              </div>
            </div>
          </BaseFormRadio>
        </BaseFormGroup>
        <BaseFormGroup class="col-lg-6 col-md-12">
          <BaseFormRadio
            id="select-layout-squared-padding"
            v-model="editData.layout_type"
            class="align-items-center"
            value="squared-padding"
          >
            <div
              id="squared-padding"
              class="preview-container notification--squared-padding"
            >
              <div class="d-flex">
                <div class="preview__image">
                  <div class="preview__image-mask" />
                  <img
                    src="https://cdn.hypershapes.com/assets/product-default-image.png"
                  >
                </div>
                <div class="preview__details">
                  <b
                    class="preview__details-title"
                  >XXX from (City), (Country)...</b>
                  <p class="preview__details-action">
                    just submitted a form
                  </p>
                  <p class="preview__details-time">
                    5 mins ago
                  </p>
                  <span class="verified-text">
                    <i class="fas fa-check-circle" />
                    <span>Verified by Hypershapes</span>
                  </span>
                </div>
              </div>
            </div>
          </BaseFormRadio>
        </BaseFormGroup>
        <BaseFormGroup class="col-lg-6 col-md-12">
          <BaseFormRadio
            id="select-layout-rounded-padding"
            v-model="editData.layout_type"
            class="align-items-center"
            value="rounded-padding"
          >
            <div
              id="rounded-padding"
              class="preview-container notification--rounded-padding"
            >
              <div class="d-flex">
                <div class="preview__image">
                  <div class="preview__image-mask" />
                  <img
                    src="https://cdn.hypershapes.com/assets/product-default-image.png"
                  >
                </div>
                <div class="preview__details">
                  <b
                    class="preview__details-title"
                  >XXX from (City), (Country)...</b>
                  <p class="preview__details-action">
                    just submitted a form
                  </p>
                  <p class="preview__details-time">
                    5 mins ago
                  </p>
                  <span class="verified-text">
                    <i class="fas fa-check-circle" />
                    <span>Verified by Hypershapes</span>
                  </span>
                </div>
              </div>
            </div>
          </BaseFormRadio>
        </BaseFormGroup>
        <BaseFormGroup label="Message Text">
          <BaseFormTextarea
            id="message-text"
            v-model="editData.message"
            rows="4"
            placeholder="Type message here..."
          />
        </BaseFormGroup>
        <BaseFormGroup label="Image">
          <BaseImagePreview
            id="image"
            modal-id="image-modal"
            size="lg"
            :default-image="
              editData.image_path ||
                'https://cdn.hypershapes.com/assets/product-default-image.png'
            "
            @delete="deleteImage"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Desktop Position"
          :col="6"
        >
          <BaseFormSelect
            id="desktop-position"
            v-model="editData.desktop_position"
            label-key="name"
            value-key="value"
            :options="[
              { name: 'Top Left', value: 'top-left' },
              { name: 'Top Right', value: 'top-right' },
              { name: 'Bottom Left', value: 'bottom-left' },
              { name: 'Bottom Right', value: 'bottom-right' },
            ]"
          />
        </BaseFormGroup>
        <BaseFormGroup
          label="Mobile Position"
          :col="6"
        >
          <BaseFormSelect
            id="mobile-position"
            v-model="editData.mobile_position"
            label-key="name"
            value-key="value"
            :options="[
              { name: 'Top', value: 'top' },
              { name: 'Bottom', value: 'bottom' },
            ]"
          />
        </BaseFormGroup>
      </BaseCard>
      <BaseCard
        has-header
        title="Advanced"
      >
        <BaseFormGroup
          label="Replace the exact time passed with &quot;Recently&quot; if greater than"
        >
          <BaseFormInput
            id="time-value"
            v-model="timeValue"
            type="number"
            min="1"
            max="60"
            @input="
              timeValue = timeValue < 1 ? 1 : timeValue > 60 ? 60 : timeValue
            "
          >
            <template #append>
              <BaseFormSelect
                v-model="editData.time_type"
                :options="['Hours', 'Minutes']"
              />
            </template>
          </BaseFormInput>
        </BaseFormGroup>
        <BaseFormGroup
          description="Will always show “Someone” instead of contact’s name"
        >
          <BaseFormSwitch v-model="editData.is_anonymous">
            Show all the conversions as anonymous
          </BaseFormSwitch>
        </BaseFormGroup>
        <BaseFormGroup
          description="E.g. If the contact’s first name is David Teo, then show David only"
        >
          <BaseFormSwitch v-model="editData.show_first_name">
            Show first word in first name only
          </BaseFormSwitch>
        </BaseFormGroup>
      </BaseCard>
    </template>

    <template #right>
      <BaseCard
        has-header
        title="Preview"
      >
        <BaseFormGroup>
          <div :class="layoutClass">
            <div v-show="editData.event_type === 'product' || 'form'">
              <div class="d-flex">
                <div class="preview__image">
                  <div
                    v-show="editData.image_path == null"
                    class="preview__image-mask"
                  />
                  <img
                    :src="
                      editData.image_path ||
                        'https://cdn.hypershapes.com/assets/product-default-image.png'
                    "
                  >
                </div>
                <div class="preview__details">
                  <b
                    v-show="editData.event_type === 'form'"
                    class="preview__details-title"
                  >XXX from (City), (Country)...</b>
                  <p
                    :style="{
                      '-webkit-line-clamp': 2,
                    }"
                  >
                    <span
                      v-if="editData.event_type === 'product'"
                      id="name"
                      :class="fontClass"
                    >{{ name }}</span>
                    <br v-if="editData.event_type === 'product'">
                    <span
                      id="event"
                      class="preview__details-action"
                    >{{
                      event
                    }}</span>
                    <span
                      id="eventName"
                      :class="fontClass"
                    >{{
                      eventName
                    }}</span>
                    <span
                      v-if="
                        editData.event_type === 'form' &&
                          editData.message !== null
                      "
                      class="preview__details-action"
                    >{{ editData.message }}</span>
                  </p>
                  <p
                    v-show="
                      editData.message !== null &&
                        editData.event_type !== 'form'
                    "
                    :style="{
                      '-webkit-line-clamp': 1,
                    }"
                  >
                    <span class="preview__details-action">{{
                      editData.message
                    }}</span>
                  </p>
                  <p class="preview__details-time">
                    {{ timeMessage }}
                  </p>
                  <a
                    href="https://my.hypershapes.com"
                    target="_blank"
                    class="verified-text"
                  >
                    <i class="fas fa-check-circle" />&nbsp;
                    <span>Verified by Hypershapes</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </BaseFormGroup>
      </BaseCard>
    </template>

    <template #footer>
      <BaseButton
        type="link"
        class="me-5"
        href="/marketing/social-proof"
      >
        Cancel
      </BaseButton>
      <BaseButton @click="save">
        Save
      </BaseButton>
    </template>
  </BasePageLayout>
  <ImageUploader @update-value="chooseImage" />
</template>

<script>
import ImageUploader from '@shared/components/ImageUploader.vue';
import cloneDeep from 'lodash/cloneDeep';

export default {
  name: 'SocialProofEdit',

  components: {
    ImageUploader,
  },

  props: {
    notification: {
      type: Object,
      default: () => {},
    },
    types: {
      type: Array,
      default: () => [],
    },
    products: {
      type: Array,
      default: () => [],
    },
    forms: {
      type: Array,
      default: () => [],
    },
    funnels: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      selected: 1,
      selectedImageIndex: 0,
      formName: 'Data Source',
      editData: {},
      showErr: false,
      checkedFormList: [],
      checkedProductList: [],
      checkedFunnelList: [],
      timeValue: 5,
    };
  },

  computed: {
    name() {
      const name = this.editData.is_anonymous
        ? 'Someone  from (City), (Country)...'
        : 'XXX  from (City), (Country)...';
      // name =
      //   this.editData.message === null || this.editData.message === ''
      //     ? name
      //     : '';
      return name;
    },

    event() {
      let event =
        this.editData.event_type === 'product'
          ? ' just purchased '
          : ' submitted';
      event =
        this.editData.message === null || this.editData.message === ''
          ? event
          : '';
      return event;
    },

    eventName() {
      let eventName =
        this.editData.event_type === 'product' ? ' Product ABC' : ' a form';
      eventName =
        this.editData.message === null || this.editData.message === ''
          ? eventName
          : '';
      return eventName;
    },

    timeMessage() {
      // let timeMessage = this.editData.message === null||this.editData.message === '' ? '5 mins ago' : '';
      const timeMessage = '5 mins ago';
      return timeMessage;
    },

    layoutClass() {
      switch (this.editData.layout_type) {
        case 'squared':
          return 'preview-container';
        case 'squared-padding':
          return 'preview-container notification--squared-padding';
        case 'rounded':
          return 'preview-container notification--rounded';
        case 'rounded-padding':
          return 'preview-container notification--rounded-padding';
        default:
          return '';
      }
    },

    fontClass() {
      const fontClass =
        this.editData.event_type === 'product'
          ? 'preview__details-title'
          : 'preview__details-action';

      return fontClass;
    },

    productsName() {
      return this.products.map((product) => {
        return {
          id: product.id,
          name: product.productTitle,
        };
      });
    },

    formsName() {
      return this.forms.map((form) => {
        return {
          id: form.id,
          name: form.title,
        };
      });
    },

    funnelsName() {
      return this.funnels.map((funnel) => {
        return {
          id: funnel.id,
          name: funnel.funnel_name,
        };
      });
    },
  },

  mounted() {
    eventBus.$on('addNotificationImage', (image) => {
      this.$set(this.editData, 'image_path', image);
    });
    for (let i = 0; i < this.types?.length; i++) {
      if (this.types[i].target_type === 'product')
        this.checkedProductList.push({
          id: this.types[i].target_id,
          name: this.products.find(
            (product) => product.id === this.types[i].target_id
          )?.productTitle,
        });
      if (this.types[i].target_type === 'form')
        this.checkedFormList.push({
          id: this.types[i].target_id,
          name: this.forms.find((form) => form.id === this.types[i].target_id)
            ?.title,
        });
      if (this.types[i].target_type === 'funnel')
        this.checkedFunnelList.push({
          id: this.types[i].target_id,
          name: this.funnels.find(
            (funnel) => funnel.id === this.types[i].target_id
          )?.funnel_name,
        });
    }
  },

  created() {
    this.editData = cloneDeep(this.notification);
    this.editData.is_anonymous = Boolean(this.editData?.is_anonymous ?? false);
    this.editData.show_first_name = Boolean(
      this.editData?.show_first_name ?? false
    );
    if (
      this.editData.event_type === null &&
      this.editData.display_type === null &&
      this.editData.layout_type === null
    ) {
      this.editData.event_type = 'product';
      this.editData.is_all_selected = '1';
      this.editData.display_type = 'online-store';
      this.editData.layout_type = 'squared';
      this.editData.greater_than_time = 5;
    }

    this.timeValue = this.notification.greater_than_time;
    if (this.editData.time_type === 'Hours')
      this.timeValue = Math.floor(this.notification.greater_than_time / 60);
  },

  methods: {
    chooseImage(e) {
      this.editData.image_path = e;
    },

    deleteImage() {
      this.editData.image_path = null;
    },

    save() {
      if (this.editData.name.trim() === '') {
        this.$toast.warning('Warninig', 'Check the required field!');
        return;
      }
      const referenceKey = this.notification.reference_key;
      this.editData.greater_than_time =
        this.editData.time_type === 'Hours'
          ? this.timeValue * 60
          : this.timeValue;

      axios
        .post(`/notification/save-edit/${referenceKey}`, {
          name: this.editData.name,
          eventType: this.editData.event_type,
          isAllSelected: this.editData.is_all_selected,
          displayType: this.editData.display_type,
          layoutType: this.editData.layout_type,
          message:
            (this.editData?.message ?? '').trim() === ''
              ? null
              : this.editData.message,
          image: this.editData.image_path,
          mobilePosition: this.editData.mobile_position,
          desktopPosition: this.editData.desktop_position,
          greaterThanTime: this.editData.greater_than_time,
          timeType: this.editData.time_type,
          isAnonymous: this.editData.is_anonymous,
          showFirstName: this.editData.show_first_name,
          checkedFormList: [...this.checkedFormList],
          checkedProductList: [...this.checkedProductList],
          checkedFunnelList: [...this.checkedFunnelList],
        })
        .then((response) => {
          this.$toast.success(
            'Success',
            'Successfully Saved Social Proof Notification'
          );
          this.$inertia.visit('/marketing/social-proof', { replace: true });
          // const reference_key  = this.editData.reference_key;
        })
        .catch((error) => {
          this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },
  },
};
</script>

<style scoped lang="scss">
// ***************************
// New style for social proof notification ui redesign
p {
  margin-bottom: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
}

.primary-small-square-button {
  color: $h-primary-text;
}

.preview {
  &-container {
    border: 1px solid #ced4da;
    width: 275px;
    height: 60px;
    padding: 0;
    overflow: hidden;
    margin: 10px auto;
    border-radius: 5px;
    position: relative;
    min-height: 60px;
    background: #fff;
  }

  &__image {
    width: 60px;
    height: 60px;
    // margin: auto 0;
    display: flex;
    justify-content: center;
    align-items: center;

    &-mask {
      position: absolute;
      width: 60px;
      height: 100%;
      background: #000;
      opacity: 0.1;
      top: 0px;
    }

    img {
      object-fit: cover;
      width: 100%;
      height: 100%;
    }
  }

  &__details {
    padding: 3px 12px 3px 15px;
    margin: auto 0;
    line-height: 1.1;
    max-width: 215px;
    position: relative;
    top: -2px;
    width: -webkit-fill-available !important;

    &-title {
      font-size: 10px;
      font-weight: 700;
    }

    &-action {
      font-size: 10px;
    }

    &-time {
      font-size: 8px;
      color: #808080;
      padding-top: 4px;
    }
  }
}

.verified-text {
  text-decoration: none;
  position: absolute;
  right: 10px;
  bottom: 2px;
  font-size: 8px;
  color: gray;

  i {
    font-size: 8px;
    color: $h-primary;
  }

  span {
    color: $h-primary;
    font-size: 8px;
    font-weight: bold;
  }
}

.notification {
  &--rounded {
    $border: 3px;
    border-radius: 60px;
    .preview__image-mask {
      border-radius: 50%;
      border: 3px solid #fff;
    }

    img {
      border: 4px solid #fff;
      border-radius: 30px;
      margin-top: -1px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }

    .verified-text {
      right: 20px;
    }
  }

  &--rounded-padding {
    $border: 8px;
    border-radius: 60px;
    .preview__image-mask {
      border-radius: 50%;
      border: $border solid #fff;
    }

    img {
      border: $border solid #fff;
      border-radius: 60px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }

    .verified-text {
      right: 20px;
    }
  }

  &--squared-padding {
    $border: 8px;
    .preview__image-mask {
      border: $border solid #fff;
      border-radius: 10px;
    }

    img {
      border: $border solid #fff;
      border-radius: 10px;
    }

    .preview__details {
      padding-left: #{15px - $border};
    }
  }
}
</style>

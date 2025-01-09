<template>
  <BaseModal
    :modal-id="modalId"
    :title="
      type === 'email'
        ? 'Preview of Sharing Email'
        : 'Preview of Social Networks Message'
    "
  >
    <template v-if="validSaleChannel && !selectedMeta?.domain">
      <div
        class="d-flex align-items-center bg-light-info rounded p-5"
        style="text-align: justify"
      >
        <span class="text-muted fw-bold">
          The selected sale channel's domain is not yet configured. Please
          configure it first in <a href="/domain/settings">domain settings</a>.
        </span>
      </div>
    </template>
    <template v-if="validSaleChannel && selectedMeta?.domain">
      <ul
        v-if="type !== 'email'"
        class="nav nav-pills nav-pills-custom mb-3"
      >
        <!--begin::Item-->
        <li
          v-for="(network, index) in socialNetworks"
          :key="index"
          class="nav-item mb-3 me-3 me-lg-6"
        >
          <!--begin::Link-->
          <button
            class="nav-link d-flex justify-content-between flex-column flex-center overflow-hidden py-4"
            :class="{
              active: selectedNetwork === network,
            }"
            @click="selectedNetwork = network"
          >
            <!--begin::Icon-->
            <div
              class="nav-icon text-capitalize"
              style="color: black"
            >
              {{ network }}
            </div>
            <!--end::Icon-->
            <!--begin::Bullet-->
            <span
              class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"
            />
            <!--end::Bullet-->
          </button>
          <!--end::Link-->
        </li>
        <!--end::Item-->
      </ul>
      <div class="sharedMessages full-height">
        <div class="preview-box-wrapper">
          <div
            class="social-preview-box w-auto my-4"
            style="margin-left=-100px"
          >
            <div
              class="preview-box-class social-preview-fb shadow-sm"
              :class="{
                'd-none': selectedNetwork !== 'facebook',
              }"
            >
              <div class="d-flex user-info">
                <div class="avatar avatar-xs border flex-grow-0 flex-shrink-0">
                  <img
                    src="https://media.hypershapes.com/images/avatar.jpeg"
                    alt="image description"
                    style="width: -webkit-fill-available"
                  >
                </div>
                <div
                  class="pl-1"
                  style="width: -webkit-fill-available"
                >
                  <span
                    class="d-block font-link font-weight-bold text-black position-relative ms-2"
                  >User Name
                    <img
                      class="position-absolute right-0"
                      src="https://static.upviral.com/assets_new/images/icon-three-dot.png"
                      alt="image description"
                    ></span>
                  <time
                    class="d-block font-10 ms-2"
                    datetime="2022-06-13T12:19"
                  >9h<img
                    src="https://static.upviral.com/assets_new/images/icon-public-access.png"
                    alt="image description"
                  ></time>
                </div>
              </div>
              <div class="post-image">
                <img
                  :src="
                    selectedMetaInfo?.fb_image ??
                      'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                  "
                  alt="image description"
                  class="social-preview-image0"
                  style="object-fit: scale-down"
                >
              </div>
              <div class="post-details">
                <span class="d-block font-link text-gray-dark">{{
                  domain
                }}</span>
                <h3
                  class="h5 text-black font-weight-bold font-link post-title mb-1 social-network-title"
                >
                  {{
                    selectedMetaInfo?.fb_title ?? selectedMetaInfo?.seo_title
                  }}
                </h3>
                <span
                  class="d-block post-desc font-color font-caption social-network-description"
                >{{
                  selectedMetaInfo?.fb_description ??
                    selectedMetaInfo?.meta_description
                }}</span>
              </div>
            </div>
            <div
              class="preview-box-class social-preview-linkedin shadow-sm"
              :class="{
                'd-none': selectedNetwork !== 'linkedin',
              }"
            >
              <div class="d-flex user-info">
                <div class="avatar avatar-xs border flex-grow-0 flex-shrink-0">
                  <img
                    src="https://media.hypershapes.com/images/avatar.jpeg"
                    alt="image description"
                    style="width: -webkit-fill-available"
                  >
                </div>
                <div
                  class="pl-1"
                  style="width: -webkit-fill-available"
                >
                  <span
                    class="d-block font-link font-weight-bold text-black position-relative"
                  >User Name
                    <img
                      class="position-absolute right-0"
                      src="https://static.upviral.com/assets_new/images/icon-three-dot.png"
                      alt="image description"
                    ></span>
                  <span class="d-block font-10">139 followers</span>
                </div>
              </div>
              <div class="post-image">
                <img
                  :src="
                    selectedMetaInfo?.fb_image ??
                      'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                  "
                  alt="image description"
                  style="object-fit: scale-down"
                >
              </div>
              <div class="post-details">
                <div class="position-relative">
                  <span
                    class="h5 text-black font-weight-bold font-link post-title mb-1 social-network-title title-width"
                  >{{
                    selectedMetaInfo?.fb_title ?? selectedMetaInfo?.seo_title
                  }}</span>
                  <span
                    class="learn-more position-absolute font-weight-bold right-0"
                  >Learn more</span>
                </div>
                <span class="d-block font-link text-gray-dark">{{
                  domain
                }}</span>
              </div>
            </div>
            <div
              class="preview-box-class social-preview-twitter shadow-sm"
              :class="{
                'd-none': selectedNetwork !== 'twitter',
              }"
            >
              <div class="d-flex user-info">
                <div class="avatar avatar-xs border flex-grow-0 flex-shrink-0">
                  <img
                    src="https://media.hypershapes.com/images/avatar.jpeg"
                    alt="image description"
                    style="width: -webkit-fill-available"
                  >
                </div>
                <div
                  class="pl-1"
                  style="width: -webkit-fill-available"
                >
                  <span
                    class="d-block mb-1 font-link font-weight-bold text-black position-relative"
                  >User Name <label class="twitter-dot">&nbsp;</label>
                    <span class="font-weight-semibold font-10 time-color">
                      18h</span>
                    <img
                      class="position-absolute right-0"
                      src="https://static.upviral.com/assets_new/images/icon-three-dot.png"
                      alt="image description"
                    >
                  </span>
                  <div
                    class="font-weight-medium font-10 text-black twitter-preset-text"
                  >
                    {{ socialMessage }}
                    <!-- <a
                  href="javascript:;"
                  class="text-brand-twitter"
                >@ohiobookclub</a> -->
                  </div>
                </div>
              </div>
              <div class="post-detail-wrapper">
                <div class="post-detail-inner-wrapper">
                  <div class="post-image">
                    <img
                      :src="
                        selectedMetaInfo?.fb_image ??
                          'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                      "
                      alt="image description"
                      style="object-fit: scale-down"
                    >
                  </div>
                  <div class="post-details">
                    <h3
                      class="h5 text-black font-weight-bold font-link post-title mb-1 social-network-title"
                    >
                      {{
                        selectedMetaInfo?.fb_title ??
                          selectedMetaInfo?.seo_title
                      }}
                    </h3>
                    <span
                      class="d-block post-desc font-color font-weight-medium font-caption mb-1 social-network-description"
                    >{{
                      selectedMetaInfo?.fb_description ??
                        selectedMetaInfo?.meta_description
                    }}</span>
                    <span class="d-block font-link text-gray-dark">{{
                      domain
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="preview-box-class social-preview-telegram py-2 px-4"
            :class="{
              'd-none': selectedNetwork !== 'telegram',
            }"
          >
            <a href="#">{{ `https://${domain}` }}</a>
            <div class="mt-2">
              {{ socialMessage }}
            </div>
            <div
              class="telegram-message d-flex"
              style="
                  margin-bottom: 1rem;
                  margin-top: 0.5rem;
                  padding-left: 0.625rem;
                  position: relative;
                "
            >
              <div class="post-details p-2">
                <span style="font-weight: 400">
                  {{ domain }}
                </span>
                <p
                  class="mb-0"
                  style="font-weight: 500"
                >
                  {{
                    selectedMetaInfo?.fb_title ?? selectedMetaInfo?.seo_title
                  }}
                </p>
                <span style="font-size: 12px; line-height: 1.25">{{
                  selectedMetaInfo?.fb_description ??
                    selectedMetaInfo?.meta_description
                }}</span>
              </div>
              <div style="width: 5rem; height: 5rem; position: relative">
                <img
                  :src="
                    selectedMetaInfo?.fb_image ??
                      'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                  "
                  alt="image description"
                  class="w-100 h-100 position-absolute d-block"
                  width="400"
                  height="400"
                  style="
                      object-fit: cover;
                      border-radius: 0.5rem;
                      vertical-align: middle;
                    "
                >
              </div>
            </div>
            <div class="d-flex justify-content-end">
              16:21 PM
              <img
                src="https://static.upviral.com/assets_new/images/whatsapp-tick.png"
                alt="image description"
                style="object-fit: scale-down; width: 20px"
              >
            </div>
          </div>
          <div
            class="preview-box-class social-preview-whatsapp"
            :class="{
              'd-none': selectedNetwork !== 'whatsapp',
            }"
          >
            <div class="post-detail-wrapper">
              <div class="post-detail-inner-wrapper d-flex">
                <!-- <div class="post-image">
                    <img
                      :src="
                        selectedMetaInfo?.fb_image ??
                          'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                      "
                      alt="image description"
                      style="object-fit: scale-down"
                    >
                  </div> -->
                <div class="post-details">
                  <h3
                    class="font-12-px text-black font-weight-bold post-title mb-1 social-network-title"
                  >
                    {{
                      selectedMetaInfo?.fb_title ??
                        selectedMetaInfo?.seo_title
                    }}
                  </h3>
                  <span
                    class="d-block post-desc font-color font-weight-medium font-caption font-10 mb-1 social-network-description"
                  >{{
                    selectedMetaInfo?.fb_description ??
                      selectedMetaInfo?.meta_description
                  }}</span>
                  <span class="d-block font-link font-10 text-gray-dark">{{
                    domain
                  }}</span>
                </div>
              </div>
              <div class="link-detail position-relative">
                <a href="#">{{ `https://${domain}` }}</a>
                <span class="position-absolute right-0 font-10 whatsapp-tick">
                  16:21
                  <img
                    src="https://static.upviral.com/assets_new/images/whatsapp-tick.png"
                    alt="image description"
                  >
                </span>
              </div>
            </div>
          </div>
          <div
            class="preview-box-class social-preview-messanger"
            :class="{
              'd-none': selectedNetwork !== 'messenger',
            }"
          >
            <div class="post-detail-wrapper">
              <div class="post-detail-inner-wrapper shadow-sm">
                <div class="post-image">
                  <img
                    :src="
                      selectedMetaInfo?.fb_image ??
                        'https://media.hypershapes.com/images/hypershapes_icon_nobg_new.png'
                    "
                    alt="image description"
                    style="object-fit: scale-down"
                  >
                </div>
                <div class="post-details">
                  <h4
                    class="text-black font-weight-bold post-title mb-1 social-network-title"
                  >
                    {{
                      selectedMetaInfo?.fb_title ??
                        selectedMetaInfo?.seo_title
                    }}
                  </h4>
                  <span class="d-block text-gray-dark">{{ domain }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="setup-box share-via-email added email-box p-0 shadow-md rounded-lg w-auto"
        :class="{
          'd-none': selectedNetwork !== 'email',
        }"
      >
        <div class="email-header">
          <img
            class=""
            src="https://static.upviral.com/assets_new/images/icon-mac-menu.png"
            alt="image description"
          >
        </div>
        <div class="email-body">
          <!-- <div class="field">
            <span class="field-label">To:</span>
          </div> -->
          <div class="field">
            <span class="field-label">Subject:</span>
            <span class="email-share-topic">{{ emailSubject }}</span>
          </div>
          <div class="field message">
            <textarea
              :value="emailMessage"
              class="email-share-description"
              disabled
              cols="70"
              rows="20"
            />
          </div>
        </div>
      </div>
    </template>
    <template v-if="!validSaleChannel">
      <div
        class="d-flex align-items-center bg-light-info rounded p-5"
        style="text-align: justify"
      >
        <span class="text-muted fw-bold">
          Please select the sale channel. Select the funnel first as well if
          Funnel sale channel is selected.
        </span>
      </div>
    </template>
  </BaseModal>
</template>

<script>
export default {
  props: {
    modalId: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: () => 'social',
    },
    socialNetworks: {
      type: Array,
      default: () => ['facebook', 'twitter'],
    },
    socialMessage: {
      type: String,
      default: '',
    },
    emailSubject: {
      type: String,
      default: '',
    },
    emailMessage: {
      type: String,
      default: '',
    },
    funnelId: {
      type: String,
      default: null,
    },
    saleChannel: {
      type: String,
      default: null,
    },
    metaInfo: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      selectedNetwork: this.socialNetworks[0] || null,
    };
  },
  computed: {
    validSaleChannel() {
      if (this.saleChannel && this.saleChannel === 'funnel') {
        return this.funnelId;
      }
      return this.saleChannel;
    },
    selectedMeta() {
      return this.metaInfo.find((el) => el.type === this.saleChannel);
    },
    selectedMetaInfo() {
      const selectedMeta = this.metaInfo.find(
        (el) => el.type === this.saleChannel
      );
      if (selectedMeta.type === 'funnel') {
        return selectedMeta.metaInfo.find(
          (el) => el.funnel_id === this.funnelId
        );
      }
      return selectedMeta.metaInfo;
    },
    domain() {
      return this.selectedMeta.type === 'funnel'
        ? `${this.selectedMeta.domain}/${this.selectedMetaInfo?.path}`
        : this.selectedMeta.domain;
    },
  },
  watch: {
    type(newValue) {
      if (newValue) {
        this.selectedNetwork =
          newValue === 'email' ? newValue : this.socialNetworks[0] || null;
      }
    },
  },
};
</script>
<style scoped lang="scss">
.social-preview-box .learn-more {
  padding: 5px 15px;
  border: 1px solid #2967bc;
  border-radius: 20px;
  color: #2967bc;
  font-size: 12px;
  max-height: 48px;
}
.font-weight-bold {
  font-weight: bold !important;
}
.sharedMessages {
  max-height: 682px;
}
.preview-box-wrapper {
  padding-left: 35px;
  padding-right: 35px;
  background: #f9f9f9;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  height: 100%;
  max-height: 560px;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
}
.social-preview-box {
  background: #fff;
  border-radius: 8px;
  width: 400px !important;
}

.shadow-sm {
  box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 8%) !important;
}
.social-preview-box .user-info {
  padding: 16px 12px;
}
.social-preview-box .post-image {
  max-width: 400px;
  height: 208px;
}
.social-preview-box .post-image img {
  width: 100%;
  height: 100%;
}
.social-preview-box .post-details {
  padding: 12px;
}
.social-preview-box .font-color {
  color: #8d939e !important;
}
.avatar.avatar-xs {
  width: 40px;
  height: 40px;
}
.avatar {
  width: 44px;
  height: 44px;
  border: 2px solid #f5f5f5;
  border-radius: 100%;
  overflow: hidden;
}
.social-preview-box .post-detail-wrapper {
  padding: 0 10px 10px 54px;
}
.social-preview-whatsapp .post-detail-wrapper {
  padding: 3px;
  background: #e1f6cb;
  box-shadow: 0 2px 6px rgb(208 201 214 / 50%);
  border-radius: 12px;
}
.right-0 {
  right: 0;
}
.setup-box.added,
.added.setup-box-small,
.added.setup-box-medium,
.setup-box.prize,
.prize.setup-box-small,
.prize.setup-box-medium {
  padding-bottom: 42px;
  text-align: left;
  border: 1px solid #eaeaea;
  box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 8%);
  -webkit-transition: border-color 0.3s linear;
  transition: border-color 0.3s linear;
}
.setup-box.email-box .email-header,
.email-box.setup-box-small .email-header,
.email-box.setup-box-medium .email-header {
  background: #eaeaea;
  height: 54px;
  padding: 16px 20px;
}
.setup-box.email-box .email-body,
.email-box.setup-box-small .email-body,
.email-box.setup-box-medium .email-body {
  padding: 16px 20px;
}
.setup-box.email-box .field,
.email-box.setup-box-small .field,
.email-box.setup-box-medium .field {
  border-bottom: 1px solid #e6e6e6;
  min-height: 31px;
  padding: 6px 0;
  font-size: 14px;
  line-height: 1.3571428571;
  color: #384354;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: start;
  -ms-flex-align: start;
  align-items: flex-start;
}
*,
*::before,
*::after {
  box-sizing: border-box;
}
user agent stylesheet div {
  display: block;
}
.setup-box.added,
.added.setup-box-small,
.added.setup-box-medium,
.setup-box.prize,
.prize.setup-box-small,
.prize.setup-box-medium {
  padding-bottom: 42px;
  text-align: left;
  border: 1px solid #eaeaea;
  box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 8%);
  -webkit-transition: border-color 0.3s linear;
  transition: border-color 0.3s linear;
}
.setup-box,
.setup-box-small,
.setup-box-medium {
  width: 250px;
  min-height: 220px;
  padding: 20px;
  background: #fbfbfb;
  color: #797979;
  border-radius: 4px;
  text-align: center;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  position: relative;
}
.setup-box.email-box .field.message,
.email-box.setup-box-small .field.message,
.email-box.setup-box-medium .field.message {
  display: block;
  padding: 16px 0;
}
.email-share-description {
  border: transparent;
  background: transparent;
}

.telegram-message {
  &::before {
    content: '';
    display: block;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 0.2rem;
    background: rgb(69, 175, 84);
    border-radius: 0.125rem;
  }
}
</style>

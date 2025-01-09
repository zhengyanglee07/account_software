<template>
  <div>
    <div class="d-flex flex-column flex-md-row justify-content-start justify-content-md-between align-items-start align-items-md-center my-4">
      <div class="d-flex justify-content-center align-items-center mb-2 mb-md-0">
        <BaseButton
          type="link"
          class="me-3"
          data-bs-toggle="tooltip"
          data-bs-placement="bottom"
          title="Back to previous page"
          href="/funnels"
        >
          <i class="fa-solid fa-arrow-left fs-3" />
        </BaseButton>
        <h1 
          class="fs-2 mb-0 d-inline-block text-truncate"
          style="max-width: 350px;"
        >
          {{ funnelDetails.funnel_name }}
        </h1>
      </div>
      <div>
        <BaseButton
          id="funnel-actions"
          type="secondary"
          class="me-3"
          has-dropdown-icon
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          More Actions
        </BaseButton>
        <BaseButton @click="triggerSettingModal">
          <i class="fa fa-cog me-2" />
          Settings
        </BaseButton>
        <BaseDropdown id="funnel-actions">
          <BaseDropdownOption
            id="can-share-funnel-button"
            text="Share Funnel"
            data-bs-toggle="modal"
            @click="triggerShareFunnelModal"
          />
          <BaseDropdownOption
            text="Duplicate Funnel"
            @click="duplicateFunnel(funnelDetails.id)"
          />
          <BaseDropdownOption
            text="Delete Funnel"
            @click="triggerDeleteConfirmation(true)"
          />
        </BaseDropdown>
      </div>
    </div>
    <!-- <div
      v-show="isLoading"
      class="loader"
    >
      <i class="fas fa-circle-notch loader-icon fa-spin fast-spin" />
    </div> -->
    <div class="row gx-5">
      <div class="col-md-4">
        <BaseCard>
          <div class="stepper stepper-pills stepper-column pt-5">
            <div class="stepper-nav">
              <div
                v-for="landing in sortedLandingPages"
                :key="landing.id"
                class="stepper-item"
                :class="{ current: selectedLandingPage === landing.id }"
                @click="selectedLandingPage = landing.id"
              >
                <div class="stepper-line w-40px" />
                <div class="stepper-icon w-40px h-40px">
                  <span class="stepper-number">
                    {{ landing.index + 1 }}
                  </span>
                </div>
                <div class="stepper-label">
                  <h3 class="stepper-title">
                    {{ landing.name }}
                  </h3>
                  <div class="stepper-desc">
                    {{ landing.is_published ? 'Published' : 'Draft' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <BaseButton
            id="add-landing-page-button"
            class="w-100"
            type="light-primary"
            @click="
              triggerTemplateModal();
              chosenLandingId = null;
            "
          >
            Add Step
          </BaseButton>
        </BaseCard>
      </div>
      <div class="col-md-8">
        <BaseCard>
          <BaseFormGroup>
            <BaseFormInput
              :id="`landing-url${selectedLandingPageDetails.id}`"
              type="text"
              readonly
              :model-value="
                selectedLandingPageDetails.type === 'Draft'
                  ? `${hostname}${pagePublishUrl}`
                  : pagePublishUrl
              "
            >
              <template #prepend>
                <BaseButton
                  id="copy-to-clipboard"
                  type="link"
                  :data-clipboard-target="`#landing-url${selectedLandingPageDetails.id}`"
                  title="Copy to clipboard"
                >
                  <i class="fa-solid fa-clipboard" />
                </BaseButton>
              </template>
              <template #append>
                <BaseButton
                  type="link"
                  :href="pagePublishUrl"
                  is-open-in-new-tab
                  title="View Page"
                >
                  <i class="fas fa-external-link-alt" />
                </BaseButton>
              </template>
            </BaseFormInput>
          </BaseFormGroup>
          <div>
            <div class="landing-preview-container mx-auto mx-md-0">
              <img
                v-if="isNewCreatedLandingPage"
                class="w-100"
                src="https://axiomoptics.com/wp-content/uploads/2019/08/placeholder-images-image_large.png"
                alt="No design"
                style="height: 85%"
              >
              <iframe
                v-else
                id="scaled-frame"
                :src="`/${pagePreviewUrl}`"
                frameborder="0"
                width="100%"
                height="240px"
                scrolling="no"
              />
              <div class="landing-action-button-wrapper text-center mt-3">
                <BaseButton
                  v-if="selectedLandingPageDetails.element != null"
                  class="edit-button"
                  size="sm"
                  is-window-redirection
                  :href="`/builder/landing/${selectedLandingPageDetails.reference_key}/editor`"
                >
                  Edit Page
                </BaseButton>
                <BaseButton
                  v-if="selectedLandingPageDetails.element == null"
                  class="edit-button"
                  size="sm"
                  @click="
                    triggerTemplateModal();
                    chosenLandingId = selectedLandingPageDetails.id;
                  "
                >
                  Edit Page
                </BaseButton>
                <BaseButton
                  id="landing-action"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                  class="landing-dropdown-button ms-1 px-3 text-center"
                  size="sm"
                >
                  <i class="fa-solid fa-caret-down" />
                </BaseButton>
                <BaseDropdown id="landing-action">
                  <BaseDropdownOption
                    text="Duplicate Page"
                    @click="duplicateLandingPage"
                  />
                  <BaseDropdownOption
                    :text="`Change To ${currentLandingStatus}`"
                    @click="updateLandingPageStatus"
                  />
                  <BaseDropdownOption
                    text="Delete Page"
                    @click="triggerDeleteConfirmation(false)"
                  />
                </BaseDropdown>
              </div>
            </div>
          </div>
        </BaseCard>
      </div>
    </div>
  </div>

  <BaseModal
    title="Funnel Settings"
    modal-id="funnel-setting-modal"
  >
    <BaseFormGroup label="Funnel Name">
      <BaseFormInput v-model="tempFunnelName" />
    </BaseFormGroup>
    <BaseFormGroup label="Domain Name">
      <BaseFormSelect
        v-model="funnelDetails.domain_name"
        :options="allDomain"
        label-key="domain"
        value-key="domain"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Currency">
      <CurrencySelect
        v-model="selectedCurrency"
        :options="currencies"
      />
    </BaseFormGroup>
    <BaseFormGroup
      label="Affiliate Badge"
      :description="
        !canDisableBadges
          ? 'Upgrade to Triangle plan to disable affiliate badge'
          : ''
      "
    >
      <BaseFormCheckBox
        id="checkbox-1"
        v-model="funnelDetails.has_affiliate_badge"
        :value="false"
        :disabled="!canDisableBadges"
      >
        Disable Affiliate Badge
      </BaseFormCheckBox>
    </BaseFormGroup>
    <BaseFormGroup label="Head Tracking Code">
      <BaseFormTextarea
        id="tracking-head"
        v-model="funnelDetails.tracking_header"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Body Tracking Code">
      <BaseFormTextarea
        id="tracking-body"
        v-model="funnelDetails.tracking_body"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="updateFunnelSettings">
        Save
      </BaseButton>
    </template>
  </BaseModal>

  <!-- Modal to select template for new LP -->
  <TemplateModal
    :funnel-id="funnelDetails.id"
    :landing-index="funnelLandingPages.length"
    :landingpage-id="chosenLandingId"
  />

  <ShareFunnelModal :share-funnel-url="shareFunnelUrl" />
</template>

<script>
import TemplateModal from '@shared/components/TemplateModal.vue';
import funnelMixin from '@funnel/mixins/funnelMixin.js';
import * as ClipboardJS from 'clipboard';
import { Modal } from 'bootstrap';
import funnelAPI from '@funnel/api/funnelAPI.js';
import landingAPI from '@funnel/api/landingAPI.js';
// import Draggable from 'vuedraggable';

export default {
  components: {
    TemplateModal,
    // Draggable,
  },

  mixins: [funnelMixin],

  props: {
    allDomain: { type: Array, default: () => [] },
    funnel: { type: Object, default: () => {} },
    funnelLandingPage: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
  },

  data() {
    return {
      funnelDetails: {},
      funnelLandingPages: [],
      settingModal: null,
      tempFunnelName: null,
      chosenLandingId: null,
      isLoading: false,
      isCopied: false,
      selectedLandingPage: null,
      hostname: window.location.host,
      selectedCurrency: null,
    };
  },

  computed: {
    canDisableBadges() {
      return !this.$page.props.permissionData.featureForPaidPlan.includes(
        'can-disabled-badge'
      );
    },
    sortedLandingPages() {
      return this.funnelLandingPages.slice().sort((a, b) => a.index - b.index);
    },

    funnelUrl() {
      const firstLanding = this.sortedLandingPages[0];
      if (firstLanding === undefined) return null;
      return `https://${this.funnelDetails.domain_name}/${firstLanding.path}`;
    },

    selectedLandingPageDetails() {
      return this.sortedLandingPages.find(
        (e) => e.id === this.selectedLandingPage
      );
    },

    isNewCreatedLandingPage() {
      const landing = this.selectedLandingPageDetails;
      return landing?.element === null;
    },

    pagePreviewUrl() {
      const { reference_key: refKey } = this.selectedLandingPageDetails;
      return `builder/landing/${refKey}/preview`;
    },

    funnelDefaultDomain() {
      return this.allDomain.find((domain) => {
        return (
          domain.type === 'funnel' && domain.type_id === this.funnelDetails.id
        );
      });
    },

    firstPublishedPage() {
      return this.sortedLandingPages.find(
        (landing) => landing.is_published
      );
    },

    pagePublishUrl() {
      const { id, type, path } = this.selectedLandingPageDetails;
      if (type === 'Draft') return `/${this.pagePreviewUrl}`;
      return this.funnelDefaultDomain && this.firstPublishedPage?.id === id
        ? `https://${this.funnelDefaultDomain.domain}`
        : `https://${this.funnelDetails.domain_name}/${path ?? ''}`;
    },

    currentLandingStatus() {
      return this.selectedLandingPageDetails?.is_published
        ? 'Draft'
        : 'Publish';
    },
  },

  watch: {
    selectedCurrency(val) {
      if (!val) return;
      this.funnelDetails.currency = {
        country: val.substring(0, 1),
        currency: val,
      };
    },
  },

  created() {
    this.funnelDetails = this.funnel;
    this.selectedCurrency = this.funnel.currency.currency;
    this.funnelLandingPages = this.funnelLandingPage;
    //* Always show the first landing page as default
    this.selectedLandingPage = this.sortedLandingPages[0]?.id;
    //* to avoid the typed new funnel name reflect at outside directly
    this.tempFunnelName = { ...this.funnelDetails }.funnel_name;
  },

  mounted() {
    //* clipboard
    const clipboard = new ClipboardJS('#copy-to-clipboard');
  },

  methods: {
    triggerShareFunnelModal() {
      this.modal = new Modal(document.getElementById('share-funnel-modal'));
      this.modal.show();
      this.generateShareFunnelURL(this.funnelDetails.reference_key);
    },

    updateLandingPageStatus() {
      const landingId = this.selectedLandingPageDetails.id;
      landingAPI
        .update(landingId, {
          is_published: !this.selectedLandingPageDetails?.is_published,
          funnelId: this.funnelDetails.id,
        })
        .then((response) => {
          this.funnelLandingPages = response.data;
          this.$toast.success('Success', 'Successfully Updated');
        })
        .catch((error) => {
          console.error(error);
          this.$toast.error(
            'Error',
            'Unexpected Error Occured. Please try again'
          );
        });
    },

    duplicateLandingPage() {
      landingAPI
        .duplicate({
          funnelId: this.funnelDetails.id,
          landingId: this.selectedLandingPageDetails.id,
        })
        .then((response) => {
          this.$toast.success('Success', 'Successfully Duplicated');
          this.funnelLandingPages.push(response.data);
          this.selectedLandingPage = response.data.id;
        })
        .catch((error) => {
          console.error(error);
          this.$toast.error(
            'Error',
            'Unexpected Error Occured. Please try again'
          );
        });
    },

    deleteLandingPage() {
      const { id, index } = this.selectedLandingPageDetails;
      landingAPI
        .delete(id)
        .then((response) => {
          this.$toast.success('Success', 'Successfully Deleted');
          this.funnelLandingPages = response.data.funnelLandingPages;
          this.selectedLandingPage =
            this.funnelLandingPages.find((e) => {
              return e.index === index;
            })?.id ?? this.sortedLandingPages[0]?.id;
        })
        .catch((error) => {
          console.error(error);
          this.$toast.error(
            'Error',
            'Unexpected Error Occured. Please try again'
          );
        });
    },

    updateFunnelSettings() {
      if (this.tempFunnelName === '') {
        this.$toast.error('Error', 'Funnel Name is blank');
        return;
      }
      funnelAPI
        .updateSettings(this.funnelDetails.id, {
          funnelName: this.tempFunnelName,
          funnelDetails: this.funnelDetails,
          canDisableBadges: this.canDisableBadges,
        })
        .then((response) => {
          if (response.data.duplicated) {
            this.$toast.error('Error', 'Duplicated Funnel Name');
            return;
          }
          this.$toast.success('Success', 'Successfully Updated');
          this.funnelDetails = response.data;
          this.settingModal.hide();
        })
        .catch((error) => {
          this.$toast.error('Error', 'Unexpected Error Occured');
        });
    },

    reorderLandingPages({ moved: { newIndex, oldIndex } }) {
      this.isLoading = true;
      landingAPI
        .reorder(oldIndex, newIndex, this.funnelDetails.id)
        .then((response) => {
          this.isLoading = false;
          this.funnelLandingPages = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },

    triggerDeleteConfirmation(isFunnel = false) {
      // eslint-disable-next-line no-restricted-globals
      const answer = confirm(
        `Are you sure want to delete this ${
          isFunnel ? 'funnel' : 'landing page'
        }?`
      );
      if (!answer) return;
      if (isFunnel) {
        this.deleteFunnel(this.funnelDetails.id);
      } else {
        this.deleteLandingPage();
      }
    },

    triggerTemplateModal() {
      new Modal(document.getElementById('template-modal')).show();
    },

    triggerSettingModal() {
      this.settingModal = new Modal(
        document.getElementById('funnel-setting-modal')
      );
      this.settingModal.show();
    },
  },
};
</script>

<style scoped lang="scss">
.stepper-item {
  cursor: pointer;
}

.stepper.stepper-pills .stepper-item .stepper-label .stepper-title {
  color: #7e8299 !important;
}

.stepper.stepper-pills .stepper-item:hover {
  .stepper-label .stepper-title {
    color: #3f4254 !important;
  }

  .stepper-icon {
    background: #009ef7;
  }

  .stepper-number {
    color: white !important;
  }
}

.stepper.stepper-pills .stepper-item.current {
  .stepper-label .stepper-title {
    color: #3f4254 !important;
  }
}

.stepper.stepper-pills .stepper-item.current:last-child .stepper-icon {
  background: #009ef7;

  .stepper-number {
    display: block;
  }
}

.landing-preview-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  width: 18rem;
  height: 320px;
  padding: 10px;
  border: none;
  box-shadow: 0 0 28px 0 rgb(82 63 105 / 5%);
  background-color: #f1f2f2;
  border-radius: 2.5px;
  position: relative;

  .landing-action-button-wrapper {
    position: absolute;
    bottom: 10px;
  }
}

.edit-button {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.landing-dropdown-button {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.loader {
  z-index: 10;
  background-color: #0000003b;
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}

.loader-icon {
  position: fixed;
  top: 50%;
  left: 50%;
  font-size: 50px;
}

.fast-spin {
  -webkit-animation: fa-spin 0.8s infinite linear;
  animation: fa-spin 0.8s infinite linear;
}

#scaled-frame {
  width: 840px;
  height: 1000px;
  border: 0px;
  zoom: 0.25;
  position: absolute;
  top: 10px;
  left: 10px;
  pointer-events: none;
  -moz-transform: scale(0.25);
  -moz-transform-origin: 0 0;
  -o-transform: scale(0.25);
  -o-transform-origin: 0 0;
  -webkit-transform: scale(0.25);
  -webkit-transform-origin: 0 0;
}

@media screen and (-webkit-min-device-pixel-ratio: 0) {
  #scaled-frame {
    zoom: 1;
  }
}
</style>

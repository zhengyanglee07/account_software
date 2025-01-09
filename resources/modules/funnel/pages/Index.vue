<template>
  <BaseDatatable
    :table-headers="tableHeaders"
    :table-datas="funnelsArray"
    title="funnel"
    @delete="deleteFunnel"
  >
    <template #action-button>
      <BaseButton
        id="add-funnel-button"
        has-add-icon
        @click="triggerChooseGoalModal"
      >
        Add Funnel
      </BaseButton>
    </template>

    <template #action-options="{ row: { id, reference_key: refKey } }">
      <BaseDropdownOption
        text="Share"
        data-bs-toggle="modal"
        data-bs-target="#share-funnel-modal"
        @click="generateShareFunnelURL(refKey)"
      />
      <BaseDropdownOption
        text="Duplicate"
        @click="duplicateFunnel(id)"
      />
    </template>
  </BaseDatatable>

  <BaseModal
    modal-id="choose-goal-modal"
    title="Choose a goal"
  >
    <div
      v-for="funnelOption in funnelOptions"
      :key="funnelOption.key"
      class="col-md-6"
    >
      <OnboardingSelectionCard
        class="w-100"
        style="margin-bottom: 0 !important"
        :is-active="funnelOption.type === selectedFunnelType"
        @click="selectedFunnelType = funnelOption.type"
      >
        <template #title>
          {{ funnelOption.title }}
        </template>
        <template #image>
          <img
            alt="img"
            :src="funnelOption.image"
            class="image-style"
            style="width: 80px"
          >
        </template>
        <template #description>
          {{ funnelOption.description }}
        </template>
      </OnboardingSelectionCard>
    </div>

    <template #footer>
      <BaseButton @click="triggerNameModal">
        Next
      </BaseButton>
    </template>
  </BaseModal>

  <TemplateNameModal
    type="funnel"
    :is-save-template="false"
    @create="createNewFunnel"
  />

  <ShareFunnelModal :share-funnel-url="shareFunnelUrl" />

  <ConfirmCloneFunnelModal
    v-if="popOut"
    :reference-key="referenceKey"
    modal-id="confirm-clone-funnel-modal"
  />
</template>
<script>
import { Modal } from 'bootstrap';
import funnelMixin from '@funnel/mixins/funnelMixin.js';
import ConfirmCloneFunnelModal from '@funnel/components/ConfirmCloneFunnelModal.vue';
import funnelAPI from '@funnel/api/funnelAPI.js';
import landingAPI from '@funnel/api/landingAPI.js';
//* Media
import salesFunnelImage from '@funnel/assets/media/sales-funnel.svg';
import leadMagnetFunnelImage from '@funnel/assets/media/lead-magnet-funnel.svg';

import OnboardingSelectionCard from '@onboarding/components/OnboardingSelectionCard.vue';

export default {
  components: {
    ConfirmCloneFunnelModal,
    OnboardingSelectionCard,
  },

  mixins: [funnelMixin],

  props: {
    allFunnels: { type: Array, default: () => [] },
    popOut: { type: Boolean, default: false },
    referenceKey: { type: String, default: '' },
  },

  data() {
    return {
      funnels: [],
      selectedFunnelType: 'lead',
      chooseGoalModal: null,
      funnelNameModal: null,
      confirmCloneFunnelModal: null,
      funnelOptions: [
        {
          title: 'Lead Magnet',
          type: 'lead',
          description: 'Collect email address',
          image: leadMagnetFunnelImage,
        },
        {
          title: 'Sales',
          type: 'sales',
          description: 'Sell your products and services',
          image: salesFunnelImage,
        },
      ],
      tableHeaders: [
        {
          name: 'Funnel Name',
          key: 'funnel_name',
          width: '55%',
        },
        {
          name: 'Last Modified',
          key: 'updated_at',
          width: '25%',
          isDateTime: true,
        },
      ],
    };
  },

  computed: {
    funnelsArray() {
      return this.funnels.map((funnel) => {
        return {
          id: funnel.id,
          funnel_name: funnel.funnel_name,
          updated_at: funnel.last_modified,
          reference_key: funnel.reference_key,
          editLink: `/funnel/${funnel.reference_key}`,
        };
      });
    },
  },

  mounted() {
    if (this.popOut === 'true') {
      this.confirmCloneFunnelModal = new Modal(
        document.getElementById('confirm-clone-funnel-modal')
      );
      this.confirmCloneFunnelModal.show();
    }
    this.funnels = this.allFunnels;
  },

  methods: {
    triggerChooseGoalModal() {
      this.selectedFunnelType = 'lead';
      this.chooseGoalModal = new Modal(
        document.getElementById('choose-goal-modal')
      );
      this.chooseGoalModal.show();
    },

    triggerNameModal() {
      this.chooseGoalModal.hide();
      this.funnelNameModal = new Modal(
        document.getElementById('template-name-modal')
      );
      this.funnelNameModal.show();
    },

    async createNewFunnel(funnelName) {
      funnelAPI
        .create(funnelName)
        .then((response) => {
          const { id, reference_key: referenceKey } = response.data;
          if (this.selectedFunnelType === 'custom') {
            this.$inertia.visit(`/funnel/${referenceKey}`);
            return;
          }
          const nameArray = {
            lead: [
              'Lead Page',
              // 'Thank you Page'
            ],
            sales: [
              'Sales Page',
              // 'Upsell Page',
              // 'Order Summary Page'
            ],
            webinar: [
              'Registration',
              // 'Thank You',
              // 'Broadcast Room'
            ],
          };
          this.createLandingFromTemplate(
            id,
            nameArray[this.selectedFunnelType],
            referenceKey
          );
        })
        .catch((error) => {
          switch (error.response.status) {
            case 500:
              this.$toast.error(
                'Error',
                'Funnel name existed. Please try again with a new name.'
              );
              this.funnelNameModal.show();
              break;
            default:
              this.$toast.error(
                'Error',
                'Something went wrong. Please try again later.'
              );
          }
        });
    },

    createLandingFromTemplate(id, nameArray, referenceKey) {
      landingAPI
        .create(id, nameArray)
        .then((response) => {
          window.location.replace(`/funnel/${referenceKey}`);
        })
        .catch((error) => {
          console.error(error);
          this.$toast.error(
            'Error',
            'Something went wrong. Please try again later.'
          );
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.col-md-6:last-child {
  @media (max-width: 480px) {
    margin-top: 15px;
  }
}
</style>

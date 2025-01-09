<template>
  <BaseModal
    :modal-id="modalId"
    title="Select Prize Winners"
    manual
  >
    <div
      v-if="selectingWinner && !suggestedWinners.length"
      class="px-6"
    >
      <div
        class="d-flex align-items-center bg-light-info p-5 mt-3 mb-5 px-8"
        style="text-align: justify"
      >
        <span class="text-muted fw-bold">
          Selecting winners suggestion ...
        </span>
      </div>
    </div>
    <CampaignWinnerDatatable
      v-if="!loading && isEnded"
      :winners="suggestedWinners"
      type="suggested"
      :selected="!selectingWinner"
      @select-winner="selectWinner"
      @accept-winner="acceptWinner"
    />
    <CampaignWinnerDatatable
      v-if="!loading && isEnded"
      :winners="acceptedWinners"
      type="accepted"
      @deny-winner="denyWinner"
    />
    <template #footer>
      <BaseButton
        id="add-prize"
        :disabled="acceptedWinners.length < 1 || savingWinner || !isEnded"
        @click="saveWinner"
      >
        <span> Confirm </span>
        <span v-if="savingWinner">
          <i
            class="fas fa-spinner fa-pulse"
            style="margin-left: 5px"
          />
        </span>
      </BaseButton>
    </template>
    <div
      v-if="!isEnded"
      class="m-10"
    >
      The prize winners only can be selected after the campaign is ended.
    </div>
  </BaseModal>
</template>

<script>
import { nanoid } from 'nanoid';
import { Modal } from 'bootstrap';
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import CampaignWinnerDatatable from '@referral/components/CampaignWinnerDatatable.vue';

export default {
  components: {
    CampaignWinnerDatatable,
  },
  props: {
    modalId: {
      type: String,
      default: '',
    },
    campaign: {
      type: Object,
      default: () => ({}),
    },
    prizes: {
      type: Object,
      default: () => ({}),
    },
    isEnded: {
      type: Boolean,
      default: () => false,
    },
    participants: {
      type: Array,
      default: () => [],
    },
    winners: {
      type: Array,
      default: () => [],
    },
    noOfWinner: {
      type: Number,
      default: () => 0,
    },
    accepted: {
      type: Number,
      default: () => 0,
    },
  },

  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      selectingWinner: false,
      suggestedWinners: [],
      acceptedWinners: [],
      savingWinner: false,
      err: false,
      loading: true,
      watched: false,
    };
  },
  validations() {
    return {};
  },
  watch: {
    winners(newValue) {
      if (newValue && !this.watched) {
        this.acceptedWinners = this.participants?.filter((participant) =>
          newValue.includes(participant?.id)
        );
        this.watched = true;
      }
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);
      modalEl.addEventListener('show.bs.modal', () => {
        if (
          this.isEnded &&
          !this.suggestedWinners.length &&
          this.noOfWinner > this.acceptedWinners?.length
        ) {
          this.loading = true;
          this.selectWinner();
        }
      });
    }, 1000);
    this.loading = false;
  },
  methods: {
    acceptWinner(selectedWinner) {
      this.acceptedWinners.push(selectedWinner);
      this.suggestedWinners = this.suggestedWinners.filter(
        (winner) => winner.id !== selectedWinner.id
      );
    },
    denyWinner(selectedWinner) {
      this.acceptedWinners = this.acceptedWinners.filter(
        (winner) => winner.id !== selectedWinner.id
      );
      this.suggestedWinners.push(selectedWinner);
    },
    async selectWinner() {
      this.selectingWinner = true;
      try {
        await this.$axios({
          method: 'post',
          url: `select-winner/${this.campaign?.reference_key}`,
          data: {
            acceptedWinners: this.acceptedWinners.map((el) => {
              return el.contactRandomId;
            }),
            noOfWinner: this.prizes.noOfWinner ?? 0,
          },
        }).then(({ data }) => {
          this.selectingWinner = false;
          this.suggestedWinners = this.participants?.filter((participant) =>
            data.includes(participant?.contactRandomId)
          );
          this.$toast.success('Success', 'Winner(s) have been selected.');
          this.loading = false;
        });
      } catch (error) {
        console.log(error);
        this.selectingWinner = false;
      }
    },
    async saveWinner() {
      if (!this.acceptedWinners.length) {
        this.$toast.warning('Warning', 'Accept at least 1 prize winner.');
        return;
      }
      this.savingWinner = true;
      Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
      this.$emit(
        'save-winner',
        this.acceptedWinners.map((el) => {
          return el.id;
        })
      );
      this.savingWinner = false;
    },
  },
};
</script>

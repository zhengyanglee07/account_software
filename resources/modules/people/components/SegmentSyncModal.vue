<template>
  <BaseModal
    :modal-id="modalId"
    is-overflow-visible
    title="Sync Segment"
  >
    <BaseFormGroup
      label="Segment ad audience name:"
      error-message="Field is required"
    >
      <BaseFormInput
        v-model="adAudienceName"
        :class="{
          'error-border': showErrors && v$.adAudienceName.$invalid,
        }"
        type="text"
        @input="showErrors = false"
      />
    </BaseFormGroup>

    <div class="mt-3">
      <BaseFormGroup
        label="Choose from one of the social media provider below to sync:"
      />
      <BaseMultiSelect
        v-model="selectedSocialMedia"
        label="name"
        :options="amendedOptions"
        style="min-width: 10rem"
        :selectable="(s) => !usedSocialMedias.includes(s.name.toLowerCase())"
      >
        <!-- <template #option="option">
          <div class="option_wrapper">
            <i :class="option.iconClass" />
            <strong>{{ option.name }}</strong>
          </div>
        </template> -->
      </BaseMultiSelect>
    </div>

    <template #footer>
      <BaseButton
        :disabled="syncingSegment"
        @click="syncSegment"
      >
        <span v-if="syncingSegment">
          <div class="spinner--white-small">
            <div class="loading-animation loading-container my-0">
              <div class="shape shape1" />
              <div class="shape shape2" />
              <div class="shape shape3" />
              <div class="shape shape4" />
            </div>
          </div>
        </span>

        <span v-if="!syncingSegment"> Sync </span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
/* eslint-disable no-unused-expressions */
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'SegmentSyncModal',
  props: {
    modalId: String,
    segmentId: Number,
    availableSocialMedias: Array,
    usedSocialMedias: Array,
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      options: [
        {
          iconClass: 'fa fa-google',
          name: 'Google',
        },
        {
          iconClass: 'fa fa-facebook',
          name: 'Facebook',
        },
      ],
      selectedSocialMedia: '',
      adAudienceName: '',

      showErrors: false,
      syncingSegment: false,
    };
  },
  validations: {
    adAudienceName: {
      required,
    },
  },
  computed: {
    amendedOptions() {
      return this.options.filter((option) =>
        this.availableSocialMedias.includes(option.name.toLowerCase())
      );
    },
  },
  // mounted() {
  //   // clear previously chosen provider after user close the modal
  //   // $(`#${this.modalId}`).on('hidden.bs.modal', this.clearSelectedSocialMedia);
  //   const modalEl = document.getElementById(this.modalId);
  //   modalEl.addEventListener('hidden.bs.modal', function (event) {
  //     this.clearSelectedSocialMedia;
  //   });
  // },
  methods: {
    clearSelectedSocialMedia() {
      this.selectedSocialMedia = '';
    },
    async syncSegment() {
      const { adAudienceName } = this;
      const socialMediaName = this.selectedSocialMedia.name?.toLowerCase();

      if (this.v$.$invalid) {
        this.showErrors = true;
        return;
      }

      if (!socialMediaName) {
        this.$toast.error('Error', 'Please select one social media to connect');
        return;
      }

      this.syncingSegment = true;

      try {
        const res = await this.$axios.put(
          `/segments/${this.segmentId}/synchronize`,
          {
            adAudienceName,
            socialMediaName,
          }
        );

        this.$emit('sync-segment', res.data);
        this.adAudienceName = '';
      } catch (err) {
        if (err.response.status === 422) {
          this.$toast.warning('Warning', err.response.data.message);
          return;
        }

        this.$toast.error('Error', 'Unexpected error occurs');
      } finally {
        this.syncingSegment = false;
      }
    },
  },
};
</script>

<style scoped lang="scss"></style>

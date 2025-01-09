<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Save Segment"
  >
    <BaseFormGroup
      for="segmentName"
      label="Enter a segment name"
    >
      <BaseFormInput
        id="segmentName"
        v-model="segmentName"
        type="text"
      />

      <template
        v-if="segmentNameShowError('segmentName')"
        #error-message
      >
        Segment name is required
      </template>
    </BaseFormGroup>

    <!-- <span
      v-if="segmentNameShowError('segmentName')"
      class="error"
    >
      Segment name is required
    </span> -->

    <template #footer>
      <BaseButton
        :disabled="saving"
        @click="saveSegment"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { required } from '@vuelidate/validators';
import { validateConditionFilters } from '@people/lib/conditionFilters.js';
import { validationFailedNotification } from '@shared/lib/validations.js';
import { Modal } from 'bootstrap';

export default {
  name: 'SaveSegmentModal',
  props: {
    modalId: String,
  },
  data() {
    return {
      segmentName: '',
      segmentNameError: false,
      saving: false,
    };
  },
  validations: {
    segmentName: {
      required,
    },
  },
  computed: mapState('people', ['conditionFilters']),
  methods: {
    ...mapMutations('people', ['updateConditionFiltersShowErrors']),

    segmentNameShowError(modelName) {
      return this.segmentNameError && this.$v[modelName].$invalid;
    },
    saveSegment() {
      const { segmentName } = this;
      const { conditionFilters } = this;

      this.segmentNameError = false;
      if (!segmentName) {
        this.segmentNameError = true;
        return;
      }

      if (!validateConditionFilters(conditionFilters)) {
        // show validation error styles in all inputs/selects
        this.updateConditionFiltersShowErrors({ show: true });

        this.$toast.error(
          'Failed to save segment',
          'Please check your conditions.'
        );
        return;
      }

      this.saving = true;
      this.$axios
        .post('/segments', {
          name: segmentName,
          conditionFilters,
        })
        .then(() => {
          this.segmentName = '';
          this.$toast.success('Success', 'Your new segment has been saved.');
          // $(`#${this.modalId}`).modal('hide');
          Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
        })
        .catch((error) => {
          if (error.response.status !== 422) {
            this.$toast.error(
              'Failed',
              'Unexpected error occurs. Segment could not be saved'
            );
          }

          validationFailedNotification(error);
        })
        .finally(() => {
          this.saving = false;
        });
    },
  },
};
</script>

<style scoped lang="scss"></style>

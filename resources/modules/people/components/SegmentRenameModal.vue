<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Edit Segment"
  >
    <BaseFormGroup label="Segment Name">
      <BaseFormInput
        id="rename-segment-input"
        v-model="segmentName"
        type="text"
        @input="showErr = false"
      />
      <template
        v-if="showErr && v$.segmentName.required.$invalid"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton @click="renameSegment">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

export default {
  props: {
    modalId: String,
    segmentId: [String, Number],
  },
  setup() {
    return { v$: useVuelidate() };
  },
  validations: {
    segmentName: {
      required,
    },
  },
  data() {
    return {
      segmentName: '',
      showErr: false,
    };
  },
  mounted() {
    eventBus.$on('get-segment-name', (segmentName) => {
      this.segmentName = segmentName;
      const modal = document.getElementById(this.modalId);
      modal?.addEventListener('hidden.bs.modal', () => {
        this.segmentName = segmentName;
        this.showErr = false;
      });
    });
  },
  methods: {
    renameSegment() {
      this.showErr = false;
      if (this.v$.$invalid) {
        this.showErr = true;
        return;
      }
      this.$emit('rename-segment', this.segmentName);
    },
  },
};
</script>

<style scoped lang="scss"></style>

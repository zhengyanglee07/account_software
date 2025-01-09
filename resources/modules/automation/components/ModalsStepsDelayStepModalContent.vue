<template>
  <div class="modal-body row py-10 px-lg-17">
    <div class="d-flex align-items-end">
      <BaseFormGroup
        label="Wait for..."
        col="lg-6"
        class="px-1"
      >
        <BaseFormInput
          v-model.number="delay.duration"
          type="number"
          min="1"
          @input="clearValidationErrors"
        />
        <span
          v-if="showValidationErrors && v$.delay.duration.required.$invalid"
          class="text-danger"
        >
          Field is required
        </span>
        <span
          v-else-if="
            showValidationErrors && v$.delay.duration.minValue.$invalid
          "
          class="text-danger"
        >
          Duration must be at least {{ delay.unit === 'minute' ? 5 : 1 }}
        </span>
      </BaseFormGroup>
      <BaseFormGroup col="lg-6">
        <BaseFormSelect
          v-model="delay.unit"
          :options="[
            { name: 'minute(s)', value: 'minute' },
            { name: 'hour(s)', value: 'hour' },
            { name: 'day(s)', value: 'day' },
          ]"
          label-key="name"
          value-key="value"
          @update:modelValue="clearValidationErrors"
        />
      </BaseFormGroup>
    </div>
  </div>

  <button
    v-show="false"
    id="automation-add-step-delay-modal-close-button"
    type="button"
    data-bs-dismiss="modal"
    aria-label="Close"
  />
  <div class="modal-footer flex-center">
    <BaseButton
      v-if="isAddStepModal"
      type="light"
      @click="$emit('back', $event)"
    >
      Back
    </BaseButton>
    <BaseButton @click="saveDelayStep">
      Save
    </BaseButton>
  </div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import modalSaveBtnMixin from '@automation/mixins/modalSaveBtnMixin.js';
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { Modal } from 'bootstrap';

export default {
  name: 'DelayStepModalContent',
  mixins: [modalSaveBtnMixin],
  props: {
    value: {
      type: Object,
      default: () => ({}),
    },
    modelValue: {
      type: Object,
      default: () => ({}),
    },
    isAddStepModal: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
  data() {
    return {
      showValidationErrors: false,
    };
  },
  validations: {
    delay: {
      duration: {
        required,
        minValue (val) {
          if (this.delay.unit === 'minute') {
            return val >= 5;
          }
          return val > 0;
        },
      },
    },
  },
  computed: {
    ...mapState('automations', ['modal']),
    delay: {
      get() {
        if (Object.keys(this.modelValue).length !== 0) return this.modelValue;
        return this.value;
      },
      set(value) {
        this.$emit('input', value);
      },
    },
  },
  methods: {
    ...mapActions('automations', ['insertOrUpdateStep']),
    ...mapMutations('automations', ['updateStep', 'insertStep']),

    clearValidationErrors() {
      this.showValidationErrors = false;
    },

    generateDelayDesc() {
      const { duration } = this.delay;
      const unit = (this.delay.unit || '') + (duration > 1 ? 's' : '');

      return `Wait for ${duration} ${unit}`;
    },

    async saveDelayStep() {
      this.showValidationErrors = this.v$.$invalid;
      if (this.showValidationErrors) {
        return;
      }

      const {
        data: { id, index, parent, config },
      } = this.modal;

      this.saving = true;

      await this.insertOrUpdateStep({
        id,
        index,
        data: {
          type: 'delay',
          kind: 'delay',
          desc: this.generateDelayDesc(),
          properties: this.delay,
        },
        parent,
        config,
      });

      this.saving = false;
      this.$emit('close-modal');
      // new Modal(document.getElementById('automation-add-step-modal')).hide();
      Modal.getInstance(
        document.getElementById('automation-add-step-modal')
      ).hide();
    },
  },
};
</script>

<style scoped lang="scss"></style>

<template>
  <BaseModal
    :modal-id="modalId"
    :title="
      Object.keys(prize).length ? 'Edit Grand Prize' : 'Setup up Grand Prize'
    "
  >
    <BaseFormGroup
      label="Title"
      :error-message="
        err === true && v$.prizeTitle.required.$invalid
          ? 'Title is required'
          : ''
      "
    >
      <BaseFormInput
        id="prize-title"
        v-model="prizeTitle"
        type="text"
        placeholder="e.g. Win a Tesla Model S"
      />
    </BaseFormGroup>
    <BaseFormGroup label="Number of Winners">
      <BaseFormInput
        id="prize-winners"
        v-model="noOfWinner"
        type="number"
        :min="1"
      />
    </BaseFormGroup>
    <div
      class="d-flex align-items-center bg-light-info rounded p-5"
      style="text-align: justify"
    >
      <span class="text-muted fw-bold">
        When the campaign ends, winners will be selected randomly among the
        participants. Each point the participant earns will be converted into a
        chance to win the prize. Once winners are selected, you can either
        confirm the winners or re-trigger winner selection.
      </span>
    </div>
    <template #footer>
      <BaseButton
        id="add-prize"
        @click="addPrize"
      >
        {{ Object.keys(prize).length ? 'Update' : 'Add' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { nanoid } from 'nanoid';
import { Modal } from 'bootstrap';
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

export default {
  props: {
    modalId: {
      type: String,
      default: '',
    },
    prize: {
      type: Object,
      default: () => {},
    },
  },

  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      prizeTitle: '',
      noOfWinner: 1,
      err: false,
    };
  },
  validations() {
    return {
      prizeTitle: {
        required,
      },
      noOfWinner: {
        required,
      },
    };
  },
  watch: {
    prize: {
      deep: true,
      handler(newValue) {
        if (newValue) {
          this.prizeTitle = newValue?.prizeTitle ?? '';
          this.noOfWinner = newValue?.noOfWinner ?? 1;
        }
      },
    },
  },
  methods: {
    addPrize() {
      if (this.noOfWinner <= 0) {
        this.$toast.warning(
          'Warning',
          'Number of winners should be greater than 0'
        );
        return;
      }
      this.err = this.v$.$invalid;
      if (!this.err) {
        this.$emit('edit-prize', {
          id: this.prize?.id ?? nanoid(),
          prizeTitle: this.prizeTitle,
          noOfWinner: this.noOfWinner,
        });

        // hide the modal
        Modal.getInstance(document.getElementById(`${this.modalId}`)).hide();
      }
    },
  },
};
</script>

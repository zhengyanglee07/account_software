<template>
  <BaseModal
    :modal-id="modalId"
    :scrollable="false"
    :small="true"
    :title="`Delete ${title}`"
    no-dismiss
  >
    <template v-if="deletionError">
      <p class="text-danger">
        Oops! Something Went Wrong.
      </p>
      <p class="text-danger">
        You have to enter "DELETE".
      </p>
    </template>
    <template v-else>
      <p class="fw-bold">
        Are you sure?
      </p>
      <p class="fw-bold">
        You won't be able to revert this.
      </p>
      <slot name="extra-content" />
    </template>
    <input
      v-model="deleteText"
      class="m-container__input form-control firstInput"
      type="text"
      :placeholder="`Enter 'DELETE'`"
      @keyup.enter="eventOnSave"
    >

    <template #footer>
      <BaseButton
        type="secondary"
        data-bs-dismiss="modal"
        @click="eventOnCancel"
      >
        Dismiss
      </BaseButton>
      <BaseButton @click="eventOnSave">
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import BaseModal from '@shared/components/BaseModal.vue';

const bootstrap = typeof window !== `undefined` && import('bootstrap');

let modalInstance;

export default {
  name: 'DeleteConfirmationModal',

  components: {
    BaseModal,
  },

  props: {
    title: {
      type: String,
      required: true,
    },
    modalId: {
      type: String,
      default: 'delete-modal',
    },
    manualHide: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      deleteText: null,
      deletionError: false,
    };
  },

  mounted() {
    const modalElem = document.querySelector(`#${this.modalId}`);
    modalElem.addEventListener('show.bs.modal', () => {
      this.resetData();
      bootstrap?.then(({ Modal }) => {
        modalInstance = new Modal(modalElem);
      });
    });
  },

  methods: {
    eventOnCancel() {
      this.$emit('cancel');
      this.resetData();
    },

    eventOnSave() {
      if (this.deleteText === 'DELETE') {
        this.$emit('save');
        if (!this.manualHide) {
          modalInstance.hide();
        }
        this.resetData();
      } else {
        this.deletionError = true;
      }
    },

    resetData() {
      Object.assign(this.$data, this.$options.data.call(this));
    },

    focusInput() {
      // this.$refs[`${this.modalId}firstInput`].focus()
      // console.log(this.$refs[`${this.modalId}firstInput`])
      $(`#${this.modalId}firstInput`).focus();
    },
  },
};
</script>

<style scoped lang="scss">
.m-container__text--error,
.m-container__text,
.m-container__text--bold,
.m-container__text {
  text-align: left;
}
</style>

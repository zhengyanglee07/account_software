<template>
  <div
    :id="id"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    :aria-labelledby="`${id}Label`"
    aria-hidden="true"
    data-bs-backdrop="static"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="m-container__header">
          <h5
            :id="`${id}Label`"
            class="m-container__header-title h-three mb-0 text-capitalize"
          >
            {{ title }}
          </h5>
          <button
            type="button"
            class="close-button"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="close"
          >
            <span
              class=""
              aria-hidden="true"
            >&times;</span>
          </button>
        </div>
        <div
          class="modal-body"
          :class="modal_size"
        >
          <slot name="body" />
        </div>
        <div class="m-container__footer">
          <button
            :id="`${id}-close-button`"
            type="button"
            class="cancel-button p-two"
            data-bs-dismiss="modal"
            :hidden="secondary_hidden"
            @click="cancel"
          >
            Cancel
          </button>
          <button
            type="button"
            :class="
              primaryBtnClass !== undefined
                ? primaryBtnClass
                : 'primary-small-square-button'
            "
            :disabled="disabledStatus"
            :hidden="primary_hidden"
            @click="save"
          >
            {{ button_title }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: [
    'id',
    'title',
    'disabled-status',
    'modal_size',
    'button_title',
    'primary_hidden',
    'secondary_hidden',
    'primaryBtnClass',
  ],

  emits: ['close-button', 'cancel-button', 'save-button'],

  methods: {
    close() {
      this.$emit('close-button');
    },

    cancel() {
      this.$emit('cancel-button');
    },

    save() {
      this.$emit('save-button');
    },
  },
};
</script>

<style lang="scss" scoped>
</style>

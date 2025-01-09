<template>
  <BaseModal
    :modal-id="modalId"
    title="Edit Affiliate Group"
  >
    <BaseFormGroup label="Name">
      <BaseFormInput
        v-model="title"
        type="text"
        @input="clearError"
      />
      <template
        v-if="showValidationErrors && v$.title.required"
        #error-message
      >
        Field is required
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="saving"
        @click="save"
      >
        <div v-if="saving">
          <i class="fas fa-circle-notch fa-spin pe-0" />
        </div>
        <span v-else>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script>
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { validationFailedNotification } from '@shared/lib/validations.js';

export default {
  name: 'UpdateAffiliateMemberGroupModal',
  props: {
    modalId: {
      type: String,
      required: true,
    },
    group: {
      type: Object,
      default: () => ({}),
    },
  },
  setup() {
    return { v$: useVuelidate() };
  },
  data() {
    return {
      title: '',
      saving: false,
      showValidationErrors: false,
    };
  },
  validations: {
    title: {
      required,
    },
  },
  watch: {
    group: {
      handler() {
        this.title = this.group?.title;
      },
      immediate: true,
    },
  },
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);

      modalEl.addEventListener('hidden.bs.modal', () => {
        this.title = this.group?.title;
        this.showValidationErrors = false;
      });
    }, 1000);
  },
  methods: {
    clearError() {
      this.showValidationErrors = false;
    },

    async save() {
      this.showValidationErrors = this.v$.$invalid;

      if (this.showValidationErrors) {
        return;
      }

      this.saving = true;

      const { id } = this.group;
      const { title } = this;

      try {
        await this.$axios.put(`/affiliate/members/groups/${id}`, {
          title,
        });

        this.$toast.success('Success', 'Successfully updated group');
        // this.$emit('update', {
        //   id,
        //   title,
        // });
        this.$emit('hide');
        this.$inertia.visit('/affiliate/members/groups');
      } catch (err) {
        validationFailedNotification(err);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

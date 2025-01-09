<template>
  <BaseModal
    :modal-id="modalId"
    :small="true"
    title="Add Affiliate Group"
  >
    <BaseFormGroup label="Enter a name for affiliate group">
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
import { validationFailedNotification } from '@shared/lib/validations.js';
import useVuelidate from '@vuelidate/core';

export default {
  name: 'CreateAffiliateMemberGroupModal',
  props: {
    modalId: {
      type: String,
      required: true,
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
  mounted() {
    setTimeout(() => {
      const modalEl = document.getElementById(this.modalId);

      modalEl.addEventListener('show.bs.modal', () => {
        this.title = '';
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

      const { title } = this;
      try {
        await this.$axios.post('/affiliate/members/groups', {
          title,
        });

        this.$toast.success('Success', 'Successfully created new group');
        this.title = '';
        // this.$emit('create', {
        //   title,
        // });
        this.$emit('hide');
        this.$inertia.visit('/affiliate/members/groups');
      } catch (err) {
        console.log(err);
        this.$toast.error('Error', 'Something went wrong');
        // validationFailedNotification(err);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

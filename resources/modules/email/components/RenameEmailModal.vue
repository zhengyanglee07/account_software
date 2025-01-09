<template>
  <BaseModal
    title="Rename Email"
    modal-id="rename-email-modal"
  >
    <BaseFormGroup
      label="Enter a new name"
      :error-message="errorMessage"
    >
      <BaseFormInput
        id="new-email-name"
        v-model="emailName"
        type="text"
        required
      />
    </BaseFormGroup>
    <template #footer>
      <BaseButton
        :disabled="isUpdating"
        @click="renameEmail"
      >
        <span>Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch, inject } from 'vue';
import axios from 'axios';

const $toast = inject('$toast');

const props = defineProps({
  data: { type: Object, default: () => {} },
});

const emits = defineEmits(['updated']);

const emailName = ref(null);
watch(
  () => props.data.emailName,
  (name) => {
    emailName.value = name;
  }
);

const isUpdating = ref(false);
const errorMessage = ref(null);
const renameEmail = () => {
  errorMessage.value = null;
  isUpdating.value = true;
  axios
    .post(`/emails/${props.data.referenceKey}/rename`, {
      name: emailName.value,
    })
    .then(() => {
      props.data.modal.hide();
      emits('updated', {
        emailRef: props.data.referenceKey,
        newName: emailName.value,
      });
      $toast.success('Success', 'Successfully renamed email');
    })
    .catch((error) => {
      if (error?.response.status === 422) {
        errorMessage.value = error?.response?.data?.message;
        return;
      }
      $toast.error('Error', 'Failed to rename email');
    })
    .finally(() => {
      isUpdating.value = false;
    });
};
</script>

<style></style>

<template>
  <BaseModal
    title="New order notification email"
    :modal-id="modalId"
  >
    <BaseFormGroup
      label="Email address to receive notification email"
      description=" All the upcoming notification emails will be sent to this email address"
    >
      <BaseFormInput
        v-model="form.email"
        type="email"
        required
        :error-message="errors.email"
        placeholder="You may enter multiple email addresses separated by ,"
        @input="updateEmail"
      />
      <BaseBadge
        v-for="(email, index) in emailList"
        :key="index"
        has-delete-button
        :text="email"
        @delete="deleteEmail(index)"
      />
    </BaseFormGroup>

    <template #footer>
      <BaseButton
        :disabled="isLoading"
        @click="saveOrderNotificationEmail"
      >
        Save
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, inject, watch, onMounted } from 'vue';
import eventBus from '@services/eventBus.js';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  notificationEmail: { type: String, default: null },
  save: { type: Boolean, default: false },
});

const emits = defineEmits(['email']);

const modalId = 'order-notification-email-modal';

const $toast = inject('$toast');
const form = useForm({
  email: '',
  emailList: [],
});

const errors = computed(() => usePage().props.errors);

const emailList = ref([]);

const closeModal = () => {
  emits('email', emailList.value.join(', '));
  eventBus.$emit(`hide-modal-${modalId}`);
};

const isLoading = ref(false);

const saveOrderNotificationEmail = () => {
  isLoading.value = true;
  form.emailList = emailList.value;
  form.post('/settings/notification/email/save', {
    onSuccess: () => {
      closeModal();
      $toast.success('Success', 'Order notification email updated');
    },
    onError: () => {
      $toast.error(
        'Error',
        errors.value.message || 'Failed to update order notification email'
      );
    },
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

watch(
  () => props.save,
  () => {
    if (props.save) saveOrderNotificationEmail();
  }
);

const updateEmail = () => {
  if (!form.email.includes(',')) return;
  const list = form.email.split(',');
  emailList.value.push(list[0]);
  form.email = null;
};

const deleteEmail = (index) => {
  emailList.value.splice(index, 1);
};

const initializeEmail = (emails) => {
  form.email = null;
  emailList.value = emails.split(',');
};

watch(
  () => props.notificationEmail,
  (email) => {
    initializeEmail(email);
  }
);

onMounted(() => {
  initializeEmail(props.notificationEmail);
});
</script>

<style></style>

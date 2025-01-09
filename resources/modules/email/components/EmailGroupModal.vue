<template>
  <BaseModal
    :title="modalTitle"
    modal-id="email-group-modal"
  >
    <BaseFormGroup
      v-if="isCreateNewGroup && action !== 'delete'"
      label="New group name"
      required
    >
      <BaseFormInput
        v-model="groupName"
        type="text"
        required
        max="191"
      />
      <template #label-row-end>
        <BaseButton
          type="link"
          @click="changeAction(false)"
        >
          Choose existing group
        </BaseButton>
      </template>
    </BaseFormGroup>

    <BaseFormGroup
      v-else
      label="Choose an email group"
      required
    >
      <BaseFormSelect
        v-model="selectedGroup"
        :options="emailGroups"
      >
        <option
          value=""
          disabled
        >
          Select email group
        </option>
      </BaseFormSelect>

      <template
        v-if="action === 'add'"
        #label-row-end
      >
        <BaseButton
          type="link"
          @click="changeAction(true)"
        >
          Create new group
        </BaseButton>
      </template>
    </BaseFormGroup>

    <template #footer>
      <BaseButton>
        <i
          v-if="loading"
          class="fas fa-spinner fa-pulse"
        />
        <span
          v-else
          @click="submit"
        >Save</span>
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, computed, inject, watch, onBeforeMount, onMounted } from 'vue';
import axios from 'axios';
import useEmail from '@email/hooks/useEmail.js';

const $toast = inject('$toast');

const props = defineProps({
  modal: { type: Object, default: () => {} },
  action: { type: String, default: 'add' },
});

const {
  emailGroups,
  selectedEmailIds,
  setEmailGroups,
  setEmails,
  resetSelectedEmailIds,
} = useEmail();

const isCreateNewGroup = ref(false);

const modalTitle = computed(
  () => `${props.action === 'add' ? 'Add' : 'Remove'} Group`
);

const changeAction = (isNew) => {
  setTimeout(() => {
    isCreateNewGroup.value = isNew;
  }, 100);
};

const selectedGroup = ref(null);

const initializeEmailGroup = () => {
  selectedGroup.value = emailGroups.value[0] ?? null;
};

const groupName = ref('');
const loading = ref(false);
const createGroup = () => {
  if (!groupName.value) {
    $toast.error('Error', 'Please fill in group name');
    return;
  }
  setTimeout(() => {
    loading.value = true;
  }, 100);
  axios
    .put(`/emails/group/${groupName.value}`)
    .then(({ data }) => {
      setEmailGroups(data.emailGroups);
      initializeEmailGroup();
      changeAction(false);
      $toast.success('Success', 'Successfully created group');
    })
    .catch(({ response }) => {
      $toast.error(
        'Error',
        response?.data?.message ?? 'Unexpected Error Occured'
      );
    })
    .finally(() => {
      loading.value = false;
    });
};

onMounted(() => {
  initializeEmailGroup();
});

watch(
  () => emailGroups,
  () => {
    initializeEmailGroup();
  }
);

const assignGroup = () => {
  if (!selectedGroup.value) {
    $toast.error('Error', 'Please select email group');
    return;
  }
  isCreateNewGroup.value = false;
  loading.value = true;
  axios
    .post('/emails/group/assign', {
      name: selectedGroup.value,
      emailIds: selectedEmailIds.value,
      action: props.action,
    })
    .then(({ data }) => {
      setEmails(data.emails);
      resetSelectedEmailIds();
      $toast.success('Success', 'Successfully assign group');
      props.modal.hide();
    })
    .catch(({ response }) => {
      $toast.error(
        'Error',
        response?.data?.message ?? 'Unexpected Error Occured'
      );
    })
    .finally(() => {
      loading.value = false;
    });
};

const deleteGroup = () => {
  loading.value = true;
  axios
    .delete(`/emails/group/${selectedGroup.value}`)
    .then(({ data }) => {
      setEmails(data.emails);
      setEmailGroups(data.emailGroups);
      resetSelectedEmailIds();
      $toast.success('Success', 'Group deleted successfully');
      props.modal.hide();
    })
    .catch(({ response }) => {
      $toast.error(
        'Error',
        response?.data?.message ?? 'Unexpected Error Occured'
      );
    })
    .finally(() => {
      loading.value = false;
    });
};

const submit = () => {
  if (isCreateNewGroup.value) createGroup();
  else assignGroup();
};
</script>

<style></style>

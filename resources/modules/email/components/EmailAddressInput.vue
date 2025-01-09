<template>
  <BaseFormGroup
    label="Email Address"
    label-for="senderEmail"
    required
    :col="col"
  >
    <template #label-row-end>
      <BaseButton
        v-if="refreshState === 'initial'"
        type="link"
        @click="getSenders"
      >
        Refresh
        <i
          class="ms-2 fa-solid fa-rotate-right"
          style="color: inherit"
        />
      </BaseButton>
      <BaseButton
        v-if="refreshState === 'fetching'"
        type="link"
      >
        Fetching
        <i
          class="ms-2 fa-solid fa-spinner fa-spin-pulse"
          style="color: inherit"
        />
      </BaseButton>
      <BaseButton
        v-if="refreshState === 'updated'"
        type="link"
      >
        Updated
        <i class="ms-2 fa-solid fa-circle-check text-success" />
      </BaseButton>
    </template>
    <BaseFormSelect
      v-model="value"
      :options="senderArray?.map((e) => e.email_address)"
      @change="emits('change')"
    >
      <option
        value=""
        selected
        disabled
      >
        Choose email address
      </option>
    </BaseFormSelect>
    <template
      v-if="sender.domainErr"
      #error-message
    >
      Domain is required
    </template>
    <BaseButton
      type="link"
      @click="showModal"
    >
      Add new sender email
    </BaseButton>
  </BaseFormGroup>

  <AddSenderEmailModal :senders="senderArray" />
</template>

<script setup>
import { ref, inject, computed, onBeforeMount, watch } from 'vue';
import axios from 'axios';
import AddSenderEmailModal from '@email/components/AddSenderEmailModal.vue';
import eventBus from '@services/eventBus';

const props = defineProps({
  modelValue: { type: String, default: '' },
  sender: { type: Object, default: () => {} },
  isFetchByDefault: { type: Boolean, default: false },
  senders: { type: Array, default: null },
  col: { type: Number, default: 12 },
});
const emits = defineEmits(['update:modelValue', 'change', 'updateSenders']);

const $toast = inject('$toast');
const refreshState = ref('initial');
const senderArray = ref(null);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emits('update:modelValue', val);
  },
});

const getSenders = () => {
  refreshState.value = 'fetching';
  axios
    .get('/senders/verified')
    .then(({ data: { senders } }) => {
      emits('updateSenders', senders);
      senderArray.value = senders;
      refreshState.value = 'updated';
      setTimeout(() => {
        refreshState.value = 'initial';
      }, 2000);
    })
    .catch((err) => {
      console.error(err);
      refreshState.value = 'initial';
      $toast.error('Error', 'Failed to load senders');
    });
};

onBeforeMount(() => {
  if (props.isFetchByDefault) getSenders();
});

watch(
  () => props.senders,
  (val) => {
    senderArray.value = val;
  }
);

const showModal = () => {
  eventBus.$emit('show-add-sender-email-modal');
};
</script>

<style></style>
